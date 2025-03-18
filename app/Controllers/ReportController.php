<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AuthService;

class ReportController {
    private $authMiddleware;
    private $attendanceModel;
    private $departmentModel;
    private $employeeModel;
    private $authService;
    
    public function __construct() {
        $this->authMiddleware = new AuthMiddleware();
        $this->attendanceModel = new Attendance();
        $this->departmentModel = new Department();
        $this->employeeModel = new Employee();
        $this->authService = new AuthService();
    }
    
    public function index() {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('reports.read');
        
        // Get departments for filter
        $departments = $this->departmentModel->getAllDepartments();
        
        // Load the reports view
        require_once __DIR__ . '/../../resources/views/reports/index.php';
    }
    
    public function attendance() {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('reports.read');
        
        // Get filter parameters
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        $departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : null;
        $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : null;
        
        // Get departments for filter
        $departments = $this->departmentModel->getAllDepartments();
        
        // Get employees for filter
        $employees = [];
        if ($departmentId) {
            $employees = $this->employeeModel->getEmployeesByDepartment($departmentId);
        } else {
            $employees = $this->employeeModel->getAllEmployees();
        }
        
        // Get attendance data
        $attendanceData = [];
        if ($employeeId) {
            // Get attendance for specific employee
            $attendanceData = $this->attendanceModel->getAttendanceByEmployeeAndDateRange($employeeId, $startDate, $endDate);
        } else {
            // Get attendance summary for all employees or by department
            $attendanceData = $this->attendanceModel->getAttendanceSummaryByDateRange($startDate, $endDate);
            
            if ($departmentId) {
                // Filter by department
                $attendanceData = array_filter($attendanceData, function($item) use ($departmentId) {
                    return $item->department_id == $departmentId;
                });
            }
        }
        
        // Load the attendance report view
        require_once __DIR__ . '/../../resources/views/reports/attendance.php';
    }
    
    public function department() {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('reports.read');
        
        // Get filter parameters
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        $departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : null;
        
        // Get departments for filter
        $departments = $this->departmentModel->getAllDepartments();
        
        // Get department attendance summary
        $departmentData = $this->attendanceModel->getAttendanceSummaryByDepartment($startDate, $endDate, $departmentId);
        
        // Load the department report view
        require_once __DIR__ . '/../../resources/views/reports/department.php';
    }
    
    public function export() {
        // Check if user has permission
        $this->authMiddleware->handleWithPermission('reports.read');
        
        // Get filter parameters
        $reportType = isset($_GET['type']) ? $_GET['type'] : 'attendance';
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        $departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : null;
        $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : null;
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $reportType . '_report_' . date('Y-m-d') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        if ($reportType === 'attendance') {
            // Export attendance report
            if ($employeeId) {
                // Export individual employee attendance
                $attendanceData = $this->attendanceModel->getAttendanceByEmployeeAndDateRange($employeeId, $startDate, $endDate);
                $employee = $this->employeeModel->getEmployeeById($employeeId);
                
                // Write CSV header
                fputcsv($output, [
                    'Employee ID', 'Employee Name', 'Department', 'Date', 'Time In', 'Time Out', 
                    'Status', 'Work Duration (min)', 'Overtime (min)', 'Late (min)', 'Notes'
                ]);
                
                // Write data rows
                foreach ($attendanceData as $record) {
                    fputcsv($output, [
                        $employee->employee_id,
                        $employee->first_name . ' ' . $employee->last_name,
                        $employee->department_name,
                        $record->date,
                        $record->time_in,
                        $record->time_out,
                        ucfirst(str_replace('_', ' ', $record->status)),
                        $record->work_duration_minutes,
                        $record->overtime_minutes,
                        $record->late_minutes,
                        $record->notes
                    ]);
                }
            } else {
                // Export attendance summary
                $attendanceData = $this->attendanceModel->getAttendanceSummaryByDateRange($startDate, $endDate);
                
                if ($departmentId) {
                    // Filter by department
                    $attendanceData = array_filter($attendanceData, function($item) use ($departmentId) {
                        return $item->department_id == $departmentId;
                    });
                }
                
                // Write CSV header
                fputcsv($output, [
                    'Employee ID', 'Employee Name', 'Department', 'Present Days', 'Absent Days', 
                    'Late Days', 'Leave Days', 'Total Work Hours', 'Total Overtime Hours', 'Total Late Hours'
                ]);
                
                // Write data rows
                foreach ($attendanceData as $record) {
                    fputcsv($output, [
                        $record->employee_code,
                        $record->employee_name,
                        $record->department_name,
                        $record->present_days,
                        $record->absent_days,
                        $record->late_days,
                        $record->leave_days,
                        round($record->total_work_minutes / 60, 2),
                        round($record->total_overtime_minutes / 60, 2),
                        round($record->total_late_minutes / 60, 2)
                    ]);
                }
            }
        } elseif ($reportType === 'department') {
            // Export department report
            $departmentData = $this->attendanceModel->getAttendanceSummaryByDepartment($startDate, $endDate, $departmentId);
            
            // Write CSV header
            fputcsv($output, [
                'Department', 'Total Employees', 'Present Count', 'Absent Count', 
                'Late Count', 'Leave Count', 'Total Work Hours', 'Total Overtime Hours'
            ]);
            
            // Write data rows
            foreach ($departmentData as $record) {
                fputcsv($output, [
                    $record->department_name,
                    $record->total_employees,
                    $record->present_count,
                    $record->absent_count,
                    $record->late_count,
                    $record->leave_count,
                    round($record->total_work_minutes / 60, 2),
                    round($record->total_overtime_minutes / 60, 2)
                ]);
            }
        }
        
        fclose($output);
        exit;
    }
} 