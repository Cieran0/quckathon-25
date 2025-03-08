<?php
session_start();

// Redirect to login page if the user is not authenticated
if (!isset($_SESSION['session_token'])) {
    header("Location: login.php");
    exit();
}
else if (isset($_SESSION['session_token'])) {
    header("Location: projects.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Green Team - Connecting Young People with Nature</title>
    <link href="tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/flowbite@1.5.3/dist/flowbite.min.css" />
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
            color: white;
            font-family: Arial, sans-serif;
        }

        .info-card {
            background-color: rgba(255, 255, 255, 0.1); /* Semi-transparent white for a lighter background */
            padding: 2rem;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .info-card h3 {
            margin-top: 0;
            font-size: 1.5rem;
            font-weight: bold;
            color: #f5f5f5;
        }
        .info-card p {
            font-size: 1rem;
            color: #d1d1d1;
        }
        .cta-button {
            padding: 12px 24px;
            background-color: #00a169; /* Brighter green for call to action */
            color: white;
            font-size: 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .cta-button:hover {
            background-color: #006844; /* Darker green on hover */
        }

        /* Section header */
        h1, p {
            color: white;
        }

        .text-gray-800 {
            color: #f5f5f5 !important;
        }
    </style>
</head>
<body class="bg-custom-green flex flex-col h-screen">
    <?php include 'header.php'; ?>

    <main class="flex flex-grow flex-col items-center justify-center p-4">
        <!-- Introduction Section -->
        <section class="text-center mb-12">
            <h1 class="text-3xl font-semibold mb-4">The Green Team</h1>
            <p class="text-xl mb-6">Connecting Young People with Nature</p>
            <p class="text-base max-w-3xl mx-auto mb-6">
                The Green Team has been successfully running programmes of outdoor activities for young people since 1995.
                Our programmes offer a unique blend of practical conservation tasks, outdoor fun, environmental education,
                and personal development. We work with individuals, school groups, and referring partners. There really is something for everybody.
            </p>
        </section>

        <!-- Information Cards Section -->
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="info-card">
                <h3>Our Mission</h3>
                <p>To connect young people with nature, providing opportunities for personal growth and environmental impact.</p>
            </div>
            <div class="info-card">
                <h3>Our Programmes</h3>
                <p>We offer diverse outdoor activities, from hands-on conservation to environmental education and team-building exercises.</p>
            </div>
            <div class="info-card">
                <h3>Get Involved</h3>
                <p>We welcome individuals and school groups to join our programmes and make a difference for the environment.</p>
            </div>
            <div class="info-card">
                <h3>Learn More</h3>
                <a href="https://www.greenteam.org.uk/" class="underline text-custom-white hover:text-gray-600">
    Learn more about our organisation on our main website here
</a>

            </div>
        </section>

    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
