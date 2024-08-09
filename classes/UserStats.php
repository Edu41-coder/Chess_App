<?php
require_once 'Database.php';

class UserStats {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function updateStats($user_id, $result, $gameMode) {
        $win = $result === 'win' ? 1 : 0;
        $loss = $result === 'loss' ? 1 : 0;
        $draw = $result === 'draw' ? 1 : 0;

        // Debugging: Log the values being bound
        error_log("Updating stats for user_id: $user_id, win: $win, loss: $loss, draw: $draw, gameMode: $gameMode");

        if ($gameMode === 'ai') {
            $stmt = $this->db->prepare("UPDATE user_stats SET 
                                        games_against_stockfish = games_against_stockfish + 1, 
                                        wins_against_stockfish = wins_against_stockfish + :win, 
                                        losses_against_stockfish = losses_against_stockfish + :loss, 
                                        draws_against_stockfish = draws_against_stockfish + :draw 
                                        WHERE user_id = :user_id");
        } else {
            $stmt = $this->db->prepare("UPDATE user_stats SET 
                                        games_against_players = games_against_players + 1, 
                                        wins_against_players = wins_against_players + :win, 
                                        losses_against_players = losses_against_players + :loss, 
                                        draws_against_players = draws_against_players + :draw 
                                        WHERE user_id = :user_id");
        }

        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':win', $win);
        $stmt->bindParam(':loss', $loss);
        $stmt->bindParam(':draw', $draw);

        // Debugging: Log the SQL query
        error_log("Executing query: " . $stmt->queryString);

        if ($stmt->execute()) {
            // Debugging: Log the number of affected rows
            error_log("Rows affected: " . $stmt->rowCount());
            return true;
        } else {
            // Debugging: Log any errors
            error_log("Error executing query: " . implode(", ", $stmt->errorInfo()));
            return false;
        }
    }

    public static function getStats($user_id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM user_stats WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Add more stats-related methods as needed
}
?>