<?php

namespace App\Helpers;

class PyZK {
    /**
     * Test connection to a ZKTeco device using PyZK
     * 
     * @param string $ip      Device IP address
     * @param int    $port    Device port (default: 4370)
     * @param int    $timeout Connection timeout in seconds (default: 5)
     * @return array          Connection test result
     */
    public static function testConnection($ip, $port = 4370, $timeout = 5) {
        $command = "python " . self::getScriptPath() . " test $ip $port $timeout";
        $output = self::executeCommand($command);
        
        return json_decode($output, true) ?: [
            'status' => 'error',
            'message' => 'Failed to parse response: ' . $output
        ];
    }
    
    /**
     * Get attendance logs from a ZKTeco device using PyZK
     * 
     * @param string $ip      Device IP address
     * @param int    $port    Device port (default: 4370)
     * @param bool   $getAll  Whether to get all records (default: false)
     * @return array          Attendance logs
     */
    public static function getAttendance($ip, $port = 4370, $getAll = false) {
        $allParam = $getAll ? "all" : "";
        $command = "python " . self::getScriptPath() . " get_attendance $ip $port $allParam";
        $output = self::executeCommand($command);
        
        $result = json_decode($output, true);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to parse response: ' . $output
            ];
        }
        
        if ($result['status'] === 'error') {
            return [
                'success' => false,
                'message' => $result['message']
            ];
        }
        
        // Format the records to match the expected format
        $logs = [];
        foreach ($result['records'] as $record) {
            $logs[] = [
                'id' => $record['id'],
                'uid' => $record['uid'],
                'timestamp' => $record['timestamp'],
                'status' => $record['status'] ?? 'check-in'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Successfully retrieved attendance logs',
            'logs' => $logs
        ];
    }
    
    /**
     * Capture attendance events in real-time from a ZKTeco device
     * 
     * @param string   $ip        Device IP address
     * @param int      $port      Device port (default: 4370)
     * @param int      $timeout   Timeout in seconds (default: 60)
     * @param callable $callback  Callback function to process events (optional)
     * @return array              Captured events
     */
    public static function liveCapture($ip, $port = 4370, $timeout = 60, $callback = null) {
        error_log("Starting live capture for IP: {$ip}, Port: {$port}, Timeout: {$timeout}");
        
        $command = "python " . self::getScriptPath() . " live_capture $ip $port $timeout";
        error_log("Executing command: {$command}");
        
        // Start the process
        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];
        
        $process = proc_open($command, $descriptorspec, $pipes);
        
        if (!is_resource($process)) {
            error_log("Failed to start the live capture process");
            return [
                'success' => false,
                'message' => 'Failed to start the live capture process'
            ];
        }
        
        // Set pipes to non-blocking mode
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);
        
        $events = [];
        $startTime = time();
        $running = true;
        
        error_log("Live capture process started, waiting for events...");
        
        // Set a hard limit on execution time to prevent runaway processes
        $maxExecutionTime = min($timeout, 300); // Maximum 5 minutes per request
        
        try {
            while ($running && (time() - $startTime) < $maxExecutionTime) {
                $stdout = fgets($pipes[1]);
                
                if ($stdout) {
                    error_log("Received output: " . trim($stdout));
                    $data = json_decode($stdout, true);
                    
                    if ($data) {
                        if ($data['status'] === 'event' && isset($data['data'])) {
                            error_log("Received event: " . json_encode($data['data']));
                            $events[] = $data['data'];
                            
                            // Call the callback function if provided
                            if (is_callable($callback)) {
                                call_user_func($callback, $data['data']);
                            }
                        } elseif ($data['status'] === 'connected') {
                            error_log("Successfully connected to device");
                        } elseif ($data['status'] === 'completed' || $data['status'] === 'error') {
                            error_log("Received status: " . $data['status'] . ", message: " . ($data['message'] ?? 'No message'));
                            $running = false;
                        }
                    }
                }
                
                // Check stderr for errors
                $stderr = fgets($pipes[2]);
                if ($stderr) {
                    error_log("PyZK Error: " . $stderr);
                }
                
                // Avoid CPU hogging
                usleep(100000); // 100ms
            }
        } catch (\Exception $e) {
            error_log("Exception in live capture: " . $e->getMessage());
        } finally {
            // Always ensure we clean up properly
            foreach ($pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            
            // Terminate the process
            if (is_resource($process)) {
                // Try to terminate gracefully first
                proc_terminate($process);
                // Force kill after a short delay if still running
                usleep(500000); // 500ms
                proc_close($process);
            }
        }
        
        error_log("Closing live capture process after " . (time() - $startTime) . " seconds");
        
        return [
            'success' => true,
            'message' => 'Live capture completed',
            'events' => $events
        ];
    }
    
    /**
     * Get the path to the Python script
     * 
     * @return string  Path to the Python script
     */
    private static function getScriptPath() {
        $scriptPath = dirname(dirname(__DIR__)) . '/scripts/zk_realtime.py';
        
        // Check if the script exists
        if (!file_exists($scriptPath)) {
            error_log("ERROR: Python script not found at: " . $scriptPath);
        } else {
            // Check if the script is executable
            if (!is_executable($scriptPath) && !chmod($scriptPath, 0755)) {
                error_log("WARNING: Could not make Python script executable: " . $scriptPath);
            }
        }
        
        return $scriptPath;
    }
    
    /**
     * Execute a command and return the output
     * 
     * @param string $command  Command to execute
     * @param int    $timeout  Command timeout in seconds (default: 30)
     * @return string          Command output
     */
    private static function executeCommand($command, $timeout = 30) {
        // Add error logging for command execution
        error_log("Executing command: " . $command);
        
        // Set a timeout for the command
        if (stripos(PHP_OS, 'WIN') === 0) {
            // Windows
            $command = "timeout /t $timeout /nobreak > nul & " . $command;
        } else {
            // Linux/Unix/Mac
            $command = "timeout $timeout " . $command;
        }
        
        // Use 2>&1 to capture both stdout and stderr
        $output = shell_exec($command . " 2>&1");
        
        if ($output === null) {
            error_log("Command execution failed: " . $command);
            return '{"status":"error","message":"Command execution failed"}';
        }
        
        error_log("Command output: " . substr($output, 0, 200) . (strlen($output) > 200 ? '...' : ''));
        return $output;
    }
    
    /**
     * Get attendance data from a ZKTeco device by date range
     * 
     * @param string $ip         Device IP address
     * @param string $startDate  Start date (format: YYYY-MM-DD)
     * @param string $endDate    End date (format: YYYY-MM-DD)
     * @param int    $port       Device port (default: 4370)
     * @return array             Attendance data
     */
    public static function getAttendanceByDateRange($ip, $startDate, $endDate, $port = 4370) {
        $command = "python " . self::getScriptPath() . " get_attendance_by_date $ip $port $startDate $endDate";
        $output = self::executeCommand($command);
        
        $result = json_decode($output, true);
        
        if ($result === null) {
            return [
                'success' => false,
                'message' => 'Failed to parse response: ' . $output,
                'data' => []
            ];
        }
        
        return $result;
    }
    
    /**
     * Get real-time attendance data from a ZKTeco device
     * 
     * @param string $ip    Device IP address
     * @param int    $port  Device port (default: 4370)
     * @return array        Real-time attendance data
     */
    public static function getRealTimeAttendance($ip, $port = 4370) {
        // Use 'live_capture' command instead of 'get_realtime'
        $command = "python " . self::getScriptPath() . " live_capture $ip $port";
        $output = self::executeCommand($command);
        
        // Log the raw output for debugging
        error_log("PyZK getRealTimeAttendance raw output: " . $output);
        
        $result = json_decode($output, true);
        
        if (!$result) {
            error_log("Failed to parse output from Python script: " . $output);
            return [
                'success' => false,
                'message' => 'Failed to parse response: ' . $output,
                'records' => []
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Successfully retrieved real-time attendance data',
            'records' => $result['records'] ?? []
        ];
    }
    
    /**
     * Start real-time capture on a ZKTeco device
     * 
     * @param string $ip    Device IP address
     * @param int    $port  Device port (default: 4370)
     * @return array        Result of the operation
     */
    public static function startRealTimeCapture($ip, $port = 4370) {
        // Use 'live_capture' command with 'start' parameter
        $command = "python " . self::getScriptPath() . " live_capture $ip $port start";
        $output = self::executeCommand($command);
        
        $result = json_decode($output, true);
        
        if (!$result) {
            error_log("Failed to parse output from Python script: " . $output);
            return [
                'success' => false,
                'message' => 'Failed to parse response: ' . $output
            ];
        }
        
        return [
            'success' => $result['status'] === 'success',
            'message' => $result['message'] ?? 'Unknown error'
        ];
    }
    
    /**
     * Stop real-time capture on a ZKTeco device
     * 
     * @param string $ip    Device IP address
     * @param int    $port  Device port (default: 4370)
     * @return array        Result of the operation
     */
    public static function stopRealTimeCapture($ip, $port = 4370) {
        // Use 'live_capture' command with 'stop' parameter
        $command = "python " . self::getScriptPath() . " live_capture $ip $port stop";
        $output = self::executeCommand($command);
        
        $result = json_decode($output, true);
        
        if (!$result) {
            error_log("Failed to parse output from Python script: " . $output);
            return [
                'success' => false,
                'message' => 'Failed to parse response: ' . $output
            ];
        }
        
        return [
            'success' => $result['status'] === 'success',
            'message' => $result['message'] ?? 'Unknown error'
        ];
    }
} 