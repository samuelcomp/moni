<?php
// Script to set up the database and tables

// Load environment variables
require_once __DIR__ . '/../app/Helpers/Env.php';
App\Helpers\Env::load();

echo "Starting database setup...\n";

// Get database configuration from environment variables
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'attendance_db';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

try {
    // Connect to MySQL without selecting a database
    $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create the database if it doesn't exist
    echo "Creating database if it doesn't exist...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '{$database}' created or already exists.\n";
    
    // Select the database
    $pdo->exec("USE `{$database}`");
    
    // Create users table
    echo "Creating users table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `password` varchar(255) NOT NULL,
        `role` varchar(50) DEFAULT 'user',
        `status` tinyint(1) DEFAULT 1,
        `created_at` timestamp NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Create devices table
    echo "Creating devices table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `devices` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `device_name` varchar(255) NOT NULL,
        `ip_address` varchar(45) NOT NULL,
        `port` int(11) DEFAULT 4370,
        `status` tinyint(1) DEFAULT 1,
        `created_at` timestamp NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Create employees table
    echo "Creating employees table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `employees` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `email` varchar(255) DEFAULT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `department` varchar(100) DEFAULT NULL,
        `position` varchar(100) DEFAULT NULL,
        `biometric_id` varchar(50) NOT NULL,
        `status` tinyint(1) DEFAULT 1,
        `created_at` timestamp NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `biometric_id` (`biometric_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Create attendances table
    echo "Creating attendances table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `attendances` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Create a default admin user if none exists
    echo "Creating default admin user if none exists...\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetchColumn();
    
    if ($adminCount == 0) {
        $name = 'Admin';
        $email = 'admin@example.com';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $role = 'admin';
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);
        
        echo "Default admin user created with email: {$email} and password: admin123\n";
    } else {
        echo "Admin user already exists.\n";
    }
    
    echo "Database setup completed successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 