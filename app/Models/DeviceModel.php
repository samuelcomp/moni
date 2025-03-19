<?php

namespace App\Models;

use PDO;
use PDOException;

class DeviceModel extends BaseModel
{
    protected $table = 'devices';

    /**
     * Get all devices
     * 
     * @return array Array of devices
     */
    public function getAllDevices()
    {
        try {
            // Check if device_name column exists
            $checkColumn = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'device_name'");
            $columnExists = $checkColumn->rowCount() > 0;
            
            if ($columnExists) {
                $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY device_name");
            } else {
                // Fall back to ordering by ID or name if it exists
                $checkNameColumn = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'name'");
                $nameColumnExists = $checkNameColumn->rowCount() > 0;
                
                if ($nameColumnExists) {
                    $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY name");
                } else {
                    $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY id");
                }
            }
            
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in getAllDevices: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get active devices
     * 
     * @return array Array of active devices
     */
    public function getActiveDevices()
    {
        try {
            // Check if device_name column exists
            $checkColumn = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'device_name'");
            $columnExists = $checkColumn->rowCount() > 0;
            
            // Check if status column exists
            $statusColumn = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'status'");
            $statusExists = $statusColumn->rowCount() > 0;
            
            // Build the query based on existing columns
            $sql = "SELECT * FROM {$this->table}";
            
            // Add status filter if status column exists
            if ($statusExists) {
                $sql .= " WHERE status = 1";
            }
            
            // Add ordering
            if ($columnExists) {
                $sql .= " ORDER BY device_name";
            } else {
                // Check for name column
                $nameColumn = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'name'");
                if ($nameColumn->rowCount() > 0) {
                    $sql .= " ORDER BY name";
                } else {
                    $sql .= " ORDER BY id";
                }
            }
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in getActiveDevices: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get device by ID
     * 
     * @param int $id Device ID
     * @return object|false Device object or false if not found
     */
    public function getDeviceById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in getDeviceById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new device
     * 
     * @param array $data Device data
     * @return int|false The ID of the new device or false on failure
     */
    public function create($data)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (device_name, ip_address, port, status, created_at, updated_at) VALUES (:device_name, :ip_address, :port, :status, NOW(), NOW())");
            $stmt->bindParam(':device_name', $data['device_name']);
            $stmt->bindParam(':ip_address', $data['ip_address']);
            $stmt->bindParam(':port', $data['port']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in create device: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a device
     * 
     * @param int $id Device ID
     * @param array $data Device data
     * @return bool True on success, false on failure
     */
    public function update($id, $data)
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET device_name = :device_name, ip_address = :ip_address, port = :port, status = :status, updated_at = NOW() WHERE id = :id");
            $stmt->bindParam(':device_name', $data['device_name']);
            $stmt->bindParam(':ip_address', $data['ip_address']);
            $stmt->bindParam(':port', $data['port']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in update device: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a device
     * 
     * @param int $id Device ID
     * @return bool True on success, false on failure
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in delete device: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a device log entry
     * 
     * @param array $data Log data
     * @return int|bool Log ID on success, false on failure
     */
    public function createLog($data)
    {
        try {
            // Check if device_logs table exists, create it if not
            $this->db->query("
                CREATE TABLE IF NOT EXISTS device_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    device_id INT NOT NULL,
                    action VARCHAR(50) NOT NULL,
                    status VARCHAR(20) NOT NULL,
                    message TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
                )
            ");
            
            $stmt = $this->db->prepare("
                INSERT INTO device_logs (device_id, action, status, message)
                VALUES (:device_id, :action, :status, :message)
            ");
            
            $stmt->bindParam(':device_id', $data['device_id']);
            $stmt->bindParam(':action', $data['action']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':message', $data['message']);
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in createLog: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update device status
     * 
     * @param int $id Device ID
     * @param string $status New status
     * @return bool True on success, false on failure
     */
    public function updateStatus($id, $status)
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status = :status, updated_at = NOW() WHERE id = :id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in updateStatus: " . $e->getMessage());
            return false;
        }
    }
} 