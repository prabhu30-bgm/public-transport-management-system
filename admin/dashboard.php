<?php
// Security Headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

require_once '../auth/session.php';
require_once '../config/database.php';
require_once '../includes/validation.php';

// Session Validation
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: login.php");
    exit();
}

// Page title
$page_title = 'Dashboard';

// Dashboard Counts
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM drivers");
$stmt->execute();
$driverCount = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM buses");
$stmt->execute();
$busCount = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM routes");
$stmt->execute();
$routeCount = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM schedules");
$stmt->execute();
$scheduleCount = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM schedules WHERE status='departed'");
$stmt->execute();
$activeTrips = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings");
$stmt->execute();
$bookingCount = $stmt->get_result()->fetch_assoc()['count'];

// Trip Status Counts
$statusCounts = [];

$stmt = $conn->prepare("
    SELECT status, COUNT(*) as count 
    FROM trip_reports 
    GROUP BY status
");

$stmt->execute();

$statusResult = $stmt->get_result();

while ($row = $statusResult->fetch_assoc()) {
    $statusCounts[$row['status']] = $row['count'];
}

// Trip Analytics
$tripsByDay = [];
$dayLabels = [];

$stmt = $conn->prepare("
SELECT DATE(departure_date) as date, COUNT(*) as count
FROM schedules
WHERE departure_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
GROUP BY DATE(departure_date)
ORDER BY date ASC
");

$stmt->execute();

$result = $stmt->get_result();

$tripData = [];

while ($row = $result->fetch_assoc()) {
    $tripData[$row['date']] = $row['count'];
}

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));

    $dayLabels[] = date('D', strtotime("-$i days"));

    $tripsByDay[] = isset($tripData[$date]) ? $tripData[$date] : 0;
}

// Trip Report Status
$issueTypes = [];

$stmt = $conn->prepare("
SELECT status, COUNT(*) as count 
FROM trip_reports 
GROUP BY status
");

$stmt->execute();

$issueResult = $stmt->get_result();

while ($row = $issueResult->fetch_assoc()) {
    $issueTypes[$row['status']] = $row['count'];
}

// Statistics
$bookingStats = [
    'total' => 0,
    'confirmed' => 0,
    'cancelled' => 0,
    'completed' => 0,
    'today' => 0,
    'revenue' => 0
];

$statsSql = "
SELECT
COUNT(*) as total,
SUM(CASE WHEN status='scheduled' THEN 1 ELSE 0 END) as confirmed,
SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled,
SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
SUM(CASE WHEN departure_date=CURDATE() THEN 1 ELSE 0 END) as today
FROM schedules
";

$statsResult = $conn->query($statsSql);

if ($statsResult && $statsResult->num_rows > 0) {
    $bookingStats = $statsResult->fetch_assoc();
}

// Recent Schedules
$recentBookings = [];

$recentSchedulesSql = "
SELECT
s.id,
r.route_name,
s.departure_date,
s.departure_time,
s.status,
b.bus_number,
d.name as driver_name
FROM schedules s
JOIN buses b ON s.bus_id = b.id
JOIN drivers d ON s.driver_id = d.id
JOIN routes r ON s.route_id = r.id
ORDER BY s.created_at DESC
LIMIT 5
";

$recentSchedulesResult = $conn->query($recentSchedulesSql);

if ($recentSchedulesResult && $recentSchedulesResult->num_rows > 0) {
    while ($row = $recentSchedulesResult->fetch_assoc()) {
        $recentBookings[] = $row;
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>

    <div class="row">

        <div class="col-xl col-md-4 col-sm-6 mb-4">
            <div class="card shadow border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-xs font-weight-bold text-primary text-uppercase mb-1">Drivers</h6>
                            <h3 class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $driverCount; ?></h3>
                        </div>
                        <div class="text-primary opacity-5"><i class="fas fa-user fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-4">
            <div class="card shadow border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-xs font-weight-bold text-success text-uppercase mb-1">Buses</h6>
                            <h3 class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $busCount; ?></h3>
                        </div>
                        <div class="text-success opacity-5"><i class="fas fa-bus fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-4">
            <div class="card shadow border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-xs font-weight-bold text-info text-uppercase mb-1">Routes</h6>
                            <h3 class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $routeCount; ?></h3>
                        </div>
                        <div class="text-info opacity-5"><i class="fas fa-route fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-4">
            <div class="card shadow border-left-danger h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Bookings</h6>
                            <h3 class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $bookingCount; ?></h3>
                        </div>
                        <div class="text-danger opacity-5"><i class="fas fa-ticket-alt fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-4">
            <div class="card shadow border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-xs font-weight-bold text-warning text-uppercase mb-1">Active Trips</h6>
                            <h3 class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $activeTrips; ?></h3>
                        </div>
                        <div class="text-warning opacity-5"><i class="fas fa-road fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Schedules -->
    <div class="card shadow mb-4">

        <div class="card-header">
            <h5 class="mb-0">Recent Schedules</h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Driver</th>
                            <th>Bus</th>
                            <th>Route</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!empty($recentBookings)): ?>

                        <?php foreach ($recentBookings as $booking): ?>

                            <tr>

                                <td><?php echo $booking['id']; ?></td>

                                <td><?php echo $booking['driver_name']; ?></td>

                                <td><?php echo $booking['bus_number']; ?></td>

                                <td><?php echo $booking['route_name']; ?></td>

                                <td>
                                    <?php echo date('d M Y', strtotime($booking['departure_date'])); ?>
                                </td>

                                <td>
                                    <?php echo date('h:i A', strtotime($booking['departure_time'])); ?>
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="text-center">
                                No schedules found
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- Recent Trip Reports -->
    <div class="card shadow mb-4">

        <div class="card-header">
            <h5 class="mb-0">Recent Trip Reports</h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Driver</th>
                            <th>Bus</th>
                            <th>Route</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php

                    $issuesSql = "
                    SELECT tr.*, d.name as driver_name, 
                    b.bus_number, r.route_name
                    FROM trip_reports tr
                    JOIN schedules s ON tr.schedule_id = s.id
                    JOIN drivers d ON s.driver_id = d.id
                    JOIN buses b ON s.bus_id = b.id
                    JOIN routes r ON s.route_id = r.id
                    ORDER BY tr.created_at DESC
                    LIMIT 5
                    ";

                    $issuesResult = $conn->query($issuesSql);

                    if ($issuesResult && $issuesResult->num_rows > 0):

                        while ($issue = $issuesResult->fetch_assoc()):

                    ?>

                        <tr>

                            <td><?php echo $issue['id']; ?></td>

                            <td><?php echo $issue['driver_name']; ?></td>

                            <td><?php echo $issue['bus_number']; ?></td>

                            <td><?php echo $issue['route_name']; ?></td>

                            <td>
                                <span class="badge bg-info">
                                    <?php echo ucfirst($issue['status']); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo date('d M Y h:i A', strtotime($issue['created_at'])); ?>
                            </td>

                        </tr>

                    <?php
                        endwhile;
                    else:
                    ?>

                        <tr>
                            <td colspan="6" class="text-center">
                                No trip reports found
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>