<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/User.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();
$user = new User($db->getConnection());
$username = $user->getUsernameById($_SESSION['user_id']);

// Ensure the PGN files directory exists
$pgnDir = __DIR__ . '/pgn_files';
if (!is_dir($pgnDir)) {
    die('PGN files directory does not exist.');
}

$pgnFiles = array_diff(scandir($pgnDir), array('.', '..'));

include __DIR__ . '/templates/header.php';
?>

<div class="container text-center mt-5">
    <h1>View Chess Games</h1>
    <p>Welcome, <?php echo htmlspecialchars($username); ?>! Select a game to view:</p>
    <ul class="list-group">
        <?php if (empty($pgnFiles)): ?>
            <li class="list-group-item">No PGN files available.</li>
        <?php else: ?>
            <?php foreach ($pgnFiles as $file): ?>
                <li class="list-group-item">
                    <a href="view_games.php?file=<?php echo urlencode($file); ?>"><?php echo htmlspecialchars($file); ?></a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <?php
    if (isset($_GET['file']) && in_array($_GET['file'], $pgnFiles)) {
        $filePath = $pgnDir . '/' . $_GET['file'];
        if (file_exists($filePath)) {
            $pgnContent = file_get_contents($filePath);
        } else {
            $pgnContent = 'File not found.';
        }
    }
    ?>

    <?php if (isset($pgnContent)): ?>
        <h2 class="mt-5">Game: <?php echo htmlspecialchars($_GET['file']); ?></h2>
        <div id="board" style="width: 400px; margin: auto;"></div>
        <pre id="pgn" style="display: none;"><?php echo htmlspecialchars($pgnContent); ?></pre>
        <div class="mt-3">
            <button id="prevBtn" class="btn btn-secondary">Previous</button>
            <button id="nextBtn" class="btn btn-secondary">Next</button>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
<script src="<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>/js/jquery-3.6.0.min.js"></script>
<script src="<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>/js/chess.js"></script>
<script src="<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>/js/chessboard.js"></script>
<link rel="stylesheet" href="<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>/js/chessboard.css" />

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('pgn')) {
            var pgn = document.getElementById('pgn').textContent;
            var board = Chessboard('board', {
                draggable: true,
                dropOffBoard: 'trash',
                sparePieces: true
            });
            var game = new Chess();
            game.load_pgn(pgn);

            var moves = game.history();
            var currentMove = 0;

            function updateBoard() {
                game.reset();
                for (var i = 0; i < currentMove; i++) {
                    game.move(moves[i]);
                }
                board.position(game.fen());
            }

            document.getElementById('prevBtn').addEventListener('click', function () {
                if (currentMove > 0) {
                    currentMove--;
                    updateBoard();
                }
            });

            document.getElementById('nextBtn').addEventListener('click', function () {
                if (currentMove < moves.length) {
                    game.move(moves[currentMove]);
                    currentMove++;
                    board.position(game.fen());
                }
            });

            updateBoard();
        }
    });
</script>