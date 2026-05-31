<?php
require_once '../auth/session.php';
require_once '../config/database.php';
requireDriver();

// Set page title
$page_title = 'Dashboard';

// Get driver ID from session
$driverId = $_SESSION['user_id'];

// Get current server time
$currentTime = date('H:i:s');
$currentDate = date('Y-m-d');

// Process departure button click
if (isset($_POST['departure']) && isset($_POST['schedule_id'])) {
    $scheduleId = $_POST['schedule_id'];
    $isDeparting = isset($_POST['depart_type']) ? $_POST['depart_type'] : 'on_time';
    $delayReason = '';

    if ($isDeparting == 'delayed' && isset($_POST['delay_reason'])) {
        $delayReason = $_POST['delay_reason'];
    }

    // First, check if the schedule exists and belongs to this driver
    $checkSql = "SELECT id FROM schedules WHERE id = $scheduleId AND driver_id = $driverId";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows == 0) {
        // Schedule doesn't exist or doesn't belong to this driver
        header("Location: dashboard.php?error=invalid_schedule");
        exit();
    }

    // Update schedule status
    $updateSql = "UPDATE schedules SET status = 'departed' WHERE id = $scheduleId AND driver_id = $driverId";
    $updateResult = $conn->query($updateSql);

    if ($updateResult) {
        // Only create trip report if schedule was updated successfully
        // Create trip report with notification for admin
        if ($isDeparting == 'on_time') {
            $reportSql = "INSERT INTO trip_reports (schedule_id, actual_departure_time, status, remarks)
                         VALUES ($scheduleId, NOW(), 'on_time', 'Driver started the trip at " . date('h:i A') . "')";
        } else {
            $reportSql = "INSERT INTO trip_reports (schedule_id, actual_departure_time, status, remarks)
                         VALUES ($scheduleId, NOW(), 'delayed', 'Driver reported DELAYED departure at " . date('h:i A') . ". Reason: $delayReason')";
        }

        try {
            $conn->query($reportSql);
            // Redirect to refresh the page with success message
            $status = ($isDeparting == 'on_time') ? 'success' : 'delayed';
            header("Location: dashboard.php?departed=$status");
            exit();
        } catch (Exception $e) {
            // Log the error
            error_log("Error creating trip report: " . $e->getMessage());
            header("Location: dashboard.php?error=trip_report_failed");
            exit();
        }
    } else {
        header("Location: dashboard.php?error=update_failed");
        exit();
    }
}

// Process complete trip button click
if (isset($_POST['complete_trip']) && isset($_POST['schedule_id'])) {
    $scheduleId = $_POST['schedule_id'];
    $remarks = isset($_POST['completion_remarks']) ? $_POST['completion_remarks'] : '';

    // First, check if the schedule exists, belongs to this driver, and is in departed status
    // Also get route information to check estimated time
    $checkSql = "SELECT s.id, s.route_id, r.estimated_time, tr.actual_departure_time
                 FROM schedules s
                 JOIN routes r ON s.route_id = r.id
                 LEFT JOIN trip_reports tr ON s.id = tr.schedule_id
                 WHERE s.id = $scheduleId AND s.driver_id = $driverId AND s.status = 'departed'";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows == 0) {
        // Schedule doesn't exist, doesn't belong to this driver, or isn't in departed status
        header("Location: dashboard.php?error=invalid_complete");
        exit();
    }

    // Get route estimated time and actual departure time
    $tripData = $checkResult->fetch_assoc();
    $estimatedMinutes = $tripData['estimated_time']; // This is in minutes
    $actualDepartureTime = $tripData['actual_departure_time'];

    // Calculate the earliest possible completion time
    $departureTimestamp = strtotime($actualDepartureTime);
    $earliestCompletionTimestamp = $departureTimestamp + ($estimatedMinutes * 60); // Convert minutes to seconds
    $currentTimestamp = time();

    // Check if enough time has passed
    if ($currentTimestamp < $earliestCompletionTimestamp) {
        // Trip cannot be completed yet - it's too early
        $remainingMinutes = ceil(($earliestCompletionTimestamp - $currentTimestamp) / 60);
        header("Location: dashboard.php?error=too_early&remaining=$remainingMinutes&estimated=$estimatedMinutes");
        exit();
    }

    // Update schedule status
    $updateSql = "UPDATE schedules SET status = 'completed' WHERE id = $scheduleId AND driver_id = $driverId AND status = 'departed'";
    $updateResult = $conn->query($updateSql);

    if ($updateResult) {
        // Check if trip report exists
        $checkReportSql = "SELECT id FROM trip_reports WHERE schedule_id = $scheduleId";
        $checkReportResult = $conn->query($checkReportSql);

        if ($checkReportResult->num_rows > 0) {
            // Update trip report
            $updateReportSql = "UPDATE trip_reports SET
                                actual_arrival_time = NOW(),
                                status = 'completed',
                                remarks = CONCAT(remarks, ' | Trip completed at " . date('h:i A') . ". " . $remarks . "')
                                WHERE schedule_id = $scheduleId";
            try {
                $conn->query($updateReportSql);
                header("Location: dashboard.php?trip=completed");
                exit();
            } catch (Exception $e) {
                error_log("Error updating trip report: " . $e->getMessage());
                header("Location: dashboard.php?error=report_update_failed");
                exit();
            }
        } else {
            // No trip report exists, create one
            $createReportSql = "INSERT INTO trip_reports (schedule_id, actual_departure_time, actual_arrival_time, status, remarks)
                               VALUES ($scheduleId, NOW(), NOW(), 'completed', 'Trip completed at " . date('h:i A') . ". " . $remarks . "')";
            try {
                $conn->query($createReportSql);
                header("Location: dashboard.php?trip=completed");
                exit();
            } catch (Exception $e) {
                error_log("Error creating trip report: " . $e->getMessage());
                header("Location: dashboard.php?error=report_create_failed");
                exit();
            }
        }
    } else {
        header("Location: dashboard.php?error=complete_failed");
        exit();
    }
}

// Process issue report submission
if (isset($_POST['report_issue']) && isset($_POST['schedule_id'])) {
    $scheduleId = $_POST['schedule_id'];
    $issueType = isset($_POST['issue_type']) ? $_POST['issue_type'] : '';
    $issueDescription = isset($_POST['issue_description']) ? $_POST['issue_description'] : '';
    $location = isset($_POST['location']) ? $_POST['location'] : '';

    // Validate required fields
    if (empty($issueType) || empty($issueDescription)) {
        header("Location: dashboard.php?error=issue_fields_required");
        exit();
    }

    // Check if the schedule exists and belongs to this driver
    $checkSql = "SELECT id FROM schedules WHERE id = $scheduleId AND driver_id = $driverId AND status = 'departed'";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows == 0) {
        // Schedule doesn't exist, doesn't belong to this driver, or isn't in departed status
        header("Location: dashboard.php?error=invalid_issue_report");
        exit();
    }

    // Insert the issue report
    $issueDescription = $conn->real_escape_string($issueDescription);
    $location = $conn->real_escape_string($location);

    $insertSql = "INSERT INTO issue_reports (schedule_id, driver_id, issue_type, issue_description, location)
                 VALUES ($scheduleId, $driverId, '$issueType', '$issueDescription', '$location')";

    try {
        if ($conn->query($insertSql)) {
            // Successfully reported the issue
            header("Location: dashboard.php?issue=reported");
            exit();
        } else {
            // Failed to insert the report
            header("Location: dashboard.php?error=issue_report_failed");
            exit();
        }
    } catch (Exception $e) {
        error_log("Error reporting issue: " . $e->getMessage());
        header("Location: dashboard.php?error=issue_report_failed");
        exit();
    }
}

// Process cancel trip button click
if (isset($_POST['cancel_trip']) && isset($_POST['schedule_id'])) {
    $scheduleId = $_POST['schedule_id'];
    $cancelReason = isset($_POST['cancel_reason']) ? $_POST['cancel_reason'] : '';

    if (empty($cancelReason)) {
        header("Location: dashboard.php?error=cancel_reason_required");
        exit();
    }

    // First, check if the schedule exists and belongs to this driver
    $checkSql = "SELECT id, status FROM schedules WHERE id = $scheduleId AND driver_id = $driverId";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows == 0) {
        // Schedule doesn't exist or doesn't belong to this driver
        header("Location: dashboard.php?error=invalid_cancel");
        exit();
    }

    // Update schedule status
    $updateSql = "UPDATE schedules SET status = 'cancelled' WHERE id = $scheduleId AND driver_id = $driverId";
    $updateResult = $conn->query($updateSql);

    if ($updateResult) {
        // Check if there's an existing trip report
        $checkReportSql = "SELECT id FROM trip_reports WHERE schedule_id = $scheduleId";
        $checkReportResult = $conn->query($checkReportSql);

        if ($checkReportResult->num_rows > 0) {
            // Update existing trip report
            $updateReportSql = "UPDATE trip_reports SET
                                status = 'cancelled',
                                remarks = CONCAT(remarks, ' | Trip CANCELLED at " . date('h:i A') . ". Reason: " . $cancelReason . "')
                                WHERE schedule_id = $scheduleId";
            try {
                $conn->query($updateReportSql);
                header("Location: dashboard.php?trip=cancelled");
                exit();
            } catch (Exception $e) {
                error_log("Error updating trip report: " . $e->getMessage());
                header("Location: dashboard.php?error=cancel_report_update_failed");
                exit();
            }
        } else {
            // Create new trip report for cancelled trip
            $reportSql = "INSERT INTO trip_reports (schedule_id, actual_departure_time, status, remarks)
                         VALUES ($scheduleId, NOW(), 'cancelled', 'Trip CANCELLED at " . date('h:i A') . ". Reason: $cancelReason')";
            try {
                $conn->query($reportSql);
                header("Location: dashboard.php?trip=cancelled");
                exit();
            } catch (Exception $e) {
                error_log("Error creating trip report: " . $e->getMessage());
                header("Location: dashboard.php?error=cancel_report_create_failed");
                exit();
            }
        }
    } else {
        header("Location: dashboard.php?error=cancel_failed");
        exit();
    }
}
?>

<?php
// Include header
include 'includes/header.php';
?>
<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
    <h1 class="section-title">Driver Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <span class="btn btn-sm btn-outline-primary"><i class="fas fa-user-circle me-2"></i>Welcome, <?php echo $_SESSION['name']; ?></span>
        </div>
    </div>
</div>

<!-- Dashboard Stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-4 mb-md-0">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <h5 class="card-title">Today's Date</h5>
                <h3 class="mb-0"><?php echo date('F d, Y'); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4 mb-md-0">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h5 class="card-title">Current Time</h5>
                <h3 class="mb-0" id="currentTime"><?php echo date('h:i:s A'); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4 mb-md-0">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h5 class="card-title">Time Zone</h5>
                <h3 class="mb-0"><?php echo date_default_timezone_get(); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-server"></i>
                </div>
                <h5 class="card-title">Server Time</h5>
                <h3 class="mb-0"><?php echo date('H:i'); ?></h3>
                <small class="text-muted"><?php echo date('Y-m-d'); ?></small>
            </div>
        </div>
    </div>
</div>

<!-- Time Sync Note -->
<div class="alert alert-info alert-dismissible fade show mb-4 animate__animated animate__fadeIn" role="alert">
    <div class="d-flex align-items-center">
        <div class="flex-shrink-0 me-3">
            <i class="fas fa-info-circle fa-2x"></i>
        </div>
        <div class="flex-grow-1">
            <h5 class="alert-heading">Time Synchronization Information</h5>
            <p class="mb-0">All trip schedules and reports use the server's time zone. If you're experiencing any time display issues, please contact the administrator.</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- Status Alerts -->
<div class="status-alerts">
    <?php if (isset($_GET['departed'])): ?>
        <?php if ($_GET['departed'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="alert-heading">Departure Successful!</h5>
                    <p class="mb-0">You have departed for your trip on time. The admin has been notified.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php elseif ($_GET['departed'] == 'delayed'): ?>
        <div class="alert alert-warning alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="alert-heading">Delayed Departure Recorded</h5>
                    <p class="mb-0">You have reported a delayed departure. The admin has been notified about the delay.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['trip'])): ?>
        <?php if ($_GET['trip'] == 'completed'): ?>
        <div class="alert alert-info alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <i class="fas fa-flag-checkered fa-2x"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="alert-heading">Trip Completed!</h5>
                    <p class="mb-0">You have successfully marked this trip as completed. The admin has been notified.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php elseif ($_GET['trip'] == 'cancelled'): ?>
        <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <i class="fas fa-times-circle fa-2x"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="alert-heading">Trip Cancelled</h5>
                    <p class="mb-0">You have cancelled this trip. The admin has been notified about the cancellation.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['issue']) && $_GET['issue'] == 'reported'): ?>
    <div class="alert alert-warning alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <i class="fas fa-tools fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">Issue Reported!</h5>
                <p class="mb-0">Your issue has been reported successfully. The admin has been notified and will take appropriate action.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
</div>

<!-- Error Alerts -->
<?php if (isset($_GET['error'])): ?>
<div class="error-alerts">
    <?php if ($_GET['error'] == 'cancel_reason_required'): ?>
    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <i class="fas fa-exclamation-circle fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">Reason Required</h5>
                <p class="mb-0">You must provide a reason for cancelling the trip.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php elseif ($_GET['error'] == 'invalid_schedule'): ?>
    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <i class="fas fa-exclamation-circle fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">Invalid Schedule</h5>
                <p class="mb-0">The selected schedule does not exist or does not belong to you.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php elseif ($_GET['error'] == 'invalid_complete'): ?>
    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <i class="fas fa-exclamation-circle fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">Cannot Complete Trip</h5>
                <p class="mb-0">You can only complete trips that are in progress (departed status).</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php elseif ($_GET['error'] == 'invalid_cancel'): ?>
    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <i class="fas fa-exclamation-circle fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">Cannot Cancel Trip</h5>
                <p class="mb-0">The selected trip cannot be cancelled.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php elseif ($_GET['error'] == 'too_early'): ?>
    <div class="alert alert-warning alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <i class="fas fa-clock fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">Trip Cannot Be Completed Yet</h5>
                <p class="mb-0">This route has an estimated time of <strong><?php echo isset($_GET['estimated']) ? $_GET['estimated'] : '?'; ?> minutes</strong>. You need to wait approximately <strong><?php echo isset($_GET['remaining']) ? $_GET['remaining'] : '?'; ?> more minutes</strong> before you can mark this trip as completed.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php elseif ($_GET['error'] == 'trip_report_failed' || $_GET['error'] == 'report_update_failed' || $_GET['error'] == 'report_create_failed' || $_GET['error'] == 'cancel_report_update_failed' || $_GET['error'] == 'cancel_report_create_failed'): ?>
    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <i class="fas fa-database fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">System Error</h5>
                <p class="mb-0">There was a problem updating the trip report. Please try again or contact the administrator.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php elseif ($_GET['error'] == 'update_failed' || $_GET['error'] == 'complete_failed' || $_GET['error'] == 'cancel_failed'): ?>
    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <i class="fas fa-database fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">System Error</h5>
                <p class="mb-0">There was a problem updating the trip status. Please try again or contact the administrator.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php elseif ($_GET['error'] == 'issue_fields_required'): ?>
    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <i class="fas fa-exclamation-circle fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">Missing Information</h5>
                <p class="mb-0">Please select an issue type and provide a description.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php elseif ($_GET['error'] == 'invalid_issue_report'): ?>
    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <i class="fas fa-exclamation-circle fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">Cannot Report Issue</h5>
                <p class="mb-0">You can only report issues for trips that are currently in progress.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php elseif ($_GET['error'] == 'issue_report_failed'): ?>
    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <i class="fas fa-database fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">System Error</h5>
                <p class="mb-0">There was a problem submitting your issue report. Please try again or contact the administrator.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Today's Schedule -->
<div class="card animate__animated animate__fadeIn">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-calendar-day me-2 text-primary"></i>Today's Schedule</h5>
        <span class="badge bg-primary"><?php echo date('F d, Y'); ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                        <th><i class="fas fa-bus me-2"></i>Bus</th>
                        <th><i class="fas fa-route me-2"></i>Route</th>
                        <th><i class="fas fa-clock me-2"></i>Departure</th>
                        <th><i class="fas fa-info-circle me-2"></i>Status</th>
                        <th><i class="fas fa-cogs me-2"></i>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get today's schedules for the driver
                    $sql = "SELECT s.id, b.bus_number, r.route_name, r.start_location, r.end_location,
                            s.departure_time, s.status
                            FROM schedules s
                            JOIN buses b ON s.bus_id = b.id
                            JOIN routes r ON s.route_id = r.id
                            WHERE s.driver_id = $driverId AND s.departure_date = CURDATE()
                            ORDER BY s.departure_time ASC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row['id'] . "</td>";
                                            echo "<td>" . $row['bus_number'] . "</td>";
                                            echo "<td>" . $row['route_name'] . " (" . $row['start_location'] . " to " . $row['end_location'] . ")</td>";
                                            echo "<td>" . date('h:i A', strtotime($row['departure_time'])) . "</td>";

                                            // Status badge
                                            $statusClass = '';
                                            switch ($row['status']) {
                                                case 'scheduled':
                                                    $statusClass = 'bg-primary';
                                                    break;
                                                case 'departed':
                                                    $statusClass = 'bg-success';
                                                    break;
                                                case 'completed':
                                                    $statusClass = 'bg-info';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'bg-danger';
                                                    break;
                                            }

                                            echo "<td><span class='badge " . $statusClass . "'>" . ucfirst($row['status']) . "</span></td>";

                                            // Action button (Departure)
                                            echo "<td>";
                                            if ($row['status'] == 'scheduled') {
                                                // Show scheduled departure time
                                                $scheduledTime = $row['departure_time'];
                                                echo "<div class='small text-muted mb-2'>";
                                                echo "Scheduled: " . date('h:i A', strtotime($scheduledTime));
                                                echo "</div>";

                                                // Departure button with modal trigger
                                                echo "<button type='button' class='btn btn-success btn-sm departure-btn' data-bs-toggle='modal' data-bs-target='#departModal" . $row['id'] . "'>";
                                                echo "Depart Now";
                                                echo "</button>";

                                                // Departure Modal with On-Time/Delayed options
                                                echo "<div class='modal fade' id='departModal" . $row['id'] . "' tabindex='-1' aria-labelledby='departModalLabel" . $row['id'] . "' aria-hidden='true'>";
                                                echo "<div class='modal-dialog modal-dialog-centered'>";
                                                echo "<div class='modal-content'>";
                                                echo "<div class='modal-header bg-success text-white'>";
                                                echo "<h5 class='modal-title' id='departModalLabel" . $row['id'] . "'><i class='fas fa-bus me-2'></i>Departure Options</h5>";
                                                echo "<button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>";
                                                echo "</div>";
                                                echo "<form method='post' action=''>";
                                                echo "<div class='modal-body'>";
                                                echo "<input type='hidden' name='schedule_id' value='" . $row['id'] . "'>";

                                                // Get route info
                                                $routeInfoSql = "SELECT r.route_name, r.start_location, r.end_location, b.bus_number, s.departure_time
                                                               FROM schedules s
                                                               JOIN routes r ON s.route_id = r.id
                                                               JOIN buses b ON s.bus_id = b.id
                                                               WHERE s.id = " . $row['id'];
                                                $routeInfoResult = $conn->query($routeInfoSql);

                                                if ($routeInfoResult && $routeInfoResult->num_rows > 0) {
                                                    $routeInfo = $routeInfoResult->fetch_assoc();
                                                    $routeName = $routeInfo['route_name'];
                                                    $startLocation = $routeInfo['start_location'];
                                                    $endLocation = $routeInfo['end_location'];
                                                    $busNumber = $routeInfo['bus_number'];
                                                    $departureTime = date('h:i A', strtotime($routeInfo['departure_time']));

                                                    echo "<div class='trip-info mb-4'>";
                                                    echo "<h6 class='text-muted mb-3'>Trip Information</h6>";
                                                    echo "<div class='info-card'>";
                                                    echo "<div class='info-item'><i class='fas fa-route text-primary me-2'></i><strong>Route:</strong> $routeName</div>";
                                                    echo "<div class='info-item'><i class='fas fa-map-marker-alt text-danger me-2'></i><strong>From:</strong> $startLocation</div>";
                                                    echo "<div class='info-item'><i class='fas fa-map-marker-alt text-success me-2'></i><strong>To:</strong> $endLocation</div>";
                                                    echo "<div class='info-item'><i class='fas fa-bus text-primary me-2'></i><strong>Bus:</strong> $busNumber</div>";
                                                    echo "<div class='info-item'><i class='fas fa-clock text-primary me-2'></i><strong>Scheduled Departure:</strong> $departureTime</div>";
                                                    echo "</div>";
                                                    echo "</div>";
                                                }

                                                echo "<div class='mb-3'>";
                                                echo "<label class='form-label'><i class='fas fa-info-circle me-2 text-primary'></i>Departure Status</label>";
                                                echo "<div class='form-check mb-2'>";
                                                echo "<input class='form-check-input' type='radio' name='depart_type' id='onTimeRadio" . $row['id'] . "' value='on_time' checked>";
                                                echo "<label class='form-check-label' for='onTimeRadio" . $row['id'] . "'><i class='fas fa-check-circle text-success me-1'></i>On Time</label>";
                                                echo "</div>";
                                                echo "<div class='form-check'>";
                                                echo "<input class='form-check-input' type='radio' name='depart_type' id='delayedRadio" . $row['id'] . "' value='delayed'>";
                                                echo "<label class='form-check-label' for='delayedRadio" . $row['id'] . "'><i class='fas fa-clock text-warning me-1'></i>Delayed</label>";
                                                echo "</div>";
                                                echo "</div>";
                                                echo "<div class='mb-3 delay-reason-container" . $row['id'] . "' style='display:none;'>";
                                                echo "<label for='delay_reason" . $row['id'] . "' class='form-label'><i class='fas fa-comment-alt me-2 text-primary'></i>Reason for Delay</label>";
                                                echo "<div class='input-group'>";
                                                echo "<span class='input-group-text'><i class='fas fa-edit'></i></span>";
                                                echo "<textarea class='form-control' id='delay_reason" . $row['id'] . "' name='delay_reason' rows='3' placeholder='Please explain the reason for delay'></textarea>";
                                                echo "</div>";
                                                echo "</div>";
                                                echo "</div>";
                                                echo "<div class='modal-footer'>";
                                                echo "<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'><i class='fas fa-times me-2'></i>Cancel</button>";
                                                echo "<button type='submit' name='departure' class='btn btn-success'><i class='fas fa-bus me-2'></i>Confirm Departure</button>";
                                                echo "</div>";
                                                echo "</form>";
                                                echo "</div>";
                                                echo "</div>";
                                                echo "</div>";

                                                // JavaScript to show/hide delay reason
                                                echo "<script>";
                                                echo "document.addEventListener('DOMContentLoaded', function() {";
                                                echo "  const delayedRadio" . $row['id'] . " = document.getElementById('delayedRadio" . $row['id'] . "');";
                                                echo "  const onTimeRadio" . $row['id'] . " = document.getElementById('onTimeRadio" . $row['id'] . "');";
                                                echo "  const delayReasonContainer" . $row['id'] . " = document.querySelector('.delay-reason-container" . $row['id'] . "');";
                                                echo "  delayedRadio" . $row['id'] . ".addEventListener('change', function() {";
                                                echo "    if(this.checked) {";
                                                echo "      delayReasonContainer" . $row['id'] . ".style.display = 'block';";
                                                echo "    }";
                                                echo "  });";
                                                echo "  onTimeRadio" . $row['id'] . ".addEventListener('change', function() {";
                                                echo "    if(this.checked) {";
                                                echo "      delayReasonContainer" . $row['id'] . ".style.display = 'none';";
                                                echo "    }";
                                                echo "  });";
                                                echo "});";
                                                echo "</script>";
                                            } else if ($row['status'] == 'departed') {
                                                // Get route estimated time and calculate expected completion time
                                                $routeInfoSql = "SELECT r.estimated_time, tr.actual_departure_time
                                                               FROM schedules s
                                                               JOIN routes r ON s.route_id = r.id
                                                               LEFT JOIN trip_reports tr ON s.id = tr.schedule_id
                                                               WHERE s.id = " . $row['id'];
                                                $routeInfoResult = $conn->query($routeInfoSql);

                                                if ($routeInfoResult && $routeInfoResult->num_rows > 0) {
                                                    $routeInfo = $routeInfoResult->fetch_assoc();
                                                    $estimatedMinutes = $routeInfo['estimated_time'];
                                                    $departureTime = $routeInfo['actual_departure_time'];

                                                    if ($departureTime) {
                                                        $departureTimestamp = strtotime($departureTime);
                                                        $expectedCompletionTimestamp = $departureTimestamp + ($estimatedMinutes * 60);
                                                        $expectedCompletionTime = date('h:i A', $expectedCompletionTimestamp);
                                                        $currentTimestamp = time();
                                                        $remainingMinutes = max(0, ceil(($expectedCompletionTimestamp - $currentTimestamp) / 60));

                                                        echo "<div class='small text-success mb-2'>Trip in progress</div>";
                                                        echo "<div class='small text-muted mb-2'>";
                                                        echo "<strong>Estimated completion:</strong> $expectedCompletionTime<br>";
                                                        echo "<strong>Remaining time:</strong> ~$remainingMinutes minutes";
                                                        echo "</div>";
                                                    } else {
                                                        echo "<div class='small text-success mb-2'>Trip in progress</div>";
                                                    }
                                                } else {
                                                    echo "<div class='small text-success mb-2'>Trip in progress</div>";
                                                }

                                                // Add direct action buttons with forms
                                                echo "<div class='btn-group'>";

                                                // Complete Trip button (direct form)
                                                echo "<form method='post' action='' class='me-1'>";
                                                echo "<input type='hidden' name='schedule_id' value='" . $row['id'] . "'>";
                                                echo "<button type='submit' name='complete_trip' class='btn btn-info btn-sm'>";
                                                echo "<i class='fas fa-check-circle'></i> Complete";
                                                echo "</button>";
                                                echo "</form>";

                                                // Report Issue button with modal trigger
                                                echo "<button type='button' class='btn btn-warning btn-sm report-issue-btn' data-bs-toggle='modal' data-bs-target='#reportIssueModal" . $row['id'] . "' style='border-radius: 18px;'>";
                                                echo "<i class='fas fa-exclamation-triangle'></i> Report Issue";
                                                echo "</button>";

                                                // Report Issue Modal
                                                $modalId = "reportIssueModal" . $row['id'];
                                                $modalLabel = "reportIssueModalLabel" . $row['id'];
                                                ?>
                                                <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalLabel ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-warning text-white">
                                                                <h5 class="modal-title" id="<?= $modalLabel ?>"><i class="fas fa-exclamation-triangle me-2"></i>Report Issue</h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="post" action="">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="schedule_id" value="<?= $row['id'] ?>">
                                                                    
                                                                    <?php
                                                                    // Get trip info for context
                                                                    $tripInfoSql = "SELECT r.route_name, r.start_location, r.end_location, b.bus_number, s.departure_time
                                                                                  FROM schedules s
                                                                                  JOIN routes r ON s.route_id = r.id
                                                                                  JOIN buses b ON s.bus_id = b.id
                                                                                  WHERE s.id = " . $row['id'];
                                                                    $tripInfoResult = $conn->query($tripInfoSql);

                                                                    if ($tripInfoResult && $tripInfoResult->num_rows > 0):
                                                                        $tripInfo = $tripInfoResult->fetch_assoc();
                                                                        $departureTime = date('h:i A', strtotime($tripInfo['departure_time']));
                                                                    ?>
                                                                    <div class="trip-info mb-4">
                                                                        <h6 class="text-muted mb-3">Current Trip Information</h6>
                                                                        <div class="info-card">
                                                                            <div class="info-item"><i class="fas fa-route text-primary me-2"></i><strong>Route:</strong> <?= $tripInfo['route_name'] ?></div>
                                                                            <div class="info-item"><i class="fas fa-map-marker-alt text-danger me-2"></i><strong>From:</strong> <?= $tripInfo['start_location'] ?></div>
                                                                            <div class="info-item"><i class="fas fa-map-marker-alt text-success me-2"></i><strong>To:</strong> <?= $tripInfo['end_location'] ?></div>
                                                                            <div class="info-item"><i class="fas fa-bus text-primary me-2"></i><strong>Bus:</strong> <?= $tripInfo['bus_number'] ?></div>
                                                                            <div class="info-item"><i class="fas fa-clock text-primary me-2"></i><strong>Departure Time:</strong> <?= $departureTime ?></div>
                                                                        </div>
                                                                    </div>
                                                                    <?php endif; ?>

                                                                    <div class="mb-3">
                                                                        <label class="form-label"><i class="fas fa-tag me-2 text-primary"></i>Issue Type</label>
                                                                        <select class="form-select" name="issue_type" required>
                                                                            <option value="">Select Issue Type</option>
                                                                            <option value="tyre_puncture">Tyre Puncture</option>
                                                                            <option value="mechanical">Mechanical Issue</option>
                                                                            <option value="accident">Accident</option>
                                                                            <option value="passenger">Passenger Issue</option>
                                                                            <option value="traffic">Traffic Delay</option>
                                                                            <option value="weather">Weather Condition</option>
                                                                            <option value="other">Other</option>
                                                                        </select>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Location</label>
                                                                        <div class="input-group">
                                                                            <span class="input-group-text"><i class="fas fa-map-pin"></i></span>
                                                                            <input type="text" class="form-control" name="location" placeholder="Enter current location" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label"><i class="fas fa-comment-alt me-2 text-primary"></i>Issue Description</label>
                                                                        <div class="input-group">
                                                                            <span class="input-group-text"><i class="fas fa-edit"></i></span>
                                                                            <textarea class="form-control" name="issue_description" rows="3" placeholder="Please describe the issue in detail" required></textarea>
                                                                        </div>
                                                                    </div>

                                                                    <div class="alert alert-info">
                                                                        <i class="fas fa-info-circle me-2"></i> For emergencies, please contact:
                                                                        <ul class="mb-0 mt-2">
                                                                            <li><i class="fas fa-phone-alt me-2"></i> Emergency: 911</li>
                                                                            <li><i class="fas fa-tools me-2"></i> Roadside Assistance: 1800-123-4567</li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Cancel</button>
                                                                    <button type="submit" name="report_issue" class="btn btn-warning"><i class="fas fa-exclamation-triangle me-2"></i>Submit Report</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    const modal = document.querySelector('#<?= $modalId ?>');
                                                    const issueTypeSelect = modal.querySelector('select[name="issue_type"]');
                                                    const descriptionTextarea = modal.querySelector('textarea[name="issue_description"]');
                                                    
                                                    const placeholders = {
                                                        tyre_puncture: 'Please describe the tyre condition and location of puncture...',
                                                        mechanical: 'Please describe the mechanical issue and any warning lights...',
                                                        accident: 'Please describe the accident details, any injuries, and vehicle damage...',
                                                        passenger: 'Please describe the passenger issue and any immediate concerns...',
                                                        traffic: 'Please describe the traffic situation and estimated delay...',
                                                        weather: 'Please describe the weather conditions and their impact...'
                                                    };

                                                    issueTypeSelect.addEventListener('change', function() {
                                                        descriptionTextarea.placeholder = placeholders[this.value] || 'Please describe the issue in detail...';
                                                    });
                                                });
                                                </script>
                                                <?php

                                                // Cancel Trip button (direct form)
                                                echo "<form method='post' action=''>";
                                                echo "<input type='hidden' name='schedule_id' value='" . $row['id'] . "'>";
                                                echo "<input type='hidden' name='cancel_reason' value='Trip cancelled from dashboard'>";
                                                echo "<button type='submit' name='cancel_trip' class='btn btn-danger btn-sm'>";
                                                echo "<i class='fas fa-times-circle'></i> Cancel";
                                                echo "</button>";
                                                echo "</form>";

                                                echo "</div>";

                                            } else if ($row['status'] == 'completed') {
                                                echo "<span class='text-info'><i class='fas fa-check-circle'></i> Trip completed</span>";
                                            } else if ($row['status'] == 'cancelled') {
                                                echo "<span class='text-danger'><i class='fas fa-times-circle'></i> Trip cancelled</span>";
                                            } else {
                                                echo "<span class='text-muted'>No action needed</span>";
                                            }
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No schedules for today</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Upcoming Schedules -->
<div class="card mt-4 animate__animated animate__fadeIn" style="animation-delay: 0.2s;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i>Upcoming Schedules</h5>
        <button class="btn btn-sm btn-outline-primary" onclick="window.location.href='schedule.php'"><i class="fas fa-eye me-1"></i> View All</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                        <th><i class="fas fa-bus me-2"></i>Bus</th>
                        <th><i class="fas fa-route me-2"></i>Route</th>
                        <th><i class="fas fa-calendar me-2"></i>Date</th>
                        <th><i class="fas fa-clock me-2"></i>Departure</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get upcoming schedules for the driver
                    $sql = "SELECT s.id, b.bus_number, r.route_name, r.start_location, r.end_location,
                            s.departure_date, s.departure_time
                            FROM schedules s
                            JOIN buses b ON s.bus_id = b.id
                            JOIN routes r ON s.route_id = r.id
                            WHERE s.driver_id = $driverId AND
                                  (s.departure_date > CURDATE() OR
                                  (s.departure_date = CURDATE() AND s.departure_time > '$currentTime'))
                            ORDER BY s.departure_date ASC, s.departure_time ASC
                            LIMIT 5";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['bus_number'] . "</td>";
                            echo "<td>" . $row['route_name'] . " (" . $row['start_location'] . " to " . $row['end_location'] . ")</td>";
                            echo "<td>" . date('F d, Y', strtotime($row['departure_date'])) . "</td>";
                            echo "<td>" . date('h:i A', strtotime($row['departure_time'])) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No upcoming schedules</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Trips -->
<div class="card mt-4 animate__animated animate__fadeIn" style="animation-delay: 0.3s;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Recent Trips</h5>
        <button class="btn btn-sm btn-outline-primary" onclick="window.location.href='trips.php'"><i class="fas fa-list me-1"></i> View All Trips</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                        <th><i class="fas fa-bus me-2"></i>Bus</th>
                        <th><i class="fas fa-route me-2"></i>Route</th>
                        <th><i class="fas fa-calendar me-2"></i>Date</th>
                        <th><i class="fas fa-info-circle me-2"></i>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get recent trips for the driver
                    $sql = "SELECT s.id, b.bus_number, r.route_name, r.start_location, r.end_location,
                            s.departure_date, s.status
                            FROM schedules s
                            JOIN buses b ON s.bus_id = b.id
                            JOIN routes r ON s.route_id = r.id
                            WHERE s.driver_id = $driverId AND
                                  (s.status = 'completed' OR s.status = 'cancelled')
                            ORDER BY s.departure_date DESC, s.departure_time DESC
                            LIMIT 5";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['bus_number'] . "</td>";
                            echo "<td>" . $row['route_name'] . " (" . $row['start_location'] . " to " . $row['end_location'] . ")</td>";
                            echo "<td>" . date('F d, Y', strtotime($row['departure_date'])) . "</td>";

                            // Status badge
                            $statusClass = '';
                            switch ($row['status']) {
                                case 'completed':
                                    $statusClass = 'bg-info';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'bg-danger';
                                    break;
                            }

                            echo "<td><span class='badge " . $statusClass . "'>" . ucfirst($row['status']) . "</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center py-4'>
                            <div class='d-flex flex-column align-items-center'>
                                <i class='fas fa-route text-muted mb-3' style='font-size: 2.5rem;'></i>
                                <p class='mb-0'>No recent trips found</p>
                            </div>
                        </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Driver Stats -->
<div class="row mt-4 mb-4">
    <div class="col-md-4 mb-4 mb-md-0">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5 class="card-title">Completed Trips</h5>
                <?php
                $completedSql = "SELECT COUNT(*) as count FROM schedules WHERE driver_id = $driverId AND status = 'completed'";
                $completedResult = $conn->query($completedSql);
                $completedCount = ($completedResult && $completedResult->num_rows > 0) ? $completedResult->fetch_assoc()['count'] : 0;
                ?>
                <h3 class="mb-0"><?php echo $completedCount; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4 mb-md-0">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.6s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h5 class="card-title">This Month's Trips</h5>
                <?php
                $monthSql = "SELECT COUNT(*) as count FROM schedules
                            WHERE driver_id = $driverId
                            AND MONTH(departure_date) = MONTH(CURRENT_DATE())
                            AND YEAR(departure_date) = YEAR(CURRENT_DATE())";
                $monthResult = $conn->query($monthSql);
                $monthCount = ($monthResult && $monthResult->num_rows > 0) ? $monthResult->fetch_assoc()['count'] : 0;
                ?>
                <h3 class="mb-0"><?php echo $monthCount; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.7s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-road"></i>
                </div>
                <h5 class="card-title">Upcoming Trips</h5>
                <?php
                $upcomingSql = "SELECT COUNT(*) as count FROM schedules
                                WHERE driver_id = $driverId
                                AND (departure_date > CURDATE()
                                OR (departure_date = CURDATE() AND departure_time > '$currentTime'))
                                AND status = 'scheduled'";
                $upcomingResult = $conn->query($upcomingSql);
                $upcomingCount = ($upcomingResult && $upcomingResult->num_rows > 0) ? $upcomingResult->fetch_assoc()['count'] : 0;
                ?>
                <h3 class="mb-0"><?php echo $upcomingCount; ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Direct Form Styles -->
<style>
    /* Direct Form Styles */
    .depart-form {
        display: inline;
    }

    .btn-group form {
        margin-right: 5px;
    }

    .btn-group form:last-child {
        margin-right: 0;
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

<!-- Direct form submission script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Direct form submission script loaded');

    // Clean up any modal artifacts that might be left over
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
