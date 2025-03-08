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
            display: block; /* Centers the SVG */
            margin: 0 auto 1rem auto; /* Centers horizontally and adds bottom margin */
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
            display: block; /* Centers the SVG */
            margin: 0 auto 1rem auto; /* Centers horizontally and adds bottom margin */
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
                <input type="text" placeholder="Search Projects..." class="w-full px-4 py-2 rounded-l border border-r-0 bg-custom-green placeholder-gray-300">
                <button class="px-4 py-2 bg-green-600 rounded-r hover:bg-green-400 text-white">Search</button>
            </div>

            <div class="grid grid-cols-4 gap-4">
                <?php if ($errorMessage): ?>
                    <div class="project-card p-4 text-red-500">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <a href="/project.php?id=<?= htmlspecialchars($project['id']) ?>" class="block">
                            <div class="project-card">
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
                <div class="new-project-card">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="white" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M24 10h-10v-10h-4v10h-10v4h10v10h4v-10h10z"/>
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
</body>
</html>