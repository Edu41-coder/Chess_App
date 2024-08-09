import ChessGame from './chessGame.js';

class AIGame extends ChessGame {
  constructor(boardId, timers, gameLength, increment, playerColor) {
      super(boardId, timers, gameLength, increment, "ai", playerColor);
      this.initializeStockfish();
  }

  initializeStockfish() {
      this.stockfish = new Worker("js/stockfish.js");
      this.stockfish.onmessage = this.onStockfishMessage.bind(this);
      this.stockfish.postMessage("uci");
      this.stockfish.postMessage("ucinewgame");
      this.stockfish.postMessage("isready");
      console.log("Stockfish initialized"); // Debugging statement
  }

  startGame() {
      super.startGame();
      if (this.playerColor === "black") {
          this.makeAIMove();
      }
  }

  makeAIMove() {
      if (this.gameOver) return; // Prevent AI moves if the game is over
      const fen = this.game.fen();
      console.log(`Sending position to Stockfish: ${fen}`); // Debugging statement
      this.stockfish.postMessage(`position fen ${fen}`);
      this.stockfish.postMessage("go depth 15");
  }

  onStockfishMessage(event) {
      if (this.gameOver) return; // Prevent processing if the game is over
      console.log("Stockfish message:", event.data); // Debugging statement
      const match = event.data.match(/^bestmove\s([a-h][1-8][a-h][1-8])/);
      if (match) {
          const move = match[1];
          console.log("AI move:", move); // Debugging statement
          this.game.move({
              from: move.substring(0, 2),
              to: move.substring(2, 4),
              promotion: "q",
          });
          this.board.position(this.game.fen());
          // Check for game-ending conditions
          if (this.game.in_checkmate()) {
              const winner = this.currentPlayer === "white" ? "Black" : "White";
              this.endGame(`${winner} wins by checkmate!`, winner === this.playerColor ? "win" : "loss");
              return;
          }
          if (this.game.in_draw() || this.game.in_stalemate() || this.game.in_threefold_repetition()) {
              this.endGame("Draw!", "draw");
              return;
          }
          this.addIncrementToCurrentPlayer();
          this.switchPlayer();
      }
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
      // Check for game-ending conditions
      if (this.game.in_checkmate()) {
          const winner = this.currentPlayer === "white" ? "White" : "Black";
          this.endGame(`${winner} wins by checkmate!`, winner === this.playerColor ? "win" : "loss");
          return;
      }
      if (this.game.in_draw() || this.game.in_stalemate() || this.game.in_threefold_repetition()) {
          this.endGame("Draw!", "draw");
          return;
      }
      this.addIncrementToCurrentPlayer();
      this.switchPlayer();
  }

  switchPlayer() {
      super.switchPlayer();
      if (this.currentPlayer !== this.playerColor) {
          this.makeAIMove();
      }
  }
}

// Export the class for use in other files
export default AIGame;