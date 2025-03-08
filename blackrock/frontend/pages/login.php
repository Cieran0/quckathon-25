<?php
session_start();

if(isset($_SESSION['session_token'])) {
    header('Location: projects.php');
    exit();
}

$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($username) || empty($password)) {
        $errorMessage = "Username and password are required.";
    } else {
        // Setup cURL request
        $url = 'http://10.201.121.182:8000/login';
        $data = [
            'username' => $username,
            'password' => $password
        ];
        $jsonData = json_encode($data);


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Process response
        if ($response === false) {
            $errorMessage = "Network error: " . curl_error($ch);
        } elseif ($httpCode != 200) {
            $errorMessage = "Server error: HTTP $httpCode";
        } else {
            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errorMessage = "Invalid response format";
            } elseif (!isset($responseData['session_token'])) {
                $errorMessage = "Login failed - Invalid credentials";
            } else {
                // Save session token
                $_SESSION['session_token'] = $responseData['session_token'];
                // Redirect to dashboard or protected page
                header('Location: projects.php');
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
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
    <header class="flex items-center justify-between p-4 bg-white header-bottom-border">
        <img src="logo.png" alt="Logo" class="h-10">
        <nav class="flex items-center">
            <a href="#projects" class="mr-4 text-custom-green hover:text-black">Projects</a>
            <a href="#analytics" class="mr-4 text-custom-green hover:text-black">Analytics</a>
        </nav>
    </header>

    <div class="login-container">
        <form method="POST" action="login.php" class="login-form">
            <h2>Login</h2>
            <?php if ($errorMessage): ?>
                <p class="text-red-500 mb-4"><?php echo htmlspecialchars($errorMessage); ?></p>
            <?php endif; ?>
            <input type="text" name="username" placeholder="Username" class="mb-4" required>
            <input type="password" name="password" placeholder="Password" class="mb-4" required>
            <button type="submit" class="w-full">Log In</button>
        </form>
    </div>

    <footer class="dark-gray text-white py-4 text-center">
        <p class="text-sm">&copy; 2024 The Green Team. All rights reserved.</p>
    </footer>
</body>
</html>