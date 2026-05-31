<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['role'] === 'user';

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'User Portal'; ?> - Bus Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Additional Custom CSS for enhanced UI -->
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-width-collapsed: 80px;
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #4cc9f0;
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
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
            background: linear-gradient(180deg, var(--secondary-color), #2c3e50);
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
            background: rgba(67, 97, 238, 0.2);
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
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
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
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.05), rgba(76, 201, 240, 0.05));
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
            background: linear-gradient(135deg, var(--primary-color), var(--success-color));
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
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.05), rgba(76, 201, 240, 0.05));
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
            background-color: rgba(67, 97, 238, 0.02);
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
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
            color: white;
            box-shadow: 0 4px 15px rgba(76, 201, 240, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--warning-color), var(--danger-color));
            color: white;
            box-shadow: 0 4px 15px rgba(247, 37, 133, 0.3);
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
            background-color: rgba(67, 97, 238, 0.05);
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

        /* Hero Section for Homepage */
        .hero-section {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.9), rgba(58, 12, 163, 0.9)), url('../assets/images/bus-hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0 100px;
            margin-bottom: 50px;
            position: relative;
            overflow: hidden;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }

        .hero-section::before {
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

        .hero-section .container {
            position: relative;
            z-index: 1;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .hero-section p.lead {
            font-size: 1.25rem;
            font-weight: 300;
            margin-bottom: 2rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* Search Box */
        .search-box {
            background-color: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .search-box h4 {
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--dark-color);
            position: relative;
            padding-bottom: 0.5rem;
        }

        .search-box h4::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 40px;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
            border-radius: 10px;
        }

        .search-box .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            color: #555;
        }

        .search-box .form-select,
        .search-box .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .search-box .form-select:focus,
        .search-box .form-control:focus {
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            border-color: var(--primary-color);
            background-color: white;
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
            .hero-section {
                padding: 80px 0 70px;
            }

            .hero-section h1 {
                font-size: 2.5rem;
            }

            .search-box {
                margin-top: 30px;
            }

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
            .hero-section h1 {
                font-size: 2rem;
            }

            .hero-section p.lead {
                font-size: 1rem;
            }

            .search-box {
                padding: 20px;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .dashboard-icon {
                font-size: 2rem;
                margin-bottom: 15px;
            }
        }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                border-radius: 0 0 15px 15px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                padding: 1rem 0.5rem;
            }
            .navbar-nav .nav-link {
                color: #fff !important;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg top-navbar">
        <div class="container-fluid d-flex align-items-center">
            <?php if ($isLoggedIn): ?>
            <!-- Sidebar toggle for desktop -->
            <button class="btn btn-link text-white me-2 d-none d-lg-block" onclick="toggleSidebar()" style="font-size:1.5rem;">
                <i class="fas fa-bars"></i>
            </button>
            <?php endif; ?>
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-bus me-2"></i>Bus Management
            </a>
            <?php if ($isLoggedIn): ?>
            <!-- Navbar collapse toggle for mobile -->
            <button class="navbar-toggler d-lg-none ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <?php endif; ?>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav d-lg-none">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'schedules.php' ? 'active' : ''; ?>" href="schedules.php">
                            <i class="fas fa-calendar-alt"></i> Schedules
                        </a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'bookings.php' ? 'active' : ''; ?>" href="bookings.php">
                            <i class="fas fa-ticket-alt"></i> My Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'login.php' ? 'active' : ''; ?>" href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'register.php' ? 'active' : ''; ?>" href="register.php">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown user-profile-dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><div class="dropdown-header">User Account</div></li>
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="bookings.php"><i class="fas fa-ticket-alt me-2"></i> My Bookings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'login.php' ? 'active' : ''; ?>" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'register.php' ? 'active' : ''; ?>" href="register.php">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if ($isLoggedIn): ?>
    <div class="container-fluid mt-5 pt-2">
        <div class="row">
            <!-- Sidebar -->
            <div class="sidebar d-none d-lg-block" id="sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                                <i class="fas fa-home"></i> <span>Home</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'schedules.php' ? 'active' : ''; ?>" href="schedules.php">
                                <i class="fas fa-calendar-alt"></i> <span>Schedules</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'bookings.php' ? 'active' : ''; ?>" href="bookings.php">
                                <i class="fas fa-ticket-alt"></i> <span>My Bookings</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                                <i class="fas fa-user"></i> <span>My Profile</span>
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link text-danger" href="../auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="main-content" id="mainContent">
    <?php else: ?>
    <div class="container mt-5 pt-4">
        <main>
    <?php endif; ?>

    <script>
    // Remove toggleNavbar function for navbar collapse
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
    }
    // Keep the click outside handler for closing the navbar if needed
    </script>
</body>
</html>
