<?php
session_start();

// Redirect unauthenticated users
if (!isset($_SESSION['session_token'])) {
    header('Location: login.php');
    exit;
}

// Sample user data (replace with actual API call or database query)
$user = [
    'name' => 'John Doe',
    'phone' => '+44 1234 567890',
    'email' => 'johndoe@example.com'
];

// Sample followed projects (replace with actual API call or database query)
$followedProjects = [
    'Project Alpha',
    'Project Beta',
    'Project Gamma'
];

    $url = 'http://10.201.121.182:8000/profile';
    $data = [
        'session_token' => $_SESSION['session_token']
    ];
    $jsonData = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Ensure response is returned

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Process response
    if ($response === false) {
        $errorMessage = "Network error: " . $curlError;
        error_log("cURL Error: " . $curlError);
    } elseif ($httpCode != 200) {
        $errorMessage = "Server error: HTTP $httpCode";
        error_log("API Response Code: $httpCode");
    } else {
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = "Invalid response format";
            error_log("JSON Decode Error: " . json_last_error_msg());
        } else {
            // Save session token
            $user = $responseData['username'];
            $phone = $responseData['phone_number'];
            $email = $responseData['email'];
            $followedProjects = $responseData['followed_projects'];
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link href="tailwind.min.css" rel="stylesheet">
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

        body {
            background-color: #006844;
            font-family: Arial, sans-serif;
            color: white;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 2rem;
        }

        .login-form {
            background-color: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            text-align: center;
            width: 350px;
        }

        .login-form h2 {
            font-size: 2rem;
            color: #006844;
            margin-bottom: 2rem;
        }

        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            margin: 1rem 0;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            background-color: #f5f5f5;
            color: #333;
            transition: border-color 0.3s ease;
        }

        .login-form input[type="text"]:focus,
        .login-form input[type="password"]:focus {
            border-color: #006844;
            outline: none;
        }

        .login-form input::placeholder {
            color: #666;
            opacity: 0.7;
        }

        .login-form button {
            width: 100%;
            padding: 12px;
            margin-top: 2rem;
            background-color: #006844;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-form button:hover {
            background-color: #00a169;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1rem 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="flex-grow flex items-center justify-center" style="margin-top: 50px;">
        <div class="w-full max-w-3xl bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold text-custom-green mb-4">User Profile</h2>
            <div class="mb-4">
                <p class="text-gray-800"><strong>Name:</strong> <?php echo htmlspecialchars($user); ?></p>
                <p class="text-gray-800"><strong class="text-gray-800">Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
                <p class="text-gray-800"><strong class="text-gray-800">Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            </div>
            
            <h3 class="text-xl font-semibold text-custom-green mb-2">Followed Projects</h3>
            <ul class="list-disc list-inside bg-gray-200 p-4 rounded">
                <?php foreach ($followedProjects as $project): ?>
                    <li class="mb-1 text-gray-800"> <?php echo htmlspecialchars($project['name']); ?> </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>