<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?> - Department Report</title>
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
        
        .chart-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .chart-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .chart-container {
                grid-template-columns: 1fr;
            }
        }
        
        .bar-chart {
            height: 300px;
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            padding: 0 10px;
        }
        
        .bar-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 60px;
        }
        
        .bar {
            width: 40px;
            background-color: #3498db;
            margin-bottom: 10px;
            border-radius: 3px 3px 0 0;
            position: relative;
        }
        
        .bar-label {
            font-size: 12px;
            text-align: center;
            word-wrap: break-word;
            max-width: 60px;
        }
        
        .bar-value {
            position: absolute;
            top: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        .present-bar {
            background-color: #27ae60;
        }
        
        .absent-bar {
            background-color: #e74c3c;
        }
        
        .late-bar {
            background-color: #f39c12;
        }
        
        .leave-bar {
            background-color: #3498db;
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
        <h2>Department Report</h2>
        
        <div class="report-container">
            <form action="/reports/department" method="GET" class="filter-form">
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
                    <select id="department_id" name="department_id">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept->id ?>" <?= $departmentId == $dept->id ? 'selected' : '' ?>>
                                <?= $dept->name ?>
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
                    <?php if ($departmentId): ?>
                        <?php 
                            $department = $this->departmentModel->getDepartmentById($departmentId);
                            echo "Department Report for " . $department->name;
                        ?>
                    <?php else: ?>
                        Department Comparison Report
                    <?php endif; ?>
                    <span style="font-weight: normal; font-size: 14px;">
                        (<?= date('d M Y', strtotime($startDate)) ?> - <?= date('d M Y', strtotime($endDate)) ?>)
                    </span>
                </div>
                
                <div class="report-actions">
                    <a href="/reports/export?type=department&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&department_id=<?= $departmentId ?>" class="btn btn-export">Export CSV</a>
                </div>
            </div>
            
            <?php if (empty($departmentData)): ?>
                <p>No department data found for the selected criteria.</p>
            <?php else: ?>
                <?php if (count($departmentData) > 1): ?>
                    <div class="chart-container">
                        <div class="chart-card">
                            <div class="chart-title">Attendance Status by Department</div>
                            <div class="bar-chart">
                                <?php foreach ($departmentData as $dept): ?>
                                    <?php
                                        $totalRecords = $dept->present_count + $dept->absent_count + $dept->late_count + $dept->leave_count;
                                        $presentPercentage = $totalRecords > 0 ? round(($dept->present_count / $totalRecords) * 100) : 0;
                                        $absentPercentage = $totalRecords > 0 ? round(($dept->absent_count / $totalRecords) * 100) : 0;
                                        $latePercentage = $totalRecords > 0 ? round(($dept->late_count / $totalRecords) * 100) : 0;
                                        $leavePercentage = $totalRecords > 0 ? round(($dept->leave_count / $totalRecords) * 100) : 0;
                                    ?>
                                    <div class="bar-group">
                                        <div class="bar present-bar" style="height: <?= $presentPercentage * 2 ?>px;">
                                            <div class="bar-value"><?= $presentPercentage ?>%</div>
                                        </div>
                                        <div class="bar-label"><?= $dept->department_name ?> (Present)</div>
                                    </div>
                                    <div class="bar-group">
                                        <div class="bar absent-bar" style="height: <?= $absentPercentage * 2 ?>px;">
                                            <div class="bar-value"><?= $absentPercentage ?>%</div>
                                        </div>
                                        <div class="bar-label"><?= $dept->department_name ?> (Absent)</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="chart-card">
                            <div class="chart-title">Work Hours by Department</div>
                            <div class="bar-chart">
                                <?php foreach ($departmentData as $dept): ?>
                                    <?php
                                        $avgWorkHours = $dept->total_employees > 0 ? round(($dept->total_work_minutes / 60) / $dept->total_employees, 1) : 0;
                                        $avgOvertimeHours = $dept->total_employees > 0 ? round(($dept->total_overtime_minutes / 60) / $dept->total_employees, 1) : 0;
                                        
                                        // Scale for visualization
                                        $workBarHeight = min($avgWorkHours * 15, 250);
                                        $overtimeBarHeight = min($avgOvertimeHours * 15, 250);
                                    ?>
                                    <div class="bar-group">
                                        <div class="bar" style="height: <?= $workBarHeight ?>px;">
                                            <div class="bar-value"><?= $avgWorkHours ?> hrs</div>
                                        </div>
                                        <div class="bar-label"><?= $dept->department_name ?> (Work)</div>
                                    </div>
                                    <div class="bar-group">
                                        <div class="bar" style="height: <?= $overtimeBarHeight ?>px; background-color: #9b59b6;">
                                            <div class="bar-value"><?= $avgOvertimeHours ?> hrs</div>
                                        </div>
                                        <div class="bar-label"><?= $dept->department_name ?> (OT)</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <table>
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Total Employees</th>
                            <th>Present Count</th>
                            <th>Absent Count</th>
                            <th>Late Count</th>
                            <th>Leave Count</th>
                            <th>Total Work Hours</th>
                            <th>Total Overtime Hours</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departmentData as $dept): ?>
                            <tr>
                                <td><?= $dept->department_name ?></td>
                                <td><?= $dept->total_employees ?></td>
                                <td><?= $dept->present_count ?></td>
                                <td><?= $dept->absent_count ?></td>
                                <td><?= $dept->late_count ?></td>
                                <td><?= $dept->leave_count ?></td>
                                <td><?= round($dept->total_work_minutes / 60, 1) ?> hrs</td>
                                <td><?= round($dept->total_overtime_minutes / 60, 1) ?> hrs</td>
                                <td>
                                    <a href="/reports/attendance?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&department_id=<?= $dept->department_id ?>" class="btn">Details</a>
                                </td>
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