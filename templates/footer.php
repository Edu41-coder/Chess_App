<?php
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && $_SESSION['role'] === 'admin';
?>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <script src="js/bootstrap.bundle.min.js"></script>
    <footer class="custom-footer">
        <p>&copy; 2024 ChessApp. All rights reserved.</p>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if (!$isLoggedIn): ?>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="login.php">Login</a></li>
                <?php else: ?>
                    <li><a href="logout.php">Logout</a></li>
                    <?php if ($isAdmin): ?>
                        <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="user_dashboard.php">User Dashboard</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </nav>
    </footer>
</body>
</html>