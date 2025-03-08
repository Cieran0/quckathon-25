<header class="flex items-center justify-between p-4 bg-white header-bottom-border">
    <img src="logo.png" alt="Logo" class="h-20">
    <nav class="flex items-center">
        <a href="projects.php" class="mr-6 text-xl text-custom-green hover:text-black">Projects</a>
        <a href="graphs.php" class="mr-6 text-xl text-custom-green hover:text-black">Analytics</a>
        
        <?php if (isset($_SESSION['session_token'])): ?>
            <!-- Display logout button only if logged in -->
            <form action="logout.php">
                <button type="submit" class="px-4 py-2 rounded text-white bg-green-600 hover:bg-green-400">Logout</button>
            </form>
        <?php endif; ?>
    </nav>
</header>
