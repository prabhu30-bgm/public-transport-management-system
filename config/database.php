<?php
// Database settings for local and online hosting
// Change these values when you deploy to a live host
$host = "127.0.0.1";  // online host may use a different value, e.g. localhost or mysql.example.com
$username = "root";   // your database username
$password = "";       // your database password
$database = "project_bms"; // your database name
$port = 3307;          // local XAMPP port; change to 3306 or empty for most hosts

if (empty($port)) {
    $port = 3306;
}

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

date_default_timezone_set('Asia/Kolkata');
?>