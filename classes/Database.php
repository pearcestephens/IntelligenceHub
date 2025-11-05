<?php
/**
 * Database Connection Class
 * Provides PDO connection to MySQL database
 */

class Database {
    private $host = '127.0.0.1';
    private $db_name = 'hdgwrzntwa';
    private $username = 'hdgwrzntwa';
    private $password = 'bFUdRjh4Jx';
    private $port = 3306;
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
