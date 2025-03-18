<?php

namespace App\Services;

use App\Models\User;
use App\Services\AuditService;

class AuthService {
    private $userModel;
    private $auditService;
    
    public function __construct() {
        $this->userModel = new User();
        $this->auditService = new AuditService();
    }
    
    public function login($email, $password) {
        // Get user by email
        $user = $this->userModel->getUserByEmail($email);
        
        // Check if user exists and password is correct
        if ($user && password_verify($password, $user->password)) {
            // Set session variables
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->username;
            $_SESSION['user_email'] = $user->email;
            // Default to 'admin' role if not specified
            $_SESSION['user_role'] = $user->role ?? 'admin';
            
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
        
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function hasPermission($permission) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $role = $_SESSION['user_role'];
        
        // Define role-based permissions
        $permissions = [
            'admin' => [
                'dashboard.view',
                'employees.read', 'employees.create', 'employees.update', 'employees.delete',
                'departments.read', 'departments.create', 'departments.update', 'departments.delete',
                'attendance.read', 'attendance.create', 'attendance.update', 'attendance.delete',
                'devices.read', 'devices.create', 'devices.update', 'devices.delete',
                'reports.read'
            ],
            'manager' => [
                'dashboard.view',
                'employees.read',
                'departments.read',
                'attendance.read', 'attendance.create', 'attendance.update',
                'devices.read',
                'reports.read'
            ],
            'employee' => [
                'dashboard.view',
                'attendance.read'
            ]
        ];
        
        return in_array($permission, $permissions[$role] ?? []);
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        // Changed from findById to getUserById to match the method name in User model
        return $this->userModel->getUserById($_SESSION['user_id']);
    }
} 