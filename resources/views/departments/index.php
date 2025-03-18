<?php include_once __DIR__ . '/../layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?> - Departments</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .departments-container {
            margin-top: 20px;
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
        
        .btn-danger {
            background-color: #e74c3c;
        }
        
        .btn-warning {
            background-color: #f39c12;
        }
        
        .btn-info {
            background-color: #3498db;
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
        
        .department-card {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .department-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .department-name {
            font-size: 18px;
            font-weight: bold;
        }
        
        .department-actions {
            display: flex;
            gap: 10px;
        }
        
        .department-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            margin-bottom: 5px;
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
    </style>
</head>
<body>
    <main>
        <h2>Departments</h2>
        
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
        
        <div class="action-buttons">
            <a href="/departments/create" class="btn">Add Department</a>
        </div>
        
        <div class="departments-container">
            <?php if (empty($departments)): ?>
                <p>No departments found.</p>
            <?php else: ?>
                <?php foreach ($departments as $department): ?>
                    <div class="department-card">
                        <div class="department-header">
                            <div class="department-name"><?= $department->name ?></div>
                            <div class="department-actions">
                                <a href="/departments/view/<?= $department->id ?>" class="btn btn-info">View</a>
                                <a href="/departments/edit/<?= $department->id ?>" class="btn btn-warning">Edit</a>
                                <a href="/departments/delete/<?= $department->id ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this department?')">Delete</a>
                            </div>
                        </div>
                        
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
                                <div class="info-label">Employees</div>
                                <div class="info-value"><?= $department->employee_count ?></div>
                            </div>
                        </div>
                        
                        <?php if (isset($department->description)): ?>
                            <div class="department-description">
                                <div class="info-label">Description</div>
                                <div class="info-value"><?= $department->description ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html> 