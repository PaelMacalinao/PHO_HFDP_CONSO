<?php
/**
 * Database Configuration
 * PHO CONSO HFDP Application
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'pho_conso_hfdp');

// Create database connection
class Database {
    private $conn;
    
    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            throw new Exception("Database connection failed: " . $this->conn->connect_error . ". Create the database with database/schema.sql in phpMyAdmin.");
        }
        $this->conn->set_charset("utf8mb4");
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
    
    public function getLastInsertId() {
        return $this->conn->insert_id;
    }
    
    public function close() {
        $this->conn->close();
    }
}
