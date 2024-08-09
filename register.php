<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';

$successMessage = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance()->getConnection();
    $user = new User($db);
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Password validation
    if (strlen($password) < 9) {
        $errors[] = "Password must be at least 9 characters long.";
    }
    if (!preg_match('/[\W]/', $password)) {
        $errors[] = "Password must contain at least one special character.";
    }

    if (empty($errors)) {
        if ($user->createUser($username, $password)) {
            $successMessage = "Registration successful! Redirecting to login page...";
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 3000);
                  </script>";
        } else {
            $errors[] = "Failed to create user. Please try again.";
        }
    }
}
?>

<?php include 'templates/header.php'; ?>
<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<div class="container">
    <h1>Register</h1>
    <?php if (!empty($errors)) : ?>
        <ul class="error">
            <?php foreach ($errors as $error) : ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if ($successMessage) : ?>
        <p class="success"><?php echo $successMessage; ?></p>
    <?php endif; ?>
    <form method="POST" action="register.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <div class="input-group">
            <input type="password" id="password" name="password" class="form-control" required>
            <div class="input-group-append">
                <span class="input-group-text" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>
        <br>
        <button type="submit">Register</button>
    </form>
</div>

<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordField = document.getElementById('password');
        const icon = this.querySelector('i');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
</script>

<?php include 'templates/footer.php'; ?>