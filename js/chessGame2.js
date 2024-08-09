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

  startGame() {
    console.log("Starting game with settings:", this);
    this.board = Chessboard(this.boardId, {
      draggable: true,
      position: "start",
      orientation: this.playerColor,
      onDrop: this.onDrop.bind(this),
    });
    this.startTimer();
  }

  startTimer() {
    this.updateTimerDisplay();
    this.timerInterval = setInterval(() => {
      if (this.gameOver) return; // Stop the timer if the game is over
      if (this.currentPlayer === "white") {
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

  formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${minutes.toString().padStart(2, "0")}:${secs
      .toString()
      .padStart(2, "0")}`;
  }

  onDrop(source, target) {
    if (this.gameOver) return "snapback"; // Prevent moves if the game is over
    if (this.currentPlayer !== this.playerColor) return "snapback"; // Prevent player from moving out of turn

    const move = this.game.move({
      from: source,
      to: target,
      promotion: "q", // Always promote to a queen for simplicity
    });
    if (move === null) return "snapback"; // Illegal move
    this.board.position(this.game.fen());
    // Check for draw
    if (
      this.game.in_draw() ||
      this.game.in_stalemate() ||
      this.game.in_threefold_repetition()
    ) {
      this.endGame("Draw!", "draw");
      return;
    }
    this.switchPlayer();
  }

  switchPlayer() {
    this.currentPlayer = this.currentPlayer === "white" ? "black" : "white";
    if (this.increment > 0) {
      if (this.currentPlayer === "white") {
        this.whiteTime += this.increment;
      } else {
        this.blackTime += this.increment;
      }
    }
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

  stopGame() {
    clearInterval(this.timerInterval);
    this.gameOver = true; // Set the game over flag
    this.board.draggable = false; // Optionally, you can disable further moves
    console.log("Game stopped");
  }

  updateUserStats(result) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_stats.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        console.log("Response from server:", xhr.responseText); // Debugging statement
        if (xhr.status === 200) {
          console.log("Stats updated successfully");
        } else {
          console.error("Failed to update stats");
        }
      }
    };
    console.log(`Sending data: result=${result}&gameMode=${this.gameMode}`); // Debugging statement
    xhr.send(`result=${result}&gameMode=${this.gameMode}`);
  }
}
