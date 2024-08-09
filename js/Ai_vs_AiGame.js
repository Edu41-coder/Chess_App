import ChessGame from './chessGame.js';

class AiVsAiGame extends ChessGame {
  constructor(boardId, timers, gameLength, increment) {
      super(boardId, timers, gameLength, increment, "ai_vs_ai", "white");
      this.initializeStockfish();
  }

  initializeStockfish() {
      this.stockfishWhite = new Worker("js/stockfish.js");
      this.stockfishBlack = new Worker("js/stockfish.js");

      this.stockfishWhite.onmessage = this.onStockfishMessage.bind(this, "white");
      this.stockfishBlack.onmessage = this.onStockfishMessage.bind(this, "black");

      this.stockfishWhite.postMessage("uci");
      this.stockfishWhite.postMessage("ucinewgame");
      this.stockfishWhite.postMessage("isready");

      this.stockfishBlack.postMessage("uci");
      this.stockfishBlack.postMessage("ucinewgame");
      this.stockfishBlack.postMessage("isready");

      console.log("Stockfish instances initialized"); // Debugging statement
  }

  startGame() {
      super.startGame();
      this.makeAIMove();
  }

  makeAIMove() {
      if (this.gameOver) return; // Prevent AI moves if the game is over
      const fen = this.game.fen();
      console.log(`Sending position to Stockfish: ${fen}`); // Debugging statement

      if (this.currentPlayer === "white") {
          this.stockfishWhite.postMessage(`position fen ${fen}`);
          this.stockfishWhite.postMessage("go depth 15");
      } else {
          this.stockfishBlack.postMessage(`position fen ${fen}`);
          this.stockfishBlack.postMessage("go depth 15");
      }
  }

  onStockfishMessage(color, event) {
      if (this.gameOver) return; // Prevent processing if the game is over
      console.log(`Stockfish (${color}) message:`, event.data); // Debugging statement
      const match = event.data.match(/^bestmove\s([a-h][1-8][a-h][1-8])/);
      if (match) {
          const move = match[1];
          console.log(`AI (${color}) move:`, move); // Debugging statement
          this.game.move({
              from: move.substring(0, 2),
              to: move.substring(2, 4),
              promotion: "q",
          });
          this.board.position(this.game.fen());
          // Check for draw
          if (this.game.in_draw() || this.game.in_stalemate() || this.game.in_threefold_repetition()) {
              this.endGame("Draw!", "draw");
              return;
          }
          this.addIncrementToCurrentPlayer();
          this.switchPlayer();
          this.makeAIMove();
      }
  }

  switchPlayer() {
      super.switchPlayer();
  }
}

// Export the class for use in other files
export default AiVsAiGame;