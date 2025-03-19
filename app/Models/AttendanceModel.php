<?php

namespace App\Models;

use PDO;
use PDOException;

class AttendanceModel extends BaseModel
{
    protected $table = 'attendances';

    /**
     * Get recent attendance records
     * 
     * @param int $limit Number of records to return
     * @return array Array of attendance records
     */
    public function getRecentAttendance($limit = 20)
    {
        try {
            $sql = "SELECT a.*, d.device_name, e.name as employee_name 
                    FROM {$this->table} a
                    LEFT JOIN devices d ON a.device_id = d.id
                    LEFT JOIN employees e ON a.employee_id = e.id
                    ORDER BY a.timestamp DESC
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in getRecentAttendance: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get attendance records for a specific date
     * 
     * @param string $date Date in Y-m-d format
     * @return array Array of attendance records
     */
    public function getAttendanceByDate($date)
    {
        try {
            $startDate = $date . ' 00:00:00';
            $endDate = $date . ' 23:59:59';
            
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE timestamp BETWEEN :start_date AND :end_date ORDER BY timestamp DESC");
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            error_log("Error in getAttendanceByDate: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get attendance records since a specific timestamp
     * 
     * @param string $timestamp Timestamp in Y-m-d H:i:s format
     * @param int $deviceId Optional device ID to filter by
     * @return array Array of attendance records
     */
    public function getAttendanceSince($timestamp, $deviceId = null)
    {
        try {
            $sql = "SELECT a.*, d.device_name, e.name as employee_name 
                    FROM {$this->table} a
                    LEFT JOIN devices d ON a.device_id = d.id
                    LEFT JOIN employees e ON a.employee_id = e.id
                    WHERE a.timestamp > :timestamp";
            
            if ($deviceId) {
                $sql .= " AND a.device_id = :device_id";
            }
            
            $sql .= " ORDER BY a.timestamp DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':timestamp', $timestamp);
            
            if ($deviceId) {
                $stmt->bindParam(':device_id', $deviceId);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in getAttendanceSince: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if an attendance record exists
     * 
     * @param int $deviceId Device ID
     * @param string $biometricId Biometric ID
     * @param string $timestamp Timestamp
     * @return bool True if exists, false otherwise
     */
    public function checkAttendanceExists($deviceId, $biometricId, $timestamp)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE device_id = :device_id 
                    AND biometric_id = :biometric_id 
                    AND timestamp = :timestamp";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':device_id', $deviceId);
            $stmt->bindParam(':biometric_id', $biometricId);
            $stmt->bindParam(':timestamp', $timestamp);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return $result->count > 0;
        } catch (PDOException $e) {
            error_log("Error in checkAttendanceExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new attendance record
     * 
     * @param array $data Attendance data
     * @return int|false The ID of the new record or false on failure
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (device_id, biometric_id, employee_id, timestamp, status, punch, processed, created_at, updated_at) 
                    VALUES (:device_id, :biometric_id, :employee_id, :timestamp, :status, :punch, :processed, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':device_id', $data['device_id']);
            $stmt->bindParam(':biometric_id', $data['biometric_id']);
            $stmt->bindParam(':employee_id', $data['employee_id'] ?? null);
            $stmt->bindParam(':timestamp', $data['timestamp']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':punch', $data['punch']);
            $stmt->bindParam(':processed', $data['processed']);
            $stmt->execute();
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in create attendance: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get attendance summary for a specific date
     * 
     * @param string $date Date in Y-m-d format
     * @return object Summary object with counts
     */
    public function getAttendanceSummary($date)
    {
        try {
            // Get total employees
            $employeeStmt = $this->db->query("SELECT COUNT(*) as total FROM employees WHERE 1=1");
            $totalEmployees = $employeeStmt->fetch(\PDO::FETCH_OBJ)->total;
            
            // Get present employees
            $startDate = $date . ' 00:00:00';
            $endDate = $date . ' 23:59:59';
            
            $presentStmt = $this->db->prepare("
                SELECT COUNT(DISTINCT employee_id) as present 
                FROM {$this->table} 
                WHERE timestamp BETWEEN :start_date AND :end_date
            ");
            $presentStmt->bindParam(':start_date', $startDate);
            $presentStmt->bindParam(':end_date', $endDate);
            $presentStmt->execute();
            $present = $presentStmt->fetch(\PDO::FETCH_OBJ)->present;
            
            // Check if status column exists
            $checkStatusColumn = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'status'");
            $statusColumnExists = $checkStatusColumn->rowCount() > 0;
            
            // Get late employees
            $late = 0;
            if ($statusColumnExists) {
                $lateStmt = $this->db->prepare("
                    SELECT COUNT(DISTINCT employee_id) as late 
                    FROM {$this->table} 
                    WHERE timestamp BETWEEN :start_date AND :end_date 
                    AND status = 'late'
                ");
                $lateStmt->bindParam(':start_date', $startDate);
                $lateStmt->bindParam(':end_date', $endDate);
                $lateStmt->execute();
                $late = $lateStmt->fetch(\PDO::FETCH_OBJ)->late;
            }
            
            // Create summary object
            $summary = new \stdClass();
            $summary->total_employees = $totalEmployees;
            $summary->present = $present;
            $summary->late = $late;
            
            return $summary;
        } catch (\PDOException $e) {
            error_log("Error in getAttendanceSummary: " . $e->getMessage());
            
            // Return empty summary
            $summary = new \stdClass();
            $summary->total_employees = 0;
            $summary->present = 0;
            $summary->late = 0;
            
            return $summary;
        }
    }

    /**
     * Count attendance records by status
     * 
     * @param string $date Date in Y-m-d format
     * @param string $status Status to count (present, absent, late)
     * @return int Number of records
     */
    public function countAttendanceByStatus($date, $status = 'present')
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE DATE(timestamp) = :date";
            
            // Add status condition based on the requested status
            if ($status == 'present') {
                $sql .= " AND status = 'check-in'";
            } else if ($status == 'late') {
                $sql .= " AND status = 'late'";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return $result->count ?? 0;
        } catch (PDOException $e) {
            error_log("Error in countAttendanceByStatus: " . $e->getMessage());
            return 0;
        }
    }
} 