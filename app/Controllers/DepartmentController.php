<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AuthService;

class DepartmentController {
    private $authMiddleware;
    private $departmentModel;
    private $employeeModel;
    private $authService;
    
    public function __construct() {
        $this->authMiddleware = new AuthMiddleware();
        $this->departmentModel = new Department();
        $this->employeeModel = new Employee();
        $this->authService = new AuthService();
    }
    
    public function index() {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('departments.read');
        
        // Get departments
        $departments = $this->departmentModel->getAllDepartments();
        
        // Get employee count for each department
        foreach ($departments as $department) {
            $department->employee_count = $this->employeeModel->countEmployeesByDepartment($department->id);
        }
        
        // Load the departments view
        require_once __DIR__ . '/../../resources/views/departments/index.php';
    }
    
    public function create() {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('departments.create');
        
        // Load the create department view
        require_once __DIR__ . '/../../resources/views/departments/create.php';
    }
    
    public function store() {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('departments.create');
        
        // Validate form data
        if (empty($_POST['name'])) {
            $_SESSION['error'] = 'Department name is required';
            header('Location: /departments/create');
            exit;
        }
        
        // Prepare department data
        $departmentData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? null,
            'location' => $_POST['location'] ?? null,
            'manager_id' => $_POST['manager_id'] ?? null
        ];
        
        // Create department
        $result = $this->departmentModel->create($departmentData);
        
        if ($result) {
            $_SESSION['success'] = 'Department created successfully';
            header('Location: /departments');
        } else {
            $_SESSION['error'] = 'Failed to create department';
            header('Location: /departments/create');
        }
        exit;
    }
    
    public function edit($id) {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('departments.update');
        
        // Get department
        $department = $this->departmentModel->getDepartmentById($id);
        
        if (!$department) {
            $_SESSION['error'] = 'Department not found';
            header('Location: /departments');
            exit;
        }
        
        // Get employees for manager selection
        $employees = $this->employeeModel->getAllEmployees();
        
        // Load the edit department view
        require_once __DIR__ . '/../../resources/views/departments/edit.php';
    }
    
    public function update($id) {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('departments.update');
        
        // Validate form data
        if (empty($_POST['name'])) {
            $_SESSION['error'] = 'Department name is required';
            header("Location: /departments/edit/$id");
            exit;
        }
        
        // Prepare department data
        $departmentData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? null,
            'location' => $_POST['location'] ?? null,
            'manager_id' => $_POST['manager_id'] ?? null
        ];
        
        // Update department
        $result = $this->departmentModel->update($id, $departmentData);
        
        if ($result) {
            $_SESSION['success'] = 'Department updated successfully';
            header('Location: /departments');
        } else {
            $_SESSION['error'] = 'Failed to update department';
            header("Location: /departments/edit/$id");
        }
        exit;
    }
    
    public function delete($id) {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('departments.delete');
        
        // Check if department has employees
        $employeeCount = $this->employeeModel->countEmployeesByDepartment($id);
        
        if ($employeeCount > 0) {
            $_SESSION['error'] = 'Cannot delete department with employees. Please reassign employees first.';
            header('Location: /departments');
            exit;
        }
        
        // Delete department
        $result = $this->departmentModel->delete($id);
        
        if ($result) {
            $_SESSION['success'] = 'Department deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete department';
        }
        
        header('Location: /departments');
        exit;
    }
    
    public function view($id) {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('departments.read');
        
        // Get department
        $department = $this->departmentModel->getDepartmentById($id);
        
        if (!$department) {
            $_SESSION['error'] = 'Department not found';
            header('Location: /departments');
            exit;
        }
        
        // Get employees in department
        $employees = $this->employeeModel->getEmployeesByDepartment($id);
        
        // Get department manager
        $manager = null;
        if ($department->manager_id) {
            $manager = $this->employeeModel->getEmployeeById($department->manager_id);
        }
        
        // Load the view department view
        require_once __DIR__ . '/../../resources/views/departments/view.php';
    }
    
} 