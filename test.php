<?php
require_once 'vendor/autoload.php';

// Test the BaseModel class
try {
    $baseModel = new \App\Models\BaseModel();
    echo "BaseModel class loaded successfully!";
} catch (Exception $e) {
    echo "Error loading BaseModel: " . $e->getMessage();
} 