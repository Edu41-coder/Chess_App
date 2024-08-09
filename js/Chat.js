class Chat {
    constructor() {
      this.messages = [];
    }
  
    addMessage(gameId, playerColor, text) {
      const message = {
        gameId,
        playerColor,
        text,
        timestamp: new Date().toISOString(),
      };
      this.messages.push(message);
      return message;
    }
  
    getMessages(gameId) {
      return this.messages.filter((msg) => msg.gameId === gameId);
    }
  }
  
  module.exports = Chat;