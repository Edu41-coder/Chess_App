<?php
class Message {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function sendMessage($user_id, $game_id, $content) {
        $stmt = $this->db->prepare("INSERT INTO messages (user_id, game_id, content) VALUES (:user_id, :game_id, :content)");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':game_id', $game_id);
        $stmt->bindParam(':content', $content);
        return $stmt->execute();
    }

    public function canSendMessage($user_id, $game_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM messages WHERE user_id = :user_id AND game_id = :game_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':game_id', $game_id);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        return $count < 5;
    }

    public function getMessagesByGameId($game_id) {
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE game_id = :game_id ORDER BY created_at ASC");
        $stmt->bindParam(':game_id', $game_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>