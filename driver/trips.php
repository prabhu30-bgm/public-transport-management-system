<?php
require_once '../auth/session.php';
require_once '../config/database.php';
requireDriver();

// Set page title
$page_title = 'My Trips';

// Get driver ID from session
$driverId = $_SESSION['user_id'];

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build the query for trips
$tripsSql = "SELECT s.id, s.departure_date, s.departure_time, s.status,
              b.bus_number, r.route_name, r.start_location, r.end_location,
              tr.actual_departure_time, tr.actual_arrival_time, tr.remarks
              FROM schedules s
              JOIN buses b ON s.bus_id = b.id
              JOIN routes r ON s.route_id = r.id
              LEFT JOIN trip_reports tr ON s.id = tr.schedule_id
              WHERE s.driver_id = $driverId
              AND s.departure_date BETWEEN '$startDate' AND '$endDate'";

if ($status != 'all') {
    $tripsSql .= " AND s.status = '$status'";
}

$tripsSql .= " ORDER BY s.departure_date DESC, s.departure_time DESC";

$tripsResult = $conn->query($tripsSql);

// Include header
include 'includes/header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
    <h1 class="section-title">My Trips</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4 animate__animated animate__fadeIn">
    <div class="card-body">
        <form method="get" action="" class="row g-3">
            <div class="col-md-3">
                <label class="form-label"><i class="fas fa-calendar me-2 text-primary"></i>Start Date</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label"><i class="fas fa-calendar me-2 text-primary"></i>End Date</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label"><i class="fas fa-filter me-2 text-primary"></i>Status</label>
                <select class="form-select" name="status">
                    <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="scheduled" <?php echo $status == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="departed" <?php echo $status == 'departed' ? 'selected' : ''; ?>>Departed</option>
                    <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Trips Table -->
<div class="card animate__animated animate__fadeIn" style="animation-delay: 0.2s;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                        <th><i class="fas fa-calendar me-2"></i>Date</th>
                        <th><i class="fas fa-clock me-2"></i>Time</th>
                        <th><i class="fas fa-bus me-2"></i>Bus</th>
                        <th><i class="fas fa-route me-2"></i>Route</th>
                        <th><i class="fas fa-info-circle me-2"></i>Status</th>
                        <th><i class="fas fa-clock me-2"></i>Actual Time</th>
                        <th><i class="fas fa-comment-alt me-2"></i>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($tripsResult && $tripsResult->num_rows > 0) {
                        while ($row = $tripsResult->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . date('F d, Y', strtotime($row['departure_date'])) . "</td>";
                            echo "<td>" . date('h:i A', strtotime($row['departure_time'])) . "</td>";
                            echo "<td>" . $row['bus_number'] . "</td>";
                            echo "<td>" . $row['route_name'] . "<br><small class='text-muted'>" . $row['start_location'] . " to " . $row['end_location'] . "</small></td>";

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

                            // Actual time
                            echo "<td>";
                            if ($row['actual_departure_time']) {
                                echo "<div class='small'>";
                                echo "<strong>Departure:</strong> " . date('h:i A', strtotime($row['actual_departure_time']));
                                if ($row['actual_arrival_time']) {
                                    echo "<br><strong>Arrival:</strong> " . date('h:i A', strtotime($row['actual_arrival_time']));
                                }
                                echo "</div>";
                            } else {
                                echo "<span class='text-muted'>-</span>";
                            }
                            echo "</td>";

                            // Remarks
                            echo "<td>";
                            if ($row['remarks']) {
                                echo "<div class='small text-muted'>" . htmlspecialchars($row['remarks']) . "</div>";
                            } else {
                                echo "<span class='text-muted'>-</span>";
                            }
                            echo "</td>";

                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center py-4'>
                            <div class='d-flex flex-column align-items-center'>
                                <i class='fas fa-route text-muted mb-3' style='font-size: 2.5rem;'></i>
                                <p class='mb-0'>No trips found for the selected filters</p>
                            </div>
                        </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Trip Statistics -->
<div class="row mt-4">
    <div class="col-md-3 mb-4">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5 class="card-title">Completed Trips</h5>
                <?php
                $completedSql = "SELECT COUNT(*) as count FROM schedules 
                                WHERE driver_id = $driverId 
                                AND status = 'completed'
                                AND departure_date BETWEEN '$startDate' AND '$endDate'";
                $completedResult = $conn->query($completedSql);
                $completedCount = ($completedResult && $completedResult->num_rows > 0) ? $completedResult->fetch_assoc()['count'] : 0;
                ?>
                <h3 class="mb-0"><?php echo $completedCount; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h5 class="card-title">On-Time Departures</h5>
                <?php
                $onTimeSql = "SELECT COUNT(*) as count FROM trip_reports tr
                             JOIN schedules s ON tr.schedule_id = s.id
                             WHERE s.driver_id = $driverId 
                             AND tr.status = 'on_time'
                             AND s.departure_date BETWEEN '$startDate' AND '$endDate'";
                $onTimeResult = $conn->query($onTimeSql);
                $onTimeCount = ($onTimeResult && $onTimeResult->num_rows > 0) ? $onTimeResult->fetch_assoc()['count'] : 0;
                ?>
                <h3 class="mb-0"><?php echo $onTimeCount; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="card-title">Delayed Trips</h5>
                <?php
                $delayedSql = "SELECT COUNT(*) as count FROM trip_reports tr
                              JOIN schedules s ON tr.schedule_id = s.id
                              WHERE s.driver_id = $driverId 
                              AND tr.status = 'delayed'
                              AND s.departure_date BETWEEN '$startDate' AND '$endDate'";
                $delayedResult = $conn->query($delayedSql);
                $delayedCount = ($delayedResult && $delayedResult->num_rows > 0) ? $delayedResult->fetch_assoc()['count'] : 0;
                ?>
                <h3 class="mb-0"><?php echo $delayedCount; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.6s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h5 class="card-title">Cancelled Trips</h5>
                <?php
                $cancelledSql = "SELECT COUNT(*) as count FROM schedules 
                                WHERE driver_id = $driverId 
                                AND status = 'cancelled'
                                AND departure_date BETWEEN '$startDate' AND '$endDate'";
                $cancelledResult = $conn->query($cancelledSql);
                $cancelledCount = ($cancelledResult && $cancelledResult->num_rows > 0) ? $cancelledResult->fetch_assoc()['count'] : 0;
                ?>
                <h3 class="mb-0"><?php echo $cancelledCount; ?></h3>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?> 