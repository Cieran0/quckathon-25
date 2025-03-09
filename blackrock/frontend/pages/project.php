<?php
session_start();

// Redirect if session_token is not set
if (!isset($_SESSION['session_token'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch ALL folder and file data from the API
include 'config.php';
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://' . BACKEND_IP . ':' . BACKEND_PORT . '/project', // target url
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'session_token' => $_SESSION['session_token'],
        'id' => $id
    ]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true
]);
$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);
$rootData = $responseData['folders'][0] ?? [];
$followed_projects = $responseData['followers'];


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folders and Files</title>
    <link href="tailwind.min.css" rel="stylesheet">
    <link href="project-style.css" rel="stylesheet">
    <script src="project.js"></script>
</head>
<body class="bg-white flex flex-col h-screen">
    <?php include 'header.php'; ?>

    <main class="flex flex-grow">

    <aside class="w-64 p-4 custom-green text-white">
        <h1>Project Directory: </h1>
        <a href="project.php?id=<?php echo htmlspecialchars($responseData['id']); ?>">
            <h2 class="text-xl font-semibold mb-6"><?php echo htmlspecialchars($responseData['name']); ?></h2>
        </a>
        <ul id="folder-list"></ul>
    </aside>

        <section class="flex-grow p-4">
            <!-- Dynamic folder view container -->
            <div id="folder-view"></div>
            <!-- Upload Area -->
            <div class="mt-8">
                <h3 class="text-lg font-bold mb-4">Upload File</h3>
                <div id="upload-area" class="upload-area">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <p>Drag and drop a file here</p>
                </div>
            </div>
        </section>

        <aside class="w-64 p-4 custom-green text-white">
            <h1 class="mb-6 text-xl font-normal">Project Followers:</h1>
            <ul class="list-none p-0 m-0">
                <?php foreach ($followed_projects as $follower): ?>
                    <li class="mb-1 text-white text-xl  border-t-2 border-white">
                        <a href="profile.php?profile=<?php echo htmlspecialchars($follower); ?>" class="hover:text-gray-300 no-underline">
                            <?php echo htmlspecialchars($follower); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <button id="follow-button" class="mt-6 w-full py-2 bg-white text-black font-semibold rounded-lg hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
    <?= in_array($_SESSION['username'], $followed_projects) ? 'Unfollow Project' : 'Follow Project' ?>
</button>

        </aside>
    </main>
    <?php include 'footer.php'; ?>

    <!-- Modal -->
    <div id="file-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 id="file-name" class="text-xl font-bold mb-4">Loading...</h2>
            <div id="file-details">
                <div class="loading-spinner"></div>
            </div>
        </div>
    </div>

    <!-- Include JavaScript -->
    <script>
        const folderData = <?= json_encode($rootData) ?>;
        const sessionToken = "<?php echo $_SESSION['session_token']; ?>";
        const projectId = <?= json_encode($id) ?>;
    </script>
    <script src="project.js"></script>
</body>
</html>