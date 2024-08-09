<?php
class Game {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createGame($player1_id, $player2_id) {
        $stmt = $this->db->prepare("INSERT INTO games (player1_id, player2_id) VALUES (:player1_id, :player2_id)");
        $stmt->bindParam(':player1_id', $player1_id);
        $stmt->bindParam(':player2_id', $player2_id);
        return $stmt->execute();
    }

    public function getGameById($id) {
        $stmt = $this->db->prepare("SELECT * FROM games WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function joinGame($game_id, $player2_id) {
        $stmt = $this->db->prepare("UPDATE games SET player2_id = :player2_id WHERE id = :game_id AND player2_id IS NULL");
        $stmt->bindParam(':player2_id', $player2_id);
        $stmt->bindParam(':game_id', $game_id);
        return $stmt->execute();
    }

    // Add more game-related methods as needed
}
?>