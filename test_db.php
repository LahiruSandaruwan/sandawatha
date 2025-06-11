<?php
require_once __DIR__ . '/config/config.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Show all tables
    $stmt = $pdo->query("SHOW TABLES");
    echo "Tables in database:\n";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- {$row[0]}\n";
    }
    
    // Check if user_profiles table exists
    $stmt = $pdo->query("
        SELECT COUNT(*) as table_exists 
        FROM information_schema.tables 
        WHERE table_schema = '" . DB_NAME . "' 
        AND table_name = 'user_profiles'
    ");
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['table_exists'] > 0) {
        echo "\nuser_profiles table exists.\n";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE user_profiles");
        echo "\nTable structure:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} - {$row['Type']} ";
            echo $row['Null'] === 'NO' ? 'NOT NULL' : 'NULL';
            if ($row['Key'] === 'PRI') echo ' PRIMARY KEY';
            if ($row['Default'] !== null) echo " DEFAULT '{$row['Default']}'";
            echo "\n";
        }
    } else {
        echo "\nuser_profiles table does NOT exist!\n";
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?> 