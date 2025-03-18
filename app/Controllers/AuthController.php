<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;

class AuthController {
    private $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    public function showLoginForm() {
        // If already logged in, redirect to dashboard
        if ($this->authService->isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }
        
        // Load the login view
        require_once __DIR__ . '/../../resources/views/auth/login.php';
    }
    
    public function login() {
        // If already logged in, redirect to dashboard
        if ($this->authService->isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }
        
        // Get form data
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validate form data
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please enter both email and password';
            header('Location: /login');
            exit;
        }
        
        // Attempt to login
        if ($this->authService->login($email, $password)) {
            // Redirect to dashboard
            header('Location: /dashboard');
            exit;
        } else {
            // Set error message and redirect back to login
            $_SESSION['error'] = 'Invalid email or password';
            header('Location: /login');
            exit;
        }
    }
    
    public function logout() {
        // Logout user
        $this->authService->logout();
        
        // Redirect to login page
        header('Location: /login');
        exit;
    }
} 