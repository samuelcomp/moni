<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?> - Department Details</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .department-container {
            margin-top: 20px;
        }
        
        .department-header {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .department-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .department-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
        }
        
        .department-description {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        
        .employees-section {
            margin-top: 30px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .employee-card {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .employee-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
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
                <li><a href="/departments">Departments</a></li>
                <li><a href="/devices">Devices</a></li>
                <li><a href="/logout">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="action-buttons">
            <a href="/departments" class="btn">Back to Departments</a>
            <a href="/departments/edit/<?= $department->id ?>" class="btn btn-warning">Edit</a>
            <a href="/departments/delete/<?= $department->id ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this department?')">Delete</a>
        </div>
        
        <div class="department-container">
            <div class="department-header">
                <div class="department-name"><?= $department->name ?></div>
                
                <div class="department-info">
                    <div class="info-item">
                        <div class="info-label">Location</div>
                        <div class="info-value"><?= $department->location ?? 'Not specified' ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Manager</div>
                        <div class="info-value"><?= $department->manager_name ?? 'Not assigned' ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Total Employees</div>
                        <div class="info-value"><?= count($employees) ?></div>
                    </div>
                </div>
                
                <?php if ($department->description): ?>
                    <div class="department-description">
                        <div class="info-label">Description</div>
                        <div class="info-value"><?= $department->description ?></div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="employees-section">
                <div class="section-title">Employees</div>
                
                <?php if (empty($employees)): ?>
                    <p>No employees in this department.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Join Date</th>
                                <th>Contact</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td><?= $employee->employee_id ?></td>
                                    <td>
                                        <div class="employee-card">
                                            <div class="employee-avatar">
                                                <?= strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) ?>
                                            </div>
                                            <?= $employee->first_name . ' ' . $employee->last_name ?>
                                        </div>
                                    </td>
                                    <td><?= $employee->position ?? 'N/A' ?></td>
                                    <td><?= $employee->join_date ? date('d M Y', strtotime($employee->join_date)) : 'N/A' ?></td>
                                    <td>
                                        <?= $employee->email ?? 'N/A' ?><br>
                                        <?= $employee->phone ?? 'N/A' ?>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="/employees/view/<?= $employee->id ?>" class="btn">View</a>
                                        <a href="/attendance/employee/<?= $employee->id ?>" class="btn">Attendance</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> <?= $_ENV['APP_NAME'] ?></p>
    </footer>
</body>
</html> 