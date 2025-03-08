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

// Retrieve and validate the 'id' parameter from the query string
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $projectId = (int)$_GET['id']; // Sanitize the ID as an integer
} else {
    $errorMessage = "Invalid or missing project ID.";
    echo "Error: $errorMessage\n";
    exit;
}

// Configuration
$apiUrl = 'http://10.201.121.182:8000/project'; // Replace with the actual API endpoint
$sessionToken = $_SESSION['session_token'];

// Setup cURL request
$ch = curl_init($apiUrl);

// Prepare the POST body with the session token and project ID
$postData = [
    'session_token' => $sessionToken,
    'id' => $projectId, // Include the project ID in the request
];

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true); // Use POST method
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData)); // Send data as form-encoded

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    $errorMessage = 'Network error: ' . curl_error($ch);
} else {
    if ($httpCode === 200) {
        $responseData = json_decode($response, true);
        if (isset($responseData['folders']) && isset($responseData['files'])) {
            // Extract folders and files arrays
            $folders = $responseData['folders'];
            $files = $responseData['files'];

            // Debugging: Print folders and files to the terminal
            echo "Folders:\n";
            var_dump($folders); // Use var_dump() for detailed output

            echo "\nFiles:\n";
            var_dump($files); // Use var_dump() for detailed output
        } else {
            $errorMessage = "Invalid API response format";
        }
    } else {
        $errorMessage = "API Error ($httpCode): " . (json_decode($response, true)['error'] ?? 'Unknown error');
    }
}

curl_close($ch);

// If there's an error, print it to the terminal
if ($errorMessage) {
    echo "Error: $errorMessage\n";
}
?>