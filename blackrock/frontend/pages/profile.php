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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link href="tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-[#006844] flex flex-col min-h-screen">
    <?php include 'header.php'; ?>

    <div class="flex-grow flex items-center justify-center">
        <div class="w-full max-w-3xl bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold text-custom-green mb-4">User Profile</h2>
            <div class="mb-4">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <h3 class="text-xl font-semibold text-custom-green mb-2">Followed Projects</h3>
            <ul class="list-disc list-inside bg-gray-200 p-4 rounded">
                <?php foreach ($followedProjects as $project): ?>
                    <li class="mb-1"> <?php echo htmlspecialchars($project); ?> </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
