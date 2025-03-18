<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?> - Reports</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .reports-container {
            margin-top: 20px;
        }
        
        .report-card {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .report-description {
            margin-bottom: 15px;
            color: #555;
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
        
        .btn-primary {
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
        <h2>Reports</h2>
        
        <div class="reports-container">
            <div class="report-card">
                <div class="report-title">Attendance Report</div>
                <div class="report-description">
                    Generate detailed attendance reports for employees. Filter by date range, department, or individual employee.
                </div>
                <a href="/reports/attendance" class="btn btn-primary">Generate Report</a>
            </div>
            
            <div class="report-card">
                <div class="report-title">Department Summary Report</div>
                <div class="report-description">
                    View attendance statistics by department. Compare attendance rates, work hours, and overtime across departments.
                </div>
                <a href="/reports/department" class="btn btn-primary">Generate Report</a>
            </div>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> <?= $_ENV['APP_NAME'] ?></p>
    </footer>
</body>
</html> 