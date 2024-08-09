<?php
class Move {
    private $db;

    public function __construct() {
        // Get the database instance and establish the connection
        $database = Database::getInstance();
        $this->db = $database->connect();
    }

    public function recordMove($game_id, $player_id, $move, $move_number) {
        $stmt = $this->db->prepare("INSERT INTO moves (game_id, player_id, move, move_number) VALUES (:game_id, :player_id, :move, :move_number)");
        $stmt->bindParam(':game_id', $game_id);
        $stmt->bindParam(':player_id', $player_id);
        $stmt->bindParam(':move', $move);
        $stmt->bindParam(':move_number', $move_number);
        return $stmt->execute();
    }

    public function getMovesByGameId($game_id) {
        $stmt = $this->db->prepare("SELECT * FROM moves WHERE game_id = :game_id ORDER BY move_number ASC");
        $stmt->bindParam(':game_id', $game_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add more move-related methods as needed
}
?>