<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?> - Attendance Report</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .report-container {
            margin-top: 20px;
        }
        
        .filter-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn {
            display: inline-block;
            background-color: #2c3e50;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .btn-export {
            background-color: #27ae60;
        }
        
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
        }
        
        .report-actions {
            display: flex;
            gap: 10px;
        }
        
        .status-present {
            color: #27ae60;
        }
        
        .status-absent {
            color: #e74c3c;
        }
        
        .status-late {
            color: #f39c12;
        }
        
        .status-on_leave {
            color: #3498db;
        }
        
        .summary-card {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .summary-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 14px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <header>
        <h1><?= $_ENV['APP_NAME'] ?></h1>
        <nav>
            <ul>
                <li><a href="/dashboard">Dashboard</a></li>
                <li><a href="/employees">Employees</a></li>
                <li><a href="/attendance">Attendance</a></li>
                <li><a href="/departments">Departments</a></li>
                <li><a href="/devices">Devices</a></li>
                <li><a href="/reports">Reports</a></li>
                <li><a href="/logout">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <h2>Attendance Report</h2>
        
        <div class="report-container">
            <form action="/reports/attendance" method="GET" class="filter-form">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?= $startDate ?>">
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?= $endDate ?>">
                </div>
                
                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select id="department_id" name="department_id" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept->id ?>" <?= $departmentId == $dept->id ? 'selected' : '' ?>>
                                <?= $dept->name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="employee_id">Employee</label>
                    <select id="employee_id" name="employee_id">
                        <option value="">All Employees</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp->id ?>" <?= $employeeId == $emp->id ? 'selected' : '' ?>>
                                <?= $emp->first_name . ' ' . $emp->last_name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Generate Report</button>
                </div>
            </form>
            
            <div class="report-header">
                <div class="report-title">
                    <?php if ($employeeId): ?>
                        <?php 
                            $employee = $this->employeeModel->getEmployeeById($employeeId);
                            echo "Attendance for " . $employee->first_name . " " . $employee->last_name;
                        ?>
                    <?php else: ?>
                        <?php if ($departmentId): ?>
                            <?php 
                                $department = $this->departmentModel->getDepartmentById($departmentId);
                                echo "Attendance Summary for " . $department->name . " Department";
                            ?>
                        <?php else: ?>
                            Attendance Summary for All Employees
                        <?php endif; ?>
                    <?php endif; ?>
                    <span style="font-weight: normal; font-size: 14px;">
                        (<?= date('d M Y', strtotime($startDate)) ?> - <?= date('d M Y', strtotime($endDate)) ?>)
                    </span>
                </div>
                
                <div class="report-actions">
                    <a href="/reports/export?type=attendance&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&department_id=<?= $departmentId ?>&employee_id=<?= $employeeId ?>" class="btn btn-export">Export CSV</a>
                </div>
            </div>
            
            <?php if (empty($attendanceData)): ?>
                <p>No attendance data found for the selected criteria.</p>
            <?php else: ?>
                <?php if ($employeeId): ?>
                    <!-- Individual employee attendance details -->
                    <div class="summary-card">
                        <div class="summary-title">Attendance Summary</div>
                        <div class="summary-grid">
                            <?php
                                $presentCount = 0;
                                $absentCount = 0;
                                $lateCount = 0;
                                $leaveCount = 0;
                                $totalWorkMinutes = 0;
                                $totalOvertimeMinutes = 0;
                                
                                foreach ($attendanceData as $record) {
                                    if ($record->status == 'present') $presentCount++;
                                    if ($record->status == 'absent') $absentCount++;
                                    if ($record->status == 'late') $lateCount++;
                                    if ($record->status == 'on_leave') $leaveCount++;
                                    $totalWorkMinutes += $record->work_duration_minutes;
                                    $totalOvertimeMinutes += $record->overtime_minutes;
                                }
                            ?>
                            
                            <div class="summary-item">
                                <div class="summary-value"><?= $presentCount ?></div>
                                <div class="summary-label">Present Days</div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-value"><?= $absentCount ?></div>
                                <div class="summary-label">Absent Days</div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-value"><?= $lateCount ?></div>
                                <div class="summary-label">Late Days</div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-value"><?= $leaveCount ?></div>
                                <div class="summary-label">Leave Days</div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-value"><?= round($totalWorkMinutes / 60, 1) ?></div>
                                <div class="summary-label">Total Hours</div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-value"><?= round($totalOvertimeMinutes / 60, 1) ?></div>
                                <div class="summary-label">Overtime Hours</div>
                            </div>
                        </div>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Status</th>
                                <th>Work Hours</th>
                                <th>Overtime</th>
                                <th>Late</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceData as $record): ?>
                                <tr>
                                    <td><?= date('d M Y (D)', strtotime($record->date)) ?></td>
                                    <td><?= $record->time_in ? date('h:i A', strtotime($record->time_in)) : 'N/A' ?></td>
                                    <td><?= $record->time_out ? date('h:i A', strtotime($record->time_out)) : 'N/A' ?></td>
                                    <td class="status-<?= $record->status ?>"><?= ucfirst(str_replace('_', ' ', $record->status)) ?></td>
                                    <td><?= round($record->work_duration_minutes / 60, 1) ?> hrs</td>
                                    <td><?= round($record->overtime_minutes / 60, 1) ?> hrs</td>
                                    <td><?= $record->late_minutes ?> min</td>
                                    <td><?= $record->notes ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <!-- Attendance summary for multiple employees -->
                    <table>
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Present Days</th>
                                <th>Absent Days</th>
                                <th>Late Days</th>
                                <th>Leave Days</th>
                                <th>Total Hours</th>
                                <th>Overtime Hours</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceData as $record): ?>
                                <tr>
                                    <td><?= $record->employee_code ?></td>
                                    <td><?= $record->employee_name ?></td>
                                    <td><?= $record->department_name ?? 'N/A' ?></td>
                                    <td><?= $record->present_days ?></td>
                                    <td><?= $record->absent_days ?></td>
                                    <td><?= $record->late_days ?></td>
                                    <td><?= $record->leave_days ?></td>
                                    <td><?= round($record->total_work_minutes / 60, 1) ?> hrs</td>
                                    <td><?= round($record->total_overtime_minutes / 60, 1) ?> hrs</td>
                                    <td>
                                        <a href="/reports/attendance?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&employee_id=<?= $record->employee_id ?>" class="btn">Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> <?= $_ENV['APP_NAME'] ?></p>
    </footer>
</body>
</html> 