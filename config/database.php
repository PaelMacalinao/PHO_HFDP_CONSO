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
        // First connect without specifying a database
        $temp_conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        if ($temp_conn->connect_error) {
            throw new Exception("Database connection failed: " . $temp_conn->connect_error);
        }
        
        // Create database if it doesn't exist
        $temp_conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $temp_conn->close();
        
        // Now connect to the specific database
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            throw new Exception("Database connection failed: " . $this->conn->connect_error);
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
