class ChessGame {
  constructor(boardId, timers, gameLength, increment, gameMode, playerColor) {
    this.boardId = boardId;
    this.timers = timers;
    this.gameLength = gameLength;
    this.increment = increment;
    this.gameMode = gameMode;
    this.playerColor = playerColor;
    this.board = null;
    this.game = new Chess();
    this.whiteTime = gameLength * 60; // Convert minutes to seconds
    this.blackTime = gameLength * 60; // Convert minutes to seconds
    this.currentPlayer = "white";
    this.timerInterval = null;
    this.gameOver = false; // Flag to indicate if the game is over
  }
  initializeBoard() {
    this.board = Chessboard(this.boardId, {
      draggable: true,
      position: "start",
      orientation: this.playerColor,
      onDragStart: this.onDragStart.bind(this),
      onDrop: this.onDrop.bind(this),
      onSnapEnd: this.onSnapEnd.bind(this),
    });

    // Initialize the game object
    this.game = new Chess();
  }

  startGame() {
    console.log("Starting game with settings:", this);
    if (!this.board) {
      this.initializeBoard();
    }
    this.startTimer();
    this.updateTurnIndicator();
  }

  startTimer() {
    this.updateTimerDisplay();
    this.timerInterval = setInterval(() => {
      if (this.gameOver) return; // Stop the timer if the game is over
      if (this.game.turn() === "w") {
        this.whiteTime--;
        if (this.whiteTime <= 0) {
          this.endGame("Black wins on time!", "loss");
          return;
        }
      } else {
        this.blackTime--;
        if (this.blackTime <= 0) {
          this.endGame("White wins on time!", "loss");
          return;
        }
      }
      this.updateTimerDisplay();
    }, 1000);
    console.log("Timer started"); // Debugging statement
  }

  updateTimerDisplay() {
    document.getElementById(this.timers.white).innerText = this.formatTime(
      this.whiteTime
    );
    document.getElementById(this.timers.black).innerText = this.formatTime(
      this.blackTime
    );
  }
  updateGameState() {
    const state = this.getGameState();
    if (
      state.in_checkmate ||
      state.in_draw ||
      state.in_stalemate ||
      state.in_threefold_repetition
    ) {
      let status = "";
      if (state.in_checkmate) {
        status = `Game over, ${
          state.turn === "w" ? "Black" : "White"
        } wins by checkmate`;
      } else if (state.in_draw) {
        status = "Game over, drawn position";
      } else {
        status = "Game over";
      }
      this.endGame(
        status,
        state.in_checkmate
          ? state.turn === this.playerColor[0]
            ? "loss"
            : "win"
          : "draw"
      );
    }
  }

  updateUI(gameStarted) {
    // Define updateUICallback here
    const updateUICallback = (gameStarted) => {
      const resignButton = document.getElementById("resignGame");
      const playerColorSelect = document.getElementById("playerColor");
      const gameLengthInput = document.getElementById("gameLength");
      const incrementInput = document.getElementById("increment");
      const createGameButton = document.getElementById("createOnlineGame");
      const joinGameButton = document.getElementById("joinOnlineGame");
      const existingGamesSelect = document.getElementById("existingGames");
      const turnIndicator = document.getElementById("turnIndicator");
      document.getElementById("resignGame").style.display = gameStarted
        ? "inline-block"
        : "none";
      document.getElementById("playerColor").disabled = gameStarted;
      document.getElementById("gameLength").disabled = gameStarted;
      document.getElementById("increment").disabled = gameStarted;
      document.getElementById("turnIndicator").style.display = gameStarted
        ? "block"
        : "none";

      // Hide training-specific buttons by default
      document.getElementById("restartGame").style.display = "none";
      document.getElementById("undoMove").style.display = "none";
      if (resignButton)
        resignButton.style.display = gameStarted ? "inline-block" : "none";
      if (playerColorSelect) playerColorSelect.disabled = gameStarted;
      if (gameLengthInput) gameLengthInput.disabled = gameStarted;
      if (incrementInput) incrementInput.disabled = gameStarted;
      if (createGameButton) createGameButton.disabled = gameStarted;
      if (joinGameButton) joinGameButton.disabled = gameStarted;
      if (existingGamesSelect) existingGamesSelect.disabled = gameStarted;
      if (turnIndicator) {
        turnIndicator.style.display = gameStarted ? "block" : "none";
      }
    };
    // Call the updateUICallback function
    updateUICallback(gameStarted);
  }
  onDragStart(source, piece, position, orientation) {
    if (
      !this.game ||
      this.game.game_over() ||
      (this.game.turn() === "w" && piece.search(/^b/) !== -1) ||
      (this.game.turn() === "b" && piece.search(/^w/) !== -1) ||
      this.game.turn() !== this.playerColor[0]
    ) {
      return false;
    }
    return true;
  }
  onDrop(source, target) {
    // Check if it's the player's turn
    if (this.game.turn() !== this.playerColor[0]) {
      return "snapback";
    }

    // Try to make the move
    const move = {
      from: source,
      to: target,
      promotion: "q", // Always promote to a queen for simplicity
    };

    // Use the applyMove method to handle the move
    if (this.applyMove(move)) {
      // If the move is valid, update the game state
      this.updateBoardPosition();
      this.updateGameState();
      return null; // Allows the move
    } else {
      return "snapback"; // Invalid move, return the piece to its source
    }
  }
  setupOnlineUI() {
    document.getElementById("onlineGameOptions").style.display = "block";
    document.getElementById("startGame").style.display = "none";
    document.getElementById("resignGame").style.display = "inline-block";
    document.getElementById("resignGame").disabled = false;
    this.refreshGamesList();
  }
  formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${minutes.toString().padStart(2, "0")}:${secs
      .toString()
      .padStart(2, "0")}`;
  }

  addIncrementToCurrentPlayer() {
    if (this.increment > 0) {
      if (this.game.turn() === "w") {
        this.whiteTime += this.increment;
      } else {
        this.blackTime += this.increment;
      }
    }
  }
  onSnapEnd() {
    this.board.position(this.game.fen());
  }
  switchPlayer() {
    this.currentPlayer = this.currentPlayer === "white" ? "black" : "white";
    console.log("Player switched to:", this.currentPlayer); // Debugging statement
  }

  endGame(message, result) {
    clearInterval(this.timerInterval);
    this.gameOver = true; // Set the game over flag
    alert(message);
    // Optionally, you can disable further moves
    this.board.draggable = false;
    // Update user stats
    this.updateUserStats(result);
  }
  updateTimers(elapsedTime) {
    if (this.playerColor === "white") {
      this.whiteTime = Math.max(0, this.gameLength * 60 - elapsedTime);
      this.blackTime = this.gameLength * 60;
    } else {
      this.whiteTime = this.gameLength * 60;
      this.blackTime = Math.max(0, this.gameLength * 60 - elapsedTime);
    }
    this.updateTimerDisplay();
  }
  updateTurnIndicator() {
    const isMyTurn = this.game.turn() === this.playerColor[0];
    console.log("Is it my turn?", isMyTurn);
    const turnIndicator = document.getElementById("turnIndicator");
    if (turnIndicator) {
      turnIndicator.textContent = isMyTurn ? "Your turn" : "Opponent's turn";
    } else {
      console.warn("Turn indicator element not found in the DOM");
    }
    // Enable/disable the board based on whose turn it is
    if (this.board) {
      this.board.draggable = isMyTurn;
    } else {
      console.warn("Chessboard not initialized");
    }
  }
  stopGame() {
    clearInterval(this.timerInterval);
    this.gameOver = true; // Set the game over flag
    this.board.draggable = false; // Optionally, you can disable further moves
    console.log("Game stopped");
  }
  getGameState() {
    return {
      fen: this.game.fen(),
      turn: this.game.turn(),
      in_check: this.game.in_check(),
      in_checkmate: this.game.in_checkmate(),
      in_stalemate: this.game.in_stalemate(),
      in_draw: this.game.in_draw(),
      in_threefold_repetition: this.game.in_threefold_repetition(),
    };
  }
  applyMove(move) {
    const result = this.game.move(move);
    if (result) {
      this.addIncrementToCurrentPlayer();
      this.updateTurnIndicator();
      return true;
    }
    return false;
  }
  syncTimer(startTime) {
    try {
      if (startTime && !isNaN(startTime)) {
        const currentTime = Date.now();
        const elapsedTime = (currentTime - startTime) / 1000; // Convert to seconds
        if (elapsedTime < 0) {
          console.warn(
            "Invalid startTime: Time is in the future. Resetting to 0."
          );
          this.updateTimers(0);
        } else {
          this.updateTimers(elapsedTime);
        }
      } else if (startTime === null || startTime === undefined) {
        // If startTime is not provided, reset timers to initial state
        this.updateTimers(0);
      } else {
        console.error("Invalid startTime:", startTime);
        alert(
          "Error synchronizing game time. The game will start from the beginning."
        );
        this.updateTimers(0);
      }
    } catch (error) {
      console.error("Error in syncTimer:", error);
      alert(
        "An error occurred while synchronizing game time. The game will start from the beginning."
      );
      this.updateTimers(0);
    }
  }
  updateBoardPosition() {
    this.board.position(this.game.fen());
  }
  updateUserStats(result) {
    console.log(`Sending data: result=${result}&gameMode=${this.gameMode}`); // Debugging statement
    fetch("update_stats.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `result=${result}&gameMode=${this.gameMode}`,
    })
      .then((response) => response.text())
      .then((data) => {
        console.log("Response from server:", data); // Debugging statement
        console.log("Stats updated successfully");
      })
      .catch((error) => {
        console.error("Failed to update stats:", error);
      });
  }

  isValidJSON(message) {
    try {
      JSON.parse(message);
      return true;
    } catch (e) {
      return false;
    }
  }
  resetGame() {
    this.game.reset();
    this.board.position(this.game.fen());
    this.whiteTime = this.gameLength * 60;
    this.blackTime = this.gameLength * 60;
    this.gameOver = false;
    this.updateTimerDisplay();
    this.updateTurnIndicator();
  }
  resetUI() {
    // Reset general UI elements
    document.getElementById("resignGame").style.display = "none";
    document.getElementById("playerColor").disabled = false;
    document.getElementById("gameLength").disabled = false;
    document.getElementById("increment").disabled = false;
    document.getElementById("startGame").style.display = "inline-block";
    document.getElementById("turnIndicator").style.display = "none";
    document.getElementById("restartGame").style.display = "none";
    document.getElementById("undoMove").style.display = "none";

    // Reset timers
    document.getElementById("white-timer").innerText = this.formatTime(
      this.gameLength * 60
    );
    document.getElementById("black-timer").innerText = this.formatTime(
      this.gameLength * 60
    );

    // Reset the chessboard to starting position
    if (this.board) {
      this.board.position("start");
    }
  }
}

export default ChessGame;
