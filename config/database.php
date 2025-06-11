<?php
class Database {
    private static $instance = null;
    private $connection = null;
    
    private function __construct() {
        try {
            // For XAMPP, we use socket connection instead of TCP
            $dsn = sprintf(
                "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=%s;charset=utf8mb4",
                DB_NAME
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            // For XAMPP, we don't need a password by default
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Set timezone to match PHP's timezone
            $timezone = date('P');
            $this->connection->exec("SET time_zone='$timezone'");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw new Exception('Database connection failed');
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning of the instance
    private function __clone() {}
    
    // Prevent unserializing of the instance
    public function __wakeup() {}
    
    public function __destruct() {
        $this->connection = null;
        self::$instance = null;
    }
} 