<?php
// Script to reset the system by killing all related processes

echo "Starting system reset...\n";

// Kill all PHP processes related to the built-in server
echo "Killing PHP server processes...\n";
exec("taskkill /F /IM php.exe 2>NUL", $output, $return);
if ($return === 0) {
    echo "PHP processes terminated successfully.\n";
} else {
    echo "No PHP processes found or could not terminate them.\n";
}

// Kill all Python processes
echo "Killing Python processes...\n";
exec("taskkill /F /IM python.exe 2>NUL", $output, $return);
if ($return === 0) {
    echo "Python processes terminated successfully.\n";
} else {
    echo "No Python processes found or could not terminate them.\n";
}

// Wait a moment for processes to fully terminate
sleep(2);

echo "System reset completed. You can now restart the PHP server with:\n";
echo "php -S localhost:8000 -t public\n"; 