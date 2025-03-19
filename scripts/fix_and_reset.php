<?php
// Script to fix issues and reset the system

echo "Starting system fix and reset...\n";

// 1. Kill any running processes
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "Killing PHP processes...\n";
    exec("taskkill /F /IM php.exe 2>NUL", $output, $return);
    
    echo "Killing Python processes...\n";
    exec("taskkill /F /IM python.exe 2>NUL", $output, $return);
} else {
    echo "Killing PHP processes...\n";
    exec("pkill -9 php 2>/dev/null", $output, $return);
    
    echo "Killing Python processes...\n";
    exec("pkill -9 python 2>/dev/null", $output, $return);
}

// 2. Clear session files
echo "Clearing session files...\n";
$sessionPath = session_save_path();
if (!empty($sessionPath) && is_dir($sessionPath)) {
    $files = glob($sessionPath . '/sess_*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

// 3. Fix database issues
echo "Fixing database issues...\n";

try {
    // Connect to the database
    $dbConfig = include __DIR__ . '/../app/Config/database.php';
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
    $db = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the attendances table exists
    $stmt = $db->query("SHOW TABLES LIKE 'attendances'");
    if ($stmt->rowCount() == 0) {
        echo "Creating attendances table...\n";
        $sql = "CREATE TABLE IF NOT EXISTS `attendances` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `device_id` int(11) NOT NULL,
            `biometric_id` varchar(50) NOT NULL,
            `employee_id` int(11) DEFAULT NULL,
            `timestamp` datetime NOT NULL,
            `status` varchar(20) DEFAULT 'check-in',
            `punch` int(11) DEFAULT 0,
            `processed` tinyint(1) DEFAULT 0,
            `created_at` timestamp NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `device_id` (`device_id`),
            KEY `employee_id` (`employee_id`),
            KEY `biometric_id` (`biometric_id`),
            KEY `timestamp` (`timestamp`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $db->exec($sql);
    }
    
    echo "Database fixes applied successfully.\n";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

// 4. Wait for processes to fully terminate
sleep(3);

echo "\nSystem fix and reset completed.\n";
echo "Please follow these steps:\n";
echo "1. Close all browser windows completely\n";
echo "2. Open a new browser window\n";
echo "3. Clear your browser cache and cookies for localhost\n";
echo "4. Restart the PHP server with: php -S localhost:8000 -t public\n";
echo "5. Try accessing the application again\n"; 