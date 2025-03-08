<?php
session_start();

// Redirect if session_token is not set
if (!isset($_SESSION['session_token'])) {
    header('Location: login.php');
    exit;
}

// Retrieve the 'id' from the URL query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // If 'id' is missing or not numeric, handle the error
    echo "Invalid or missing ID.";
    exit;
}

$id = (int)$_GET['id']; // Ensure the ID is an integer

// Prepare data to send as an associative array
$data = [
    'session_token' => $_SESSION['session_token'],
    'id' => $id // Use the ID from the URL
];

// Convert the data to JSON format
$json_data = json_encode($data);

// Initialize cURL session
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://10.201.121.182:8000/project', // Target URL
    CURLOPT_POST => true, // Use POST method
    CURLOPT_POSTFIELDS => $json_data, // Send JSON data
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json', // Set content type to JSON
        'Content-Length: ' . strlen($json_data) // Optional: Specify content length
    ],
    CURLOPT_RETURNTRANSFER => true // Capture the response (optional)
]);

// Execute request and close cURL
$response = curl_exec($ch);
curl_close($ch);

// Handle the response from the server
echo $response;
?>