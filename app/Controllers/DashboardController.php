<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Helpers\Session;

class DashboardController {
    private $employeeModel;
    private $departmentModel;
    private $attendanceModel;
    private $leaveRequestModel;
    private $userModel;
    private $db;
    
    public function __construct() {
        $this->employeeModel = new Employee();
        $this->departmentModel = new Department();
        $this->attendanceModel = new Attendance();
        $this->leaveRequestModel = new LeaveRequest();
        $this->userModel = new User();
        $this->db = \App\Config\Database::getInstance()->getConnection();
    }
    
    public function index() {
        try {
            // Check if user is logged in
            if (!Session::isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            
            // Get counts for dashboard
            $employeeCount = $this->employeeModel->countEmployees();
            $departmentCount = $this->departmentModel->countDepartments();
            $presentCount = $this->attendanceModel->countAttendanceByStatus('present', date('Y-m-d'));
            $absentCount = $this->attendanceModel->countAttendanceByStatus('absent', date('Y-m-d'));
            $lateCount = $this->attendanceModel->countAttendanceByStatus('late', date('Y-m-d'));
            $pendingLeaveCount = $this->leaveRequestModel->countLeaveRequestsByStatus('pending');
            
            // Get recent attendance
            $recentAttendance = $this->attendanceModel->getRecentAttendance(5);
            
            // Get pending leave requests
            $pendingLeaveRequests = $this->leaveRequestModel->getLeaveRequestsByStatus('pending', 5);
            
            // Prepare data for the view
            $data = [
                'employeeCount' => $employeeCount,
                'departmentCount' => $departmentCount,
                'presentCount' => $presentCount,
                'absentCount' => $absentCount,
                'lateCount' => $lateCount,
                'pendingLeaveCount' => $pendingLeaveCount,
                'recentAttendance' => $recentAttendance,
                'pendingLeaveRequests' => $pendingLeaveRequests
            ];
            
            // Render the view
            require_once __DIR__ . '/../../resources/views/dashboard/index.php';
        } catch (\Exception $e) {
            error_log("Error in DashboardController::index: " . $e->getMessage());
            echo "An error occurred while loading the dashboard.";
        }
    }
    
    private function countEmployees() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employees");
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error counting employees: " . $e->getMessage());
            return 0;
        }
    }
    
    private function getRecentAttendance() {
        try {
            // Determine the table name
            $tableCheckStmt = $this->db->prepare("SHOW TABLES LIKE 'attendances'");
            $tableCheckStmt->execute();
            $tableName = $tableCheckStmt->rowCount() > 0 ? 'attendances' : 'attendance';
            
            $stmt = $this->db->prepare("
                SELECT a.*, e.first_name, e.last_name 
                FROM $tableName a
                JOIN employees e ON a.employee_id = e.id
                ORDER BY a.check_in DESC
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error getting recent attendance: " . $e->getMessage());
            return [];
        }
    }
} 