<?php

namespace App\Models;

use App\Config\Database;

class Employee {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllEmployees() {
        $stmt = $this->db->query("
            SELECT e.*, d.name as department_name, u.username as user_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN users u ON e.user_id = u.id
            ORDER BY e.first_name ASC
        ");
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getEmployeeById($id) {
        $stmt = $this->db->prepare("
            SELECT e.*, d.name as department_name, u.username as user_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE e.id = :id
        ");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function getEmployeeByEmployeeId($employeeId) {
        $stmt = $this->db->prepare("
            SELECT e.*, d.name as department_name, u.username as user_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE e.employee_id = :employee_id
        ");
        $stmt->bindParam(':employee_id', $employeeId, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
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
    
    public function createEmployee($data) {
        try {
            error_log("Employee::createEmployee called with data: " . json_encode($data));
            
            // Get the table columns to ensure we only use existing columns
            $columnsStmt = $this->db->prepare("DESCRIBE employees");
            $columnsStmt->execute();
            $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
            
            error_log("Available employee columns: " . json_encode($columns));
            
            // First check if user_id already exists (if provided)
            if (isset($data['user_id']) && !empty($data['user_id'])) {
                // Check if the user exists in the users table
                $userCheckStmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
                $userCheckStmt->execute([$data['user_id']]);
                
                if ($userCheckStmt->rowCount() === 0) {
                    error_log("User ID " . $data['user_id'] . " doesn't exist in users table");
                    // Remove user_id from data to avoid foreign key constraint violation
                    unset($data['user_id']);
                } else {
                    // User exists, check if an employee with this user_id already exists
                    $checkStmt = $this->db->prepare("SELECT id FROM employees WHERE user_id = ?");
                    $checkStmt->execute([$data['user_id']]);
                    $existingEmployee = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                    
                    if ($existingEmployee) {
                        error_log("Found existing employee with user_id " . $data['user_id'] . ": " . json_encode($existingEmployee));
                        
                        // Employee with this user_id already exists, update it instead
                        $updateFields = [];
                        $updateParams = [];
                        
                        foreach ($data as $field => $value) {
                            // Only include fields that exist in the table
                            if (in_array($field, $columns) && $field !== 'user_id') { // Skip user_id in updates
                                $updateFields[] = "$field = ?";
                                $updateParams[] = $value;
                            }
                        }
                        
                        if (!empty($updateFields)) {
                            $updateParams[] = $existingEmployee['id']; // Add ID for WHERE clause
                            $updateQuery = "UPDATE employees SET " . implode(", ", $updateFields) . " WHERE id = ?";
                            error_log("Update query: " . $updateQuery . " with params: " . json_encode($updateParams));
                            
                            $stmt = $this->db->prepare($updateQuery);
                            $stmt->execute($updateParams);
                        }
                        
                        return ['success' => true, 'id' => $existingEmployee['id'], 'updated' => true];
                    }
                }
            }
            
            // Prepare column names and placeholders for the INSERT
            $insertColumns = [];
            $placeholders = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                // Only include fields that exist in the table
                if (in_array($key, $columns) && $value !== null) {
                    $insertColumns[] = $key;
                    $placeholders[] = '?';
                    $values[] = $value;
                }
            }
            
            if (empty($insertColumns)) {
                error_log("No valid columns found for employee insert");
                return ['success' => false, 'message' => 'No valid data for employee creation'];
            }
            
            // Insert new employee
            $insertQuery = "INSERT INTO employees (" . implode(", ", $insertColumns) . ") VALUES (" . implode(", ", $placeholders) . ")";
            error_log("Insert query: " . $insertQuery . " with values: " . json_encode($values));
            
            $stmt = $this->db->prepare($insertQuery);
            $stmt->execute($values);
            
            $newId = $this->db->lastInsertId();
            error_log("New employee created with ID: " . $newId);
            
            return ['success' => true, 'id' => $newId];
        } catch (\PDOException $e) {
            error_log("Error in createEmployee: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()];
        }
    }
    
    public function update($id, $data) {
        $query = "UPDATE employees SET ";
        $params = [];
        
        if (isset($data['employee_id'])) {
            $query .= "employee_id = :employee_id, ";
            $params[':employee_id'] = $data['employee_id'];
        }
        
        if (isset($data['first_name'])) {
            $query .= "first_name = :first_name, ";
            $params[':first_name'] = $data['first_name'];
        }
        
        if (isset($data['last_name'])) {
            $query .= "last_name = :last_name, ";
            $params[':last_name'] = $data['last_name'];
        }
        
        if (isset($data['department_id'])) {
            $query .= "department_id = :department_id, ";
            $params[':department_id'] = $data['department_id'];
        }
        
        if (isset($data['position'])) {
            $query .= "position = :position, ";
            $params[':position'] = $data['position'];
        }
        
        if (isset($data['join_date'])) {
            $query .= "join_date = :join_date, ";
            $params[':join_date'] = $data['join_date'];
        }
        
        if (isset($data['user_id'])) {
            $query .= "user_id = :user_id, ";
            $params[':user_id'] = $data['user_id'];
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
        $stmt = $this->db->prepare("DELETE FROM employees WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function countEmployees() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM employees");
        return $stmt->fetchColumn();
    }
    
    public function getRecentEmployees($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT e.*, d.name as department_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            ORDER BY e.id DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function searchEmployees($search) {
        $search = "%$search%";
        
        $stmt = $this->db->prepare("
            SELECT e.*, d.name as department_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE e.first_name LIKE :search
            OR e.last_name LIKE :search
            OR e.employee_id LIKE :search
            OR e.position LIKE :search
            OR d.name LIKE :search
            ORDER BY e.first_name ASC
        ");
        $stmt->bindParam(':search', $search, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function create($data) {
        try {
            error_log("Employee::create called with data: " . json_encode($data));
            return $this->createEmployee($data);
        } catch (\Exception $e) {
            error_log("Error in Employee::create: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()];
        }
    }

    /**
     * Count employees by department ID
     * 
     * @param int $departmentId
     * @return int
     */
    public function countEmployeesByDepartment($departmentId) {
        $sql = "SELECT COUNT(*) as count FROM employees WHERE department_id = :department_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':department_id', $departmentId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_OBJ);
        return $result ? (int)$result->count : 0;
    }
} 