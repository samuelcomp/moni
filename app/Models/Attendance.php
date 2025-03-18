<?php

namespace App\Models;

use App\Config\Database;

class Attendance {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllAttendance() {
        $stmt = $this->db->query("
            SELECT a.*, e.name as employee_name, d.device_name
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN devices d ON a.device_id = d.id
            ORDER BY a.timestamp DESC
        ");
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getAttendanceById($id) {
        $stmt = $this->db->prepare("
            SELECT a.*, e.name as employee_name, d.device_name
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN devices d ON a.device_id = d.id
            WHERE a.id = :id
        ");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function getAttendanceByEmployeeId($employeeId) {
        $stmt = $this->db->prepare("
            SELECT a.*, e.name as employee_name, d.device_name
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN devices d ON a.device_id = d.id
            WHERE a.employee_id = :employee_id
            ORDER BY a.timestamp DESC
        ");
        $stmt->bindParam(':employee_id', $employeeId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getAttendanceByDate($date) {
        try {
            // Replace 'a.timestamp' with the correct column name
            $stmt = $this->db->prepare("SELECT a.* FROM attendances a WHERE DATE(a.check_in) = ?");
            $stmt->execute([$date]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in getAttendanceByDate: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAttendanceByDateRange($startDate, $endDate) {
        $stmt = $this->db->prepare("
            SELECT a.*, e.name as employee_name, d.device_name
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN devices d ON a.device_id = d.id
            WHERE DATE(a.timestamp) BETWEEN :start_date AND :end_date
            ORDER BY a.timestamp DESC
        ");
        $stmt->bindParam(':start_date', $startDate, \PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDate, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getRecentAttendance($limit = 20) {
        try {
            // Get the timestamp column name
            $columns = $this->getTableColumns('attendance');
            $timestampColumn = 'timestamp';
            
            if (!in_array('timestamp', $columns)) {
                if (in_array('check_time', $columns)) {
                    $timestampColumn = 'check_time';
                } elseif (in_array('datetime', $columns)) {
                    $timestampColumn = 'datetime';
                } elseif (in_array('created_at', $columns)) {
                    $timestampColumn = 'created_at';
                }
            }
            
            // Check if employees table exists
            $employeesExist = $this->tableExists('employees');
            $devicesExist = $this->tableExists('devices');
            
            // Build the query
            $query = "SELECT a.* ";
            
            // Add employee name if available
            if ($employeesExist) {
                $employeeColumns = $this->getTableColumns('employees');
                if (in_array('name', $employeeColumns)) {
                    $query .= ", e.name as employee_name ";
                } elseif (in_array('first_name', $employeeColumns) && in_array('last_name', $employeeColumns)) {
                    $query .= ", CONCAT(e.first_name, ' ', e.last_name) as employee_name ";
                }
            }
            
            // Add device name if available
            if ($devicesExist) {
                $query .= ", d.name as device_name ";
            }
            
            $query .= "FROM attendance a ";
            
            // Join with employees if available
            if ($employeesExist && in_array('employee_id', $columns)) {
                $query .= "LEFT JOIN employees e ON a.employee_id = e.id ";
            }
            
            // Join with devices if available
            if ($devicesExist && in_array('device_id', $columns)) {
                $query .= "LEFT JOIN devices d ON a.device_id = d.id ";
            }
            
            // Order by timestamp
            $query .= "ORDER BY a.$timestampColumn DESC LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            error_log('Error in getRecentAttendance: ' . $e->getMessage());
            return [];
        }
    }
    
    public function getLastAttendanceByEmployee($employeeId) {
        $stmt = $this->db->prepare("
            SELECT a.*, e.name as employee_name, d.device_name
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN devices d ON a.device_id = d.id
            WHERE a.employee_id = :employee_id
            ORDER BY a.timestamp DESC
            LIMIT 1
        ");
        $stmt->bindParam(':employee_id', $employeeId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function create($data) {
        try {
            // Get the columns in the attendance table
            $columns = $this->getTableColumns('attendance');
            
            // Build the query dynamically based on available columns
            $fields = [];
            $placeholders = [];
            $bindings = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $columns)) {
                    $fields[] = $key;
                    $placeholders[] = ":$key";
                    $bindings[":$key"] = $value;
                }
            }
            
            if (empty($fields)) {
                error_log('No valid fields for attendance creation');
                return false;
            }
            
            $query = "INSERT INTO attendance (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($bindings as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log('Error in create attendance: ' . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $data) {
        $sql = "
            UPDATE attendance 
            SET 
                employee_id = :employee_id,
                timestamp = :timestamp,
                type = :type,
                status = :status,
                notes = :notes,
                device_id = :device_id
            WHERE id = :id
        ";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':employee_id', $data['employee_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':timestamp', $data['timestamp'], \PDO::PARAM_STR);
        $stmt->bindParam(':type', $data['type'], \PDO::PARAM_STR);
        $stmt->bindParam(':status', $data['status'], \PDO::PARAM_STR);
        $stmt->bindParam(':notes', $data['notes'], \PDO::PARAM_STR);
        $stmt->bindParam(':device_id', $data['device_id'], \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("
            DELETE FROM attendance
            WHERE id = :id
        ");
        
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function getAttendanceSummary($date) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT employee_id) as total_employees,
                SUM(CASE WHEN type = 'check_in' THEN 1 ELSE 0 END) as check_ins,
                SUM(CASE WHEN type = 'check_out' THEN 1 ELSE 0 END) as check_outs,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'early_departure' THEN 1 ELSE 0 END) as early_departure
            FROM attendance
            WHERE DATE(timestamp) = :date
        ");
        $stmt->bindParam(':date', $date, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function createFromZKTeco($employeeId, $timestamp, $deviceId) {
        // Get the employee
        $employeeModel = new Employee();
        $employee = $employeeModel->getEmployeeByEmployeeId($employeeId);
        
        if (!$employee) {
            // Create a new employee if not found
            $employeeData = [
                'employee_id' => $employeeId,
                'name' => 'Employee ' . $employeeId,
                'status' => 'active'
            ];
            
            $employeeModel->create($employeeData);
            $employee = $employeeModel->getEmployeeByEmployeeId($employeeId);
            
            if (!$employee) {
                return false;
            }
        }
        
        // Determine attendance type (check-in or check-out)
        $lastAttendance = $this->getLastAttendanceByEmployee($employee->id);
        $type = 'check_in';
        
        if ($lastAttendance && date('Y-m-d', strtotime($lastAttendance->timestamp)) === date('Y-m-d', strtotime($timestamp))) {
            if ($lastAttendance->type === 'check_in') {
                $type = 'check_out';
            }
        }
        
        // Determine status
        $status = 'present';
        $workHours = new \App\Models\WorkHours();
        $schedule = $workHours->getWorkHoursByEmployeeId($employee->id);
        
        if ($schedule) {
            $checkInTime = strtotime(date('Y-m-d', strtotime($timestamp)) . ' ' . $schedule->start_time);
            $checkOutTime = strtotime(date('Y-m-d', strtotime($timestamp)) . ' ' . $schedule->end_time);
            $currentTime = strtotime($timestamp);
            
            if ($type === 'check_in' && $currentTime > $checkInTime + (15 * 60)) { // 15 minutes grace period
                $status = 'late';
            } elseif ($type === 'check_out' && $currentTime < $checkOutTime - (15 * 60)) { // 15 minutes grace period
                $status = 'early_departure';
            }
        }
        
        // Create attendance record
        $data = [
            'employee_id' => $employee->id,
            'timestamp' => $timestamp,
            'type' => $type,
            'status' => $status,
            'notes' => 'Imported from ZKTeco device',
            'device_id' => $deviceId
        ];
        
        return $this->create($data);
    }
    
    public function getAllAttendances() {
        try {
            // Replace 'timestamp' with the actual column name, likely 'check_in' or 'created_at'
            $stmt = $this->db->prepare("SELECT * FROM attendances ORDER BY check_in DESC");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in getAllAttendances: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAttendanceByDeviceLogId($deviceId, $deviceLogId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM attendance
                WHERE device_id = :device_id AND device_log_id = :device_log_id
                LIMIT 1
            ");
            
            $stmt->bindParam(':device_id', $deviceId, \PDO::PARAM_INT);
            $stmt->bindParam(':device_log_id', $deviceLogId, \PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            // If there's an error, log it and return null
            error_log('Error in getAttendanceByDeviceLogId: ' . $e->getMessage());
            return null;
        }
    }
    
    public function getEmployeeByBiometricId($biometricId) {
        try {
            // Check if the biometric_id column exists in the employees table
            $columns = $this->getTableColumns('employees');
            $biometricColumn = in_array('biometric_id', $columns) ? 'biometric_id' : 
                              (in_array('device_id', $columns) ? 'device_id' : null);
            
            if (!$biometricColumn) {
                error_log('No biometric ID column found in employees table');
                return null;
            }
            
            $query = "SELECT * FROM employees WHERE $biometricColumn = :biometric_id LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':biometric_id', $biometricId);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            error_log('Error getting employee by biometric ID: ' . $e->getMessage());
            return null;
        }
    }
    
    public function getTableColumns($table) {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM $table");
            $columns = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $columns[] = $row['Field'];
                // Check if the column allows NULL values
                if ($row['Null'] === 'YES') {
                    $columns[] = 'NULL';
                }
            }
            return $columns;
        } catch (\PDOException $e) {
            error_log("Error getting columns for table $table: " . $e->getMessage());
            return [];
        }
    }
    
    public function tableExists($table) {
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE '$table'");
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking if table $table exists: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAttendanceByBiometricIdAndTimestamp($biometricId, $timestamp) {
        try {
            // Get the timestamp column name
            $columns = $this->getTableColumns('attendance');
            $timestampColumn = 'timestamp';
            
            if (!in_array('timestamp', $columns)) {
                if (in_array('check_time', $columns)) {
                    $timestampColumn = 'check_time';
                } elseif (in_array('datetime', $columns)) {
                    $timestampColumn = 'datetime';
                } elseif (in_array('created_at', $columns)) {
                    $timestampColumn = 'created_at';
                }
            }
            
            // Build the query
            $query = "
                SELECT * FROM attendance
                WHERE biometric_id = :biometric_id
                AND DATE($timestampColumn) = DATE(:timestamp)
                AND TIME($timestampColumn) BETWEEN TIME(DATE_SUB(:timestamp, INTERVAL 1 MINUTE))
                                            AND TIME(DATE_ADD(:timestamp, INTERVAL 1 MINUTE))
                LIMIT 1
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':biometric_id', $biometricId, \PDO::PARAM_STR);
            $stmt->bindParam(':timestamp', $timestamp, \PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            error_log('Error in getAttendanceByBiometricIdAndTimestamp: ' . $e->getMessage());
            return null;
        }
    }
    
    public function getAllEmployees() {
        try {
            if (!$this->tableExists('employees')) {
                return [];
            }
            
            $stmt = $this->db->query("SELECT * FROM employees LIMIT 100");
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            error_log('Error in getAllEmployees: ' . $e->getMessage());
            return [];
        }
    }
    
    public function createEmployee($data) {
        try {
            // Check if the user_id column exists and is required
            $columns = $this->getTableColumns('employees');
            $needsUserId = in_array('user_id', $columns);
            
            // If user_id is needed, create a user first or use a default user
            if ($needsUserId) {
                // Check if we have a default admin user
                $query = "SELECT id FROM users WHERE role_id = 1 LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $adminUser = $stmt->fetch(\PDO::FETCH_OBJ);
                
                if ($adminUser) {
                    $data['user_id'] = $adminUser->id;
                } else {
                    error_log('No admin user found for employee creation');
                    return false;
                }
            }
            
            // Build the query dynamically based on available columns
            $fields = [];
            $placeholders = [];
            $bindings = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $columns)) {
                    $fields[] = $key;
                    $placeholders[] = ":$key";
                    $bindings[":$key"] = $value;
                }
            }
            
            if (empty($fields)) {
                error_log('No valid fields for employee creation');
                return false;
            }
            
            $query = "INSERT INTO employees (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($bindings as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log('Error in createEmployee: ' . $e->getMessage());
            return false;
        }
    }
    
    public function columnExists($table, $column) {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM $table LIKE '$column'");
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking if column $column exists in table $table: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if an attendance record already exists
     * 
     * @param int    $deviceId   Device ID
     * @param string $userId     User ID from the device
     * @param string $timestamp  Timestamp
     * @return bool              True if exists, false otherwise
     */
    public function checkAttendanceExists($deviceId, $userId, $timestamp) {
        try {
            // First, determine which timestamp column to use
            $timestampColumn = $this->getTimestampColumn();
            if (!$timestampColumn) {
                return false; // Can't check without knowing the timestamp column
            }
            
            // Get the employee with this user ID (biometric ID)
            $employee = $this->getEmployeeByBiometricId($userId);
            if (!$employee) {
                return false; // No employee found, so no attendance record exists
            }
            
            // Build the query
            $query = "SELECT COUNT(*) as count FROM attendance WHERE device_id = :device_id AND employee_id = :employee_id AND $timestampColumn = :timestamp";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':device_id', $deviceId);
            $stmt->bindValue(':employee_id', $employee->id);
            $stmt->bindValue(':timestamp', $timestamp);
            $stmt->execute();
            
            $result = $stmt->fetch(\PDO::FETCH_OBJ);
            
            return $result && $result->count > 0;
        } catch (\PDOException $e) {
            error_log('Error checking if attendance exists: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the timestamp column name used in the attendance table
     * 
     * @return string|null The timestamp column name or null if not found
     */
    private function getTimestampColumn() {
        $columns = $this->getTableColumns('attendance');
        
        // Check for common timestamp column names
        $possibleColumns = ['timestamp', 'check_time', 'datetime', 'created_at'];
        
        foreach ($possibleColumns as $column) {
            if (in_array($column, $columns)) {
                return $column;
            }
        }
        
        return null;
    }

    public function getAttendanceReport($startDate = null, $endDate = null, $employeeId = null) {
        try {
            $query = "SELECT a.*, e.name as employee_name 
                     FROM attendances a 
                     JOIN employees e ON a.employee_id = e.id 
                     WHERE 1=1";
            
            $params = [];
            
            if ($startDate && $endDate) {
                $query .= " AND DATE(a.check_in) BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            if ($employeeId) {
                $query .= " AND a.employee_id = ?";
                $params[] = $employeeId;
            }
            
            $query .= " ORDER BY a.check_in DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in getAttendanceReport: " . $e->getMessage());
            return [];
        }
    }

    public function createAttendance($data) {
        try {
            // First, check if the attendances table exists
            $tableCheckStmt = $this->db->prepare("SHOW TABLES LIKE 'attendances'");
            $tableCheckStmt->execute();
            $tableExists = $tableCheckStmt->rowCount() > 0;
            
            if (!$tableExists) {
                // Try with a different table name - maybe it's 'attendance' (singular)
                $tableCheckStmt = $this->db->prepare("SHOW TABLES LIKE 'attendance'");
                $tableCheckStmt->execute();
                $tableExists = $tableCheckStmt->rowCount() > 0;
                
                if ($tableExists) {
                    error_log("Found 'attendance' table instead of 'attendances'");
                    $tableName = 'attendance';
                } else {
                    error_log("Neither 'attendances' nor 'attendance' table exists");
                    return ['success' => false, 'message' => 'Attendance table not found'];
                }
            } else {
                $tableName = 'attendances';
            }
            
            // Get the table columns to ensure we only use existing columns
            $columnsStmt = $this->db->prepare("DESCRIBE " . $tableName);
            $columnsStmt->execute();
            $columns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
            
            error_log("Available attendance columns: " . json_encode($columns));
            
            // Ensure we have valid date values
            $checkInDate = isset($data['check_in']) && $data['check_in'] != '0000-00-00' 
                ? $data['check_in'] 
                : date('Y-m-d H:i:s');
            
            // Check if an attendance record already exists for this employee on this date
            $checkStmt = $this->db->prepare("SELECT id FROM " . $tableName . 
                                           " WHERE employee_id = ? AND DATE(check_in) = DATE(?)");
            $checkStmt->execute([$data['employee_id'], $checkInDate]);
            $existingAttendance = $checkStmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($existingAttendance) {
                // Update existing record
                $updateFields = [];
                $updateParams = [];
                
                foreach ($data as $field => $value) {
                    // Only include fields that exist in the table
                    if (in_array($field, $columns) && $value !== null && $value != '0000-00-00') {
                        $updateFields[] = "$field = ?";
                        $updateParams[] = $value;
                    }
                }
                
                if (!empty($updateFields)) {
                    $updateParams[] = $existingAttendance['id']; // Add ID for WHERE clause
                    $stmt = $this->db->prepare("UPDATE " . $tableName . " SET " . 
                                              implode(", ", $updateFields) . 
                                              " WHERE id = ?");
                    $stmt->execute($updateParams);
                }
                
                return ['success' => true, 'id' => $existingAttendance['id'], 'updated' => true];
            } else {
                // Insert new record with valid dates
                $insertColumns = [];
                $placeholders = [];
                $values = [];
                
                foreach ($data as $key => $value) {
                    // Only include fields that exist in the table
                    if (in_array($key, $columns) && $value !== null && $value != '0000-00-00') {
                        $insertColumns[] = $key;
                        $placeholders[] = '?';
                        $values[] = $value;
                    }
                }
                
                if (empty($insertColumns)) {
                    error_log("No valid columns found for attendance insert");
                    return ['success' => false, 'message' => 'No valid data for attendance creation'];
                }
                
                $stmt = $this->db->prepare("INSERT INTO " . $tableName . " (" . 
                                          implode(", ", $insertColumns) . 
                                          ") VALUES (" . 
                                          implode(", ", $placeholders) . 
                                          ")");
                $stmt->execute($values);
                
                return ['success' => true, 'id' => $this->db->lastInsertId()];
            }
        } catch (\PDOException $e) {
            error_log("Error in create attendance: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Count attendance records by status
     * 
     * @param string $status The status to count (present, absent, late, etc.)
     * @param string $date Optional date to filter by (YYYY-MM-DD)
     * @return int The count of attendance records with the given status
     */
    public function countAttendanceByStatus($status, $date = null) {
        try {
            // Determine the table name
            $tableCheckStmt = $this->db->prepare("SHOW TABLES LIKE 'attendances'");
            $tableCheckStmt->execute();
            $tableName = $tableCheckStmt->rowCount() > 0 ? 'attendances' : 'attendance';
            
            // Build the query
            $query = "SELECT COUNT(*) as count FROM $tableName WHERE status = ?";
            $params = [$status];
            
            // Add date filter if provided
            if ($date) {
                $query .= " AND DATE(check_in) = ?";
                $params[] = $date;
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $result['count'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error in countAttendanceByStatus: " . $e->getMessage());
            return 0;
        }
    }
} 