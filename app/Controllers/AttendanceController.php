<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Shift;
use App\Services\AuthService;
use App\Models\User;
use App\Helpers\Session;
use App\Helpers\Validator;

class AttendanceController {
    private $authMiddleware;
    private $attendanceModel;
    private $employeeModel;
    private $departmentModel;
    private $shiftModel;
    private $authService;
    private $userModel;
    
    public function __construct() {
        $this->authMiddleware = new AuthMiddleware();
        $this->attendanceModel = new Attendance();
        $this->employeeModel = new Employee();
        $this->departmentModel = new Department();
        $this->shiftModel = new Shift();
        $this->authService = new AuthService();
        $this->userModel = new User();
    }
    
    public function index() {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        // Get all attendances
        $attendances = $this->attendanceModel->getAllAttendances();
        
        // Get today's attendance
        $todayAttendance = $this->attendanceModel->getAttendanceByDate(date('Y-m-d'));
        
        // Render the view
        require_once __DIR__ . '/../../resources/views/attendance/index.php';
    }
    
    public function create() {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        // Get all employees
        $employees = $this->employeeModel->getAllEmployees();
        
        // Render the view
        require_once __DIR__ . '/../../resources/views/attendance/create.php';
        
    }
    
    public function store() {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        // Validate input
        $validator = new Validator($_POST);
        $validator->required(['employee_id', 'date', 'status']);
        
        if (!$validator->isValid()) {
            header('Location: /attendance/create?error=' . urlencode($validator->getFirstError()));
            exit;
        }
        
        // Check if attendance already exists for this employee and date
        $existingAttendance = $this->attendanceModel->getAttendanceByEmployeeAndDate($_POST['employee_id'], $_POST['date']);
        
        if ($existingAttendance) {
            header('Location: /attendance/create?error=' . urlencode('Attendance record already exists for this employee on this date'));
            exit;
        }
        
        // Create attendance
        $attendanceId = $this->attendanceModel->create($_POST);
        
        if ($attendanceId) {
            header('Location: /attendance?success=' . urlencode('Attendance record created successfully'));
        } else {
            header('Location: /attendance/create?error=' . urlencode('Failed to create attendance record'));
        }
    }
    
    public function edit($id) {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        // Get attendance
        $attendance = $this->attendanceModel->getAttendanceById($id);
        
        if (!$attendance) {
            header('Location: /attendance?error=' . urlencode('Attendance record not found'));
            exit;
        }
        
        // Get all employees
        $employees = $this->employeeModel->getAllEmployees();
        
        // Render the view
        require_once __DIR__ . '/../../resources/views/attendance/edit.php';
    }
    
    public function update($id) {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        // Validate input
        $validator = new Validator($_POST);
        $validator->required(['employee_id', 'date', 'status']);
        
        if (!$validator->isValid()) {
            header('Location: /attendance/edit/' . $id . '?error=' . urlencode($validator->getFirstError()));
            exit;
        }
        
        // Update attendance
        $success = $this->attendanceModel->update($id, $_POST);
        
        if ($success) {
            header('Location: /attendance?success=' . urlencode('Attendance record updated successfully'));
        } else {
            header('Location: /attendance/edit/' . $id . '?error=' . urlencode('Failed to update attendance record'));
        }
    }
    
    public function delete($id) {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        // Delete attendance
        $success = $this->attendanceModel->delete($id);
        
        if ($success) {
            header('Location: /attendance?success=' . urlencode('Attendance record deleted successfully'));
        } else {
            header('Location: /attendance?error=' . urlencode('Failed to delete attendance record'));
        }
    }
    
    public function clockIn() {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        // Get current user
        $userId = Session::get('user_id');
        
        // Get employee by user ID
        $employee = $this->employeeModel->getEmployeeByUserId($userId);
        
        if (!$employee) {
            // Redirect with error message
            header('Location: /dashboard?error=Employee record not found');
            exit;
        }
        
        // Get today's date
        $today = date('Y-m-d');
        
        // Check if employee has already clocked in today
        $attendance = $this->attendanceModel->getAttendanceByEmployeeAndDate($employee->id, $today);
        
        if ($attendance) {
            // Redirect with error message
            header('Location: /dashboard?error=You have already clocked in today');
            exit;
        }
        
        // Create attendance record
        $data = [
            'employee_id' => $employee->id,
            'date' => $today,
            'time_in' => date('H:i:s'),
            'status' => 'present'
        ];
        
        $this->attendanceModel->create($data);
        
        // Redirect to dashboard
        header('Location: /dashboard?success=You have successfully clocked in');
        exit;
    }
    
    public function clockOut() {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        // Get current user
        $userId = Session::get('user_id');
        
        // Get employee by user ID
        $employee = $this->employeeModel->getEmployeeByUserId($userId);
        
        if (!$employee) {
            // Redirect with error message
            header('Location: /dashboard?error=Employee record not found');
            exit;
        }
        
        // Get today's date
        $today = date('Y-m-d');
        
        // Check if employee has clocked in today
        $attendance = $this->attendanceModel->getAttendanceByEmployeeAndDate($employee->id, $today);
        
        if (!$attendance) {
            // Redirect with error message
            header('Location: /dashboard?error=You have not clocked in today');
            exit;
        }
        
        if ($attendance->time_out) {
            // Redirect with error message
            header('Location: /dashboard?error=You have already clocked out today');
            exit;
        }
        
        // Update attendance record
        $data = [
            'time_out' => date('H:i:s')
        ];
        
        $this->attendanceModel->update($attendance->id, $data);
        
        // Redirect to dashboard
        header('Location: /dashboard?success=You have successfully clocked out');
        exit;
    }
    
    public function report() {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        // Get all employees
        $employees = $this->employeeModel->getAllEmployees();
        
        // Get filter parameters
        $employeeId = $_GET['employee_id'] ?? null;
        $startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
        $endDate = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month
        
        // Get attendance records
        $attendances = $this->attendanceModel->getAttendanceReport($employeeId, $startDate, $endDate);
        
        // Render the view
        require_once __DIR__ . '/../../resources/views/attendance/report.php';
    }
} 