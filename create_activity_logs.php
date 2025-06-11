<?php
require_once __DIR__ . '/config/config.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Create activity_logs table
    $sql = "
    CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "activity_logs table created successfully!\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
} 