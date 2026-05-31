<?php
session_start();

// Store the user role before clearing session
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect based on user role
if ($role === 'user') {
    header("Location: ../user/login_standalone.php");
} elseif ($role === 'driver') {
    header("Location: ../auth/login.php");
} elseif ($role === 'admin') {
    header("Location: ../auth/login.php");
} else {
    header("Location: ../index.php");
}
exit();
?>
