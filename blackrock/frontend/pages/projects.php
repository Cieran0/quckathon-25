<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
    <header class="flex items-center justify-between p-4 bg-white header-bottom-border">
        <img src="logo.png" alt="Logo" class="h-10">
        <nav class="flex items-center">
            <a href="#projects" class="mr-4 text-custom-green hover:text-black">Projects</a>
            <a href="#analytics" class="mr-4 text-custom-green hover:text-black">Analytics</a>
            <button class="px-4 py-2 rounded text-white bg-green-600 hover:bg-green-400">Login</button>
        </nav>
    </header>

    <main class="flex flex-grow">
        <aside class="w-64 p-4 custom-green text-white">
            <h2 class="text-lg mb-2">Followed Projects</h2>
            <ul>
                <li class="mb-2"><a href="#" class="text-white hover:text-gray-300">Project 1</a></li>
                <li><a href="#" class="text-white hover:text-gray-300">Project 2</a></li>
            </ul>
        </aside>

        <section class="flex-grow p-4">
            <div class="flex items-center mb-4">
                <input type="text" placeholder="Search Projects..." class="w-full px-4 py-2 rounded-l border border-r-0 bg-custom-green placeholder-gray-300">
                <button class="px-4 py-2 bg-green-600 rounded-r hover:bg-green-400 text-white">Search</button>
            </div>
            <div class="grid grid-cols-4 gap-4">
                <div class="project-card">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <h3>Project name</h3>
                    <p>Description text</p>
                </div>
                <!-- Repeat the above project card for other projects -->
                <div class="new-project-card">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="white" width="24" height="24" viewBox="0 0 24 24"><path d="M24 10h-10v-10h-4v10h-10v4h10v10h4v-10h10z"/></svg>
                    <h3>Add Project</h3>
                    <p>Create a new project</p>
                </div>
            </div>
        </section>

        <aside class="w-64 p-4 custom-green text-white">
            <h2 class="text-lg mb-2">Filters</h2>

            <!-- Followed Projects Toggle -->
            <div class="mb-4">
                <label class="inline-block mb-2 text-white">Show Only Followed</label>
                <div class="flex items-center">
                    <input type="checkbox" id="show-followed" class="mr-2 text-green-600 focus:ring-green-500">
                    <label for="show-followed" class="text-white">On</label>
                </div>
            </div>

            <!-- Volunteer Count Filter -->
            <div class="mb-4">
                <label class="block mb-2 text-white">Volunteer Count</label>
                <div class="flex flex-col">
                    <button class="px-3 py-1 mb-1 rounded bg-white text-custom-green hover:bg-gray-100">Most Volunteers</button>
                    <button class="px-3 py-1 mb-1 rounded bg-white text-custom-green hover:bg-gray-100">Fewest Volunteers</button>
                </div>
            </div>

            <!-- Activity Level -->
            <div class="mb-4">
                <label class="block mb-2 text-white">Activity Level</label>
                <div class="flex flex-col">
                    <button class="px-3 py-1 mb-1 rounded bg-white text-custom-green hover:bg-gray-100">Recently Updated</button>
                    <button class="px-3 py-1 mb-1 rounded bg-white text-custom-green hover:bg-gray-100">Least Active</button>
                </div>
            </div>

            <!-- Project Type (if applicable) -->
            <div class="mb-4">
                <label class="block mb-2 text-white">Category</label>
                <select class="w-full px-3 py-2 rounded border border-gray-300 bg-white text-black">
                    <option value="all">All</option>
                    <option value="community">Community</option>
                    <option value="tech">Tech</option>
                    <option value="education">Education</option>
                </select>
            </div>

            <!-- Apply Button -->
            <button class="w-full px-4 py-2 mt-4 rounded bg-green-600 text-white hover:bg-green-400">
                Apply Filters
            </button>
        </aside>
    </main>

    <footer class="dark-gray text-white py-4 text-center">
        <p class="text-sm">&copy; 2024 The Green Team. All rights reserved.</p>
    </footer>
</body>
</html>