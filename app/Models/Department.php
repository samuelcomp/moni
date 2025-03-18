<?php

namespace App\Models;

use App\Config\Database;

class Department {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllDepartments() {
        $stmt = $this->db->query("
            SELECT d.*, COUNT(e.id) as employee_count
            FROM departments d
            LEFT JOIN employees e ON d.id = e.department_id
            GROUP BY d.id
            ORDER BY d.name ASC
        ");
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getDepartmentById($id) {
        $stmt = $this->db->prepare("SELECT * FROM departments WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO departments (name, code)
            VALUES (:name, :code)
        ");
        
        $stmt->bindParam(':name', $data['name'], \PDO::PARAM_STR);
        $stmt->bindParam(':code', $data['code'], \PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $query = "UPDATE departments SET ";
        $params = [];
        
        if (isset($data['name'])) {
            $query .= "name = :name, ";
            $params[':name'] = $data['name'];
        }
        
        if (isset($data['code'])) {
            $query .= "code = :code, ";
            $params[':code'] = $data['code'];
        }
        
        if (isset($data['manager_id'])) {
            $query .= "manager_id = :manager_id, ";
            $params[':manager_id'] = $data['manager_id'];
        }
        
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
        $stmt = $this->db->prepare("DELETE FROM departments WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function countDepartments() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM departments");
        return $stmt->fetchColumn();
    }
    
    public function getDepartmentsWithEmployeeCount() {
        $stmt = $this->db->query("
            SELECT d.*, COUNT(e.id) as employee_count
            FROM departments d
            LEFT JOIN employees e ON d.id = e.department_id
            GROUP BY d.id
            ORDER BY d.name ASC
        ");
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
} 