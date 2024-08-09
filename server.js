const WebSocket = require("ws");
const Chat = require("./js/Chat");

const wss = new WebSocket.Server({ port: 8080 });
const chat = new Chat();

let games = [];
let gameIdCounter = 1;

wss.on("connection", (ws) => {
  console.log("New connection established");
  ws.send("Welcome to the ChessApp WebSocket server!");

  ws.on("message", (message) => {
    console.log("Received message:", message);
    let parsedMessage;
    try {
      parsedMessage = JSON.parse(message);
    } catch (e) {
      console.error("Invalid JSON:", message);
      return;
    }

    switch (parsedMessage.type) {
      case "create":
        handleCreateGame(ws, parsedMessage);
        break;
      case "list":
        handleListGames(ws);
        break;
      case "join":
        handleJoinGame(ws, parsedMessage);
        break;
      case "move":
        handleMove(ws, parsedMessage);
        break;
      case "resign":
        handleResign(ws, parsedMessage);
        break;
      case "chat":
        handleChat(ws, parsedMessage);
      default:
        console.error("Unknown message type:", parsedMessage.type);
    }
  });

  ws.on("close", () => {
    handlePlayerDisconnect(ws);
  });
});
function handleChat(ws, message) {
  console.log("Processing chat message...");
  const game = games.find((g) => g.id === parseInt(message.gameId));
  if (game) {
    const player = game.players.find((p) => p.socket === ws);
    if (player) {
      const chatMessage = chat.addMessage(
        message.gameId,
        player.color,
        message.text
      );
      game.players.forEach((p) => {
        p.socket.send(JSON.stringify({ type: "chat", message: chatMessage }));
      });
    } else {
      ws.send(
        JSON.stringify({
          type: "error",
          message: "You are not part of this game",
        })
      );
    }
  } else {
    ws.send(JSON.stringify({ type: "error", message: "Game not found" }));
  }
}

function handleCreateGame(ws, message) {
  console.log("Creating a new game...");
  const newGame = {
    id: gameIdCounter++,
    players: [{ color: message.color, socket: ws }],
    moves: [],
    startTime: null,
    currentTurn: "white",
  };
  games.push(newGame);
  console.log("New game created with ID:", newGame.id);
  ws.send(JSON.stringify({ type: "gameCreated", gameId: newGame.id }));
  broadcastGamesList();
}

function handleListGames(ws) {
  console.log("Listing games...");
  sendGamesList(ws);
}
function handleJoinGame(ws, message) {
  console.log("Joining game with ID:", message.gameId);
  const game = games.find((g) => g.id === parseInt(message.gameId));
  if (game) {
    if (game.players.length < 2) {
      const joinedColor = game.players[0].color === "white" ? "black" : "white";
      game.players.push({ color: joinedColor, socket: ws });
      ws.send(
        JSON.stringify({ type: "joined", gameId: game.id, color: joinedColor })
      );
      if (game.players.length === 2) {
        game.startTime = Date.now();
        game.players.forEach((player) => {
          player.socket.send(
            JSON.stringify({
              type: "opponentJoined",
              gameId: game.id,
              startTime: game.startTime,
            })
          );
        });
      }
      broadcastGamesList();
    } else {
      ws.send(JSON.stringify({ type: "error", message: "Game is full" }));
    }
  } else {
    ws.send(JSON.stringify({ type: "error", message: "Game not found" }));
  }
}

function handleMove(ws, message) {
  console.log("Processing move...");
  const game = games.find((g) => g.id === parseInt(message.gameId));
  if (game) {
    const player = game.players.find((p) => p.socket === ws);
    if (player && player.color === game.currentTurn) {
      game.moves.push(message.move);
      game.currentTurn = game.currentTurn === "white" ? "black" : "white";
      game.players.forEach((p) => {
        if (p.socket !== ws) {
          p.socket.send(JSON.stringify({ type: "move", move: message.move }));
        }
      });
    } else {
      ws.send(JSON.stringify({ type: "error", message: "Not your turn" }));
    }
  } else {
    ws.send(JSON.stringify({ type: "error", message: "Game not found" }));
  }
}

function handleResign(ws, message) {
  console.log("Player resigning...");
  const game = games.find((g) => g.id === parseInt(message.gameId));
  if (game) {
    const resigningPlayer = game.players.find((p) => p.socket === ws);
    const opponent = game.players.find((p) => p.socket !== ws);
    if (resigningPlayer && opponent) {
      opponent.socket.send(JSON.stringify({ type: "opponentResigned" }));
      endGame(game, opponent.color);
    }
  }
}

function handlePlayerDisconnect(ws) {
  games = games.filter((game) => {
    const playerIndex = game.players.findIndex(
      (player) => player.socket === ws
    );
    if (playerIndex !== -1) {
      game.players.splice(playerIndex, 1);
      if (game.players.length === 1) {
        game.players[0].socket.send(JSON.stringify({ type: "opponentLeft" }));
        endGame(game, game.players[0].color);
      }
      return game.players.length > 0;
    }
    return true;
  });
  broadcastGamesList();
}

function endGame(game, winnerColor) {
  // Handle any necessary cleanup or statistics recording
  games = games.filter((g) => g.id !== game.id);
  broadcastGamesList();
}
function sendGamesList(ws) {
  const gamesList = games.map((game) => ({
    id: game.id,
    players: game.players.length,
    availableColor:
      game.players.length === 1
        ? game.players[0].color === "white"
          ? "black"
          : "white"
        : null,
  }));
  ws.send(JSON.stringify({ type: "gamesList", games: gamesList }));
}

function broadcastGamesList() {
  const gamesList = games.map((game) => ({
    id: game.id,
    players: game.players.length,
    availableColor:
      game.players.length === 1
        ? game.players[0].color === "white"
          ? "black"
          : "white"
        : null,
  }));
  wss.clients.forEach((client) => {
    if (client.readyState === WebSocket.OPEN) {
      client.send(JSON.stringify({ type: "gamesList", games: gamesList }));
    }
  });
}
console.log("WebSocket server is running on ws://localhost:8080");
