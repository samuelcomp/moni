<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use App\Services\AuthService;
use App\Helpers\Session;
use App\Helpers\Validator;

class EmployeeController {
    private $authMiddleware;
    private $employeeModel;
    private $departmentModel;
    private $userModel;
    private $authService;
    
    public function __construct() {
        $this->authMiddleware = new AuthMiddleware();
        $this->employeeModel = new Employee();
        $this->departmentModel = new Department();
        $this->userModel = new User();
        $this->authService = new AuthService();
    }
    
    public function index() {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        // Get page and search parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        
        // Get employees
        $employees = $this->employeeModel->getAllEmployees($page, 10, $search);
        
        // Get total employees for pagination
        $totalEmployees = $this->employeeModel->countEmployees();
        $totalPages = ceil($totalEmployees / 10);
        
        // Render the view
        require_once __DIR__ . '/../../resources/views/employees/index.php';
    }
    
    public function create() {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        // Get departments
        $departments = $this->departmentModel->getAllDepartments();
        
        // Render the view
        require_once __DIR__ . '/../../resources/views/employees/create.php';
    }
    
    public function store() {
        try {
            error_log("EmployeeController::store called with POST data: " . json_encode($_POST));
            
            // Validate input
            $requiredFields = ['first_name', 'last_name', 'email', 'phone'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                error_log("Missing required fields: " . implode(', ', $missingFields));
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields: ' . implode(', ', $missingFields)]);
                return;
            }
            
            // Prepare employee data
            $employeeData = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone']
            ];
            
            // Add optional fields if they exist
            $optionalFields = ['department', 'position', 'hire_date'];
            foreach ($optionalFields as $field) {
                if (isset($_POST[$field]) && !empty($_POST[$field])) {
                    $employeeData[$field] = $_POST[$field];
                }
            }
            
            // Handle user_id specially - first check if the user exists
            if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
                $db = \App\Config\Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                
                if ($stmt->rowCount() > 0) {
                    // User exists, we can use this user_id
                    $employeeData['user_id'] = $_POST['user_id'];
                } else {
                    // User doesn't exist, we need to create one
                    error_log("User ID " . $_POST['user_id'] . " doesn't exist in users table");
                    echo json_encode(['status' => 'error', 'message' => 'The specified user ID does not exist']);
                    return;
                }
            }
            
            error_log("Prepared employee data: " . json_encode($employeeData));
            
            // Create employee
            $employeeModel = new \App\Models\Employee();
            $result = $employeeModel->createEmployee($employeeData);
            
            error_log("createEmployee result: " . json_encode($result));
            
            if ($result['success']) {
                echo json_encode(['status' => 'success', 'message' => 'Employee created successfully', 'id' => $result['id']]);
            } else {
                echo json_encode(['status' => 'error', 'message' => $result['message'] ?? 'Failed to create employee']);
            }
        } catch (\Exception $e) {
            error_log("Error in EmployeeController::store: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            echo json_encode(['status' => 'error', 'message' => 'An error occurred while creating the employee: ' . $e->getMessage()]);
        }
    }
    
    public function edit($id) {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('employees.update');
        
        // Get employee
        $employee = $this->employeeModel->getEmployeeById($id);
        
        if (!$employee) {
            $_SESSION['error'] = 'Employee not found';
            header('Location: /employees');
            exit;
        }
        
        // Get departments
        $departments = $this->departmentModel->getAllDepartments();
        
        // Load the edit employee view
        require_once __DIR__ . '/../../resources/views/employees/edit.php';
    }
    
    public function update($id) {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('employees.update');
        
        // Get employee
        $employee = $this->employeeModel->getEmployeeById($id);
        
        if (!$employee) {
            $_SESSION['error'] = 'Employee not found';
            header('Location: /employees');
            exit;
        }
        
        // Validate form data
        if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['employee_id'])) {
            $_SESSION['error'] = 'Please fill in all required fields';
            header("Location: /employees/edit/$id");
            exit;
        }
        
        // Check if employee ID already exists (excluding current employee)
        $existingEmployee = $this->employeeModel->getEmployeeByEmployeeId($_POST['employee_id']);
        if ($existingEmployee && $existingEmployee->id != $id) {
            $_SESSION['error'] = 'Employee ID already exists';
            header("Location: /employees/edit/$id");
            exit;
        }
        
        // Update user account if it exists
        if ($employee->user_id) {
            $userData = [
                'email' => $_POST['email'] ?? null,
                'role' => $_POST['role'] ?? 'employee'
            ];
            
            // Update password if provided
            if (!empty($_POST['password'])) {
                $userData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            $this->userModel->update($employee->user_id, $userData);
        }
        // Create user account if username and password are provided
        else if (!empty($_POST['username']) && !empty($_POST['password'])) {
            // Check if username already exists
            $existingUser = $this->userModel->getUserByUsername($_POST['username']);
            if ($existingUser) {
                $_SESSION['error'] = 'Username already exists';
                header("Location: /employees/edit/$id");
                exit;
            }
            
            // Create user
            $userData = [
                'username' => $_POST['username'],
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'email' => $_POST['email'] ?? null,
                'role' => $_POST['role'] ?? 'employee',
                'is_active' => 1
            ];
            
            $userId = $this->userModel->create($userData);
            
            if ($userId) {
                // Update employee with user ID
                $this->employeeModel->update($id, ['user_id' => $userId]);
            }
        }
        
        // Prepare employee data
        $employeeData = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'employee_id' => $_POST['employee_id'],
            'department_id' => $_POST['department_id'] ?? null,
            'position' => $_POST['position'] ?? null,
            'email' => $_POST['email'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'address' => $_POST['address'] ?? null,
            'join_date' => $_POST['join_date'] ?? null,
            'biometric_id' => $_POST['biometric_id'] ?? null
        ];
        
        // Update employee
        $result = $this->employeeModel->update($id, $employeeData);
        
        if ($result) {
            $_SESSION['success'] = 'Employee updated successfully';
            header('Location: /employees');
        } else {
            $_SESSION['error'] = 'Failed to update employee';
            header("Location: /employees/edit/$id");
        }
        exit;
    }
    
    public function delete($id) {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('employees.delete');
        
        // Get employee
        $employee = $this->employeeModel->getEmployeeById($id);
        
        if (!$employee) {
            $_SESSION['error'] = 'Employee not found';
            header('Location: /employees');
            exit;
        }
        
        // Delete employee
        $result = $this->employeeModel->delete($id);
        
        // Delete associated user if exists
        if ($result && $employee->user_id) {
            $this->userModel->delete($employee->user_id);
        }
        
        if ($result) {
            $_SESSION['success'] = 'Employee deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete employee';
        }
        
        header('Location: /employees');
        exit;
    }
    
    public function view($id) {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('employees.read');
        
        // Get employee
        $employee = $this->employeeModel->getEmployeeById($id);
        
        if (!$employee) {
            $_SESSION['error'] = 'Employee not found';
            header('Location: /employees');
            exit;
        }
        
        // Load the view employee view
        require_once __DIR__ . '/../../resources/views/employees/view.php';
    }
} 