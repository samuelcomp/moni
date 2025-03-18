<?php

namespace App\Middleware;

use App\Services\AuthService;

class AuthMiddleware {
    private $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    public function handle() {
        if (!$this->authService->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        return true;
    }
    
    public function handleWithPermission($permission) {
        if (!$this->authService->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        if (!$this->authService->hasPermission($permission)) {
            $_SESSION['error'] = 'You do not have permission to access this resource.';
            header('Location: /dashboard');
            exit;
        }
        
        return true;
    }
} 