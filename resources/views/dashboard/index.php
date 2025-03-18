<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?> - Dashboard</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .dashboard-container {
            margin-top: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .stat-label {
            font-size: 16px;
            color: #7f8c8d;
        }
        
        .attendance-stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .attendance-card {
            background-color: white;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .attendance-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .attendance-label {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .present-card {
            border-left: 4px solid #27ae60;
        }
        
        .absent-card {
            border-left: 4px solid #e74c3c;
        }
        
        .late-card {
            border-left: 4px solid #f39c12;
        }
        
        .leave-card {
            border-left: 4px solid #3498db;
        }
        
        .dashboard-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .recent-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .recent-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .employee-card {
            display: flex;
            align-items: center;
            background-color: white;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .employee-avatar {
            width: 40px;
            height: 40px;
            background-color: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .employee-info {
            flex: 1;
        }
        
        .employee-name {
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .employee-position {
            font-size: 12px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="container">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        </div>

        <!-- Content Row -->
        <div class="row">
            <!-- Total Employees Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Employees</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $employeeCount ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Departments Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Departments</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $departmentCount ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Present Today Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Present Today</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $presentCount ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Absent Today Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Absent Today</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $absentCount ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Row -->
        <div class="row">
            <!-- Recent Attendance -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Attendance</h6>
                        <a href="/attendance" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Employee</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentAttendance)): ?>
                                        <?php foreach ($recentAttendance as $attendance): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($attendance->date) ?></td>
                                                <td><?= htmlspecialchars($attendance->first_name . ' ' . $attendance->last_name) ?></td>
                                                <td>
                                                    <?php if ($attendance->status === 'present'): ?>
                                                        <span class="badge badge-success">Present</span>
                                                    <?php elseif ($attendance->status === 'absent'): ?>
                                                        <span class="badge badge-danger">Absent</span>
                                                    <?php elseif ($attendance->status === 'late'): ?>
                                                        <span class="badge badge-warning">Late</span>
                                                    <?php elseif ($attendance->status === 'half_day'): ?>
                                                        <span class="badge badge-info">Half Day</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary"><?= htmlspecialchars(ucfirst($attendance->status)) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No recent attendance records.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Leave Requests -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Pending Leave Requests</h6>
                        <a href="/leave-requests" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pendingLeaveRequests)): ?>
                                        <?php foreach ($pendingLeaveRequests as $leave): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($leave->first_name . ' ' . $leave->last_name) ?></td>
                                                <td><?= htmlspecialchars($leave->start_date) ?></td>
                                                <td><?= htmlspecialchars($leave->end_date) ?></td>
                                                <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $leave->leave_type))) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No pending leave requests.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html> 