<?php

namespace App\Helpers;

class Session {
    /**
     * Start the session
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Set a session variable
     * 
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get a session variable
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if a session variable exists
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove a session variable
     * 
     * @param string $key
     */
    public static function remove($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Clear all session variables
     */
    public static function clear() {
        self::start();
        session_unset();
    }
    
    /**
     * Destroy the session
     */
    public static function destroy() {
        self::start();
        session_unset();
        session_destroy();
    }
    
    /**
     * Set a flash message
     * 
     * @param string $key
     * @param mixed $value
     */
    public static function setFlash($key, $value) {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }
    
    /**
     * Get a flash message
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getFlash($key, $default = null) {
        self::start();
        $value = $_SESSION['_flash'][$key] ?? $default;
        if (isset($_SESSION['_flash'][$key])) {
            unset($_SESSION['_flash'][$key]);
        }
        return $value;
    }
    
    /**
     * Check if a flash message exists
     * 
     * @param string $key
     * @return bool
     */
    public static function hasFlash($key) {
        self::start();
        return isset($_SESSION['_flash'][$key]);
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get the current user ID
     * 
     * @return int|null
     */
    public static function getUserId() {
        self::start();
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get the current user role
     * 
     * @return string|null
     */
    public static function getUserRole() {
        self::start();
        return $_SESSION['user_role'] ?? null;
    }
    
    /**
     * Check if the current user has a specific role
     * 
     * @param string|array $roles
     * @return bool
     */
    public static function hasRole($roles) {
        $userRole = self::getUserRole();
        
        if (!$userRole) {
            return false;
        }
        
        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }
        
        return $userRole === $roles;
    }
} 