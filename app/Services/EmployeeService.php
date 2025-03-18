<?php

namespace App\Services;

use App\Config\Database;

class EmployeeService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getEmployeeByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT e.*, d.name as department_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE e.user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function getEmployeeById($id) {
        $stmt = $this->db->prepare("
            SELECT e.*, d.name as department_name, u.username, u.email
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE e.id = :id
        ");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function countEmployees() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM employees");
        return $stmt->fetchColumn();
    }
    
    public function countEmployeesByDepartment($departmentId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM employees WHERE department_id = :department_id
        ");
        $stmt->bindParam(':department_id', $departmentId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
    
    public function getAllEmployees($page = 1, $limit = 20, $search = null) {
        $offset = ($page - 1) * $limit;
        
        $query = "
            SELECT e.*, d.name as department_name, u.username, u.email
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($search) {
            $query .= " AND (e.first_name LIKE :search OR e.last_name LIKE :search OR e.employee_id LIKE :search)";
            $searchParam = "%$search%";
            $params[':search'] = $searchParam;
        }
        
        $query .= " ORDER BY e.id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
} 