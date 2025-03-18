<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create database connection
$db = Database::getInstance()->getConnection();

// Create roles if they don't exist
try {
    // Check if roles table exists
    $db->query("SELECT 1 FROM roles LIMIT 1");
    
    // Check if admin role exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
    $stmt->execute(['admin']);
    
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO roles (name) VALUES ('admin')");
        echo "Admin role created.\n";
    }
    
    // Check if employee role exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
    $stmt->execute(['employee']);
    
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO roles (name) VALUES ('employee')");
        echo "Employee role created.\n";
    }
} catch (\PDOException $e) {
    echo "Roles table not found or error creating roles: " . $e->getMessage() . "\n";
}

// Get admin role ID
$stmt = $db->query("SELECT id FROM roles WHERE name = 'admin'");
$adminRole = $stmt->fetch(\PDO::FETCH_OBJ);
$adminRoleId = $adminRole->id ?? null;

// Get employee role ID
$stmt = $db->query("SELECT id FROM roles WHERE name = 'employee'");
$employeeRole = $stmt->fetch(\PDO::FETCH_OBJ);
$employeeRoleId = $employeeRole->id ?? null;

if (!$adminRoleId || !$employeeRoleId) {
    echo "Required roles not found. Please check your roles table.\n";
    exit;
}

// Create admin user
$userModel = new User();
$adminEmail = 'admin@example.com'; // Hardcoded email
$adminExists = $userModel->getUserByEmail($adminEmail);

if (!$adminExists) {
    $adminId = $userModel->create([
        'username' => 'Administrator',
        'email' => $adminEmail,
        'password' => password_hash('admin123', PASSWORD_DEFAULT), // Hardcoded password
        'role_id' => $adminRoleId
    ]);
    
    echo "Admin user created successfully.\n";
} else {
    echo "Admin user already exists.\n";
}

// Create default departments
$departmentModel = new Department();
$departments = [
    ['name' => 'Human Resources', 'code' => 'HR'],
    ['name' => 'Information Technology', 'code' => 'IT'],
    ['name' => 'Finance', 'code' => 'FIN'],
    ['name' => 'Marketing', 'code' => 'MKT'],
    ['name' => 'Operations', 'code' => 'OPS']
];

foreach ($departments as $dept) {
    $exists = $db->prepare("SELECT COUNT(*) FROM departments WHERE name = ?");
    $exists->execute([$dept['name']]);
    
    if ($exists->fetchColumn() == 0) {
        $departmentModel->create($dept);
        echo "Department '{$dept['name']}' created.\n";
    } else {
        echo "Department '{$dept['name']}' already exists.\n";
    }
}

// Create default shifts
try {
    // Check if shifts table exists
    $db->query("SELECT 1 FROM shifts LIMIT 1");
    
    // Create shifts if table exists
    $shiftModel = new Shift();
    $shifts = [
        ['name' => 'Morning Shift', 'start_time' => '09:00:00', 'end_time' => '17:00:00'],
        ['name' => 'Evening Shift', 'start_time' => '17:00:00', 'end_time' => '01:00:00'],
        ['name' => 'Night Shift', 'start_time' => '00:00:00', 'end_time' => '08:00:00'],
        ['name' => 'Flexible Hours', 'start_time' => '10:00:00', 'end_time' => '19:00:00']
    ];

    foreach ($shifts as $shift) {
        $exists = $db->prepare("SELECT COUNT(*) FROM shifts WHERE name = ?");
        $exists->execute([$shift['name']]);
        
        if ($exists->fetchColumn() == 0) {
            $shiftModel->create($shift);
            echo "Shift '{$shift['name']}' created.\n";
        } else {
            echo "Shift '{$shift['name']}' already exists.\n";
        }
    }
} catch (\PDOException $e) {
    echo "Shifts table not found or error creating shifts: " . $e->getMessage() . "\n";
}

// Create sample employees
try {
    // Check if employees table exists
    $db->query("SELECT 1 FROM employees LIMIT 1");
    
    // Create employees if table exists
    $employeeModel = new Employee();
    $departments = $db->query("SELECT id FROM departments")->fetchAll(\PDO::FETCH_COLUMN);
    
    if (!empty($departments)) {
        $employeeData = [
            [
                'employee_id' => 'EMP001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'department_id' => $departments[0],
                'position' => 'HR Manager',
                'join_date' => '2022-01-15',
                'email' => 'john.doe@example.com',
                'username' => 'johndoe'
            ],
            [
                'employee_id' => 'EMP002',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'department_id' => $departments[1],
                'position' => 'Software Developer',
                'join_date' => '2022-02-20',
                'email' => 'jane.smith@example.com',
                'username' => 'janesmith'
            ],
            [
                'employee_id' => 'EMP003',
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'department_id' => $departments[2],
                'position' => 'Financial Analyst',
                'join_date' => '2022-03-10',
                'email' => 'michael.johnson@example.com',
                'username' => 'michaelj'
            ]
        ];
        
        foreach ($employeeData as $empData) {
            $exists = $db->prepare("SELECT COUNT(*) FROM employees WHERE employee_id = ?");
            $exists->execute([$empData['employee_id']]);
            
            if ($exists->fetchColumn() == 0) {
                // Create user first
                $userExists = $userModel->getUserByEmail($empData['email']);
                
                if (!$userExists) {
                    $userId = $userModel->create([
                        'username' => $empData['username'],
                        'email' => $empData['email'],
                        'password' => password_hash('password123', PASSWORD_DEFAULT), // Default password
                        'role_id' => $employeeRoleId
                    ]);
                    
                    echo "User '{$empData['username']}' created.\n";
                    
                    // Create employee with user_id
                    $employeeModel->create([
                        'employee_id' => $empData['employee_id'],
                        'first_name' => $empData['first_name'],
                        'last_name' => $empData['last_name'],
                        'department_id' => $empData['department_id'],
                        'position' => $empData['position'],
                        'join_date' => $empData['join_date'],
                        'user_id' => $userId
                    ]);
                    
                    echo "Employee '{$empData['first_name']} {$empData['last_name']}' created.\n";
                } else {
                    echo "User '{$empData['username']}' already exists.\n";
                }
            } else {
                echo "Employee '{$empData['first_name']} {$empData['last_name']}' already exists.\n";
            }
        }
    }
} catch (\PDOException $e) {
    echo "Employees table not found or error creating employees: " . $e->getMessage() . "\n";
}

echo "Database seeding completed successfully.\n"; 