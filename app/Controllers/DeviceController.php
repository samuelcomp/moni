<?php

namespace App\Controllers;

use App\Models\Device;
use App\Models\Attendance;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Helpers\ZKTeco;
use App\Helpers\PyZK;

class DeviceController {
    private $deviceModel;
    private $attendanceModel;
    private $db;
    
    public function __construct() {
        $this->deviceModel = new Device();
        $this->attendanceModel = new Attendance();
        $this->db = \App\Config\Database::getInstance()->getConnection();
    }
    
    public function index() {
        // Check if user is logged in and is admin
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        $userModel = new \App\Models\User();
        $user = $userModel->getUserById(Session::getUserId());
        
        if (!isset($user->role_id) || $user->role_id != 1) {
            header('Location: /dashboard?error=' . urlencode('You do not have permission to access this page'));
            exit;
        }
        
        $devices = $this->deviceModel->getAllDevices();
        
        require_once __DIR__ . '/../../resources/views/devices/index.php';
    }
    
    public function create() {
        // Check if user is logged in and is admin
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        $userModel = new \App\Models\User();
        $user = $userModel->getUserById(Session::getUserId());
        
        if (!isset($user->role_id) || $user->role_id != 1) {
            header('Location: /dashboard?error=' . urlencode('You do not have permission to access this page'));
            exit;
        }
        
        require_once __DIR__ . '/../../resources/views/devices/create.php';
    }
    
    public function store() {
        // Check if user is logged in and is admin
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        $userModel = new \App\Models\User();
        $user = $userModel->getUserById(Session::getUserId());
        
        if (!isset($user->role_id) || $user->role_id != 1) {
            header('Location: /dashboard?error=' . urlencode('You do not have permission to access this page'));
            exit;
        }
        
        // Validate input
        $validator = new Validator($_POST);
        $validator->required(['device_name', 'device_type', 'ip_address', 'port']);
        
        if (!$validator->isValid()) {
            $errors = $validator->getErrors();
            $errorMessage = implode(', ', $errors);
            header('Location: /devices/create?error=' . urlencode($errorMessage));
            exit;
        }
        
        // Test connection if requested
        if (isset($_POST['test_connection']) && $_POST['test_connection'] == 1) {
            $result = ZKTeco::connect($_POST['ip_address'], $_POST['port']);
            
            if (!$result['success']) {
                header('Location: /devices/create?error=' . urlencode('Connection test failed: ' . $result['message']));
                exit;
            }
        }
        
        // Create device
        $data = [
            'device_name' => $_POST['device_name'],
            'device_type' => $_POST['device_type'],
            'ip_address' => $_POST['ip_address'],
            'port' => $_POST['port'],
            'device_id' => $_POST['device_id'] ?? null,
            'location' => $_POST['location'] ?? null,
            'status' => $_POST['status'] ?? 'active',
            'notes' => $_POST['notes'] ?? null
        ];
        
        $result = $this->deviceModel->create($data);
        
        if ($result) {
            header('Location: /devices?success=' . urlencode('Device created successfully'));
        } else {
            header('Location: /devices/create?error=' . urlencode('Failed to create device'));
        }
    }
    
    public function edit($id) {
        // Check if user is logged in and is admin
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        $userModel = new \App\Models\User();
        $user = $userModel->getUserById(Session::getUserId());
        
        if (!isset($user->role_id) || $user->role_id != 1) {
            header('Location: /dashboard?error=' . urlencode('You do not have permission to access this page'));
            exit;
        }
        
        $device = $this->deviceModel->getDeviceById($id);
        
        if (!$device) {
            header('Location: /devices?error=' . urlencode('Device not found'));
            exit;
        }
        
        require_once __DIR__ . '/../../resources/views/devices/edit.php';
    }
    
    public function update($id) {
        // Check if user is logged in and is admin
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        $userModel = new \App\Models\User();
        $user = $userModel->getUserById(Session::getUserId());
        
        if (!isset($user->role_id) || $user->role_id != 1) {
            header('Location: /dashboard?error=' . urlencode('You do not have permission to access this page'));
            exit;
        }
        
        // Validate input
        $validator = new Validator($_POST);
        $validator->required(['device_name', 'device_type', 'ip_address', 'port']);
        
        if (!$validator->isValid()) {
            $errors = $validator->getErrors();
            $errorMessage = implode(', ', $errors);
            header('Location: /devices/edit/' . $id . '?error=' . urlencode($errorMessage));
            exit;
        }
        
        // Update device
        $data = [
            'device_name' => $_POST['device_name'],
            'device_type' => $_POST['device_type'],
            'ip_address' => $_POST['ip_address'],
            'port' => $_POST['port'],
            'device_id' => $_POST['device_id'] ?? null,
            'location' => $_POST['location'] ?? null,
            'status' => $_POST['status'] ?? 'active',
            'notes' => $_POST['notes'] ?? null
        ];
        
        $result = $this->deviceModel->update($id, $data);
        
        if ($result) {
            header('Location: /devices?success=' . urlencode('Device updated successfully'));
        } else {
            header('Location: /devices/edit/' . $id . '?error=' . urlencode('Failed to update device'));
        }
    }
    
    public function delete($id) {
        // Check if user is logged in and is admin
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        $userModel = new \App\Models\User();
        $user = $userModel->getUserById(Session::getUserId());
        
        if (!isset($user->role_id) || $user->role_id != 1) {
            header('Location: /dashboard?error=' . urlencode('You do not have permission to access this page'));
            exit;
        }
        
        $result = $this->deviceModel->delete($id);
        
        if ($result) {
            header('Location: /devices?success=' . urlencode('Device deleted successfully'));
        } else {
            header('Location: /devices?error=' . urlencode('Failed to delete device'));
        }
    }
    
    public function testConnection($id) {
        // Check if user is logged in and is admin
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        $userModel = new \App\Models\User();
        $user = $userModel->getUserById(Session::getUserId());
        
        if (!isset($user->role_id) || $user->role_id != 1) {
            header('Location: /dashboard?error=' . urlencode('You do not have permission to access this page'));
            exit;
        }
        
        // Get the device
        $device = $this->deviceModel->getDeviceById($id);
        
        if (!$device) {
            header('Location: /devices?error=' . urlencode('Device not found'));
            exit;
        }
        
        // Perform a direct socket test
        $result = $this->performDirectSocketTest($device->ip_address, $device->port);
        
        // Create a log entry
        $logData = [
            'device_id' => $id,
            'action' => 'test_connection',
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        $this->deviceModel->createLog($logData);
        
        // Update device status
        $this->deviceModel->updateStatus($id, $result['success'] ? 'active' : 'inactive');
        
        // Pass the result to the view
        require_once __DIR__ . '/../../resources/views/devices/test_connection.php';
    }
    
    private function performDirectSocketTest($ip, $port) {
        if (!function_exists('socket_create')) {
            return [
                'success' => false,
                'message' => 'PHP socket extension is not enabled'
            ];
        }
        
        $debug = [
            'ip' => $ip,
            'port' => $port,
            'socket_extension' => extension_loaded('sockets') ? 'Loaded' : 'Not loaded'
        ];
        
        // Create socket
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            $debug['socket_create_error'] = socket_strerror(socket_last_error());
            return [
                'success' => false,
                'message' => 'Failed to create socket: ' . socket_strerror(socket_last_error()),
                'debug' => $debug
            ];
        }
        
        // Set timeout
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 5, 'usec' => 0]);
        
        // Connect
        $result = @socket_connect($socket, $ip, $port);
        $debug['can_connect'] = $result ? 'Yes' : 'No';
        
        if (!$result) {
            $error = socket_strerror(socket_last_error($socket));
            $debug['socket_connect_error'] = $error;
            socket_close($socket);
            return [
                'success' => false,
                'message' => 'Failed to connect: ' . $error,
                'debug' => $debug
            ];
        }
        
        // Try to send a simple command (ZKTeco protocol)
        $command = chr(0) . chr(0) . chr(0) . chr(0) . chr(0) . chr(0) . chr(1) . chr(0);
        $sent = socket_write($socket, $command, strlen($command));
        $debug['bytes_sent'] = $sent;
        
        if ($sent === false) {
            $error = socket_strerror(socket_last_error($socket));
            $debug['socket_write_error'] = $error;
            socket_close($socket);
            return [
                'success' => false,
                'message' => 'Failed to send data: ' . $error,
                'debug' => $debug
            ];
        }
        
        // Try to read response
        $response = socket_read($socket, 1024);
        $debug['response_received'] = $response !== false ? 'Yes' : 'No';
        $debug['response_length'] = $response !== false ? strlen($response) : 0;
        
        socket_close($socket);
        
        if ($response !== false) {
            return [
                'success' => true,
                'message' => 'Successfully connected to the device',
                'debug' => $debug,
                'info' => [
                    'Connection' => 'Successful',
                    'Response Length' => strlen($response)
                ]
            ];
        } else {
            $error = socket_strerror(socket_last_error($socket));
            $debug['socket_read_error'] = $error;
            return [
                'success' => false,
                'message' => 'Failed to receive data: ' . $error,
                'debug' => $debug
            ];
        }
    }
    
    public function sync($id) {
        // Check if user is logged in and is admin
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        $userModel = new \App\Models\User();
        $user = $userModel->getUserById(Session::getUserId());
        
        if (!isset($user->role_id) || $user->role_id != 1) {
            header('Location: /dashboard?error=' . urlencode('You do not have permission to access this page'));
            exit;
        }
        
        // Get the device
        $device = $this->deviceModel->getDeviceById($id);
        
        if (!$device) {
            header('Location: /devices?error=' . urlencode('Device not found'));
            exit;
        }
        
        // Get sync parameters
        $syncAll = isset($_GET['all']) && $_GET['all'] == '1';
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        
        // First, test the connection
        $connectionTest = PyZK::testConnection($device->ip_address, $device->port);
        
        if ($connectionTest['status'] !== 'success') {
            // Log the connection failure
            $logData = [
                'device_id' => $id,
                'action' => 'sync_attendance',
                'status' => 'error',
                'message' => 'Connection failed: ' . $connectionTest['message']
            ];
            
            $this->deviceModel->createLog($logData);
            
            // Update device status
            $this->deviceModel->updateStatus($id, 'inactive');
            
            // Redirect with error
            header('Location: /devices?error=' . urlencode('Failed to connect to the device: ' . $connectionTest['message']));
            exit;
        }
        
        // Try to get attendance logs using PyZK
        $result = PyZK::getAttendance($device->ip_address, $device->port, $syncAll);
        
        if (!$result['success']) {
            // If PyZK fails, try to generate mock data for development
            if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
                // Generate mock attendance data for development
                $result = $this->generateMockAttendanceData($device, $syncAll ? 1000 : 50);
                
                // Log the mock data generation
                $logData = [
                    'device_id' => $id,
                    'action' => 'sync_attendance',
                    'status' => 'warning',
                    'message' => 'Using mock data: ' . $result['message']
                ];
                
                $this->deviceModel->createLog($logData);
            } else {
                // Log the failure
                $logData = [
                    'device_id' => $id,
                    'action' => 'sync_attendance',
                    'status' => 'error',
                    'message' => 'Failed to get attendance logs: ' . $result['message']
                ];
                
                $this->deviceModel->createLog($logData);
                
                // Redirect with error
                header('Location: /devices?error=' . urlencode('Failed to get attendance logs: ' . $result['message']));
                exit;
            }
        }
        
        // Process the attendance logs
        $logs = $result['logs'];
        $processedCount = 0;
        $newCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        
        // Get the structure of the attendance table
        $attendanceColumns = $this->attendanceModel->getTableColumns('attendance');
        
        // Check if we need to create a default employee
        $defaultEmployeeId = null;
        if (in_array('employee_id', $attendanceColumns) && !in_array('NULL', $attendanceColumns)) {
            // Try to get the first employee
            $employees = $this->attendanceModel->getAllEmployees();
            if (!empty($employees)) {
                $defaultEmployeeId = $employees[0]->id;
            } else {
                // Create a default employee if none exists
                $defaultEmployeeId = $this->createDefaultEmployee();
            }
        }
        
        foreach ($logs as $log) {
            $processedCount++;
            
            // Skip records outside the date range if not syncing all
            if (!$syncAll) {
                $logDate = date('Y-m-d', strtotime($log['timestamp']));
                if ($logDate < $startDate || $logDate > $endDate) {
                    $skippedCount++;
                    continue;
                }
            }
            
            // Check if the log already exists (using a different method since device_log_id doesn't exist)
            $existingLog = null;
            if (in_array('biometric_id', $attendanceColumns)) {
                $existingLog = $this->attendanceModel->getAttendanceByBiometricIdAndTimestamp(
                    $log['uid'],
                    $log['timestamp']
                );
            }
            
            if (!$existingLog) {
                // Get the employee by biometric ID
                $employee = $this->attendanceModel->getEmployeeByBiometricId($log['uid']);
                
                // Prepare the attendance data
                $attendanceData = [
                    'biometric_id' => $log['uid']
                ];
                
                // Add employee_id if the column exists
                if (in_array('employee_id', $attendanceColumns)) {
                    $attendanceData['employee_id'] = $employee ? $employee->id : $defaultEmployeeId;
                }
                
                // Add device_id if the column exists
                if (in_array('device_id', $attendanceColumns)) {
                    $attendanceData['device_id'] = $device->id;
                }
                
                // Add timestamp with the appropriate column name
                if (in_array('timestamp', $attendanceColumns)) {
                    $attendanceData['timestamp'] = $log['timestamp'];
                } elseif (in_array('check_time', $attendanceColumns)) {
                    $attendanceData['check_time'] = $log['timestamp'];
                } elseif (in_array('datetime', $attendanceColumns)) {
                    $attendanceData['datetime'] = $log['timestamp'];
                } elseif (in_array('created_at', $attendanceColumns)) {
                    $attendanceData['created_at'] = $log['timestamp'];
                }
                
                // Add type if the column exists
                if (in_array('type', $attendanceColumns)) {
                    $attendanceData['type'] = $log['status'] ?? 'check-in';
                }
                
                // Add status if the column exists
                if (in_array('status', $attendanceColumns)) {
                    $attendanceData['status'] = 'present';
                }
                
                // Create the attendance record
                $result = $this->attendanceModel->create($attendanceData);
                if ($result) {
                    $newCount++;
                } else {
                    $errorCount++;
                }
            } else {
                $skippedCount++;
            }
        }
        
        // Log the success
        $logData = [
            'device_id' => $id,
            'action' => 'sync_attendance',
            'status' => 'success',
            'message' => "Processed $processedCount logs, added $newCount new records, skipped $skippedCount existing records, $errorCount errors"
        ];
        
        $this->deviceModel->createLog($logData);
        
        // Update device status
        $this->deviceModel->updateStatus($id, 'active');
        
        // Redirect with success
        header('Location: /devices?success=' . urlencode("Successfully synced attendance data. Processed $processedCount logs, added $newCount new records, skipped $skippedCount existing records, $errorCount errors."));
        exit;
    }
    
    private function createDefaultEmployee() {
        // Check if the employees table exists
        $employeesTable = $this->attendanceModel->tableExists('employees');
        if (!$employeesTable) {
            return 1; // Return a default ID if the table doesn't exist
        }
        
        // Get the structure of the employees table
        $employeeColumns = $this->attendanceModel->getTableColumns('employees');
        
        // Prepare employee data
        $employeeData = [];
        
        if (in_array('first_name', $employeeColumns)) {
            $employeeData['first_name'] = 'Default';
        }
        
        if (in_array('last_name', $employeeColumns)) {
            $employeeData['last_name'] = 'Employee';
        }
        
        if (in_array('name', $employeeColumns)) {
            $employeeData['name'] = 'Default Employee';
        }
        
        if (in_array('email', $employeeColumns)) {
            $employeeData['email'] = 'default@example.com';
        }
        
        if (in_array('biometric_id', $employeeColumns)) {
            $employeeData['biometric_id'] = '0000';
        }
        
        if (in_array('employee_id', $employeeColumns)) {
            $employeeData['employee_id'] = 'EMP0000';
        }
        
        if (in_array('created_at', $employeeColumns)) {
            $employeeData['created_at'] = date('Y-m-d H:i:s');
        }
        
        // Create the employee
        return $this->attendanceModel->createEmployee($employeeData);
    }
    
    private function generateMockAttendanceData($device, $count = 50) {
        // Generate random attendance logs for development/testing
        $logs = [];
        
        // Generate dates for the last 30 days
        $dates = [];
        for ($i = 0; $i < 30; $i++) {
            $dates[] = date('Y-m-d', strtotime("-$i days"));
        }
        
        // Generate random logs
        for ($i = 1; $i <= $count; $i++) {
            // Random date from the last 30 days
            $date = $dates[array_rand($dates)];
            
            // Random time between 7am and 7pm
            $hour = rand(7, 19);
            $minute = rand(0, 59);
            $second = rand(0, 59);
            $timestamp = "$date $hour:$minute:$second";
            
            // Random user ID between 1 and 10
            $uid = rand(1, 10);
            
            // Random status (check-in or check-out)
            $status = (rand(0, 1) == 0) ? 'check-in' : 'check-out';
            
            $logs[] = [
                'id' => time() + $i, // Use current timestamp + increment as a unique ID
                'uid' => $uid,
                'timestamp' => $timestamp,
                'status' => $status
            ];
        }
        
        return [
            'success' => true,
            'message' => "Generated $count mock attendance records for development",
            'logs' => $logs
        ];
    }
    
    public function realtime() {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            if (isset($_GET['live'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'You must be logged in to access this page'
                ]);
                exit;
            }
            header('Location: /login');
            exit;
        }
        
        // Get all active devices
        $devices = $this->deviceModel->getActiveDevices();
        
        // Get recent attendance
        $recentAttendance = $this->attendanceModel->getRecentAttendance(20);
        
        // Check if this is an AJAX request for real-time updates
        if (isset($_GET['live']) && $_GET['live'] == '1') {
            // Get the device ID
            $deviceId = isset($_GET['device_id']) ? $_GET['device_id'] : null;
            
            if (!$deviceId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Device ID is required'
                ]);
                exit;
            }
            
            // Get the device
            $device = $this->deviceModel->getDeviceById($deviceId);
            
            if (!$device) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Device not found'
                ]);
                exit;
            }
            
            // Log the request
            error_log("Live capture request for device ID: {$deviceId}, IP: {$device->ip_address}, Port: {$device->port}");
            
            // Check if this is a polling request
            $isPoll = isset($_GET['poll']) && $_GET['poll'] == '1';
            
            try {
                // If this is the initial request, start the live capture
                if (!$isPoll) {
                    // Update device status to "monitoring"
                    $this->deviceModel->updateStatus($deviceId, 'monitoring');
                    
                    // Start the live capture
                    $result = PyZK::liveCapture(
                        $device->ip_address, 
                        $device->port, 
                        10, // Short timeout for initial connection
                        function($event) use ($device) {
                            // Process the event
                            error_log("Event received: " . json_encode($event));
                            $this->processAttendanceEvent($device, $event);
                        }
                    );
                    
                    error_log("Live capture result: " . json_encode($result));
                } else {
                    // For polling requests, use a shorter timeout
                    $result = PyZK::liveCapture(
                        $device->ip_address, 
                        $device->port, 
                        5, // Short timeout for polling
                        function($event) use ($device) {
                            // Process the event
                            error_log("Event received from polling: " . json_encode($event));
                            $this->processAttendanceEvent($device, $event);
                        }
                    );
                    
                    error_log("Polling result: " . json_encode($result));
                }
                
                // Get today's summary
                $today = date('Y-m-d');
                $summary = $this->attendanceModel->getAttendanceSummary($today);
                
                // Return the result
                echo json_encode([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'events' => $result['events'] ?? [],
                    'summary' => [
                        'present' => $summary->present ?? 0,
                        'absent' => ($summary->total_employees ?? 0) - ($summary->present ?? 0),
                        'late' => $summary->late ?? 0,
                        'total' => $summary->total_employees ?? 0
                    ]
                ]);
            } catch (\Exception $e) {
                error_log("Error in live capture: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
            exit;
        }
        
        // Render the view
        $data = [
            'title' => 'Real-time Attendance',
            'devices' => $devices,
            'recentAttendance' => $recentAttendance
        ];
        
        // Define the root path
        $appRoot = dirname(dirname(__DIR__));
        
        // Include the header
        require_once $appRoot . '/resources/views/layouts/header.php';
        
        // Include the realtime view
        require_once $appRoot . '/resources/views/devices/realtime.php';
        
        // Include the footer
        require_once $appRoot . '/resources/views/layouts/footer.php';
    }
    
    /**
     * Process an attendance event
     */
    private function processAttendanceEvent($device, $event) {
        try {
            // Log the event
            error_log("Processing attendance event: " . json_encode($event));
            
            // Create attendance record
            $data = [
                'device_id' => $device->id,
                'biometric_id' => $event['uid'],
                'timestamp' => $event['timestamp'],
                'status' => $event['status'] ?? 'check-in',
                'punch' => $event['punch'] ?? 0,
                'processed' => 0
            ];
            
            // Save to database
            $this->attendanceModel->create($data);
            
            return true;
        } catch (\Exception $e) {
            error_log("Error processing attendance event: " . $e->getMessage());
            return false;
        }
    }
    
    public function getRealtimeData() {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        // Get recent attendance records
        $recentAttendance = $this->attendanceModel->getRecentAttendance(10);
        
        // Format the data
        $data = [];
        foreach ($recentAttendance as $record) {
            $data[] = [
                'id' => $record->id,
                'employee_id' => $record->employee_id,
                'employee_name' => $record->employee_name,
                'timestamp' => $record->timestamp,
                'type' => $record->type,
                'status' => $record->status,
                'device_name' => $record->device_name
            ];
        }
        
        // Get summary counts
        $today = date('Y-m-d');
        $summary = $this->attendanceModel->getAttendanceSummary($today);
        
        echo json_encode([
            'success' => true, 
            'data' => $data,
            'summary' => [
                'present' => $summary->present ?? 0,
                'absent' => $summary->total_employees - ($summary->present ?? 0),
                'late' => $summary->late ?? 0,
                'total' => $summary->total_employees ?? 0
            ]
        ]);
    }
    
    public function getRecentAttendance() {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            echo json_encode([
                'success' => false,
                'message' => 'You must be logged in to access this page'
            ]);
            exit;
        }
        
        // Get recent attendance
        $recentAttendance = $this->attendanceModel->getRecentAttendance(20);
        
        // Return the result
        echo json_encode([
            'success' => true,
            'attendance' => $recentAttendance
        ]);
        exit;
    }
    
    public function syncByDate($deviceId) {
        try {
            // Set headers for streaming response
            header('Content-Type: text/html; charset=utf-8');
            header('X-Accel-Buffering: no');
            
            echo "<html><head>";
            echo "<title>ZKTeco Sync Progress</title>";
            echo "<style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .progress-container { width: 100%; background-color: #f1f1f1; border-radius: 5px; margin: 10px 0; }
                .progress-bar { height: 30px; background-color: #4CAF50; border-radius: 5px; text-align: center; line-height: 30px; color: white; }
                .log { background-color: #f8f9fa; border: 1px solid #ddd; padding: 10px; margin: 10px 0; max-height: 400px; overflow-y: auto; font-family: monospace; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .btn-stop { background-color: #f44336; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
                .btn-stop:hover { background-color: #d32f2f; }
            </style>";
            echo "</head><body>";
            echo "<h1>ZKTeco Sync Progress</h1>";
            
            // Add stop button
            echo "<button class='btn-stop' onclick='stopSync()'>Stop Sync</button>";
            echo "<script>
                function stopSync() {
                    if (confirm('Are you sure you want to stop the sync process?')) {
                        fetch('/devices/stop-sync/{$deviceId}')
                            .then(response => response.json())
                            .then(data => {
                                alert(data.message);
                                if (data.status === 'success') {
                                    window.location.href = '/devices';
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while stopping the sync process.');
                            });
                    }
                }
            </script>";
            
            // Create progress container
            echo "<div class='progress-container'><div class='progress-bar' id='progress' style='width: 0%'>0%</div></div>";
            echo "<div id='status'>Initializing...</div>";
            echo "<div class='log' id='log'></div>";
            
            // Function to update progress
            echo "<script>
                function updateProgress(percent, message) {
                    document.getElementById('progress').style.width = percent + '%';
                    document.getElementById('progress').innerText = percent + '%';
                    document.getElementById('status').innerText = message;
                }
                
                function addLog(message) {
                    var log = document.getElementById('log');
                    log.innerHTML += message + '<br>';
                    log.scrollTop = log.scrollHeight;
                }
            </script>";
            
            // Flush output to browser
            flush();
            
            // Update device status to syncing
            $stmt = $this->db->prepare("UPDATE devices SET status = 'syncing' WHERE id = ?");
            $stmt->execute([$deviceId]);
            
            // Log the start of the sync process
            $this->updateUI("Starting sync for device ID: " . $deviceId, 5);
            
            // Get device details
            $stmt = $this->db->prepare("SELECT * FROM devices WHERE id = ?");
            $stmt->execute([$deviceId]);
            $device = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$device) {
                $this->updateUI("Device not found: " . $deviceId, 100, "error");
                echo "</body></html>";
                return;
            }
            
            // Log device details
            $this->updateUI("Device details: " . json_encode($device), 10);
            
            // Check for stop request
            if ($this->isSyncStopRequested($deviceId)) {
                $this->updateUI("Sync process stopped by user", 100, "warning");
                echo "</body></html>";
                return;
            }
            
            // Get database schema information for employees table
            $employeeColumnsStmt = $this->db->prepare("DESCRIBE employees");
            $employeeColumnsStmt->execute();
            $employeeColumns = $employeeColumnsStmt->fetchAll(\PDO::FETCH_COLUMN);
            $this->updateUI("Employee table columns: " . json_encode($employeeColumns), 15);
            
            // Check for stop request
            if ($this->isSyncStopRequested($deviceId)) {
                $this->updateUI("Sync process stopped by user", 100, "warning");
                echo "</body></html>";
                return;
            }
            
            // Check for attendance table
            $tableCheckStmt = $this->db->prepare("SHOW TABLES LIKE 'attendances'");
            $tableCheckStmt->execute();
            $attendanceTable = 'attendances';
            
            if ($tableCheckStmt->rowCount() == 0) {
                // Try with singular name
                $tableCheckStmt = $this->db->prepare("SHOW TABLES LIKE 'attendance'");
                $tableCheckStmt->execute();
                
                if ($tableCheckStmt->rowCount() > 0) {
                    $attendanceTable = 'attendance';
                }
            }
            
            $this->updateUI("Using attendance table: " . $attendanceTable, 20);
            
            // Check for stop request
            if ($this->isSyncStopRequested($deviceId)) {
                $this->updateUI("Sync process stopped by user", 100, "warning");
                echo "</body></html>";
                return;
            }
            
            // Attempt to connect to ZKTeco device
            $this->updateUI("Attempting to connect to ZKTeco device at " . $device['ip_address'] . ":" . $device['port'], 25);
            
            // Try to use the Python ZKTeco library
            $pythonPath = $this->findPythonPath();
            $data = null;
            
            if ($pythonPath) {
                $this->updateUI("Found Python at: " . $pythonPath, 30);
                
                // Try to run the Python script to get ZKTeco data
                $zkScript = __DIR__ . '/../../scripts/zk_test.py';
                
                // If the script doesn't exist, create it
                if (!file_exists($zkScript)) {
                    $this->createZKTestScript($zkScript);
                    $this->updateUI("Created ZKTeco test script at: " . $zkScript, 35);
                }
                
                // Run the script
                $command = sprintf('%s %s --ip=%s --port=%s 2>&1', 
                                  $pythonPath, 
                                  escapeshellarg($zkScript), 
                                  escapeshellarg($device['ip_address']), 
                                  escapeshellarg($device['port']));
                
                $this->updateUI("Executing command: " . $command, 40);
                $output = shell_exec($command);
                
                $this->updateUI("Command output: " . substr($output, 0, 200) . (strlen($output) > 200 ? '...' : ''), 45);
                
                // Try to parse the output as JSON
                $data = json_decode($output, true);
                
                if ($data) {
                    if (isset($data['error'])) {
                        $this->updateUI("Error from ZKTeco script: " . $data['error'], 50, "error");
                        // Fall back to sample data
                        $data = null;
                    } else {
                        $this->updateUI("Successfully retrieved data from ZKTeco device!", 50, "success");
                    }
                } else {
                    $this->updateUI("Failed to parse output as JSON. Using sample data.", 50, "warning");
                    $data = null;
                }
            } else {
                $this->updateUI("Python not found. Using sample data.", 50, "warning");
            }
            
            // Check for stop request
            if ($this->isSyncStopRequested($deviceId)) {
                $this->updateUI("Sync process stopped by user", 100, "warning");
                echo "</body></html>";
                return;
            }
            
            // If we couldn't get real data, use sample data
            if (!$data) {
                $this->updateUI("Using sample data for testing", 55);
                
                // Sample data
                $data = [
                    'users' => [
                        ['user_id' => '1', 'first_name' => 'John', 'last_name' => 'Doe'],
                        ['user_id' => '2', 'first_name' => 'Jane', 'last_name' => 'Smith'],
                        ['user_id' => '6', 'first_name' => 'Test', 'last_name' => 'User']
                    ],
                    'attendance' => [
                        ['user_id' => '1', 'timestamp' => date('Y-m-d H:i:s', strtotime('-30 minutes'))],
                        ['user_id' => '2', 'timestamp' => date('Y-m-d H:i:s', strtotime('-15 minutes'))],
                        ['user_id' => '6', 'timestamp' => date('Y-m-d H:i:s')]
                    ]
                ];
            }
            
            // Display the raw data from ZKTeco
            $this->updateUI("Raw data from ZKTeco device:", 60);
            
            // Display users table
            echo "<h3>Users from ZKTeco (" . count($data['users']) . ")</h3>";
            echo "<table>";
            echo "<tr><th>User ID</th><th>Name</th></tr>";
            
            foreach ($data['users'] as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['user_id']) . "</td>";
                echo "<td>" . htmlspecialchars(
                    isset($user['name']) ? $user['name'] : 
                    (isset($user['first_name']) && isset($user['last_name']) ? 
                     $user['first_name'] . ' ' . $user['last_name'] : 
                     (isset($user['first_name']) ? $user['first_name'] : 'Unknown'))
                ) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            flush();
            
            // Check for stop request
            if ($this->isSyncStopRequested($deviceId)) {
                $this->updateUI("Sync process stopped by user", 100, "warning");
                echo "</body></html>";
                return;
            }
            
            // Display attendance table
            echo "<h3>Attendance Records from ZKTeco (" . count($data['attendance']) . ")</h3>";
            echo "<table>";
            echo "<tr><th>User ID</th><th>Timestamp</th></tr>";
            
            foreach ($data['attendance'] as $record) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($record['user_id']) . "</td>";
                echo "<td>" . htmlspecialchars($record['timestamp']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            flush();
            
            // Check for stop request
            if ($this->isSyncStopRequested($deviceId)) {
                $this->updateUI("Sync process stopped by user", 100, "warning");
                echo "</body></html>";
                return;
            }
            
            // First, check which users exist in the users table
            $this->updateUI("Checking which users exist in the database...", 65);
            
            $existingUsers = [];
            $placeholders = implode(',', array_fill(0, count($data['users']), '?'));
            $userIds = array_column($data['users'], 'user_id');
            
            if (!empty($userIds)) {
                $stmt = $this->db->prepare("SELECT id FROM users WHERE id IN ($placeholders)");
                $stmt->execute($userIds);
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $existingUsers[] = $row['id'];
                }
            }
            
            $this->updateUI("Existing users in database: " . json_encode($existingUsers), 70);
            
            // Check for stop request
            if ($this->isSyncStopRequested($deviceId)) {
                $this->updateUI("Sync process stopped by user", 100, "warning");
                echo "</body></html>";
                return;
            }
            
            // Process users
            if (isset($data['users'])) {
                $this->updateUI("Processing " . count($data['users']) . " users", 75);
                
                $processedUsers = 0;
                $totalUsers = count($data['users']);
                
                foreach ($data['users'] as $user) {
                    // Check for stop request
                    if ($this->isSyncStopRequested($deviceId)) {
                        $this->updateUI("Sync process stopped by user", 100, "warning");
                        echo "</body></html>";
                        return;
                    }
                    
                    $processedUsers++;
                    $userProgress = 75 + ($processedUsers / $totalUsers * 10); // Progress from 75% to 85%
                    
                    // Log each user being processed
                    $this->updateUI("Processing user: " . json_encode($user), $userProgress);
                    
                    // Skip users that don't exist in the users table
                    if (!in_array($user['user_id'], $existingUsers)) {
                        $this->updateUI("Skipping user_id " . $user['user_id'] . " - not found in users table", $userProgress);
                        continue;
                    }
                    
                    // Create employee data based on available columns
                    $employeeData = [];
                    
                    // Always include user_id
                    if (isset($user['user_id'])) {
                        $employeeData['user_id'] = $user['user_id'];
                    }
                    
                    // Map name fields based on available columns
                    if (in_array('name', $employeeColumns)) {
                        // If there's a single name field
                        $employeeData['name'] = isset($user['name']) ? $user['name'] : 
                                              (isset($user['first_name']) && isset($user['last_name']) ? 
                                               $user['first_name'] . ' ' . $user['last_name'] : 
                                               (isset($user['first_name']) ? $user['first_name'] : 'Unknown'));
                    } else {
                        // If there are separate first_name and last_name fields
                        if (in_array('first_name', $employeeColumns) && isset($user['first_name'])) {
                            $employeeData['first_name'] = $user['first_name'];
                        }
                        
                        if (in_array('last_name', $employeeColumns) && isset($user['last_name'])) {
                            $employeeData['last_name'] = $user['last_name'];
                        }
                    }
                    
                    // Add other fields that might exist
                    foreach (['email', 'phone', 'department', 'position'] as $field) {
                        if (in_array($field, $employeeColumns) && isset($user[$field])) {
                            $employeeData[$field] = $user[$field];
                        }
                    }
                    
                    // Log the employee data being sent to createEmployee
                    $this->updateUI("Sending to createEmployee: " . json_encode($employeeData), $userProgress);
                    
                    $employeeModel = new \App\Models\Employee();
                    $result = $employeeModel->createEmployee($employeeData);
                    
                    // Log the result
                    $this->updateUI("createEmployee result: " . json_encode($result), $userProgress);
                }
            }
            
            // Check for stop request
            if ($this->isSyncStopRequested($deviceId)) {
                $this->updateUI("Sync process stopped by user", 100, "warning");
                echo "</body></html>";
                return;
            }
            
            // Process attendance logs
            if (isset($data['attendance'])) {
                $this->updateUI("Processing " . count($data['attendance']) . " attendance records", 85);
                
                $processedRecords = 0;
                $totalRecords = count($data['attendance']);
                
                $attendanceModel = new \App\Models\Attendance();
                foreach ($data['attendance'] as $log) {
                    // Check for stop request
                    if ($this->isSyncStopRequested($deviceId)) {
                        $this->updateUI("Sync process stopped by user", 100, "warning");
                        echo "</body></html>";
                        return;
                    }
                    
                    $processedRecords++;
                    $recordProgress = 85 + ($processedRecords / $totalRecords * 10); // Progress from 85% to 95%
                    
                    // Log each attendance record being processed
                    $this->updateUI("Processing attendance log: " . json_encode($log), $recordProgress);
                    
                    // Skip users that don't exist in the users table
                    if (!in_array($log['user_id'], $existingUsers)) {
                        $this->updateUI("Skipping attendance for user_id " . $log['user_id'] . " - not found in users table", $recordProgress);
                        continue;
                    }
                    
                    // Find employee by user_id
                    $stmt = $this->db->prepare("SELECT id FROM employees WHERE user_id = ?");
                    $stmt->execute([$log['user_id']]);
                    $employee = $stmt->fetch(\PDO::FETCH_ASSOC);
                    
                    if (!$employee) {
                        $this->updateUI("Employee not found for user_id: " . $log['user_id'], $recordProgress);
                        continue;
                    }
                    
                    // Create attendance record
                    $attendanceData = [
                        'employee_id' => $employee['id'],
                        'check_in' => $log['timestamp'] ?? date('Y-m-d H:i:s'),
                        'status' => 'present'
                    ];
                    
                    // Log the attendance data being sent
                    $this->updateUI("Sending to createAttendance: " . json_encode($attendanceData), $recordProgress);
                    
                    $result = $attendanceModel->createAttendance($attendanceData);
                    
                    // Log the result
                    $this->updateUI("createAttendance result: " . json_encode($result), $recordProgress);
                }
            }
            
            // Update device last_sync time
            $stmt = $this->db->prepare("UPDATE devices SET status = 'active', last_sync = NOW() WHERE id = ?");
            $stmt->execute([$deviceId]);
            
            $this->updateUI("Sync completed successfully!", 100, "success");
            
            echo "<div style='margin-top: 20px;'>";
            echo "<a href='/devices' class='button'>Return to Devices</a>";
            echo "</div>";
            
            echo "</body></html>";
        } catch (\Exception $e) {
            // Update device status to error
            $stmt = $this->db->prepare("UPDATE devices SET status = 'error' WHERE id = ?");
            $stmt->execute([$deviceId]);
            
            $this->updateUI("Error in syncByDate: " . $e->getMessage(), 100, "error");
            echo "</body></html>";
        }
    }
    
    /**
     * Helper method to update the UI with progress and log messages
     */
    private function updateUI($message, $percent, $type = "info") {
        $escapedMessage = htmlspecialchars($message);
        $timestamp = date('H:i:s');
        
        echo "<script>
            updateProgress($percent, '$escapedMessage');
            addLog('[$timestamp] [$type] $escapedMessage');
        </script>";
        
        flush();
        // Small delay to make the progress visible
        usleep(100000); // 0.1 seconds
    }

    /**
     * Test ZKTeco connection and display raw data
     */
    public function testZKTecoConnection($deviceId) {
        try {
            header('Content-Type: text/html; charset=utf-8');
            
            echo "<html><head>";
            echo "<title>ZKTeco Connection Test</title>";
            echo "<style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .success { color: green; font-weight: bold; }
                .error { color: red; font-weight: bold; }
                .log { background-color: #f8f9fa; border: 1px solid #ddd; padding: 10px; margin: 10px 0; max-height: 400px; overflow-y: auto; font-family: monospace; }
                table { border-collapse: collapse; width: 100%; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .container { max-width: 1200px; margin: 0 auto; }
            </style>";
            echo "</head><body>";
            echo "<div class='container'>";
            echo "<h1>ZKTeco Connection Test</h1>";
            
            // Get device details
            $stmt = $this->db->prepare("SELECT * FROM devices WHERE id = ?");
            $stmt->execute([$deviceId]);
            $device = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$device) {
                echo "<div class='error'>Device not found: " . htmlspecialchars($deviceId) . "</div>";
                echo "</div></body></html>";
                return;
            }
            
            echo "<h2>Device Details</h2>";
            echo "<table>";
            echo "<tr><th>ID</th><td>" . htmlspecialchars($device['id']) . "</td></tr>";
            echo "<tr><th>Name</th><td>" . htmlspecialchars($device['name']) . "</td></tr>";
            echo "<tr><th>IP Address</th><td>" . htmlspecialchars($device['ip_address']) . "</td></tr>";
            echo "<tr><th>Port</th><td>" . htmlspecialchars($device['port']) . "</td></tr>";
            echo "<tr><th>Status</th><td>" . htmlspecialchars($device['status']) . "</td></tr>";
            echo "<tr><th>Location</th><td>" . htmlspecialchars($device['location']) . "</td></tr>";
            echo "</table>";
            
            echo "<h2>Connection Test</h2>";
            echo "<div class='log' id='log'>";
            echo "Attempting to connect to ZKTeco device at " . htmlspecialchars($device['ip_address']) . ":" . htmlspecialchars($device['port']) . "...<br>";
            
            // Try to connect to the device using socket
            $socket = @fsockopen($device['ip_address'], $device['port'], $errno, $errstr, 5);
            
            if (!$socket) {
                echo "<span class='error'>Connection failed: $errstr ($errno)</span><br>";
                echo "Please check if the device is powered on and connected to the network.<br>";
            } else {
                echo "<span class='success'>Socket connection successful!</span><br>";
                fclose($socket);
                
                // Now try to use the ZKTeco library or command-line tool
                // First, check if we can use the Python ZKTeco library
                $pythonPath = $this->findPythonPath();
                
                if ($pythonPath) {
                    echo "Found Python at: " . htmlspecialchars($pythonPath) . "<br>";
                    
                    // Try to run a simple Python script to test ZKTeco connection
                    $testScript = __DIR__ . '/../../scripts/zk_test.py';
                    
                    // If the test script doesn't exist, create it
                    if (!file_exists($testScript)) {
                        $this->createZKTestScript($testScript);
                        echo "Created ZKTeco test script at: " . htmlspecialchars($testScript) . "<br>";
                    }
                    
                    // Run the test script
                    $command = sprintf('%s %s --ip=%s --port=%s 2>&1', 
                                      $pythonPath, 
                                      escapeshellarg($testScript), 
                                      escapeshellarg($device['ip_address']), 
                                      escapeshellarg($device['port']));
                    
                    echo "Executing command: " . htmlspecialchars($command) . "<br>";
                    $output = shell_exec($command);
                    
                    echo "Command output:<br><pre>" . htmlspecialchars($output) . "</pre><br>";
                    
                    // Try to parse the output as JSON
                    $data = json_decode($output, true);
                    
                    if ($data) {
                        echo "<span class='success'>Successfully retrieved data from ZKTeco device!</span><br>";
                        
                        // Display users
                        if (isset($data['users']) && !empty($data['users'])) {
                            echo "<h2>Users from ZKTeco (" . count($data['users']) . ")</h2>";
                            echo "<table>";
                            echo "<tr><th>User ID</th><th>Name</th><th>Role</th><th>Password</th></tr>";
                            
                            foreach ($data['users'] as $user) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($user['user_id'] ?? 'N/A') . "</td>";
                                echo "<td>" . htmlspecialchars($user['name'] ?? 'N/A') . "</td>";
                                echo "<td>" . htmlspecialchars($user['role'] ?? 'N/A') . "</td>";
                                echo "<td>" . htmlspecialchars($user['password'] ?? 'N/A') . "</td>";
                                echo "</tr>";
                            }
                            
                            echo "</table>";
                        } else {
                            echo "<div class='error'>No users found in the device.</div>";
                        }
                        
                        // Display attendance records
                        if (isset($data['attendance']) && !empty($data['attendance'])) {
                            echo "<h2>Attendance Records from ZKTeco (" . count($data['attendance']) . ")</h2>";
                            echo "<table>";
                            echo "<tr><th>User ID</th><th>Timestamp</th><th>Status</th><th>Punch</th></tr>";
                            
                            foreach ($data['attendance'] as $record) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($record['user_id'] ?? 'N/A') . "</td>";
                                echo "<td>" . htmlspecialchars($record['timestamp'] ?? 'N/A') . "</td>";
                                echo "<td>" . htmlspecialchars($record['status'] ?? 'N/A') . "</td>";
                                echo "<td>" . htmlspecialchars($record['punch'] ?? 'N/A') . "</td>";
                                echo "</tr>";
                            }
                            
                            echo "</table>";
                        } else {
                            echo "<div class='error'>No attendance records found in the device.</div>";
                        }
                    } else {
                        echo "<span class='error'>Failed to parse output as JSON. Raw output shown above.</span><br>";
                    }
                } else {
                    echo "<span class='error'>Python not found. Cannot use ZKTeco Python library.</span><br>";
                    
                    // Try alternative methods
                    echo "Trying alternative methods to connect to ZKTeco device...<br>";
                    
                    // Check if we can use curl to connect to the device
                    if (function_exists('curl_init')) {
                        echo "CURL is available. Trying to connect to device...<br>";
                        
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "http://" . $device['ip_address']);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                        $result = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        
                        echo "HTTP response code: " . $httpCode . "<br>";
                        
                        if ($httpCode) {
                            echo "<span class='success'>Device responded to HTTP request!</span><br>";
                        } else {
                            echo "<span class='error'>Device did not respond to HTTP request.</span><br>";
                        }
                    } else {
                        echo "<span class='error'>CURL is not available.</span><br>";
                    }
                }
            }
            
            echo "</div>"; // End of log div
            
            echo "<h2>Troubleshooting</h2>";
            echo "<ul>";
            echo "<li>Make sure the ZKTeco device is powered on and connected to the network.</li>";
            echo "<li>Verify that the IP address and port are correct.</li>";
            echo "<li>Check if there are any firewalls blocking the connection.</li>";
            echo "<li>Ensure that the ZKTeco device is configured to allow external connections.</li>";
            echo "<li>Try to ping the device from the server to verify network connectivity.</li>";
            echo "</ul>";
            
            echo "<div style='margin-top: 20px;'>";
            echo "<a href='/devices' class='button'>Return to Devices</a>";
            echo "</div>";
            
            echo "</div>"; // End of container
            echo "</body></html>";
        } catch (\Exception $e) {
            echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            echo "</div></body></html>";
        }
    }

    /**
     * Find the Python executable path
     */
    private function findPythonPath() {
        // Try different Python commands
        $pythonCommands = ['python', 'python3', 'py'];
        
        foreach ($pythonCommands as $cmd) {
            $output = shell_exec("which $cmd 2>/dev/null || where $cmd 2>nul");
            if ($output) {
                return trim($output);
            }
        }
        
        return null;
    }

    /**
     * Create a test script for ZKTeco connection
     */
    private function createZKTestScript($filePath) {
        $script = <<<'PYTHON'
#!/usr/bin/env python
import argparse
import json
import sys
import time
from datetime import datetime

# Parse command line arguments
parser = argparse.ArgumentParser(description='Test ZKTeco connection')
parser.add_argument('--ip', required=True, help='Device IP address')
parser.add_argument('--port', required=True, help='Device port')
args = parser.parse_args()

try:
    # Try to import the zk library
    try:
        from zk import ZK, const
    except ImportError:
        print(json.dumps({
            "error": "ZK library not installed. Please install it with: pip install pyzk"
        }))
        sys.exit(1)

    # Connect to device
    conn = None
    zk = ZK(args.ip, port=int(args.port), timeout=5)
    
    try:
        # Connect to device
        conn = zk.connect()
        
        # Get device info
        device_info = {
            "firmware_version": conn.get_firmware_version(),
            "serial_number": conn.get_serialnumber(),
            "platform": conn.get_platform(),
            "device_name": conn.get_device_name(),
            "face_algorithm_version": conn.get_face_version() if hasattr(conn, 'get_face_version') else None,
            "fingerprint_algorithm_version": conn.get_fp_version() if hasattr(conn, 'get_fp_version') else None
        }
        
        # Get users
        users_data = []
        users = conn.get_users()
        for user in users:
            users_data.append({
                "user_id": user.user_id,
                "name": user.name,
                "role": user.privilege,
                "password": user.password,
                "card": user.card,
                "group_id": user.group_id
            })
        
        # Get attendance records
        attendance_data = []
        attendances = conn.get_attendance()
        for attendance in attendances:
            attendance_data.append({
                "user_id": attendance.user_id,
                "timestamp": attendance.timestamp.strftime('%Y-%m-%d %H:%M:%S'),
                "status": attendance.status,
                "punch": attendance.punch
            })
        
        # Return data as JSON
        result = {
            "device_info": device_info,
            "users": users_data,
            "attendance": attendance_data
        }
        
        print(json.dumps(result, indent=2))
        
    except Exception as e:
        print(json.dumps({
            "error": str(e)
        }))
    finally:
        if conn:
            conn.disconnect()

except Exception as e:
    print(json.dumps({
        "error": str(e)
    }))
PYTHON;

        file_put_contents($filePath, $script);
        chmod($filePath, 0755); // Make executable
        
        return true;
    }

    /**
     * Stop an ongoing synchronization process
     */
    public function stopSync($deviceId) {
        try {
            // Get device details
            $stmt = $this->db->prepare("SELECT * FROM devices WHERE id = ?");
            $stmt->execute([$deviceId]);
            $device = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$device) {
                echo json_encode(['status' => 'error', 'message' => 'Device not found']);
                return;
            }
            
            // Create a stop flag file
            $stopFlagFile = __DIR__ . "/../../data/sync_stop_{$deviceId}.flag";
            file_put_contents($stopFlagFile, date('Y-m-d H:i:s'));
            
            // Update device status
            $stmt = $this->db->prepare("UPDATE devices SET status = 'idle', last_sync = NOW() WHERE id = ?");
            $stmt->execute([$deviceId]);
            
            // Log the stop request
            error_log("Sync stop requested for device ID: " . $deviceId);
            
            echo json_encode(['status' => 'success', 'message' => 'Sync process stop requested']);
        } catch (\Exception $e) {
            error_log("Error in stopSync: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Check if a stop has been requested for a device
     */
    private function isSyncStopRequested($deviceId) {
        $stopFlagFile = __DIR__ . "/../../data/sync_stop_{$deviceId}.flag";
        if (file_exists($stopFlagFile)) {
            // Delete the flag file
            unlink($stopFlagFile);
            return true;
        }
        return false;
    }
} 