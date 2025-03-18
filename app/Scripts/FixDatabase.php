<?php

require_once __DIR__ . '/../../public/index.php';

try {
    $db = \App\Config\Database::getInstance()->getConnection();
    
    echo "Starting database fix script...\n";
    
    // 1. Check if user_id is required in employees table
    $stmt = $db->prepare("DESCRIBE employees");
    $stmt->execute();
    $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    $userIdColumn = null;
    foreach ($columns as $column) {
        if ($column['Field'] === 'user_id') {
            $userIdColumn = $column;
            break;
        }
    }
    
    if ($userIdColumn) {
        echo "Found user_id column in employees table: " . json_encode($userIdColumn) . "\n";
        
        // Check if it's nullable
        if ($userIdColumn['Null'] === 'NO') {
            echo "user_id column is NOT NULL, modifying to allow NULL values...\n";
            
            // First, drop the foreign key constraint
            $stmt = $db->prepare("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'employees'
                AND COLUMN_NAME = 'user_id'
                AND REFERENCED_TABLE_NAME = 'users'
            ");
            $stmt->execute();
            $constraint = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($constraint) {
                echo "Dropping foreign key constraint: " . $constraint['CONSTRAINT_NAME'] . "\n";
                $db->exec("ALTER TABLE employees DROP FOREIGN KEY " . $constraint['CONSTRAINT_NAME']);
            }
            
            // Then modify the column to allow NULL
            $db->exec("ALTER TABLE employees MODIFY user_id INT NULL");
            
            // Re-add the foreign key constraint, but with ON DELETE SET NULL
            $db->exec("ALTER TABLE employees ADD CONSTRAINT employees_user_id_fk 
                      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
            
            echo "Modified user_id column to allow NULL values and updated foreign key constraint\n";
        } else {
            echo "user_id column already allows NULL values\n";
        }
    } else {
        echo "user_id column not found in employees table\n";
    }
    
    echo "Database fix script completed successfully\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} 