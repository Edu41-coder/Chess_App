<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance()->getConnection();
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

        if ($userData && $password === $userData['password']) { // No password_verify
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['role'] = $userData['role'];
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Invalid username or password.";
        }
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