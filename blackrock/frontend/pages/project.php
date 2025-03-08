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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folders and Files</title>
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
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            max-width: 600px;
        }
        .close-modal {
            float: right;
            cursor: pointer;
            font-size: 1.5rem;
        }
        /* Loading Spinner */
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* Drag-and-Drop Upload Area Styles */
        .upload-area {
            width: 100%;
            height: 200px;
            border: 2px dashed #ccc;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            color: #666;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }
        .upload-area.dragover {
            border-color: #006844;
            background-color: #e6f7f2;
        }
        .upload-area svg {
            width: 40px;
            height: 40px;
            margin-bottom: 10px;
            color: #006844;
        }
    </style>
</head>
<body class="bg-white flex flex-col h-screen">
    <?php include 'header.php'; ?>
    <main class="flex flex-grow">
        <aside class="w-64 p-4 custom-green text-white">
            <h2 class="text-lg mb-2">Favourite Folders</h2>
            <ul>
                <li class="mb-2"><a href="#" class="text-white hover:text-gray-300">Folder 1</a></li>
                <li><a href="#" class="text-white hover:text-gray-300">Folder 2</a></li>
            </ul>
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
            <!-- Filters aside remains the same -->
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
    <script>
        // Pass all folder data from PHP to JavaScript
        const folderData = <?= json_encode($rootData) ?>;
        // Expose session token to JavaScript
        const sessionToken = "<?php echo $_SESSION['session_token']; ?>";
        document.addEventListener('DOMContentLoaded', () => {
            let currentFolder = folderData; // Start with the root folder
            const navigationStack = [currentFolder]; // Track navigation history

            // Render initial view
            renderFolder(currentFolder);

            function renderFolder(folder) {
                const container = document.getElementById('folder-view');
                container.innerHTML = `
                    <div class="flex items-center mb-4">
                        <input type="text" placeholder="Search..." class="w-full px-4 py-2 rounded-l border border-r-0 bg-custom-green placeholder-gray-300">
                        <button class="px-4 py-2 bg-green-600 rounded-r hover:bg-green-400 text-white">Search</button>
                    </div>
                    ${navigationStack.length > 1 ? 
                        `<button onclick="goBack()" class="px-4 py-2 mb-4 bg-green-600 rounded hover:bg-green-400 text-white">Back</button>` : ''}
                    <div class="grid grid-cols-4 gap-4">${renderItems(folder)}</div>
                `;
            }

            function renderItems(folder) {
                return `
                    ${folder.folders.map(f => `
                        <div class="project-card cursor-pointer" onclick="openFolder(${f.id})">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                            <h3>${f.name}</h3>
                            <p>Folder</p>
                        </div>
                    `).join('')}
                    ${folder.files.map(file => `
                        <div class="project-card cursor-pointer" onclick="openFile('${file.id}', '${file.name}')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3>${file.name}</h3>
                            <p>File</p>
                        </div>
                    `).join('')}
                `;
            }

            window.openFolder = (folderId) => {
                const findFolderById = (folders, id) => {
                    for (const folder of folders) {
                        if (folder.id === id) return folder;
                        if (folder.folders && folder.folders.length > 0) {
                            const found = findFolderById(folder.folders, id);
                            if (found) return found;
                        }
                    }
                    return null;
                };
                const newFolder = findFolderById([folderData], folderId);
                if (newFolder) {
                    currentFolder = newFolder;
                    navigationStack.push(currentFolder);
                    renderFolder(currentFolder);
                } else {
                    console.error('Folder not found:', folderId);
                }
            };

            window.goBack = () => {
                if (navigationStack.length > 1) {
                    navigationStack.pop();
                    currentFolder = navigationStack[navigationStack.length - 1];
                    renderFolder(currentFolder);
                }
            };

            // Open File Modal and Fetch Data
            window.openFile = (fileId, name) => {
                const modal = document.getElementById('file-modal');
                const fileDetails = document.getElementById('file-details');
                const fileName = document.getElementById('file-name');
                // Reset modal content
                fileName.innerText = 'Loading...';
                fileDetails.innerHTML = '<div class="loading-spinner"></div>';
                modal.style.display = 'block';
                // Fetch file details from the server
                fetch('http://192.168.0.7:8040/file', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ file_id: Number(fileId) })
                })
                .then(response => {
                    if (!response.ok) {
                        console.log(response);
                        return "{}";
                    };
                    return response.json();
                })
                .then(data => {
                    // Populate modal with file details
                    console.log(data);
                    fileName.innerText = name;
                    fileDetails.innerHTML = `
                        <p><strong>Type:</strong> ${data.mime_type || 'Unknown'}</p>
                        <p><strong>Size:</strong> ${data.size || 'Unknown'}</p>
                        <p><strong>Last Updated:</strong> ${data.latest_created_at || 'Unknown'}</p>
                        <button onclick="downloadFile(${fileId}, '${name}', '${data.latest_version_number}')" 
                                class="mt-4 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-700">
                            Download
                        </button>
                    `;
                })
                .catch(error => {
                    console.error('Error fetching file details:', error);
                    fileName.innerText = 'Error';
                    fileDetails.innerHTML = '<p>Failed to load file details. Please try again later.</p>';
                });
            };

            // Close Modal
            window.closeModal = () => {
                const modal = document.getElementById('file-modal');
                modal.style.display = 'none';
            };

            // Close modal when clicking outside
            window.onclick = (event) => {
                const modal = document.getElementById('file-modal');
                if (event.target === modal) {
                    closeModal();
                }
            };

            // Download File Functionality
            window.downloadFile = (fileId, filename, ver) => {
                fetch('http://192.168.0.7:8040/download', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        file_id: Number(fileId),
                        version_number: Number(ver),
                        session_token: sessionToken
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Download failed');
                    return response.blob();
                })
                .then(blob => {
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    URL.revokeObjectURL(url);
                    document.body.removeChild(link);
                })
                .catch(error => {
                    console.error('Download error:', error);
                    alert('Failed to download the file.');
                });
            };

            // Drag-and-Drop Upload Area Behavior
            const uploadArea = document.getElementById('upload-area');
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', async (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');

                const files = e.dataTransfer.files;
                if (files.length === 0) return;

                const file = files[0];
                await handleFileUpload(file);
            });

            // Handle File Upload
            async function handleFileUpload(file) {
    try {
        // Debugging: Log the current folder and session token
        console.log('Current Folder:', currentFolder);
        console.log('Session Token:', sessionToken);

        // Create FormData object and append required fields
        const formData = new FormData();
        formData.append('file', file); // The actual file being uploaded
        formData.append('session_token', sessionToken); // Session token for authentication
        formData.append('file_name', file.name); // Name of the file
        formData.append('folder_id', currentFolder.id); // ID of the current folder
        formData.append('project_id', <?php echo($id);?>); // ID of the project
        formData.append('mime_type', file.type); // MIME type of the file

        // Debugging: Log the FormData contents
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }

        // Show loading feedback in the upload area
        const uploadArea = document.getElementById('upload-area');
        uploadArea.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 16v1a3 3 0 013 3h10a3 3 0 013-3v-1m-4-8l-4-4m0 0L8 8m4-4v12z"></path>
            </svg>
            <p>Uploading...</p>
        `;

        // Send the file to the backend
        const response = await fetch('http://192.168.0.7:8040/upload', {
            method: 'POST',
            body: formData,
        });

        // Check if the response is successful
        if (!response.ok) {
            const errorText = await response.text(); // Get error details from the backend
            throw new Error(`Upload failed with status ${response.status}: ${errorText}`);
        }

        // Parse the JSON response
        const result = await response.json();

        // Notify the user of success
        alert(`Upload successful! File ID: ${result.file_id}, Version: ${result.version_number}`);

        // Refresh the folder view to reflect the new file
        renderFolder(currentFolder);

    } catch (error) {
        // Log the error and notify the user
        console.error('Upload error:', error);
        alert('Upload failed. Please try again.');

    } finally {
        // Reset the upload area UI regardless of success or failure
        const uploadArea = document.getElementById('upload-area');
        uploadArea.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <p>Drag and drop a file here</p>
        `;
    }
}
        });
    </script>
</body>
</html>