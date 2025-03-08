<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/flowbite@1.5.3/dist/flowbite.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
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
            margin: 0 auto 1rem auto; 
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
            margin: 0 auto 1rem auto; 
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
    <header class="flex items-center justify-between p-4 bg-white header-bottom-border">
        <img src="logo.png" alt="Logo" class="h-10">
        <nav class="flex items-center">
            <a href="#projects" class="mr-4 text-custom-green hover:text-black">Projects</a>
            <a href="#analytics" class="mr-4 text-custom-green hover:text-black">Analytics</a>
            <button class="px-4 py-2 rounded text-white bg-green-600 hover:bg-green-400">Login</button>
        </nav>
    </header>

    <main class="flex flex-grow">
        <!-- Left Aside - Selected Projects -->
        <aside class="w-64 p-4 custom-green text-white">
            <h2 class="text-lg mb-2">Selected Projects</h2>
            <ul>
                <li class="mb-2 flex items-center">
                    <input type="checkbox" id="project1" class="mr-2">
                    <label for="project1" class="text-white hover:text-gray-300">Project 1</label>
                </li>
                <li class="flex items-center">
                    <input type="checkbox" id="project2" class="mr-2">
                    <label for="project2" class="text-white hover:text-gray-300">Project 2</label>
                </li>
                <!-- Add more projects as needed -->
            </ul>
            <button class="mt-4 px-4 py-2 bg-green-600 rounded hover:bg-green-400 text-white">Clear</button>
        </aside>

        <!-- Main Content - Analytics Charts -->
        <section class="flex-grow p-4">
            <div class="flex items-center mb-4">
                <input type="text" placeholder="Search Projects..." 
                    class="w-full px-4 py-2 rounded-l border border-r-0 bg-custom-green placeholder-gray-300">
                <button class="px-4 py-2 bg-green-600 rounded-r hover:bg-green-400 text-white">Search</button>
                <button class="px-4 py-2 ml-2 bg-green-600 rounded hover:bg-green-400 text-white">Analyze</button>
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

    <footer class="dark-gray text-white py-4 text-center">
        <p class="text-sm">&copy; 2024 The Green Team. All rights reserved.</p>
    </footer>

    <script>
        // Sample data for charts
        const labels = ['Metric 1', 'Metric 2', 'Metric 3', 'Metric 4', 'Metric 5'];
        const data1 = [80, 60, 40, 70, 90];
        const data2 = [50, 75, 65, 85, 45];
        const data3 = [30, 90, 60, 80, 50];

        // Chart configurations
        function createChart(canvasId, data) {
            new Chart(document.getElementById(canvasId), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Performance Metrics',
                        data: data,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 206, 86, 0.5)',
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(153, 102, 255, 0.5)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Initialize charts
        createChart('chart1', data1);
        createChart('chart2', data2);
        createChart('chart3', data3);
    </script>
</body>
</html>