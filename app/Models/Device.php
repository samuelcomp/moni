<?php

namespace App\Models;

use App\Config\Database;

class Device {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllDevices() {
        $stmt = $this->db->query("
            SELECT * FROM devices
            ORDER BY created_at DESC
        ");
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getDeviceById($id) {
        $stmt = $this->db->prepare("
            SELECT * FROM devices
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function getDeviceByDeviceId($deviceId) {
        $stmt = $this->db->prepare("
            SELECT * FROM devices
            WHERE device_id = :device_id
        ");
        $stmt->bindParam(':device_id', $deviceId, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function getActiveDevices() {
        $stmt = $this->db->prepare("
            SELECT * FROM devices
            WHERE status = 'active'
        ");
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function create($data) {
        // Get the table structure to determine the correct column names
        $stmt = $this->db->query("DESCRIBE devices");
        $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        // Build the SQL query dynamically based on the existing columns
        $validColumns = array_intersect(array_keys($data), $columns);
        
        if (empty($validColumns)) {
            throw new \Exception("No valid columns found for the devices table");
        }
        
        $columnNames = implode(', ', $validColumns);
        $placeholders = implode(', ', array_map(function($col) { return ":$col"; }, $validColumns));
        
        $sql = "INSERT INTO devices ($columnNames) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($validColumns as $column) {
            $stmt->bindParam(":$column", $data[$column], \PDO::PARAM_STR);
        }
        
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        // Get the table structure to determine the correct column names
        $stmt = $this->db->query("DESCRIBE devices");
        $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        // Build the SQL query dynamically based on the existing columns
        $validColumns = array_intersect(array_keys($data), $columns);
        
        if (empty($validColumns)) {
            throw new \Exception("No valid columns found for the devices table");
        }
        
        $setClause = implode(', ', array_map(function($col) { return "$col = :$col"; }, $validColumns));
        
        $sql = "UPDATE devices SET $setClause, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        
        foreach ($validColumns as $column) {
            $stmt->bindParam(":$column", $data[$column], \PDO::PARAM_STR);
        }
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("
            DELETE FROM devices
            WHERE id = :id
        ");
        
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function updateStatus($id, $status) {
        // First check if the status column exists
        $stmt = $this->db->query("SHOW COLUMNS FROM devices LIKE 'status'");
        $statusColumnExists = $stmt->rowCount() > 0;
        
        if ($statusColumnExists) {
            $stmt = $this->db->prepare("
                UPDATE devices
                SET status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
            
            return $stmt->execute();
        } else {
            // If status column doesn't exist, just update the updated_at timestamp
            $stmt = $this->db->prepare("
                UPDATE devices
                SET updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            
            // Create a log entry about the missing column
            $this->createLog([
                'device_id' => $id,
                'action' => 'update_status',
                'status' => 'warning',
                'message' => 'Status column does not exist in devices table. Status update skipped.'
            ]);
            
            return $stmt->execute();
        }
    }
    
    public function syncAttendance($deviceId) {
        // Get the device
        $device = $this->getDeviceById($deviceId);
        
        if (!$device) {
            return false;
        }
        
        // Create a log entry for the sync
        $stmt = $this->db->prepare("
            INSERT INTO device_logs (device_id, action, status, message)
            VALUES (:device_id, 'sync', 'started', 'Starting attendance sync')
        ");
        
        $stmt->bindParam(':device_id', $deviceId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $logId = $this->db->lastInsertId();
        
        return $logId;
    }
    
    public function updateSyncStatus($logId, $status, $message) {
        $stmt = $this->db->prepare("
            UPDATE device_logs
            SET status = :status,
                message = :message,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $stmt->bindParam(':id', $logId, \PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, \PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, \PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    public function getAttendanceLogsByDevice($deviceId, $date = null) {
        $query = "
            SELECT al.*, e.first_name, e.last_name, e.employee_id as emp_id
            FROM attendance_logs al
            LEFT JOIN employees e ON al.biometric_id = e.biometric_id
            WHERE al.device_id = :device_id
        ";
        
        $params = [':device_id' => $deviceId];
        
        if ($date) {
            $query .= " AND DATE(al.timestamp) = :date";
            $params[':date'] = $date;
        }
        
        $query .= " ORDER BY al.timestamp DESC LIMIT 100";
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function countDevices() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM devices");
        return $stmt->fetchColumn();
    }
    
    public function createLog($data) {
        $stmt = $this->db->prepare("
            INSERT INTO device_logs (device_id, action, status, message)
            VALUES (:device_id, :action, :status, :message)
        ");
        
        $stmt->bindParam(':device_id', $data['device_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':action', $data['action'], \PDO::PARAM_STR);
        $stmt->bindParam(':status', $data['status'], \PDO::PARAM_STR);
        $stmt->bindParam(':message', $data['message'], \PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    public function getLogsByDeviceId($deviceId) {
        $stmt = $this->db->prepare("
            SELECT * FROM device_logs
            WHERE device_id = :device_id
            ORDER BY created_at DESC
        ");
        
        $stmt->bindParam(':device_id', $deviceId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function createSyncProgress($deviceId, $data) {
        try {
            $query = "INSERT INTO sync_progress (device_id, status, message, progress, start_date, end_date, 
                     total_records, processed_records, new_records, skipped_records, error_records, created_at) 
                     VALUES (:device_id, :status, :message, :progress, :start_date, :end_date, 
                     :total_records, :processed_records, :new_records, :skipped_records, :error_records, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindValue(':device_id', $deviceId);
            $stmt->bindValue(':status', $data['status']);
            $stmt->bindValue(':message', $data['message']);
            $stmt->bindValue(':progress', $data['progress']);
            $stmt->bindValue(':start_date', $data['start_date']);
            $stmt->bindValue(':end_date', $data['end_date']);
            $stmt->bindValue(':total_records', $data['total_records']);
            $stmt->bindValue(':processed_records', $data['processed_records']);
            $stmt->bindValue(':new_records', $data['new_records']);
            $stmt->bindValue(':skipped_records', $data['skipped_records']);
            $stmt->bindValue(':error_records', $data['error_records']);
            
            $stmt->execute();
            
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log('Error creating sync progress: ' . $e->getMessage());
            return false;
        }
    }
    
    public function updateSyncProgress($progressId, $data) {
        try {
            $setClause = '';
            $params = [];
            
            foreach ($data as $key => $value) {
                $setClause .= "$key = :$key, ";
                $params[":$key"] = $value;
            }
            
            $setClause .= "updated_at = NOW()";
            
            $query = "UPDATE sync_progress SET $setClause WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindValue(':id', $progressId);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log('Error updating sync progress: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getSyncProgress($deviceId) {
        try {
            $query = "SELECT * FROM sync_progress WHERE device_id = :device_id ORDER BY created_at DESC LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':device_id', $deviceId);
            $stmt->execute();
            
            $progress = $stmt->fetch(\PDO::FETCH_OBJ);
            
            if (!$progress) {
                return [
                    'status' => 'not_found',
                    'message' => 'No sync progress found',
                    'progress' => 0
                ];
            }
            
            return [
                'id' => $progress->id,
                'device_id' => $progress->device_id,
                'status' => $progress->status,
                'message' => $progress->message,
                'progress' => $progress->progress,
                'start_date' => $progress->start_date,
                'end_date' => $progress->end_date,
                'total_records' => $progress->total_records,
                'processed_records' => $progress->processed_records,
                'new_records' => $progress->new_records,
                'skipped_records' => $progress->skipped_records,
                'error_records' => $progress->error_records,
                'created_at' => $progress->created_at,
                'updated_at' => $progress->updated_at
            ];
        } catch (\PDOException $e) {
            error_log('Error getting sync progress: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Error getting sync progress: ' . $e->getMessage(),
                'progress' => 0
            ];
        }
    }
    
    public function getTableColumns($table) {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM $table");
            $columns = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $columns[] = $row['Field'];
            }
            return $columns;
        } catch (\PDOException $e) {
            error_log("Error getting columns for table $table: " . $e->getMessage());
            return [];
        }
    }
    
    public function testConnection() {
        try {
            $this->db->query("SELECT 1");
            return true;
        } catch (\PDOException $e) {
            error_log("Database connection test failed: " . $e->getMessage());
            return false;
        }
    }
} 