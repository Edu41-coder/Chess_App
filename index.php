<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Game.php';
require_once 'classes/Move.php';
require_once 'classes/Message.php';
require_once 'classes/UserStats.php';

// Fetch the username if the user is logged in
$username = '';
if (isset($_SESSION['user_id'])) {
    $db = Database::getInstance();
    $user = new User($db->getConnection());
    $username = $user->getUsernameById($_SESSION['user_id']);
}
?>

<?php include 'templates/header.php'; ?>

<div class="container text-center mt-5">
    <h1>Welcome to ChessApp</h1>
    <img src="img/chess_cristal.jpg" alt="Chess Cristal" class="img-fluid my-4">
    <?php if (isset($_SESSION['user_id'])) : ?>
        <?php if ($_SESSION['role'] === 'admin') : ?>
            <p class="custom-message">Welcome, Admin <?php echo htmlspecialchars($username); ?>! You can manage users, view messages, and see the banned users list by clicking the button below.</p>
            <a href="admin_dashboard.php" class="btn btn-primary">Go to Admin Dashboard</a>
        <?php else : ?>
            <p class="custom-message">Welcome, <?php echo htmlspecialchars($username); ?>! You can view your stats and play games by clicking the button below.</p>
            <a href="user_dashboard.php" class="btn btn-primary">Go to User Dashboard</a>
            <a href="view_games.php" class="btn btn-secondary mt-2">View Chess Games</a>
        <?php endif; ?>
    <?php else : ?>
        <p>Please register or login to access the features.</p>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>
<script src="./js/jquery-3.6.0.min.js"></script>
<script src="./js/chess.js"></script>
<script src="./js/chessboard.js"></script>
<link rel="stylesheet" href="./styles/styles.css">

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // WebSocket connection
        let socket = new WebSocket("ws://localhost:8080");

        socket.onopen = function(e) {
            console.log("[open] Connection established");
            console.log("Sending to server");
            socket.send("Hello from " + "<?php echo htmlspecialchars($username); ?>");
        };

        socket.onmessage = function(event) {
            console.log(`[message] Data received from server: ${event.data}`);
            // Handle incoming messages (e.g., opponent's move)
        };

        socket.onclose = function(event) {
            if (event.wasClean) {
                console.log(`[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`);
            } else {
                console.log('[close] Connection died');
            }
        };

        socket.onerror = function(error) {
            console.log(`[error] ${error.message}`);
        };

        // Function to send a move
        function sendMove(move) {
            socket.send(JSON.stringify(move));
        }

        // Example button to send a move
        document.getElementById('sendMoveBtn').addEventListener('click', function() {
            sendMove({from: 'e2', to: 'e4'});
        });
    });
</script>

<!-- Add a button to send a move -->
<button id="sendMoveBtn" class="btn btn-primary">Send Move</button>