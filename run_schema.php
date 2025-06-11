<?php
require_once __DIR__ . '/config/config.php';

try {
    // Get database connection
    $pdo = Database::getInstance()->getConnection();
    
    // Read schema file
    $sql = file_get_contents(__DIR__ . '/sql/schema.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', 
            preg_split("/;\s*[\r\n]+/", $sql)
        )
    );
    
    // Execute each statement
    foreach ($statements as $statement) {
        // Skip comments and empty lines
        if (empty($statement) || preg_match("/^--/", $statement)) {
            continue;
        }
        
        try {
            $stmt = $pdo->prepare($statement);
            $stmt->execute();
            while ($stmt->fetch()) {} // Consume any results
            $stmt->closeCursor();
            echo "Executed statement successfully\n";
        } catch (PDOException $e) {
            echo "Failed to execute statement: " . $e->getMessage() . "\n";
            echo "Statement: " . $statement . "\n";
        }
    }
    
    echo "\nSchema creation complete!\n";
    
} catch (Exception $e) {
    die("Database error: " . $e->getMessage() . "\n");
} 