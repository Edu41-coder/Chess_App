<?php
// Guard clause to prevent redefinition
if (!class_exists('Database')) {
    class Database {
        private const HOST = 'localhost';
        private const DB_NAME = 'chessapp';
        private const USERNAME = 'pepe';
        private const PASSWORD = 'pepe';
        
        private static $instance = null;
        private $conn;

        // Private constructor to prevent multiple instances
        private function __construct() {
            try {
                $this->conn = new PDO(
                    "mysql:host=" . self::HOST . ";dbname=" . self::DB_NAME,
                    self::USERNAME,
                    self::PASSWORD
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo "Connection error: " . $e->getMessage();
            }
        }

        // Public method to get the instance of the Database
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new Database();
            }
            return self::$instance;
        }

        // Public method to get the connection
        public function getConnection() {
            return $this->conn;
        }

        // Destructor to close the connection
        public function __destruct() {
            $this->conn = null;
        }
    }
}
?>