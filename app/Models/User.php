<?php

namespace App\Models;

use App\Config\Database;

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllUsers() {
        $stmt = $this->db->query("
            SELECT u.*, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY u.username ASC
        ");
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getUserById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id
        ");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function getUserByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function create($data) {
        // Check if role_id exists in roles table
        $roleExists = false;
        if (isset($data['role_id'])) {
            $checkRole = $this->db->prepare("SELECT COUNT(*) FROM roles WHERE id = :role_id");
            $checkRole->bindParam(':role_id', $data['role_id'], \PDO::PARAM_INT);
            $checkRole->execute();
            $roleExists = ($checkRole->fetchColumn() > 0);
        }
        
        // If role_id doesn't exist, get the default employee role
        if (!$roleExists) {
            $getDefaultRole = $this->db->prepare("SELECT id FROM roles WHERE name = :role_name LIMIT 1");
            $roleName = 'employee'; // Default role
            $getDefaultRole->bindParam(':role_name', $roleName, \PDO::PARAM_STR);
            $getDefaultRole->execute();
            $defaultRole = $getDefaultRole->fetch(\PDO::FETCH_OBJ);
            
            if ($defaultRole) {
                $data['role_id'] = $defaultRole->id;
            } else {
                // If no roles exist, create a basic employee role
                $this->db->exec("INSERT INTO roles (name) VALUES ('employee')");
                $data['role_id'] = $this->db->lastInsertId();
            }
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, role_id)
            VALUES (:username, :email, :password, :role_id)
        ");
        
        $stmt->bindParam(':username', $data['username'], \PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], \PDO::PARAM_STR);
        $stmt->bindParam(':password', $data['password'], \PDO::PARAM_STR);
        $stmt->bindParam(':role_id', $data['role_id'], \PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $query = "UPDATE users SET ";
        $params = [];
        
        if (isset($data['username'])) {
            $query .= "username = :username, ";
            $params[':username'] = $data['username'];
        }
        
        if (isset($data['email'])) {
            $query .= "email = :email, ";
            $params[':email'] = $data['email'];
        }
        
        if (isset($data['password'])) {
            $query .= "password = :password, ";
            $params[':password'] = $data['password'];
        }
        
        if (isset($data['role_id'])) {
            // Check if role_id exists in roles table
            $checkRole = $this->db->prepare("SELECT COUNT(*) FROM roles WHERE id = :check_role_id");
            $checkRole->bindParam(':check_role_id', $data['role_id'], \PDO::PARAM_INT);
            $checkRole->execute();
            $roleExists = ($checkRole->fetchColumn() > 0);
            
            if ($roleExists) {
                $query .= "role_id = :role_id, ";
                $params[':role_id'] = $data['role_id'];
            }
        }
        
        // Remove trailing comma and space
        $query = rtrim($query, ", ");
        
        $query .= " WHERE id = :id";
        $params[':id'] = $id;
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function countUsers() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return $stmt->fetchColumn();
    }
    
    public function authenticate($email, $password) {
        $user = $this->getUserByEmail($email);
        
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        
        return false;
    }
}