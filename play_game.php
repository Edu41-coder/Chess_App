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
        <div id="turnIndicator"></div>
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

            <!-- Online game options -->
            <div id="onlineGameOptions" style="display: none;">
                <button id="createOnlineGame" class="btn btn-success mt-3">Create New Online Game</button>
                <button id="refreshGames" class="btn btn-info mt-3">Refresh Games</button>
                <select id="existingGames" class="form-control mt-3">
                    <option value="">Select a game to join</option>
                </select>
                <button id="joinOnlineGame" class="btn btn-primary mt-3">Join Selected Game</button>
            </div>
            <div id="chatContainer" style="display: none;">
                <h2>Chat</h2>
                <div id="chatMessages" style="height: 200px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;"></div>
                <input type="text" id="chatInput" class="form-control mt-2" placeholder="Type a message...">
                <button id="sendChat" class="btn btn-primary mt-2">Send</button>
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

<!-- Scripts -->
<script src="js/jquery-3.6.0.min.js"></script>
<script>
    // Intercept addEventListener
    const originalAddEventListener = EventTarget.prototype.addEventListener;
    EventTarget.prototype.addEventListener = function(type, listener, options) {
        if (type === 'mousedown' || type === 'touchstart') {
            if (typeof options === 'object') {
                options.passive = false;
            } else {
                options = { passive: false };
            }
        }
        return originalAddEventListener.call(this, type, listener, options);
    };
    // Intercept console.warn to suppress specific messages
    const originalWarn = console.warn;
    console.warn = function(...args) {
        if (typeof args[0] === 'string' && args[0].includes('Unable to preventDefault inside passive event listener')) {
            // Do nothing, effectively suppressing the warning
            return;
        }
        // For all other warnings, use the original console.warn
        originalWarn.apply(console, args);
    };
</script>
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

    function updateUIForGameState(gameStarted) {
        if (currentGame) {
            try {
                currentGame.updateUI(gameStarted);
            } catch (error) {
                console.error('Error updating UI:', error);
                // Fallback to default UI update
                defaultUIUpdate(gameStarted);
            }
        } else {
            defaultUIUpdate(gameStarted);
        }
    }

    function defaultUIUpdate(gameStarted) {
        document.getElementById('resignGame').style.display = gameStarted ? 'inline-block' : 'none';
        document.getElementById('playerColor').disabled = gameStarted;
        document.getElementById('gameLength').disabled = gameStarted;
        document.getElementById('increment').disabled = gameStarted;
        document.getElementById('createOnlineGame').disabled = gameStarted;
        document.getElementById('joinOnlineGame').disabled = gameStarted;
        document.getElementById('existingGames').disabled = gameStarted;
        document.getElementById('turnIndicator').style.display = gameStarted ? 'block' : 'none';
    }

    function initializeOnlineGame() {
        const playerColor = document.getElementById('playerColor').value;
        currentGame = new OnlineGame('chessboard', playerColor, 'ws://localhost:8080');
        showOnlineGameOptions();
        currentGame.updateUI(false);
        document.getElementById("chatContainer").style.display = "block";
        setupChatHandlers();
    }

    function showOnlineGameOptions() {
        document.getElementById("onlineGameOptions").style.display = "block";
        document.getElementById("startGame").style.display = "none";
        document.getElementById("resignGame").style.display = "none";
    }

    function initializeGame(gameMode) {
        if (currentGame) {
            currentGame.resetGame();
        }

        const playerColor = document.getElementById('playerColor').value;
        const gameLength = parseInt(document.getElementById('gameLength').value);
        let increment = parseInt(document.getElementById('increment').value);
        if (increment < 0) increment = 0;
        if (increment > 15) increment = 15;

        switch (gameMode) {
            case 'online':
                initializeOnlineGame();
                return; // Exit the function early for online mode
            case 'ai':
                currentGame = new AIGame('chessboard', {
                    white: 'white-timer',
                    black: 'black-timer'
                }, gameLength, increment, playerColor);
                break;
            case 'local':
                currentGame = new LocalGame('chessboard', {
                    white: 'white-timer',
                    black: 'black-timer'
                }, gameLength, increment, playerColor);
                break;
            case 'ai_vs_ai':
                currentGame = new AiVsAiGame('chessboard', {
                    white: 'white-timer',
                    black: 'black-timer'
                }, gameLength, increment);
                break;
            case 'train':
                currentGame = new Train('chessboard', playerColor);
                break;
        }

        if (currentGame) {
            currentGame.startGame();
            currentGame.updateUI(true);
            if (gameMode !== 'train') {
                document.getElementById('resignGame').style.display = 'inline-block';
                document.getElementById('resignGame').disabled = false;
            }
        }

        // Hide online game options for non-online modes
        document.getElementById("onlineGameOptions").style.display = "none";
        document.getElementById("startGame").style.display = "inline-block";
    }

    function setupChatHandlers() {
        const chatInput = document.getElementById('chatInput');
        const sendChatButton = document.getElementById('sendChat');
        sendChatButton.addEventListener('click', () => {
            const message = chatInput.value;
            if (message && currentGame) {
                currentGame.sendChatMessage(message);
                chatInput.value = '';
            }
        });
    }

    window.addEventListener('beforeunload', function(e) {
        if (currentGame && currentGame instanceof OnlineGame) {
            currentGame.endGame('Player left', 'loss');
        }
    });

    document.getElementById('gameMode').addEventListener('change', function() {
        const gameMode = this.value;
        if (currentGame) {
            try {
                currentGame.resetUI();
            } catch (error) {
                console.error('Error resetting UI:', error);
                defaultUIUpdate(false);
            }
        }
        if (gameMode === 'online') {
            initializeOnlineGame();
            if (currentGame) {
                currentGame.updateUI(false);
            }
        } else {
            currentGame = null;
            document.getElementById("onlineGameOptions").style.display = "none";
            document.getElementById("startGame").style.display = "inline-block";
        }
    });

    document.getElementById('startGame').addEventListener('click', function() {
        initializeGame(document.getElementById('gameMode').value);
    });

    document.getElementById('resignGame').addEventListener('click', function() {
        if (currentGame) {
            if (currentGame instanceof OnlineGame) {
                currentGame.resignGame();
            } else {
                currentGame.endGame('You resigned', 'loss');
            }
            this.disabled = true;
            try {
                currentGame.resetUI();
            } catch (error) {
                console.error('Error resetting UI:', error);
                defaultUIUpdate(false);
            }
        }
    });

    document.getElementById('restartGame').addEventListener('click', function() {
        if (currentGame && currentGame instanceof Train) {
            currentGame.startGame();
        }
    });

    document.getElementById('undoMove').addEventListener('click', function() {
        if (currentGame && currentGame instanceof Train) {
            currentGame.undoMove();
        }
    });

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

    document.addEventListener('DOMContentLoaded', function() {
        const chessboard = document.getElementById('chessboard');

        chessboard.addEventListener('mousedown', function(e) {
            e.preventDefault();
        }, {
            passive: false
        });

        chessboard.addEventListener('touchstart', function(e) {
            e.preventDefault();
        }, {
            passive: false
        });
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
    }

    .custom-row {
        margin-top: 20px;
    }
</style>
<?php include 'templates/footer.php'; ?>