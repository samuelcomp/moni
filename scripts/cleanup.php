<?php
// Script to clean up any zombie Python processes

// Find all Python processes related to our ZK script
$command = "ps aux | grep 'python.*zk_realtime.py' | grep -v grep";
exec($command, $output);

foreach ($output as $line) {
    // Extract the PID
    $parts = preg_split('/\s+/', trim($line));
    if (isset($parts[1]) && is_numeric($parts[1])) {
        $pid = $parts[1];
        echo "Killing Python process with PID: $pid\n";
        exec("kill -9 $pid");
    }
}

echo "Cleanup completed\n"; 