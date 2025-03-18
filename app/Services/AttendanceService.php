<?php

namespace App\Services;

use App\Config\Database;

class AttendanceService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function countAttendanceByStatus($status, $date) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM attendance_records
            WHERE status = :status AND date = :date
        ");
        $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
    
    public function countAttendanceByStatusAndDepartment($status, $date, $departmentId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM attendance_records ar
            JOIN employees e ON ar.employee_id = e.id
            WHERE ar.status = :status AND ar.date = :date AND e.department_id = :department_id
        ");
        $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, \PDO::PARAM_STR);
        $stmt->bindParam(':department_id', $departmentId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
    
    public function getRecentAttendance($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT ar.*, e.first_name, e.last_name, e.employee_id as emp_id, d.name as department_name, s.name as shift_name
            FROM attendance_records ar
            JOIN employees e ON ar.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN shifts s ON ar.shift_id = s.id
            ORDER BY ar.date DESC, ar.time_in DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getRecentAttendanceByDepartment($departmentId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT ar.*, e.first_name, e.last_name, e.employee_id as emp_id, d.name as department_name, s.name as shift_name
            FROM attendance_records ar
            JOIN employees e ON ar.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN shifts s ON ar.shift_id = s.id
            WHERE e.department_id = :department_id
            ORDER BY ar.date DESC, ar.time_in DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':department_id', $departmentId, \PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getRecentAttendanceByEmployee($employeeId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT ar.*, s.name as shift_name
            FROM attendance_records ar
            LEFT JOIN shifts s ON ar.shift_id = s.id
            WHERE ar.employee_id = :employee_id
            ORDER BY ar.date DESC, ar.time_in DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':employee_id', $employeeId, \PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getUpcomingHolidays($limit = 3) {
        $today = date('Y-m-d');
        
        $stmt = $this->db->prepare("
            SELECT * FROM holidays
            WHERE date >= :today
            ORDER BY date ASC
            LIMIT :limit
        ");
        $stmt->bindParam(':today', $today, \PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getAttendanceByDate($date) {
        $stmt = $this->db->prepare("
            SELECT ar.*, e.first_name, e.last_name, e.employee_id as emp_id, d.name as department_name, s.name as shift_name
            FROM attendance_records ar
            JOIN employees e ON ar.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN shifts s ON ar.shift_id = s.id
            WHERE ar.date = :date
            ORDER BY e.department_id, e.first_name, e.last_name
        ");
        $stmt->bindParam(':date', $date, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getAttendanceByDateAndDepartment($date, $departmentId) {
        $stmt = $this->db->prepare("
            SELECT ar.*, e.first_name, e.last_name, e.employee_id as emp_id, s.name as shift_name
            FROM attendance_records ar
            JOIN employees e ON ar.employee_id = e.id
            LEFT JOIN shifts s ON ar.shift_id = s.id
            WHERE ar.date = :date AND e.department_id = :department_id
            ORDER BY e.first_name, e.last_name
        ");
        $stmt->bindParam(':date', $date, \PDO::PARAM_STR);
        $stmt->bindParam(':department_id', $departmentId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getAttendanceByEmployee($employeeId, $startDate, $endDate) {
        $stmt = $this->db->prepare("
            SELECT ar.*, s.name as shift_name
            FROM attendance_records ar
            LEFT JOIN shifts s ON ar.shift_id = s.id
            WHERE ar.employee_id = :employee_id
            AND ar.date BETWEEN :start_date AND :end_date
            ORDER BY ar.date DESC
        ");
        $stmt->bindParam(':employee_id', $employeeId, \PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $startDate, \PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDate, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
} 