import ChessGame from "./chessGame.js";
class OnlineGame extends ChessGame {
  constructor(boardId, playerColor, socketUrl) {
    super(
      boardId,
      { white: "white-timer", black: "black-timer" },
      10,
      5,
      "online",
      playerColor
    );
    this.socket = new WebSocket(socketUrl);
    this.setupSocketListeners();
    this.gameId = null;
    this.opponentColor = playerColor === "white" ? "black" : "white";
    this.messageQueue = [];
    this.isConnected = false;
  }
  setupSocketListeners() {
    this.socket.onopen = (event) => this.onSocketOpen(event);
    this.socket.onmessage = (event) => this.handleMessage(event.data);
    this.socket.onclose = (event) => this.onSocketClose(event);
    this.socket.onerror = (error) => this.onSocketError(error);
  }
  handleMessage(message) {
    if (typeof message === "string" && message.startsWith("Welcome")) {
      console.log("Received welcome message:", message);
      return;
    }
    let data;
    try {
      data = JSON.parse(message);
    } catch (error) {
      console.log("Received non-JSON message:", message);
      return;
    }

    console.log("Received message:", data);

    switch (data.type) {
      case "gameCreated":
        this.gameId = data.gameId;
        console.log("New game created with ID:", this.gameId);
        this.refreshGamesList();
        break;
      case "gamesList":
        this.updateGamesList(data.games);
        break;
      case "joined":
        console.log("Successfully joined game:", data.gameId);
        this.gameId = parseInt(data.gameId);
        this.playerColor = data.color; // Update the player's color
        this.opponentColor = this.playerColor === "white" ? "black" : "white";
        break;
      case "opponentJoined":
        console.log("Opponent joined the game");
        this.startGameWithOpponent(data.startTime);
        break;
      case "move":
        this.handleOpponentMove(data.move);
        break;
      case "opponentLeft":
        this.handleOpponentLeft();
        break;
      case "opponentResigned":
        this.handleOpponentResigned();
        break;
      case "chat":
        this.displayChatMessage(data.message);
        break;
      case "error":
        console.error("Server error:", data.message);
        alert("Error: " + data.message);
        this.resetGameState();
        break;
      default:
        console.log("Unhandled message type:", data.type);
    }
  }
  sendChatMessage(text) {
    this.sendMessage({
      type: "chat",
      gameId: this.gameId,
      text: text,
    });
  }
  displayChatMessage(message) {
    const chatMessages = document.getElementById("chatMessages");
    const newMessage = document.createElement("div");
    newMessage.textContent = `${message.playerColor}: ${message.text}`;
    chatMessages.appendChild(newMessage);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }
  onDragStart(source, piece, position, orientation) {
    if (!this.opponentJoined) {
      return false;
    }
    return super.onDragStart(source, piece, position, orientation);
  }
  onDrop(source, target) {
    if (this.game.turn() !== this.playerColor[0]) {
      return "snapback";
    }

    const move = this.game.move({
      from: source,
      to: target,
      promotion: "q",
    });

    if (move === null) return "snapback";

    this.sendMove(move);
    this.updateGameState();
    return null;
  }
  sendMove(move) {
    this.sendMessage({
      type: "move",
      gameId: this.gameId,
      move: move,
    });
  }
  updateTurnIndicator() {
    super.updateTurnIndicator();
    // Add any online-specific turn indicator logic here if needed
  }
  updateUI(gameStarted) {
    super.updateUI(gameStarted);
    document.getElementById("onlineGameOptions").style.display = gameStarted
      ? "none"
      : "block";
    document.getElementById("startGame").style.display = "none";
    document.getElementById("createOnlineGame").disabled = gameStarted;
    document.getElementById("joinOnlineGame").disabled = gameStarted;
    document.getElementById("existingGames").disabled = gameStarted;
    document.getElementById("resignGame").style.display = gameStarted
      ? "inline-block"
      : "none";
    document.getElementById("refreshGames").disabled = gameStarted;
  }
  syncTimer(startTime) {
    const currentTime = Date.now();
    const elapsedTime = Math.floor((currentTime - startTime) / 1000);
    const remainingTime = Math.max(this.gameLength * 60 - elapsedTime, 0);

    this.whiteTime = remainingTime;
    this.blackTime = remainingTime;

    this.updateTimerDisplay();
    this.startTimer();
  }
  sendMessage(message) {
    if (this.isConnected) {
      try {
        this.socket.send(JSON.stringify(message));
      } catch (error) {
        console.error("Error sending message:", error);
        this.messageQueue.push(message);
      }
    } else {
      this.messageQueue.push(message);
    }
  }
  startGame() {
    // This method is intentionally left empty to prevent starting the game prematurely
    console.log("Waiting for opponent to join...");
  }
  startOnlineGame() {
    super.startGame();
    console.log("Online game initialized");
    this.updateUI(true);
  }
  createNewGame() {
    console.log("Creating a new online game...");
    this.sendMessage({ type: "create", color: this.playerColor });
  }
  refreshGamesList() {
    console.log("Refreshing games list...");
    this.sendMessage({ type: "list" });
  }
  joinGame(gameId) {
    console.log("Joining game with ID:", gameId);
    this.sendMessage({
      type: "join",
      gameId: gameId,
    });
  }
  onConnectionEstablished() {
    // Perform any actions that need to happen right after connection is established
    this.refreshGamesList();
  }
  onSocketOpen(event) {
    console.log("[open] Connection established");
    this.isConnected = true;
    this.messageQueue.forEach((message) => this.sendMessage(message));
    this.messageQueue = [];
    this.onConnectionEstablished();
  }

  onSocketClose(event) {
    this.isConnected = false;
    if (event.wasClean) {
      console.log(
        `[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`
      );
    } else {
      console.log("[close] Connection died");
    }
    this.handleDisconnection();
  }

  onSocketError(error) {
    console.log(`[error] ${error.message}`);
    alert("Connection error. Please try again later.");
  }

  startGameWithOpponent(startTime) {
    super.startGame(); // Call the parent class's startGame method
    this.opponentJoined = true;
    this.board.orientation(this.playerColor);
    this.updateTurnIndicator();
    this.syncTimer(startTime);
    this.updateUI(true);
    console.log("Game started with opponent");
  }
  startGameWithOpponent(startTime) {
    super.startGame(); // Call the parent class's startGame method
    this.opponentJoined = true;
    this.board.orientation(this.playerColor);
    this.updateTurnIndicator();
    this.syncTimer(startTime);
    this.updateUI(true);
    console.log("Game started with opponent");
  }
  startGameWithOpponent(startTime) {
    super.startGame(); // Call the parent class's startGame method
    this.opponentJoined = true;
    this.board.orientation(this.playerColor);
    this.updateTurnIndicator();
    this.syncTimer(startTime);
    this.updateUI(true);
    console.log("Game started with opponent");
  }
  startGameWithOpponent(startTime) {
    super.startGame(); // Call the parent class's startGame method
    this.opponentJoined = true;
    this.board.orientation(this.playerColor);
    this.updateTurnIndicator();
    this.syncTimer(startTime);
    this.updateUI(true);
    console.log("Game started with opponent");
  }
  startGameWithOpponent(startTime) {
    super.startGame(); // Call the parent class's startGame method
    this.opponentJoined = true;
    this.board.orientation(this.playerColor);
    this.updateTurnIndicator();
    this.syncTimer(startTime);
    this.updateUI(true);
    console.log("Game started with opponent");
  }
  handleOpponentMove(move) {
    const result = this.game.move(move);
    if (result) {
      this.board.position(this.game.fen());
      this.updateGameState();
      this.switchPlayer();
      this.updateTurnIndicator();
    }
  }

  updateGamesList(games) {
    const select = document.getElementById("existingGames");
    select.innerHTML = '<option value="">Select a game to join</option>';
    games.forEach((game) => {
      const option = document.createElement("option");
      option.value = game.id;
      option.textContent = `Game ${game.id} (${game.availableColor || "Full"})`;
      select.appendChild(option);
    });
  }
  updateGameState() {
    super.updateGameState();
    // Add any online-specific game state update logic here if needed
  }

  handleOpponentLeft() {
    console.log("Opponent left the game");
    alert("Your opponent has left the game. You win!");
    this.endGame("Opponent left", "win");
    this.updateUI(false);
  }

  handleOpponentResigned() {
    console.log("Opponent resigned the game");
    alert("Your opponent has resigned. You win!");
    this.endGame("Opponent resigned", "win");
    this.updateUI(false);
  }

  handleDisconnection() {
    console.log("Disconnected from server");
    alert("You have been disconnected from the server. The game has ended.");
    this.resetGameState();
    this.updateUI(false);
  }

  resetGameState() {
    this.gameId = null;
    this.opponentJoined = false;
    this.game.reset();
    this.board.position("start");
    this.refreshGamesList();
    this.updateUI(false);
  }
  endGame(reason, result) {
    super.endGame(reason, result);

    // Online-specific cleanup
    this.opponentJoined = false;
    this.gameId = null;

    // Refresh the games list
    this.refreshGamesList();

    // Update the UI for the ended game state
    this.updateUI(false);

    console.log(`Online game ended. Reason: ${reason}, Result: ${result}`);
  }
  resignGame() {
    super.endGame("You resigned", "loss");
    this.sendMessage({ type: "resign", gameId: this.gameId });
  }

  resetGame() {
    super.resetGame();
    this.opponentJoined = false;
    this.gameId = null;
    this.refreshGamesList();
    this.updateUI(false);
  }
  resetUI() {
    super.resetUI(); // Call the parent class resetUI method

    // Reset online-specific UI elements
    document.getElementById("onlineGameOptions").style.display = "block";
    document.getElementById("createOnlineGame").disabled = false;
    document.getElementById("joinOnlineGame").disabled = false;
    document.getElementById("existingGames").disabled = false;
    document.getElementById("startGame").style.display = "none";

    // Clear the existing games list
    const existingGamesSelect = document.getElementById("existingGames");
    existingGamesSelect.innerHTML =
      '<option value="">Select a game to join</option>';

    this.refreshGamesList(); // Refresh the list of available games
  }

  hideOnlineUI() {
    document.getElementById("onlineGameOptions").style.display = "none";
    document.getElementById("startGame").style.display = "inline-block";
  }
}

export default OnlineGame;
