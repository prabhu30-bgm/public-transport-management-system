<?php
require_once 'config/database.php';

// Check if the user is logged in as admin
// session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     // Redirect to login page if not logged in as admin
//     header("Location: auth/login.php?error=unauthorized");
//     exit();
// }

// Check if the confirmation was submitted
// $confirmed = isset($_POST['confirm']) && $_POST['confirm'] === 'yes';

// HTML header
// echo "<!DOCTYPE html>\n";
// echo "<html lang='en'>\n";
// echo "<head>\n";
// echo "    <meta charset='UTF-8'>\n";
// echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
// echo "    <title>Database Cleanup - Bus Management System</title>\n";
// echo "    <!-- Bootstrap CSS -->\n";
// echo "    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
// echo "    <!-- Font Awesome -->\n";
// echo "    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>\n";
// echo "    <!-- Custom CSS -->\n";
// echo "    <link rel='stylesheet' href='assets/css/style.css'>\n";
// echo "</head>\n";
// echo "<body>\n";
// echo "<div class='container mt-5'>\n";
// echo "    <div class='row'>\n";
// echo "        <div class='col-md-8 offset-md-2'>\n";
// echo "            <div class='card'>\n";
// echo "                <div class='card-header bg-primary text-white'>\n";
// echo "                    <h3><i class='fas fa-database'></i> Database Cleanup</h3>\n";
// echo "                </div>\n";
// echo "                <div class='card-body'>\n";

// if (!$confirmed) {
    // Show confirmation form
    // echo "<h4 class='text-danger'><i class='fas fa-exclamation-triangle'></i> Warning: This will delete all trip data!</h4>";
    // echo "<p>This action will permanently delete:</p>";
    // echo "<ul>";
    // echo "<li>All scheduled trips</li>";
    // echo "<li>All trip reports</li>";
    // echo "<li>All issue reports</li>";
    // echo "<li>All tickets</li>";
    // echo "<li>All bookings</li>";
    // echo "</ul>";
    // echo "<p class='text-danger'><strong>This action cannot be undone.</strong></p>";
    // echo "<form method='post' action=''>";
    // echo "<input type='hidden' name='confirm' value='yes'>";
    // echo "<button type='submit' class='btn btn-danger'><i class='fas fa-trash'></i> Yes, Delete All Trip Data</button>";
    // echo " <a href='admin/dashboard.php' class='btn btn-secondary'><i class='fas fa-times'></i> Cancel</a>";
    // echo "</form>";
// } else {
    // Start a transaction to ensure data consistency
    // $conn->begin_transaction();

    // try {
        // First, delete records from tickets (depends on bookings)
        // $sql = "DELETE FROM tickets";
        // $conn->query($sql);
        // $ticketsDeleted = $conn->affected_rows;

        // Delete records from bookings (depends on schedules)
        // $sql = "DELETE FROM bookings";
        // $conn->query($sql);
        // $bookingsDeleted = $conn->affected_rows;

        // Delete records from issue_reports (depends on schedules)
        // $sql = "DELETE FROM issue_reports";
        // $conn->query($sql);
        // $issueReportsDeleted = $conn->affected_rows;

        // Delete records from trip_reports (depends on schedules)
        // $sql = "DELETE FROM trip_reports";
        // $conn->query($sql);
        // $tripReportsDeleted = $conn->affected_rows;

        // Finally, delete records from schedules
        // $sql = "DELETE FROM schedules";
        // $conn->query($sql);
        // $schedulesDeleted = $conn->affected_rows;

        // Commit the transaction
        // $conn->commit();

        // echo "<div class='alert alert-success'>";
        // echo "<h4><i class='fas fa-check-circle'></i> Database Cleanup Completed</h4>";
        // echo "<p>Successfully deleted:</p>";
        // echo "<ul>";
        // echo "<li>$ticketsDeleted tickets</li>";
        // echo "<li>$bookingsDeleted bookings</li>";
        // echo "<li>$issueReportsDeleted issue reports</li>";
        // echo "<li>$tripReportsDeleted trip reports</li>";
        // echo "<li>$schedulesDeleted schedules</li>";
        // echo "</ul>";
        // echo "</div>";

        // echo "<p>The database has been reset. You can now start testing from the beginning.</p>";
        // echo "<a href='admin/dashboard.php' class='btn btn-primary'><i class='fas fa-tachometer-alt'></i> Go to Admin Dashboard</a>";

    // } catch (Exception $e) {
        // An error occurred, rollback the transaction
    //     $conn->rollback();

    //     echo "<div class='alert alert-danger'>";
    //     echo "<h4><i class='fas fa-times-circle'></i> Error During Cleanup</h4>";
    //     echo "<p>An error occurred while cleaning up the database:</p>";
    //     echo "<p><code>" . $e->getMessage() . "</code></p>";
    //     echo "</div>";
    //     echo "<a href='cleanup_trips.php' class='btn btn-primary'>Try Again</a> ";
    //     echo "<a href='admin/dashboard.php' class='btn btn-secondary'>Go to Dashboard</a>";
    // }

    // Close the connection
//     $conn->close();
// }

// HTML footer
// echo "                </div>";
// echo "            </div>";
// echo "        </div>";
// echo "    </div>";
// echo "</div>";
// echo "<!-- Bootstrap JS -->";
// echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
// echo "</body>";
// echo "</html>";
?>
