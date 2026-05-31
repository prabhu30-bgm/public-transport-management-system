<?php
// Check if user is logged in and is driver
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
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
    <title><?php echo isset($page_title) ? $page_title : 'Driver Panel'; ?> - Bus Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Additional Custom CSS for driver panel -->
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-width-collapsed: 80px;
            --primary-color: #4cc9f0;
            --secondary-color: #4895ef;
            --success-color: #2ecc71;
            --warning-color: #f72585;
            --danger-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #2c3e50;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
            color: #333;
        }

        /* Navbar Styles */
        .top-navbar {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1.5rem;
            z-index: 1030;
            position: relative;
        }

        .top-navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiBwYXR0ZXJuVW5pdHM9InVzZXJTcGFjZU9uVXNlIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSgxMzUpIj48cmVjdCBpZD0icGF0dGVybi1iZyIgd2lkdGg9IjQwMCUiIGhlaWdodD0iNDAwJSIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjAyKSI+PC9yZWN0PjxjaXJjbGUgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjA1KSIgY3g9IjIwIiBjeT0iMjAiIHI9IjEiPjwvY2lyY2xlPjwvcGF0dGVybj48L2RlZnM+PHJlY3QgZmlsbD0idXJsKCNwYXR0ZXJuKSIgaGVpZ2h0PSIxMDAlIiB3aWR0aD0iMTAwJSI+PC9yZWN0Pjwvc3ZnPg==');
            opacity: 0.5;
            z-index: 0;
        }

        .navbar-brand {
            font-weight: 700;
            color: white !important;
            font-size: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .navbar-toggler {
            border: none;
            color: white;
            position: relative;
            z-index: 1;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 70px; /* Height of navbar */
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - 70px);
            background: linear-gradient(180deg, var(--secondary-color), #3a0ca3);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1020;
            overflow-y: auto;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-radius: 0 15px 15px 0;
            padding-top: 10px;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .sidebar.collapsed {
            width: var(--sidebar-width-collapsed);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.9rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            margin: 5px 10px;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }

        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background-color: var(--primary-color);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link:hover::before {
            transform: scaleY(1);
        }

        .sidebar .nav-link.active {
            color: white;
            background: rgba(76, 201, 240, 0.2);
            font-weight: 500;
        }

        .sidebar .nav-link.active::before {
            transform: scaleY(1);
        }

        .sidebar .nav-link i {
            margin-right: 12px;
            width: 22px;
            text-align: center;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 0.9rem 0;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
            font-size: 1.3rem;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: calc(100% - var(--sidebar-width));
            min-height: calc(100vh - 70px);
        }

        .main-content.expanded {
            margin-left: var(--sidebar-width-collapsed);
            width: calc(100% - var(--sidebar-width-collapsed));
        }

        /* Section Styles */
        .section-title {
            position: relative;
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
            font-weight: 600;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 50px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
        }

        /* Card Styles */
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            border: none;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
            background-color: rgba(0, 0, 0, 0.02);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-footer {
            background-color: rgba(0, 0, 0, 0.02);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1.5rem;
        }

        /* Dashboard Cards */
        .dashboard-card {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-radius: 15px;
            height: 100%;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.05), rgba(72, 149, 239, 0.05));
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card:hover::before {
            opacity: 1;
        }

        .dashboard-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            display: inline-block;
        }

        /* Table Styles */
        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.05), rgba(72, 149, 239, 0.05));
            color: var(--dark-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem 1.5rem;
            border-top: none;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .table tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            color: #555;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background-color: rgba(76, 201, 240, 0.02);
        }

        /* Button Styles */
        .btn {
            border-radius: 50px;
            font-weight: 500;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
            border: none;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            z-index: -1;
        }

        .btn:hover::before {
            width: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 0 4px 15px rgba(76, 201, 240, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
            color: white;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--warning-color), var(--danger-color));
            color: white;
            box-shadow: 0 4px 15px rgba(247, 37, 133, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
        }

        .btn-info {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            color: white;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .btn-sm {
            padding: 0.4rem 1.2rem;
            font-size: 0.875rem;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 8px;
            font-size: 0.9rem;
        }

        /* User Profile Dropdown */
        .user-profile-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .user-profile-dropdown .dropdown-toggle::after {
            display: none;
        }

        .user-profile-dropdown .dropdown-menu {
            right: 0;
            left: auto;
            min-width: 240px;
            padding: 0;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
            margin-top: 10px;
            overflow: hidden;
        }

        .user-profile-dropdown .dropdown-item {
            padding: 0.9rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .user-profile-dropdown .dropdown-item i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .user-profile-dropdown .dropdown-item:hover {
            background-color: rgba(76, 201, 240, 0.05);
        }

        .user-profile-dropdown .dropdown-divider {
            margin: 0;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .user-profile-dropdown .dropdown-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            border-radius: 15px 15px 0 0;
            position: relative;
            overflow: hidden;
        }

        .user-profile-dropdown .dropdown-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiBwYXR0ZXJuVW5pdHM9InVzZXJTcGFjZU9uVXNlIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSgxMzUpIj48cmVjdCBpZD0icGF0dGVybi1iZyIgd2lkdGg9IjQwMCUiIGhlaWdodD0iNDAwJSIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjAyKSI+PC9yZWN0PjxjaXJjbGUgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjA1KSIgY3g9IjIwIiBjeT0iMjAiIHI9IjEiPjwvY2lyY2xlPjwvcGF0dGVybj48L2RlZnM+PHJlY3QgZmlsbD0idXJsKCNwYXR0ZXJuKSIgaGVpZ2h0PSIxMDAlIiB3aWR0aD0iMTAwJSI+PC9yZWN0Pjwvc3ZnPg==');
            opacity: 0.5;
            z-index: 0;
        }

        /* Status Badges */
        .badge {
            padding: 0.5em 0.8em;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
                z-index: 1040;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }

            .main-content.expanded {
                margin-left: 0;
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .table-responsive {
                border-radius: 10px;
            }

            .table thead th {
                padding: 0.75rem 1rem;
            }

            .table tbody td {
                padding: 0.75rem 1rem;
            }
        }

        @media (max-width: 576px) {
            .section-title {
                font-size: 1.5rem;
            }

            .dashboard-icon {
                font-size: 2rem;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light top-navbar fixed-top">
        <div class="container-fluid">
            <button class="btn btn-link text-dark me-3 d-none d-lg-block" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-bus-alt me-2"></i>
                Driver Portal
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
                        <a class="nav-link <?php echo $current_page == 'schedule.php' ? 'active' : ''; ?>" href="schedule.php">
                            <i class="fas fa-calendar-alt me-2"></i> My Schedule
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'trips.php' ? 'active' : ''; ?>" href="trips.php">
                            <i class="fas fa-route me-2"></i> My Trips
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                            <i class="fas fa-user me-2"></i> My Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>

                <!-- Status Indicator -->
                <div class="mx-auto d-none d-md-block">
                    <?php
                    // Check if driver is currently on a trip
                    $driverId = $_SESSION['user_id'];
                    $checkTripSql = "SELECT s.id, s.status FROM schedules s WHERE s.driver_id = $driverId AND s.status = 'departed' AND s.departure_date = CURDATE() LIMIT 1";
                    $checkTripResult = $conn->query($checkTripSql);

                    if ($checkTripResult && $checkTripResult->num_rows > 0) {
                        $tripData = $checkTripResult->fetch_assoc();
                        echo '<div class="driver-status active-trip">
                                <i class="fas fa-circle text-success me-2"></i>
                                <span>Currently On Trip</span>
                              </div>';
                    } else {
                        echo '<div class="driver-status">
                                <i class="fas fa-circle text-secondary me-2"></i>
                                <span>Not On Trip</span>
                              </div>';
                    }
                    ?>
                </div>

                <!-- User Profile & Clock -->
                <ul class="navbar-nav ms-auto">
                    <!-- Current Time -->
                    <li class="nav-item me-3 d-none d-md-block">
                        <div class="current-time">
                            <i class="fas fa-clock me-2"></i>
                            <span id="currentTime"></span>
                        </div>
                    </li>

                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown user-profile-dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="d-none d-lg-inline me-2 text-gray-600 small"><?php echo $_SESSION['name']; ?></span>
                            <div class="avatar-circle bg-primary text-white">
                                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                            <li><div class="dropdown-header">Driver Account</div></li>
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2 text-gray-400"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="schedule.php"><i class="fas fa-calendar-alt me-2 text-gray-400"></i> My Schedule</a></li>
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
            <div class="sidebar d-none d-lg-block" id="sidebar">
                <!-- Sidebar Brand -->
                <div class="sidebar-brand">
                    <i class="fas fa-bus-alt fa-2x me-2"></i>
                    <h4>Driver Portal</h4>
                </div>

                <!-- Divider -->
                <hr class="sidebar-divider">

                <!-- Driver Info Card -->
                <div class="driver-info-card mx-3 mb-4">
                    <div class="driver-avatar">
                        <div class="avatar-circle bg-white text-primary">
                            <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                        </div>
                    </div>
                    <div class="driver-details">
                        <h6 class="driver-name"><?php echo $_SESSION['name']; ?></h6>
                        <p class="driver-id">ID: <?php echo $_SESSION['user_id']; ?></p>
                        <div class="driver-status-indicator">
                            <?php
                            if (isset($tripData) && $tripData['status'] == 'departed') {
                                echo '<span class="status-badge on-trip"><i class="fas fa-circle me-1"></i> On Trip</span>';
                            } else {
                                echo '<span class="status-badge available"><i class="fas fa-circle me-1"></i> Available</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Heading -->
                <div class="sidebar-heading">
                    Main Navigation
                </div>

                <!-- Nav Items -->
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'schedule.php' ? 'active' : ''; ?>" href="schedule.php">
                            <i class="fas fa-calendar-alt"></i> <span>My Schedule</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'trips.php' ? 'active' : ''; ?>" href="trips.php">
                            <i class="fas fa-route"></i> <span>My Trips</span>
                        </a>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider">

                    <!-- Sidebar Heading -->
                    <div class="sidebar-heading">
                        Account
                    </div>

                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                            <i class="fas fa-user"></i> <span>My Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                        </a>
                    </li>
                </ul>

                <!-- Current Time Display -->
                <div class="sidebar-time-display mt-auto mx-3 mb-4">
                    <div class="current-date-time">
                        <div id="sidebarDate" class="current-date"></div>
                        <div id="sidebarTime" class="current-time"></div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="main-content" id="mainContent">

                <!-- Additional styles for driver dashboard -->
                <style>
                    /* Driver Status Styles */
                    .driver-status {
                        display: flex;
                        align-items: center;
                        padding: 0.5rem 1rem;
                        border-radius: var(--border-radius-pill);
                        background-color: var(--light);
                        font-weight: var(--font-weight-semibold);
                    }

                    .driver-status.active-trip {
                        background-color: var(--success-bg);
                        color: var(--success);
                    }

                    /* Current Time Display */
                    .current-time {
                        display: flex;
                        align-items: center;
                        padding: 0.5rem 1rem;
                        border-radius: var(--border-radius-pill);
                        background-color: var(--light);
                        font-weight: var(--font-weight-semibold);
                    }

                    /* Driver Info Card */
                    .driver-info-card {
                        background: rgba(255, 255, 255, 0.1);
                        border-radius: var(--border-radius);
                        padding: 1rem;
                        display: flex;
                        align-items: center;
                    }

                    .driver-avatar {
                        margin-right: 1rem;
                    }

                    .driver-avatar .avatar-circle {
                        width: 50px;
                        height: 50px;
                        font-size: 1.5rem;
                    }

                    .driver-details {
                        flex: 1;
                    }

                    .driver-name {
                        margin: 0;
                        color: var(--white);
                        font-weight: var(--font-weight-semibold);
                    }

                    .driver-id {
                        margin: 0;
                        font-size: 0.8rem;
                        color: rgba(255, 255, 255, 0.7);
                    }

                    .driver-status-indicator {
                        margin-top: 0.5rem;
                    }

                    .status-badge {
                        display: inline-flex;
                        align-items: center;
                        padding: 0.25rem 0.5rem;
                        border-radius: var(--border-radius-pill);
                        font-size: 0.75rem;
                        font-weight: var(--font-weight-semibold);
                    }

                    .status-badge.on-trip {
                        background-color: rgba(28, 200, 138, 0.2);
                        color: var(--success);
                    }

                    .status-badge.available {
                        background-color: rgba(255, 255, 255, 0.2);
                        color: var(--white);
                    }

                    /* Sidebar Time Display */
                    .sidebar-time-display {
                        background: rgba(255, 255, 255, 0.1);
                        border-radius: var(--border-radius);
                        padding: 1rem;
                        text-align: center;
                    }

                    .current-date {
                        font-size: 0.9rem;
                        color: rgba(255, 255, 255, 0.8);
                        margin-bottom: 0.25rem;
                    }

                    .sidebar-time-display .current-time {
                        font-size: 1.2rem;
                        font-weight: var(--font-weight-bold);
                        color: var(--white);
                        background: none;
                        padding: 0;
                        justify-content: center;
                    }
                </style>
