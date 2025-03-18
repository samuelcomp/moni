<?php

namespace App\Controllers;

use App\Services\AuthService;

class HomeController {
    private $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    public function index() {
        // If user is logged in, redirect to dashboard
        if ($this->authService->isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }
        
        // Otherwise redirect to login
        header('Location: /login');
        exit;
    }
} 