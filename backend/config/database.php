<?php
// Define constants
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600);
}
if (!defined('MAX_LOGIN_ATTEMPTS')) {
    define('MAX_LOGIN_ATTEMPTS', 5);
}

class Database {
    private $host = '127.0.0.1';
    private $db_name = 'db_data';
    private $username = 'root';
    private $password = 'Hoang@2005';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}
?>