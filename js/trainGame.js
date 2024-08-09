import ChessGame from "./chessGame.js";

class Train extends ChessGame {
  constructor(boardId, playerColor) {
    super(boardId, {}, 0, 0, "train", playerColor);
  }

  startGame() {
    console.log("Starting training game without timer");
    this.board = Chessboard(this.boardId, {
      draggable: true,
      position: "start",
      orientation: this.playerColor,
      onDrop: this.onDrop.bind(this),
    });
  }

  onDrop(source, target) {
    if (this.gameOver) return "snapback"; // Prevent moves if the game is over
    const move = this.game.move({
      from: source,
      to: target,
      promotion: "q", // Always promote to a queen for simplicity
    });
    if (move === null) return "snapback"; // Illegal move
    this.board.position(this.game.fen());
    // Check for game-ending conditions
    if (this.game.in_checkmate()) {
      const winner = this.currentPlayer === "white" ? "White" : "Black";
      this.endGame(
        `${winner} wins by checkmate!`,
        winner === this.playerColor ? "win" : "loss"
      );
      return;
    }
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
    super.switchPlayer();
  }

  undoMove() {
    const move = this.game.undo();
    if (move) {
      this.board.position(this.game.fen());
      this.switchPlayer();
    }
  }
  // Override the updateUI method
  updateUI(gameStarted) {
    super.updateUI(gameStarted); // Call the parent class method first

    // Show training-specific buttons
    document.getElementById("restartGame").style.display = "inline-block";
    document.getElementById("undoMove").style.display = "inline-block";
    document.getElementById("resignGame").style.display = "none"; // Hide resign button in training mode
  }
}

// Export the class for use in other files
export default Train;
