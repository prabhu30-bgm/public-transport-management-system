<?php
require_once 'config/database.php';

// Add passenger_count and revenue columns to trip_reports table
$sql = "ALTER TABLE trip_reports 
        ADD COLUMN passenger_count INT DEFAULT 0,
        ADD COLUMN revenue DECIMAL(10,2) DEFAULT 0.00";

if ($conn->query($sql) === TRUE) {
    echo "Columns added successfully";
} else {
    echo "Error adding columns: " . $conn->error;
}

$conn->close();
?> 