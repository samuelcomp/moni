<?php

namespace App\Controllers;

class ShiftController {
    private $db;
    
    public function __construct() {
        $this->db = \App\Config\Database::getInstance()->getConnection();
    }
    
    public function index() {
        // Display all shifts
        echo json_encode(['status' => 'success', 'message' => 'Shifts functionality coming soon']);
    }
    
    // Add other shift-related methods as needed
} 