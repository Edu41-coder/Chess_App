<?php
class User
{
    private $db;

    // Update constructor to accept a database connection
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createUser($username, $password)
    {
        try {
            // Check if the username already exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Username already exists.");
            }

            // Insert the new user
            $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password); // No password hashing
            $stmt->execute();

            // Get the user ID of the newly created user
            $user_id = $this->db->lastInsertId();

            // Insert a row into the user_stats table for the new user
            $stmt = $this->db->prepare("INSERT INTO user_stats (user_id) VALUES (:user_id)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            return true; // Return true on success
        } catch (Exception $e) {
            // Log the exception message (optional)
            error_log($e->getMessage());
            return false; // Return false on failure
        }
    }

    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUsernameById($id)
    {
        $stmt = $this->db->prepare("SELECT username FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Add more user-related methods as needed
}
?>