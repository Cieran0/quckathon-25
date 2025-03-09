let currentFolder = null;

document.addEventListener('DOMContentLoaded', () => {
    currentFolder = folderData;
    const navigationStack = [currentFolder];
    let currentSearchTerm = '';

    // Render the folder view dynamically
    function renderFolder(folder) {
        const container = document.getElementById('folder-view');
        container.innerHTML = `
            <div class="flex items-center mb-4">
                <input type="text" id="search-input" placeholder="Search..." 
                       class="w-full px-4 py-2 rounded-l border border-r-0 bg-custom-green placeholder-gray-300"
                       value="${currentSearchTerm}">
                <button class="px-4 py-2 bg-green-600 rounded-r hover:bg-green-400 text-white">Search</button>
            </div>
            ${navigationStack.length > 1 ? 
                `<button onclick="goBack()" class="px-4 py-2 mb-4 bg-green-600 rounded hover:bg-green-400 text-white">Back</button>` : ''}
            <div class="grid grid-cols-4 gap-4">${renderItems(folder, currentSearchTerm)}</div>
        `;
        // Focus on the search input and place the cursor at the end
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.focus();
            setTimeout(() => {
                searchInput.selectionStart = searchInput.selectionEnd = searchInput.value.length;
            }, 0);
        }
    }

    // Render individual folders, files, and the "New Folder" button
    function renderItems(folder, searchTerm) {
        const filteredFolders = folder.folders.filter(f => 
            f.name.toLowerCase().includes(searchTerm.toLowerCase())
        );
        const filteredFiles = folder.files.filter(file => 
            file.name.toLowerCase().includes(searchTerm.toLowerCase())
        );

        // Add the "New Folder" button as the first item in the grid
        const newFolderButton = `
            <div class="project-card cursor-pointer text-custom-green bg-custom-green hover:bg-green-700" onclick="showCreateFolderPrompt()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <h3 class="text-white">Add Folder</h3>
                <p class="text-gray-300">Create a new folder</p>
            </div>
        `;

        if (filteredFolders.length === 0 && filteredFiles.length === 0) {
            return `<div class="col-span-4 text-red-500 text-center">No results found</div>${newFolderButton}`;
        }

        return `
            ${newFolderButton}
            ${filteredFolders.map(f => `
                <div class="project-card cursor-pointer" onclick="openFolder(${f.id})">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <h3>${f.name}</h3>
                    <p>Folder</p>
                </div>
            `).join('')}
            ${filteredFiles.map(file => `
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

    // Event listener for search input
    document.getElementById('folder-view').addEventListener('input', (e) => {
        if (e.target.id === 'search-input') {
            currentSearchTerm = e.target.value.toLowerCase();
            renderFolder(currentFolder);
        }
    });

    // Open a folder and update the view
    window.openFolder = (folderId) => {
        const findFolderById = (folders, id) => {
            for (const folder of folders) {
                if (folder.id === id) return folder;
                if (folder.folders?.length > 0) {
                    const found = findFolderById(folder.folders, id);
                    if (found) return found;
                }
            }
            return null;
        };
        const newFolder = findFolderById([folderData], folderId);
        if (newFolder) {
            currentSearchTerm = ''; // Clear search when navigating
            currentFolder = newFolder;
            navigationStack.push(currentFolder);
            renderFolder(currentFolder);
        } else {
            console.error('Folder not found:', folderId);
        }
    };

    // Go back to the previous folder
    window.goBack = () => {
        if (navigationStack.length > 1) {
            navigationStack.pop();
            currentFolder = navigationStack[navigationStack.length - 1];
            currentSearchTerm = ''; // Clear search when going back
            renderFolder(currentFolder);
        }
    };

    // Open file details modal
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
            if (!response.ok) throw new Error('Failed to fetch file details');
            return response.json();
        })
        .then(data => {
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

    // Close the modal
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

    // Download file functionality
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

    // Drag-and-drop upload area behavior
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

    // Handle file upload
    async function handleFileUpload(file) {
        try {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('session_token', sessionToken);
            formData.append('file_name', file.name);
            formData.append('folder_id', currentFolder.id);
            formData.append('project_id', projectId);
            formData.append('mime_type', file.type);
            // Show loading feedback
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
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Upload failed with status ${response.status}: ${errorText}`);
            }
            const result = await response.json();
            alert(`Upload successful! File ID: ${result.file_id}, Version: ${result.version_number}`);
            window.location.reload();
        } catch (error) {
            console.error('Upload error:', error);
            alert('Upload failed. Please try again.');
        } finally {
            // Reset the upload area UI
            uploadArea.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <p>Drag and drop a file here</p>
            `;
        }
    }

    // Open the folder name modal
    window.showCreateFolderPrompt = () => {
        const modal = document.getElementById('folder-name-modal');
        modal.classList.remove('hidden');
        console.log(modal.classList)
    };

    // Close the folder name modal
    window.closeFolderNameModal = () => {
        const modal = document.getElementById('folder-name-modal');
        modal.classList.add('hidden');
    };

    

    // Initial render
    renderFolder(currentFolder);
});

// Handle folder creation form submission
function createFolder(event) {
    event.preventDefault(); // Prevent default form submission
    const folderNameInput = document.getElementById('folder-name-input');
    const folderName = folderNameInput.value.trim();
    if (!folderName) {
        alert('Please enter a valid folder name.');
        return;
    }

    const params = new URLSearchParams(window.location.search);

    const id = Number(params.get('id'));

    // Send request to create the folder
    fetch('http://192.168.0.7:8040/new_folder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            project_id: id,
            session_token: sessionToken,
            parent_id: Number(currentFolder.id),
            name: folderName
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to create folder');
        return fetchProjectData(); // Refetch project data
    })
    .then(updatedData => {
        window.location.href = window.location.pathname + '?id=' + id;
    })
    .catch(error => {
        console.error('Error creating folder:', error);
        alert('Failed to create folder.');
    });
}

// Helper function to refetch project data
function fetchProjectData() {
    return fetch('http://192.168.0.7:8040/project', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            session_token: sessionToken,
            id: projectId
        })
    })
    .then(response => response.json());
}