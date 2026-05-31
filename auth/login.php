<?php
session_start();
require_once '../config/database.php';
require_once '../includes/validation.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !Validation::validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid request";
        header("Location: ../index.php");
        exit();
    }

    // Get form data
    $role = Validation::sanitizeInput($_POST['role']);
    $username = Validation::sanitizeInput($_POST['username']);
    $password = $_POST['password']; // Don't sanitize password before verification

    // Check rate limiting
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!Validation::checkRateLimit($ip, 'login')) {
        $_SESSION['error'] = "Too many login attempts. Please try again later.";
        redirectToLogin($role);
        exit();
    }

    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username and password are required";
        redirectToLogin($role);
        exit();
    }

    // Validate username format
    $usernameValidation = Validation::validateUsername($username);
    if ($usernameValidation !== true) {
        $_SESSION['error'] = $usernameValidation;
        redirectToLogin($role);
        exit();
    }

    // Check user role and authenticate
    if ($role == 'admin') {
        // Admin authentication
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            
            if (verifyPassword($password, $admin['password'])) {
                if (!password_verify($password, $admin['password'])) {
                    $updatedHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $updatedHash, $admin['id']);
                    $updateStmt->execute();
                }

                // Set session variables
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['name'] = $admin['name'];
                $_SESSION['role'] = 'admin';
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Redirect to admin dashboard
                header("Location: ../admin/dashboard.php");
                exit();
            }
        }
        
        $_SESSION['error'] = "Invalid admin credentials";
        redirectToLogin('admin');
        exit();

    } else if ($role == 'driver') {
        // Driver authentication
        $stmt = $conn->prepare("SELECT * FROM drivers WHERE username = ? AND status = 'active'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $driver = $result->fetch_assoc();

            if (verifyPassword($password, $driver['password'])) {
                if (!password_verify($password, $driver['password'])) {
                    $updatedHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE drivers SET password = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $updatedHash, $driver['id']);
                    $updateStmt->execute();
                }

                // Set session variables
                $_SESSION['user_id'] = $driver['id'];
                $_SESSION['username'] = $driver['username'];
                $_SESSION['name'] = $driver['name'];
                $_SESSION['role'] = 'driver';
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Redirect to driver dashboard
                header("Location: ../driver/dashboard.php");
                exit();
            }
        }
        
        $_SESSION['error'] = "Invalid driver credentials or account inactive";
        redirectToLogin('driver');
        exit();

    } else if ($role == 'user') {
        // User authentication - handled separately in user/login.php
        $_SESSION['error'] = "Please use the user login page";
        redirectToLogin('user');
        exit();
    } else {
        $_SESSION['error'] = "Invalid role selected";
        header("Location: ../index.php");
        exit();
    }
} else {
    // If not a POST request, redirect to login page
    header("Location: ../index.php");
    exit();
}

// Helper function to redirect to the appropriate login page
function redirectToLogin($role) {
    switch ($role) {
        case 'admin':
            header("Location: ../admin/login.php");
            break;
        case 'driver':
            header("Location: ../driver/login.php");
            break;
        case 'user':
            header("Location: ../user/login_standalone.php");
            break;
        default:
            header("Location: ../index.php");
    }
}

function verifyPassword($password, $storedHash) {
    if (password_verify($password, $storedHash)) {
        return true;
    }
    return $password === $storedHash;
}
?>
