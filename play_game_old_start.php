<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    header('Location: index.php');
    exit;
}
?>

<?php include 'templates/header.php'; ?>
<div class="container mt-5">
    <h1 class="text-center custom-heading">Play a Game</h1>
    <div class="row custom-row">
        <div class="col-md-8">
            <div id="chessboard" class="chessboard"></div>
        </div>
        <div class="col-md-4">
            <h2>Game Settings</h2>
            <div>
                <label for="gameMode">Game Mode:</label>
                <select id="gameMode" class="form-control">
                    <option value="ai">Play Against AI</option>
                    <option value="local">Play Locally</option>
                    <option value="ai_vs_ai">AI vs AI</option>
                    <option value="train">Training Mode</option>
                    <option value="online">Play Online</option>
                </select>
            </div>
            <div>
                <label for="playerColor">Player Color:</label>
                <select id="playerColor" class="form-control">
                    <option value="white">White</option>
                    <option value="black">Black</option>
                </select>
            </div>
            <div id="gameLengthContainer">
                <label for="gameLength">Game Length (minutes):</label>
                <input type="number" id="gameLength" class="form-control" value="10">
            </div>
            <div id="incrementContainer">
                <label for="increment">Increment (seconds):</label>
                <input type="number" id="increment" class="form-control" value="5" min="0" max="15" step="1">
            </div>
            <button id="startGame" class="btn btn-primary mt-3">Start Game</button>
            <button id="resignGame" class="btn btn-danger mt-3" style="display: none;">Resign</button>
            <button id="restartGame" class="btn btn-danger mt-3" style="display: none;">Restart</button>
            <button id="undoMove" class="btn btn-secondary mt-3" style="display: none;">Undo Move</button>

            <!-- New buttons for online game -->
            <div id="onlineGameOptions" style="display: none;">
                <button id="createOnlineGame" class="btn btn-success mt-3">Create New Online Game</button>
                <button id="refreshGames" class="btn btn-info mt-3">Refresh Games</button>
                <select id="existingGames" class="form-control mt-3">
                    <option value="">Select a game to join</option>
                </select>
                <button id="joinOnlineGame" class="btn btn-primary mt-3">Join Selected Game</button>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-6">
            <h3>White Timer: <span id="white-timer">10:00</span></h3>
        </div>
        <div class="col-md-6">
            <h3>Black Timer: <span id="black-timer">10:00</span></h3>
        </div>
    </div>
</div>

<!-- Ensure jQuery is included before Chessboard.js -->
<script src="js/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="styles/chessboard.css">
<script src="js/chess.js"></script>
<script src="js/chessboard.js"></script>
<script src="js/stockfish.js"></script>
<script type="module" src="js/chessGame.js"></script>
<script type="module" src="js/OnlineGame.js"></script>
<script type="module" src="js/aiGame.js"></script>
<script type="module" src="js/localGame.js"></script>
<script type="module" src="js/Ai_vs_AiGame.js"></script>
<script type="module" src="js/trainGame.js"></script>
<script type="module">
    import OnlineGame from './js/OnlineGame.js';
    import LocalGame from './js/localGame.js';
    import Train from './js/trainGame.js';
    import AIGame from './js/aiGame.js';
    import AiVsAiGame from './js/Ai_vs_AiGame.js';

    let currentGame = null;
    function initializeOnlineGame() {
        const playerColor = document.getElementById('playerColor').value;
        currentGame = new OnlineGame('chessboard', playerColor, 'ws://localhost:8080');
        document.getElementById('onlineGameOptions').style.display = 'block';
        currentGame.refreshGamesList(); // Rafraîchir la liste dès l'initialisation
    }
    document.getElementById('startGame').addEventListener('click', function() {
        const gameMode = document.getElementById('gameMode').value;
        const playerColor = document.getElementById('playerColor').value;
        const gameLength = parseInt(document.getElementById('gameLength').value);
        let increment = parseInt(document.getElementById('increment').value);

        if (increment < 0) increment = 0;
        if (increment > 15) increment = 15;

        if (gameMode === 'ai') {
            currentGame = new AIGame('chessboard', {
                white: 'white-timer',
                black: 'black-timer'
            }, gameLength, increment, playerColor);
        } else if (gameMode === 'local') {
            currentGame = new LocalGame('chessboard', {
                white: 'white-timer',
                black: 'black-timer'
            }, gameLength, increment, playerColor);
        } else if (gameMode === 'ai_vs_ai') {
            currentGame = new AiVsAiGame('chessboard', {
                white: 'white-timer',
                black: 'black-timer'
            }, gameLength, increment);
        } else if (gameMode === 'train') {
            currentGame = new Train('chessboard', playerColor);
            document.getElementById('restartGame').style.display = 'inline-block'; // Show restart button
            document.getElementById('undoMove').style.display = 'inline-block'; // Show undo button
            document.getElementById('resignGame').style.display = 'none';
        } else if (gameMode === 'online') {
            initializeOnlineGame();            
        } else {
            document.getElementById('restartGame').style.display = 'none'; // Hide restart button for other modes
            document.getElementById('undoMove').style.display = 'none'; // Hide undo button for other modes
            document.getElementById('resignGame').style.display = 'inline-block'; // Show resign button
        }

        if (currentGame && gameMode !== 'online') {
            currentGame.startGame();
            if (gameMode !== 'train') {
                document.getElementById('resignGame').disabled = false;
            }
        }
    });

    document.getElementById('resignGame').addEventListener('click', function() {
        if (currentGame) {
            currentGame.stopGame();
            alert('You have resigned the game.');
            document.getElementById('resignGame').disabled = true;
        }
    });

    document.getElementById('restartGame').addEventListener('click', function() {
        if (currentGame && currentGame instanceof Train) {
            currentGame.startGame(); // Restart the game
        }
    });

    document.getElementById('undoMove').addEventListener('click', function() {
        if (currentGame && currentGame instanceof Train) {
            currentGame.undoMove();
        }
    });

    // Online game buttons logic
    document.getElementById('createOnlineGame').addEventListener('click', function() {
        if (currentGame && currentGame instanceof OnlineGame) {
            currentGame.createNewGame();
        } else {
            alert('Please select the "Play Online" game mode first.');
        }
    });

    document.getElementById('refreshGames').addEventListener('click', function() {
        if (currentGame && currentGame instanceof OnlineGame) {
            currentGame.refreshGamesList();
        } else {
            alert('Please select the "Play Online" game mode first.');
        }
    });

    document.getElementById('joinOnlineGame').addEventListener('click', function() {
        const selectedGameId = document.getElementById('existingGames').value;
        if (selectedGameId) {
            if (currentGame && currentGame instanceof OnlineGame) {
                currentGame.joinGame(selectedGameId);
            } else {
                alert('Please select the "Play Online" game mode first.');
            }
        } else {
            alert('Please select a game to join.');
        }
    });

    // Update the game mode change to hide/show online game options
    document.getElementById('gameMode').addEventListener('change', function() {
        const gameMode = document.getElementById('gameMode').value;
        if (gameMode === 'online') {
            initializeOnlineGame();            
        } else {
            document.getElementById('onlineGameOptions').style.display = 'none';
            currentGame = null;
        }
    });
</script>
<style>
    .chessboard {
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
    }

    .custom-heading {
        margin-top: 20px;
        /* Adjust this value to move the heading higher or lower */
    }

    .custom-row {
        margin-top: 20px;
        /* Add separation between the heading and the row */
    }
</style>
<?php include 'templates/footer.php'; ?>