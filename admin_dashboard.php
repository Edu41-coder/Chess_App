<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>

<?php include 'templates/header.php'; ?>
<div class="container">
    <h1>Admin Dashboard</h1>
    <p>Here you can manage users, view messages, and see the banned users list.</p>
    <!-- Add admin-specific functionalities here -->
</div>
<?php include 'templates/footer.php'; ?>