<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?> - Employee Attendance</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .attendance-container {
            margin-top: 20px;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .filter-group label {
            font-weight: bold;
        }
        
        .filter-group input, .filter-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .employee-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .employee-info-item {
            margin-bottom: 10px;
        }
        
        .employee-info-item label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #7f8c8d;
        }
        
        .employee-info-item span {
            font-size: 16px;
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
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .status-present {
            color: #27ae60;
            font-weight: bold;
        }
        
        .status-absent {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .status-late {
            color: #f39c12;
            font-weight: bold;
        }
        
        .status-on_leave {
            color: #3498db;
            font-weight: bold;
        }
        
        .summary-box {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .summary-item {
            flex: 1;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        
        .summary-item.present {
            background-color: rgba(39, 174, 96, 0.1);
            border: 1px solid rgba(39, 174, 96, 0.3);
        }
        
        .summary-item.absent {
            background-color: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
        
        .summary-item.late {
            background-color: rgba(243, 156, 18, 0.1);
            border: 1px solid rgba(243, 156, 18, 0.3);
        }
        
        .summary-item.leave {
            background-color: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.3);
        }
        
        .summary-number {
            font-size: 24px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .summary-label {
            color: #7f8c8d;
            font-size: 14px;
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
                <li><a href="/devices">Devices</a></li>
                <li><a href="/reports">Reports</a></li>
                <li><a href="/logout">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <h2>Employee Attendance</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="employee-info">
            <div class="employee-info-item">
                <label>Employee ID</label>
                <span><?= $employee->employee_id ?></span>
            </div>
            <div class="employee-info-item">
                <label>Name</label>
                <span><?= $employee->first_name . ' ' . $employee->last_name ?></span>
            </div>
            <div class="employee-info-item">
                <label>Department</label>
                <span><?= $employee->department_name ?? 'N/A' ?></span>
            </div>
            <div class="employee-info-item">
                <label>Email</label>
                <span><?= $employee->email ?? 'N/A' ?></span>
            </div>
        </div>
        
        <div class="filters">
            <form action="/attendance/employee/<?= $employee->id ?>" method="GET" id="filterForm">
                <div class="filter-group">
                    <label for="start_date">From:</label>
                    <input type="date" id="start_date" name="start_date" value="<?= $startDate ?>">
                </div>
                
                <div class="filter-group">
                    <label for="end_date">To:</label>
                    <input type="date" id="end_date" name="end_date" value="<?= $endDate ?>">
                </div>
                
                <button type="submit" class="btn">Filter</button>
            </form>
        </div>
        
        <div class="summary-box">
            <?php
            $presentCount = 0;
            $absentCount = 0;
            $lateCount = 0;
            $leaveCount = 0;
            
            foreach ($attendanceRecords as $record) {
                if ($record->status === 'present') $presentCount++;
                else if ($record->status === 'absent') $absentCount++;
                else if ($record->status === 'late') $lateCount++;
                else if ($record->status === 'on_leave') $leaveCount++;
            }
            ?>
            
            <div class="summary-item present">
                <div class="summary-number"><?= $presentCount ?></div>
                <div class="summary-label">Present</div>
            </div>
            
            <div class="summary-item absent">
                <div class="summary-number"><?= $absentCount ?></div>
                <div class="summary-label">Absent</div>
            </div>
            
            <div class="summary-item late">
                <div class="summary-number"><?= $lateCount ?></div>
                <div class="summary-label">Late</div>
            </div>
            
            <div class="summary-item leave">
                <div class="summary-number"><?= $leaveCount ?></div>
                <div class="summary-label">On Leave</div>
            </div>
        </div>
        
        <div class="attendance-container">
            <?php if (empty($attendanceRecords)): ?>
                <p>No attendance records found for the selected date range.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Work Duration</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendanceRecords as $record): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($record->date)) ?></td>
                                <td><?= $record->shift_name ?? 'N/A' ?></td>
                                <td class="time-display"><?= $record->time_in ? date('H:i', strtotime($record->time_in)) : 'N/A' ?></td>
                                <td class="time-display"><?= $record->time_out ? date('H:i', strtotime($record->time_out)) : 'N/A' ?></td>
                                <td>
                                    <?php if ($record->work_duration_minutes): ?>
                                        <?= floor($record->work_duration_minutes / 60) ?>h 
                                        <?= $record->work_duration_minutes % 60 ?>m
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td class="status-<?= $record->status ?>"><?= ucfirst(str_replace('_', ' ', $record->status)) ?></td>
                                <td><?= $record->notes ?? '' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> <?= $_ENV['APP_NAME'] ?></p>
    </footer>
</body>
</html> 