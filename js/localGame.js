import ChessGame from './chessGame.js';

class LocalGame extends ChessGame {
  constructor(boardId, timers, gameLength, increment, playerColor) {
    super(boardId, timers, gameLength, increment, "local", playerColor);
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
    // Check for draw
    if (this.game.in_draw() || this.game.in_stalemate() || this.game.in_threefold_repetition()) {
      this.endGame("Draw!", "draw");
      return;
    }
    this.addIncrementToCurrentPlayer();
    this.switchPlayer();
  }

  switchPlayer() {
    super.switchPlayer();
  }

  startGame() {
    super.startGame();
  }
}

// Export the class for use in other files
export default LocalGame;