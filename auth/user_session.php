<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'user';
}

// Function to require user login
function requireUser() {
    if (!isUserLoggedIn()) {
        header("Location: ../user/login.php?error=login_required");
        exit();
    }
}

// Function to redirect if already logged in
function redirectIfLoggedIn() {
    if (isUserLoggedIn()) {
        header("Location: ../user/index.php");
        exit();
    }
}
?>
