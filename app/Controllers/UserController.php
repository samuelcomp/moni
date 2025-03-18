<?php

namespace App\Controllers;

class UserController {
    private $db;
    
    public function __construct() {
        $this->db = \App\Config\Database::getInstance()->getConnection();
    }
    
    public function index() {
        // Display all users
        echo json_encode(['status' => 'success', 'message' => 'Users functionality coming soon']);
    }
    
    // Add other user-related methods as needed
} 