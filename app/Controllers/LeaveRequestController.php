<?php

namespace App\Controllers;

class LeaveRequestController {
    private $db;
    
    public function __construct() {
        $this->db = \App\Config\Database::getInstance()->getConnection();
    }
    
    public function index() {
        // Display all leave requests
        echo json_encode(['status' => 'success', 'message' => 'Leave requests functionality coming soon']);
    }
    
    // Add other leave request-related methods as needed
} 