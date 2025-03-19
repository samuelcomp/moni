<?php
// Diagnostic script to identify issues (Windows compatible)

echo "Starting system diagnostics...\n\n";

// Check running processes
echo "Checking running processes...\n";
exec("tasklist /FI \"IMAGENAME eq php.exe\" /FO LIST", $phpProcesses);
exec("tasklist /FI \"IMAGENAME eq python.exe\" /FO LIST", $pythonProcesses);

echo "PHP Processes:\n";
echo implode("\n", $phpProcesses) . "\n\n";

echo "Python Processes:\n";
echo implode("\n", $pythonProcesses) . "\n\n";

// Check memory usage
echo "Checking memory usage...\n";
exec("wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value", $memoryInfo);
echo implode("\n", $memoryInfo) . "\n\n";

// Check for error logs
echo "Checking error logs...\n";
$errorLogPath = ini_get('error_log');
if (file_exists($errorLogPath)) {
    echo "PHP error log found at: $errorLogPath\n";
    echo "Last 20 lines of PHP error log:\n";
    
    // Windows-compatible way to read the last lines of a file
    $file = new SplFileObject($errorLogPath);
    $file->seek(PHP_INT_MAX); // Seek to end of file
    $totalLines = $file->key(); // Get total line count
    
    $lastLines = [];
    $linesToRead = min(20, $totalLines);
    $startLine = max(0, $totalLines - $linesToRead);
    
    $file->seek($startLine);
    for ($i = 0; $i < $linesToRead; $i++) {
        $lastLines[] = $file->current();
        $file->next();
    }
    
    echo implode("", $lastLines) . "\n\n";
} else {
    echo "PHP error log not found at: $errorLogPath\n\n";
    
    // Try to find error logs in common locations
    $possibleLogLocations = [
        'C:/xampp/php/logs/php_error_log',
        'C:/xampp/apache/logs/error.log',
        './error_log',
        './php_error.log'
    ];
    
    foreach ($possibleLogLocations as $logPath) {
        if (file_exists($logPath)) {
            echo "Found potential error log at: $logPath\n";
            echo "Last few lines:\n";
            
            $file = new SplFileObject($logPath);
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();
            
            $lastLines = [];
            $linesToRead = min(20, $totalLines);
            $startLine = max(0, $totalLines - $linesToRead);
            
            $file->seek($startLine);
            for ($i = 0; $i < $linesToRead; $i++) {
                $lastLines[] = $file->current();
                $file->next();
            }
            
            echo implode("", $lastLines) . "\n\n";
        }
    }
}

// Check browser connections
echo "Checking browser connections (netstat)...\n";
exec("netstat -an | findstr 8000", $connections);
echo implode("\n", $connections) . "\n\n";

// Check for any hanging HTTP requests
echo "Checking for hanging HTTP requests...\n";
exec("netstat -ano | findstr ESTABLISHED | findstr :8000", $hangingRequests);
echo implode("\n", $hangingRequests) . "\n\n";

// Check disk space
echo "Checking disk space...\n";
exec("wmic logicaldisk get deviceid, freespace, size, volumename", $diskSpace);
echo implode("\n", $diskSpace) . "\n\n";

echo "Diagnostics completed.\n"; 