<?php
// Only start session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Check if user is admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check if user is driver
function isDriver() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'driver';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please login to access this page";
        header("Location: ../index.php");
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error'] = "You don't have permission to access this page";
        if (isDriver()) {
            header("Location: ../driver/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    }
}

// Redirect if not driver
function requireDriver() {
    requireLogin();
    if (!isDriver()) {
        $_SESSION['error'] = "You don't have permission to access this page";
        if (isAdmin()) {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    }
}
?>
