<?php include_once __DIR__ . '/../layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?> - Add Employee</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px 0;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-group textarea {
            height: 100px;
        }
        
        .btn {
            display: inline-block;
            background-color: #2c3e50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .btn-secondary {
            background-color: #7f8c8d;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-note {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    
    
    <main>
        <h2>Add New Employee</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form action="/employees/store" method="POST">
                <div class="form-section">
                    <div class="form-section-title">Personal Information</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone">
                        </div>
                        
                        <div class="form-group" style="grid-column: span 2;">
                            <label for="address">Address</label>
                            <textarea id="address" name="address"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="form-section-title">Employment Information</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="employee_id">Employee ID *</label>
                            <input type="text" id="employee_id" name="employee_id" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="department_id">Department</label>
                            <select id="department_id" name="department_id">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?= $department->id ?>"><?= $department->name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="position">Position</label>
                            <input type="text" id="position" name="position">
                        </div>
                        
                        <div class="form-group">
                            <label for="join_date">Join Date</label>
                            <input type="date" id="join_date" name="join_date" value="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="biometric_id">Biometric ID</label>
                            <input type="text" id="biometric_id" name="biometric_id">
                            <div class="form-note">ID used in biometric devices for attendance tracking</div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="form-section-title">System Access</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username">
                            <div class="form-note">Leave blank if no system access is required</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password">
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role">
                                <option value="employee">Employee</option>
                                <option value="department_head">Department Head</option>
                                <option value="hr_manager">HR Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 20px;">
                    <button type="submit" class="btn">Add Employee</button>
                    <a href="/employees" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> <?= $_ENV['APP_NAME'] ?></p>
    </footer>
</body>
</html>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?> 