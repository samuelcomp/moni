<?php

namespace App\Models;

use App\Config\Database;

class LeaveRequest {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllLeaveRequests() {
        $stmt = $this->db->query("
            SELECT lr.*, e.first_name, e.last_name, e.employee_id as emp_id
            FROM leave_requests lr
            LEFT JOIN employees e ON lr.employee_id = e.id
            ORDER BY lr.created_at DESC
        ");
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getLeaveRequestById($id) {
        $stmt = $this->db->prepare("
            SELECT lr.*, e.first_name, e.last_name, e.employee_id as emp_id
            FROM leave_requests lr
            LEFT JOIN employees e ON lr.employee_id = e.id
            WHERE lr.id = :id
        ");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function getLeaveRequestsByEmployeeId($employeeId) {
        $stmt = $this->db->prepare("
            SELECT lr.*, e.first_name, e.last_name, e.employee_id as emp_id
            FROM leave_requests lr
            LEFT JOIN employees e ON lr.employee_id = e.id
            WHERE lr.employee_id = :employee_id
            ORDER BY lr.created_at DESC
        ");
        $stmt->bindParam(':employee_id', $employeeId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getLeaveRequestsByStatus($status, $limit = null) {
        $sql = "
            SELECT lr.*, e.first_name, e.last_name, e.employee_id as emp_id
            FROM leave_requests lr
            LEFT JOIN employees e ON lr.employee_id = e.id
            WHERE lr.status = :status
            ORDER BY lr.created_at DESC
        ";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
        
        if ($limit !== null) {
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function countLeaveRequestsByStatus($status) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM leave_requests
            WHERE status = :status
        ");
        $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_OBJ);
        return $result->count;
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason, status)
            VALUES (:employee_id, :leave_type, :start_date, :end_date, :reason, :status)
        ");
        
        $status = $data['status'] ?? 'pending';
        
        $stmt->bindParam(':employee_id', $data['employee_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':leave_type', $data['leave_type'], \PDO::PARAM_STR);
        $stmt->bindParam(':start_date', $data['start_date'], \PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $data['end_date'], \PDO::PARAM_STR);
        $stmt->bindParam(':reason', $data['reason'], \PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE leave_requests
            SET employee_id = :employee_id,
                leave_type = :leave_type,
                start_date = :start_date,
                end_date = :end_date,
                reason = :reason,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':employee_id', $data['employee_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':leave_type', $data['leave_type'], \PDO::PARAM_STR);
        $stmt->bindParam(':start_date', $data['start_date'], \PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $data['end_date'], \PDO::PARAM_STR);
        $stmt->bindParam(':reason', $data['reason'], \PDO::PARAM_STR);
        $stmt->bindParam(':status', $data['status'], \PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("
            UPDATE leave_requests
            SET status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("
            DELETE FROM leave_requests
            WHERE id = :id
        ");
        
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
} 