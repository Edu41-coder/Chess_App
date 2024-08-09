<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the database instance and connection
    $dbInstance = Database::getInstance();
    $db = $dbInstance->connect();
    
    $user = new User($db);
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Password validation
    $errors = [];
    if (strlen($password) < 9) {
        $errors[] = "Password must be at least 9 characters long.";
    }
    if (!preg_match('/[\W]/', $password)) {
        $errors[] = "Password must contain at least one special character.";
    }

    if (empty($errors)) {
        $userData = $user->getUserByUsername($username);

        // Log the retrieved user data for debugging
        error_log("User data: " . print_r($userData, true));

        if ($userData && $password === $userData['password']) {
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['role'] = $userData['role'];
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Invalid username or password.";
            // Log the error for debugging
            error_log("Invalid username or password for username: $username");
        }
    } else {
        // Log the validation errors
        error_log("Validation errors: " . implode(", ", $errors));
    }
}
?>

<?php include 'templates/header.php'; ?>
<div class="container">
    <h1>Login</h1>
    <?php if (!empty($errors)): ?>
        <ul class="error">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Login</button>
    </form>
</div>
<?php include 'templates/footer.php'; ?>