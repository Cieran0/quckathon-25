<?php
session_start();

// Redirect unauthenticated users
if (!isset($_SESSION['session_token'])) {
    header('Location: login.php');
    exit;
}

// Fetch projects from API
$projects = [];
$errorMessage = null;

// Configuration
include 'config.php';
$apiUrl = 'http://' . BACKEND_IP . ':' . BACKEND_PORT . '/projects';
$sessionToken = $_SESSION['session_token'];

// Setup cURL request
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = [
    'session_token' => $sessionToken
];
$jsonData = json_encode($data);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);
// Execute request
$response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    $errorMessage = 'Network error: ' . curl_error($ch);
} else {
    if ($httpCode === 200) {
        $responseData = json_decode($response, true);
        if (isset($responseData['projects'])) {
            $projects = $responseData['projects'];
            $followed_projects = $responseData['followed_projects'];
        } else {
            $errorMessage = "Invalid API response format";
        }
    } else {
        $errorMessage = "API Error ($httpCode): " . (json_decode($response, true)['error'] ?? 'Unknown error');
    }
}

curl_close($ch);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects</title>
    <link href="tailwind.min.css" rel="stylesheet">
    <style>
        /* Existing styles remain unchanged */
        .custom-green {
            background-color: #006844 !important;
        }

        .text-custom-green {
            color: #006844 !important;
        }

        .dark-gray {
            background-color: #333 !important;
        }

        .header-bottom-border {
            border-bottom: 2px solid #333;
        }

        .project-card {
            background-color: #e0e0e0;
            padding: 1rem;
            text-align: center;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        :hover.project-card {
            background-color: #a9a9a9;
        }

        .project-card svg {
            display: block;
            /* Centers the SVG */
            margin: 0 auto 1rem auto;
            /* Centers horizontally and adds bottom margin */
            width: 30px;
            height: 30px;
        }

        .project-card h3 {
            margin-top: 0;
            font-size: 1rem;
        }

        .project-card p {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #666;
        }

        .new-project-card {
            background-color: #006844;
            padding: 1rem;
            text-align: center;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        :hover.new-project-card {
            background-color: #00a169;
        }

        .new-project-card svg {
            display: block;
            /* Centers the SVG */
            margin: 0 auto 1rem auto;
            /* Centers horizontally and adds bottom margin */
            width: 30px;
            height: 30px;
            color: white;
        }

        .new-project-card h3 {
            margin-top: 0;
            font-size: 1rem;
            color: white;
        }

        .new-project-card p {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #c0c0c0;
        }
    </style>
</head>

<body class="bg-white flex flex-col h-screen">
    <?php include 'header.php'; ?>

    <!-- Modal -->
    <div id="project-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 class="text-xl font-bold mb-4">Create New Project</h2>
            <form id="project-form">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="project-name">Project Name</label>
                    <input type="text" id="project-name" name="name" required
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="project-description">Description</label>
                    <textarea id="project-description" name="description" rows="3"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Create</button>
                </div>
            </form>
        </div>
    </div>

    <main class="flex flex-grow">
        <aside class="w-64 p-4 custom-green text-white">
            <h2 class="text-xl mb-6">Followed Projects</h2>
            <ul>
                <?php foreach ($followed_projects as $project): ?>
                    <li class="text-white hover:text-gray-300 cursor-pointer mb-4 pt-2 border-t-2 border-white">
                        <?php echo htmlspecialchars($project['name']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <section class="flex-grow p-4">
            <div class="flex items-center mb-4">
                <input id="searchInput" type="text" placeholder="Search Projects..." class="w-full px-4 py-2 rounded-l border border-r-0 bg-custom-green placeholder-gray-500">
                <button id="searchButton" class="px-4 py-2 bg-green-600 rounded-r hover:bg-green-400 text-white">Search</button>
            </div>

            <div class="grid grid-cols-4 gap-4">
                <?php if ($errorMessage): ?>
                    <div class="project-card p-4 text-red-500">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <a href="/project.php?id=<?= htmlspecialchars($project['id']) ?>" class="block project-wrapper transition-all duration-500 ease-in-out">
                            <div class="project-card opacity-100 transform scale-100 transition-all duration-500 ease-in-out">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <h3><?= htmlspecialchars($project['name']) ?></h3>
                                <p><?= htmlspecialchars($project['description']) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Add Project card remains static -->
                <div class="new-project-card" onclick="openModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="white" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M24 10h-10v-10h-4v10h-10v4h10v10h4v-10h10z" />
                    </svg>
                    <h3>Add Project</h3>
                    <p>Create a new project</p>
                </div>
            </div>
        </section>

        <!-- Filters aside remains the same -->
        <aside class="w-64 p-4 custom-green text-white">
            <!-- Your filters content here -->
        </aside>
    </main>

    <?php include 'footer.php'; ?>

    <script>

        function openModal() {
            document.getElementById('project-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('project-modal').classList.add('hidden');
            document.getElementById('project-form').reset();
        }

        document.getElementById('project-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                session_token: "<?php echo $_SESSION['session_token']; ?>",
                name: document.getElementById('project-name').value,
                desc: document.getElementById('project-description').value
            };

            fetch('http://192.168.0.7:8040/new_project', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                closeModal();
                window.location.href = `/project.php?id=${data.project_id}`;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to create project. Please try again.');
            });
        });
    </script>
</body>

<script>
let debounceTimer;

document.getElementById('searchInput').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let projects = document.querySelectorAll('.project-wrapper');

    // Clear the previous timeout to debounce quickly typing
    clearTimeout(debounceTimer);

    // Set a new timeout to apply filter after a short delay (e.g., 500ms)
    debounceTimer = setTimeout(() => {
        projects.forEach(project => {
            let projectName = project.querySelector('h3')?.textContent.toLowerCase() || "";

            if (filter === "") {
                // Show all projects again and fade them in
                project.style.display = "block"; // Immediately show the project
                setTimeout(() => {
                    project.style.opacity = "1";  // Smooth fade-in after display is applied
                    project.style.transform = "scale(1)";
                }, 10);
            } else if (projectName.includes(filter)) {
                // If the project matches, show it and fade it in
                project.style.display = "block";
                setTimeout(() => {
                    project.style.opacity = "1";
                    project.style.transform = "scale(1)";
                }, 10);
            } else {
                // If the project doesn't match, fade it out first, then hide it
                project.style.opacity = "0";
                project.style.transform = "scale(0.9)";
                setTimeout(() => {
                    project.style.display = "none";  // Hide after fading out
                }, 500);  // Match the fade-out duration
            }
        });
    }, 500);  // 500ms delay after typing stops (debounce time)
});
</script>




</html>