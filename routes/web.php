<?php

use App\Helpers\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DeviceController;
use App\Controllers\AttendanceController;
use App\Controllers\EmployeeController;
use App\Controllers\DepartmentController;
use App\Controllers\ReportController;
use App\Controllers\LeaveRequestController;
use App\Controllers\HolidayController;
use App\Controllers\ShiftController;
use App\Controllers\UserController;
use App\Controllers\SettingController;

// Home routes
Router::get('/', [HomeController::class, 'index']);

// Auth routes
Router::get('/login', [AuthController::class, 'showLoginForm']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/logout', [AuthController::class, 'logout']);

// Dashboard routes
Router::get('/dashboard', [DashboardController::class, 'index']);

// Device routes
Router::get('/devices', [DeviceController::class, 'index']);
Router::get('/devices/create', [DeviceController::class, 'create']);
Router::post('/devices/store', [DeviceController::class, 'store']);
Router::get('/devices/edit/{id}', [DeviceController::class, 'edit']);
Router::post('/devices/update/{id}', [DeviceController::class, 'update']);
Router::get('/devices/delete/{id}', [DeviceController::class, 'delete']);
Router::get('/devices/test-connection/{id}', [DeviceController::class, 'testConnection']);
Router::post('/devices/test-connection/{id}', [DeviceController::class, 'testConnection']);
Router::get('/devices/sync/{id}', [DeviceController::class, 'sync']);
Router::get('/devices/realtime', [DeviceController::class, 'realtime']);
Router::get('/devices/realtime-data', [DeviceController::class, 'getRealtimeData']);
Router::get('/devices/get_recent_attendance', [DeviceController::class, 'getRecentAttendance']);
Router::post('/devices/sync-by-date/{id}', [DeviceController::class, 'syncByDate']);
Router::get('/devices/stop-sync/{id}', [DeviceController::class, 'stopSync']);
Router::get('/devices/debug-database', [DeviceController::class, 'debugDatabase']);
Router::post('/devices/start-live-capture', [DeviceController::class, 'startLiveCapture']);
Router::post('/devices/stop-live-capture', [DeviceController::class, 'stopLiveCapture']);
Router::get('/devices/monitor-device', [DeviceController::class, 'monitorDevice']);
Router::get('/devices/get-attendance', [DeviceController::class, 'getAttendance']);
Router::get('/devices/get-today-attendance', [DeviceController::class, 'getTodayAttendance']);
Router::get('/devices/get-attendance-summary', [DeviceController::class, 'getAttendanceSummary']);
Router::get('/devices/export-attendance', [DeviceController::class, 'exportAttendance']);

// Attendance routes
Router::get('/attendance', [AttendanceController::class, 'index']);
Router::get('/attendance/create', [AttendanceController::class, 'create']);
Router::post('/attendance/store', [AttendanceController::class, 'store']);
Router::get('/attendance/edit/{id}', [AttendanceController::class, 'edit']);
Router::post('/attendance/update/{id}', [AttendanceController::class, 'update']);
Router::get('/attendance/delete/{id}', [AttendanceController::class, 'delete']);
Router::get('/attendance/employee/{id}', [AttendanceController::class, 'employee']);
Router::get('/attendance/report', [AttendanceController::class, 'report']);
Router::post('/attendance/report', [AttendanceController::class, 'generateReport']);

// Employee routes
Router::get('/employees', [EmployeeController::class, 'index']);
Router::get('/employees/create', [EmployeeController::class, 'create']);
Router::post('/employees/store', [EmployeeController::class, 'store']);
Router::get('/employees/edit/{id}', [EmployeeController::class, 'edit']);
Router::post('/employees/update/{id}', [EmployeeController::class, 'update']);
Router::get('/employees/delete/{id}', [EmployeeController::class, 'delete']);
Router::get('/employees/view/{id}', [EmployeeController::class, 'view']);

// Department routes
Router::get('/departments', [DepartmentController::class, 'index']);
Router::get('/departments/create', [DepartmentController::class, 'create']);
Router::post('/departments/store', [DepartmentController::class, 'store']);
Router::get('/departments/edit/{id}', [DepartmentController::class, 'edit']);
Router::post('/departments/update/{id}', [DepartmentController::class, 'update']);
Router::get('/departments/delete/{id}', [DepartmentController::class, 'delete']);
Router::get('/departments/view/{id}', [DepartmentController::class, 'view']);

// Report routes
Router::get('/reports', [ReportController::class, 'index']);
Router::get('/reports/attendance', [ReportController::class, 'attendance']);
Router::get('/reports/department', [ReportController::class, 'department']);
Router::get('/reports/export', [ReportController::class, 'export']);

// Leave Request routes
Router::get('/leave-requests', [LeaveRequestController::class, 'index']);
Router::get('/leave-requests/create', [LeaveRequestController::class, 'create']);
Router::post('/leave-requests/store', [LeaveRequestController::class, 'store']);
Router::get('/leave-requests/edit/{id}', [LeaveRequestController::class, 'edit']);
Router::post('/leave-requests/update/{id}', [LeaveRequestController::class, 'update']);
Router::get('/leave-requests/view/{id}', [LeaveRequestController::class, 'view']);
Router::get('/leave-requests/delete/{id}', [LeaveRequestController::class, 'delete']);
Router::get('/leave-requests/approve/{id}', [LeaveRequestController::class, 'approve']);
Router::get('/leave-requests/reject/{id}', [LeaveRequestController::class, 'reject']);

// Holiday routes
Router::get('/holidays', [HolidayController::class, 'index']);
Router::get('/holidays/create', [HolidayController::class, 'create']);
Router::post('/holidays/store', [HolidayController::class, 'store']);
Router::get('/holidays/edit/{id}', [HolidayController::class, 'edit']);
Router::post('/holidays/update/{id}', [HolidayController::class, 'update']);
Router::get('/holidays/delete/{id}', [HolidayController::class, 'delete']);

// Shift routes
Router::get('/shifts', [ShiftController::class, 'index']);
Router::get('/shifts/create', [ShiftController::class, 'create']);
Router::post('/shifts/store', [ShiftController::class, 'store']);
Router::get('/shifts/edit/{id}', [ShiftController::class, 'edit']);
Router::post('/shifts/update/{id}', [ShiftController::class, 'update']);
Router::get('/shifts/delete/{id}', [ShiftController::class, 'delete']);

// User routes
Router::get('/users', [UserController::class, 'index']);
Router::get('/users/create', [UserController::class, 'create']);
Router::post('/users/store', [UserController::class, 'store']);
Router::get('/users/edit/{id}', [UserController::class, 'edit']);
Router::post('/users/update/{id}', [UserController::class, 'update']);
Router::get('/users/delete/{id}', [UserController::class, 'delete']);
Router::get('/profile', [UserController::class, 'profile']);
Router::post('/profile/update', [UserController::class, 'updateProfile']);
Router::post('/profile/change-password', [UserController::class, 'changePassword']);

// Settings routes
Router::get('/settings', [SettingController::class, 'index']);
Router::post('/settings/update', [SettingController::class, 'update']);

// Dispatch the router
Router::dispatch(); 