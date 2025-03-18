<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Employee Attendance Management System">
    <meta name="author" content="Your Name">

    <title>Attendance Management System</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/test.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php
    use App\Helpers\Session;
    use App\Models\User;
    
    $userRole = 'guest';
    $userName = 'Guest';
    $roleId = 0;
    
    if (Session::isLoggedIn()) {
        $userModel = new User();
        $user = $userModel->getUserById(Session::getUserId());
        
        // Get role ID
        $roleId = isset($user->role_id) ? (int)$user->role_id : 0;
        
        // Map role ID to role name
        switch ($roleId) {
            case 1:
                $userRole = 'admin';
                break;
            case 2:
                $userRole = 'hr_manager';
                break;
            case 3:
                $userRole = 'department_head';
                break;
            case 4:
                $userRole = 'employee';
                break;
            default:
                $userRole = 'employee';
        }
        
        $userName = $user->username ?? ($user->name ?? 'User');
    }
    
    // Define role-based access
    $isAdmin = ($roleId === 1);
    $isHrManager = ($roleId === 2);
    $isDepartmentHead = ($roleId === 3);
    $isEmployee = ($roleId === 4);
    
    // Manager-level access includes HR Manager and Department Head
    $isManagerLevel = ($isHrManager || $isDepartmentHead);
    ?>

    <div class="container-fluid p-0">
        <!-- Sidebar -->
        <div class="row">
            <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse" style="min-height: 100vh;">
                <div class="sidebar-sticky pt-3">
                    <div class="text-center mb-4">
                        <h3 class="text-white">Attendance MS</h3>
                    </div>
                    <ul class="nav flex-column">
                        <?php if (Session::isLoggedIn()): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white <?= ($_SERVER['REQUEST_URI'] === '/dashboard') ? 'active bg-primary' : '' ?>" href="/dashboard">
                                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                                </a>
                            </li>
                            
                            <?php if ($isAdmin || $isHrManager): ?>
                                <li class="nav-item">
                                    <a class="nav-link text-white <?= (strpos($_SERVER['REQUEST_URI'], '/employees') === 0) ? 'active bg-primary' : '' ?>" href="/employees">
                                        <i class="fas fa-users mr-2"></i> Employees
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($isAdmin): ?>
                                <li class="nav-item">
                                    <a class="nav-link text-white <?= (strpos($_SERVER['REQUEST_URI'], '/departments') === 0) ? 'active bg-primary' : '' ?>" href="/departments">
                                        <i class="fas fa-building mr-2"></i> Departments
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <!-- All users can access attendance -->
                            <li class="nav-item">
                                <a class="nav-link text-white <?= (strpos($_SERVER['REQUEST_URI'], '/attendance') === 0 && strpos($_SERVER['REQUEST_URI'], '/attendance/report') !== 0) ? 'active bg-primary' : '' ?>" href="/attendance">
                                    <i class="fas fa-calendar-check mr-2"></i> Attendance
                                </a>
                            </li>
                            
                            <?php if ($isAdmin || $isHrManager): ?>
                                <li class="nav-item">
                                    <a class="nav-link text-white <?= (strpos($_SERVER['REQUEST_URI'], '/shifts') === 0) ? 'active bg-primary' : '' ?>" href="/shifts">
                                        <i class="fas fa-clock mr-2"></i> Shifts
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <!-- All users can access leave requests -->
                            <li class="nav-item">
                                <a class="nav-link text-white <?= (strpos($_SERVER['REQUEST_URI'], '/leave-requests') === 0) ? 'active bg-primary' : '' ?>" href="/leave-requests">
                                    <i class="fas fa-calendar-minus mr-2"></i> Leave Requests
                                </a>
                            </li>
                            
                            <?php if ($isAdmin || $isHrManager): ?>
                                <li class="nav-item">
                                    <a class="nav-link text-white <?= (strpos($_SERVER['REQUEST_URI'], '/holidays') === 0) ? 'active bg-primary' : '' ?>" href="/holidays">
                                        <i class="fas fa-calendar-alt mr-2"></i> Holidays
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($isAdmin || $isManagerLevel): ?>
                                <li class="nav-item">
                                    <a class="nav-link text-white <?= (strpos($_SERVER['REQUEST_URI'], '/attendance/report') === 0) ? 'active bg-primary' : '' ?>" href="/attendance/report">
                                        <i class="fas fa-chart-bar mr-2"></i> Reports
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($isAdmin): ?>
                                <li class="nav-item">
                                    <a class="nav-link text-white <?= (strpos($_SERVER['REQUEST_URI'], '/devices') === 0) ? 'active bg-primary' : '' ?>" href="/devices">
                                        <i class="fas fa-tablet-alt mr-2"></i> Devices
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link text-white <?= (strpos($_SERVER['REQUEST_URI'], '/users') === 0) ? 'active bg-primary' : '' ?>" href="/users">
                                        <i class="fas fa-user-cog mr-2"></i> Users
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link text-white <?= (strpos($_SERVER['REQUEST_URI'], '/settings') === 0) ? 'active bg-primary' : '' ?>" href="/settings">
                                        <i class="fas fa-cogs mr-2"></i> Settings
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link text-white <?= ($_SERVER['REQUEST_URI'] === '/login') ? 'active bg-primary' : '' ?>" href="/login">
                                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <?php if (Session::isLoggedIn()): ?>
                        <div class="mt-4 px-3">
                            <div class="text-white">
                                <small>Logged in as:</small>
                                <div class="d-flex align-items-center mt-2">
                                    <div class="mr-2">
                                        <i class="fas fa-user-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <div><?= htmlspecialchars($userName) ?></div>
                                        <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $userRole)) ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <a href="/logout" class="btn btn-danger btn-sm btn-block">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                    <button class="navbar-toggler d-md-none" type="button" data-toggle="collapse" data-target=".sidebar">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse">
                        <ul class="navbar-nav ml-auto">
                            <?php if (Session::isLoggedIn()): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($userName) ?>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="/profile">
                                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profile
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="/logout">
                                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout
                                        </a>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </nav>
                
                <!-- Page Content -->
                <div class="container-fluid">
</body>
</html> 