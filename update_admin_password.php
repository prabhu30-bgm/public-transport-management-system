<?php
require_once 'config/database.php';

$newPassword = 'admin1234';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);
$escapedHash = $conn->real_escape_string($hash);
$updateSql = "UPDATE admins SET password='$escapedHash' WHERE username='admin'";

if (!$conn->query($updateSql)) {
    echo 'Error: ' . $conn->error;
    exit(1);
}

echo 'Admin password updated successfully.';
$conn->close();
