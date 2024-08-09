<?php
// Guard clause to prevent redefinition
if (!class_exists('Database')) {
    class Database {
        private $host; // Database host
        private $db_name; // Database name
        private $username; // Database username
        private $password; // Database password
        private $conn; // PDO connection

        // Maintain the instance of the class (singleton)
        private static $instance = null;

        // Constructor accepts arguments for connection parameters
        private function __construct($host, $db_name, $username, $password) {
            $this->host = $host;
            $this->db_name = $db_name;
            $this->username = $username;
            $this->password = $password;
        }

        // Method to get the unique instance of the class (singleton)
        public static function getInstance($host = 'localhost', $db_name = 'chessapp', $username = 'pepe', $password = 'pepe') {
            if (self::$instance === null) {
                self::$instance = new Database($host, $db_name, $username, $password);
            }
            return self::$instance;
        }

        // Method to establish the database connection
        public function connect() {
            if ($this->conn === null) {
                try {
                    $this->conn = new PDO(
                        "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                        $this->username,
                        $this->password
                    );
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException $e) {
                    echo "Connection error: " . $e->getMessage();
                }
            }
            return $this->conn;
        }

        // Prevent the instance from being cloned
        private function __clone() {}

        // Prevent the instance from being unserialized
        public function __wakeup() {
            throw new Exception("Cannot unserialize a singleton.");
        }
    }
}
?>