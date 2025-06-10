<?php
require_once 'config/config.php';
require_once 'config/database.php';

try {
    $db = new Database();
    $connection = $db->connect();
    echo "Database connection successful!\n";
    
    // Test query
    $stmt = $connection->query("SELECT 1");
    $result = $stmt->fetch();
    echo "Test query successful!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 