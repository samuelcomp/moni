<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?> - Employee Details</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .employee-container {
            margin-top: 20px;
        }
        
        .employee-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .employee-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
        }
        
        .employee-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .employee-position {
            font-size: 16px;
            color: #7f8c8d;
        }
        
        .employee-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .detail-section {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .detail-item {
            margin-bottom: 15px;
        }
        
        .detail-item:last-child {
            margin-bottom: 0;
        }
        
        .detail-label {
            font-weight: bold;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 16px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
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
        
        .btn-warning {
            background-color: #f39c12;
        }
        
        .btn-danger {
            background-color: #e74c3c;
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
        <div class="action-buttons">
            <a href="/employees" class="btn">Back to Employees</a>
            <a href="/attendance/employee/<?= $employee->id ?>" class="btn">View Attendance</a>
            <a href="/employees/edit/<?= $employee->id ?>" class="btn btn-warning">Edit</a>
            <a href="/employees/delete/<?= $employee->id ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</a>
        </div>
        
        <div class="employee-container">
            <div class="employee-header">
                <div class="employee-avatar">
                    <?= strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) ?>
                </div>
                <div>
                    <div class="employee-name"><?= $employee->first_name . ' ' . $employee->last_name ?></div>
                    <div class="employee-position"><?= $employee->position ?? 'No Position' ?> at <?= $employee->department_name ?? 'No Department' ?></div>
                </div>
            </div>
            
            <div class="employee-details">
                <div class="detail-section">
                    <div class="section-title">Personal Information</div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Employee ID</div>
                        <div class="detail-value"><?= $employee->employee_id ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?= $employee->email ?? 'Not provided' ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><?= $employee->phone ?? 'Not provided' ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Address</div>
                        <div class="detail-value"><?= $employee->address ?? 'Not provided' ?></div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="section-title">Employment Information</div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Department</div>
                        <div class="detail-value"><?= $employee->department_name ?? 'Not assigned' ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Position</div>
                        <div class="detail-value"><?= $employee->position ?? 'Not assigned' ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Join Date</div>
                        <div class="detail-value"><?= $employee->join_date ? date('d M Y', strtotime($employee->join_date)) : 'Not provided' ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Biometric ID</div>
                        <div class="detail-value"><?= $employee->biometric_id ?? 'Not assigned' ?></div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="section-title">System Access</div>
                    
                    <?php if ($employee->user_id): ?>
                        <div class="detail-item">
                            <div class="detail-label">Username</div>
                            <div class="detail-value"><?= $employee->username ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Role</div>
                            <div class="detail-value"><?= ucfirst(str_replace('_', ' ', $employee->role)) ?></div>
                        </div>
                    <?php else: ?>
                        <div class="detail-value">No system access</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> <?= $_ENV['APP_NAME'] ?></p>
    </footer>
</body>
</html> 