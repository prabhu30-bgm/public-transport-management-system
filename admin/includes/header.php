<?php
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?> - Bus Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Admin JavaScript -->
    <script src="../assets/js/admin.js" defer></script>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light top-navbar fixed-top">
        <div class="container-fluid">
            <button class="btn btn-link text-dark me-3 d-block" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-bus-alt me-2"></i>
                Bus Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Mobile Navigation -->
                <ul class="navbar-nav d-lg-none">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'drivers.php' ? 'active' : ''; ?>" href="drivers.php">
                            <i class="fas fa-user me-2"></i> Drivers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'buses.php' ? 'active' : ''; ?>" href="buses.php">
                            <i class="fas fa-bus me-2"></i> Buses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'routes.php' ? 'active' : ''; ?>" href="routes.php">
                            <i class="fas fa-route me-2"></i> Routes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'bookings.php' ? 'active': '';?>" href="bookings.php">
                            <i class="fas fa-ticket-alt me-2"></i> Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
                            <i class="fas fa-users me-2"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'schedules.php' ? 'active' : ''; ?>" href="schedules.php">
                            <i class="fas fa-calendar-alt me-2"></i> Schedules
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'issues.php' ? 'active' : ''; ?>" href="issues.php">
                            <i class="fas fa-exclamation-triangle me-2"></i> Issues
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>

                <!-- Search Bar -->
                <!-- <div class="navbar-search mx-auto d-none d-md-block">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-0 bg-light shadow-none" placeholder="Search for schedules, drivers, buses..." aria-label="Search">
                        <button class="btn btn-light border-0" type="button">
                            <i class="fas fa-filter text-muted"></i>
                        </button>
                    </div>
                </div> -->

                <!-- User Profile & Notifications -->
                <!-- <ul class="navbar-nav ms-auto"> -->
                    <!-- Notifications Dropdown
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell fa-fw"></i> -->
                            <!-- Notification Counter -->
                            <!-- <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                3+
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="notificationsDropdown">
                            <h6 class="dropdown-header bg-primary text-white">
                                Notifications Center
                            </h6>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="me-3">
                                    <div class="icon-circle bg-primary-light">
                                        <i class="fas fa-file-alt text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-gray-500">December 12, 2023</div>
                                    <span>A new monthly report is ready to download!</span>
                                </div>
                            </a>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="me-3">
                                    <div class="icon-circle bg-warning-light">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-gray-500">December 11, 2023</div>
                                    <span>Driver reported an issue with Bus #103</span>
                                </div>
                            </a>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="me-3">
                                    <div class="icon-circle bg-success-light">
                                        <i class="fas fa-user-plus text-success"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-gray-500">December 10, 2023</div>
                                    <span>New driver John Doe was added to the system</span>
                                </div>
                            </a>
                            <a class="dropdown-item text-center small text-gray-500" href="#">Show All Notifications</a>
                        </div>
                    </li> -->

                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown user-profile-dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="d-none d-lg-inline me-2 text-gray-600 small"><?php echo $_SESSION['name']; ?></span>
                            <div class="avatar-circle bg-primary text-white">
                                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                            <li><div class="dropdown-header">Admin Account</div></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2 text-gray-400"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2 text-gray-400"></i> Settings</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-list me-2 text-gray-400"></i> Activity Log</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <!-- Sidebar Brand -->
                <div class="sidebar-brand">
                    <i class="fas fa-bus-alt fa-2x me-2"></i>
                    <h4>Bus Management</h4>
                </div>

                <!-- Divider -->
                <hr class="sidebar-divider">

                <!-- Sidebar Heading -->
                <div class="sidebar-heading">
                    Core
                </div>

                <!-- Nav Items -->
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                        </a>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider">

                    <!-- Sidebar Heading -->
                    <div class="sidebar-heading">
                        Management
                    </div>

                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'drivers.php' ? 'active' : ''; ?>" href="drivers.php">
                            <i class="fas fa-user"></i> <span>Drivers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'buses.php' ? 'active' : ''; ?>" href="buses.php">
                            <i class="fas fa-bus"></i> <span>Buses</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'routes.php' ? 'active' : ''; ?>" href="routes.php">
                            <i class="fas fa-route"></i> <span>Routes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'schedules.php' ? 'active' : ''; ?>" href="schedules.php">
                            <i class="fas fa-calendar-alt"></i> <span>Schedules</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'bookings.php' ? 'active': '' ;?>" href="bookings.php">
                            <i class="fas fa-ticket-alt"></i> <span>Bookings</span> 
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
                            <i class="fas fa-users"></i> <span>Users</span>
                        </a>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider">

                    <!-- Sidebar Heading -->
                    <div class="sidebar-heading">
                        Reports & Issues
                    </div>

                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                            <i class="fas fa-chart-bar"></i> <span>Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'issues.php' ? 'active' : ''; ?>" href="issues.php">
                            <i class="fas fa-exclamation-triangle"></i> <span>Issues</span>
                        </a>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider">

                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="main-content" id="mainContent">
                <!-- Page content -->
                <main class="content">
