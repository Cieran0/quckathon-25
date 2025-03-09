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
    <title>Analytics</title>
    <!-- Flowbite CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.css" rel="stylesheet" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Custom Styles */
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
    </style>
</head>
<body class="bg-white flex flex-col h-screen">
    <?php include 'header.php'; ?>

    <main class="flex flex-grow">
        <!-- Left Aside - Project Search & Selection -->
        <aside class="w-64 p-4 custom-green text-white">
            <div class="mb-4">
                <label for="projectSearch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Search Projects</label>
                <input type="text" id="projectSearch" placeholder="Search projects..." 
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            </div>
            
            <h2 class="text-lg font-bold mb-2">Available Projects</h2>
            <ul id="projectList" class="max-h-96 overflow-y-auto space-y-2">
                <?php foreach ($projects as $project): ?>
                    <li class="flex items-center project-item" data-name="<?= htmlspecialchars(strtolower($project['name'])) ?>">
                        <input type="checkbox" value="<?= $project['id'] ?>" 
                               class="mr-2 project-checkbox" id="project<?= $project['id'] ?>">
                        <label for="project<?= $project['id'] ?>" 
                               class="text-white hover:text-gray-300"><?= htmlspecialchars($project['name']) ?></label>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button id="clearSelection" class="mt-4 w-full text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                Clear Selection
            </button>
        </aside>

        <!-- Main Content - Analytics Charts -->
        <section class="flex-grow p-4">
            <div class="flex items-center justify-between mb-4">
                <button id="analyzeButton" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                    Analyze Selected
                </button>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="p-3 bg-white rounded shadow">
                    <canvas id="chart1"></canvas>
                </div>
                <div class="p-3 bg-white rounded shadow">
                    <canvas id="chart2"></canvas>
                </div>
                <div class="p-3 bg-white rounded shadow">
                    <canvas id="chart3"></canvas>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <!-- Flowbite JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
<script>
    // Pass session token from PHP to JavaScript
    let sessionToken = "<?php echo $_SESSION['session_token'] ?? ''; ?>";

    // Search functionality
    document.getElementById('projectSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('#projectList .project-item').forEach(item => {
            const projectName = item.getAttribute('data-name');
            item.style.display = projectName.includes(searchTerm) ? 'flex' : 'none';
        });
    });

    // Clear selection
    document.getElementById('clearSelection').addEventListener('click', () => {
        document.querySelectorAll('.project-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
    });

    // Analyze button handler
    document.getElementById('analyzeButton').addEventListener('click', function() {
        const selectedProjects = Array.from(
            document.querySelectorAll('.project-checkbox:checked'),
            checkbox => Number(checkbox.value)
        );

        if (selectedProjects.length === 0) {
            alert('Please select at least one project.');
            return;
        }

        fetch('http://192.168.0.7:8040/analytics', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                session_token: sessionToken,
                selected_projects: selectedProjects
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Server Response:", data); // Debugging log
            updateCharts(data.analytics);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to fetch analytics data. Please try again.');
        });
    });

    // Update charts with server data
    function updateCharts(analytics) {
        // Sort analytics by volunteers (descending)
        const sortedByVolunteers = [...analytics].sort((a, b) => b.volunteers - a.volunteers);
        const sortedByFunding = [...analytics].sort((a, b) => b.total_funding - a.total_funding);
        const sortedByFollowers = [...analytics].sort((a, b) => b.followers - a.followers);

        // Extract data for each chart
        const projectNamesVolunteers = sortedByVolunteers.map(p => p.project_name);
        const volunteers = sortedByVolunteers.map(p => p.volunteers);

        const projectNamesFunding = sortedByFunding.map(p => p.project_name);
        const funding = sortedByFunding.map(p => p.total_funding);

        const projectNamesFollowers = sortedByFollowers.map(p => p.project_name);
        const followers = sortedByFollowers.map(p => p.followers);

        // Update charts with sorted data
        updateChart('chart1', 'Volunteers', projectNamesVolunteers, volunteers, 'rgba(255, 99, 132, 0.5)', 'rgba(255, 99, 132, 1)');
        updateChart('chart2', 'Total Funding', projectNamesFunding, funding, 'rgba(54, 162, 235, 0.5)', 'rgba(54, 162, 235, 1)');
        updateChart('chart3', 'Followers', projectNamesFollowers, followers, 'rgba(75, 192, 192, 0.5)', 'rgba(75, 192, 192, 1)');
    }

    // Helper function to create or update Chart.js charts
    function updateChart(canvasId, label, labels, data, bgColor, borderColor) {
        const ctx = document.getElementById(canvasId).getContext('2d');

        // Destroy existing chart instance if it exists
        if (window[canvasId] && typeof window[canvasId].destroy === 'function') {
            window[canvasId].destroy();
        }

        // Create a new chart instance
        window[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: bgColor,
                    borderColor: borderColor,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Initialize empty charts on page load
    document.addEventListener('DOMContentLoaded', () => {
        updateChart('chart1', 'Volunteers', [], [], 'rgba(255, 99, 132, 0.5)', 'rgba(255, 99, 132, 1)');
        updateChart('chart2', 'Total Funding', [], [], 'rgba(54, 162, 235, 0.5)', 'rgba(54, 162, 235, 1)');
        updateChart('chart3', 'Followers', [], [], 'rgba(75, 192, 192, 0.5)', 'rgba(75, 192, 192, 1)');
    });
</script>
</body>
</html>