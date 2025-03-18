<?php

// Show all errors during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set timezone
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'UTC');

// Define application environment
define('DEVELOPMENT_MODE', true); // Set to false in production

// Start the application
require_once __DIR__ . '/../routes/web.php'; 