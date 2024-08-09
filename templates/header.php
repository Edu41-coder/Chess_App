<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChessApp</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        body {
            background-color: #f8f9fa; /* Light background for the body */
        }
        nav {
            background-color: #000; /* Black background for the navbar */
            padding: 10px;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
        }
        nav ul li {
            display: inline;
            margin-right: 15px;
        }
        nav ul li a {
            color: #fff; /* White text color for links */
            text-decoration: none;
        }
        nav ul li a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
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
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>