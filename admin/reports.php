<?php
require_once '../auth/session.php';
require_once '../config/database.php';
requireAdmin();

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$driverId = isset($_GET['driver_id']) ? $_GET['driver_id'] : 'all';
$routeId = isset($_GET['route_id']) ? $_GET['route_id'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get all drivers for filter dropdown
$driversSql = "SELECT * FROM drivers ORDER BY name ASC";
$driversResult = $conn->query($driversSql);
$drivers = [];

if ($driversResult->num_rows > 0) {
    while ($row = $driversResult->fetch_assoc()) {
        $drivers[] = $row;
    }
}

// Get all routes for filter dropdown
$routesSql = "SELECT * FROM routes ORDER BY route_name ASC";
$routesResult = $conn->query($routesSql);
$routes = [];

if ($routesResult->num_rows > 0) {
    while ($row = $routesResult->fetch_assoc()) {
        $routes[] = $row;
    }
}

// Build the query for trip reports
$reportsSql = "SELECT tr.*, s.departure_time as scheduled_time, s.departure_date, s.status as schedule_status,
              b.bus_number, d.name as driver_name, r.route_name, r.start_location, r.end_location,
              tr.passenger_count, tr.revenue
              FROM trip_reports tr
              JOIN schedules s ON tr.schedule_id = s.id
              JOIN buses b ON s.bus_id = b.id
              JOIN drivers d ON s.driver_id = d.id
              JOIN routes r ON s.route_id = r.id
              WHERE s.departure_date BETWEEN '$startDate' AND '$endDate'";

if ($driverId != 'all') {
    $reportsSql .= " AND s.driver_id = $driverId";
}

if ($routeId != 'all') {
    $reportsSql .= " AND s.route_id = $routeId";
}

if ($status != 'all') {
    $reportsSql .= " AND s.status = '$status'";
}

$reportsSql .= " ORDER BY s.departure_date DESC, s.departure_time DESC";
$reportsResult = $conn->query($reportsSql);
$reports = [];

if ($reportsResult->num_rows > 0) {
    while ($row = $reportsResult->fetch_assoc()) {
        $reports[] = $row;
    }
}

$page_title = "Trip Reports";
include 'includes/header.php';
?>

<!-- Page Content -->
<div class="container-fluid px-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar text-primary me-2"></i> Trip Reports
        </h1>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="" class="row g-3">
                <div class="col-md-2">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                <div class="col-md-2">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                <div class="col-md-2">
                    <label for="driver_id" class="form-label">Driver</label>
                    <select class="form-select" id="driver_id" name="driver_id">
                        <option value="all" <?php echo $driverId == 'all' ? 'selected' : ''; ?>>All Drivers</option>
                        <?php foreach ($drivers as $driver): ?>
                            <option value="<?php echo $driver['id']; ?>" <?php echo $driverId == $driver['id'] ? 'selected' : ''; ?>>
                                <?php echo $driver['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="route_id" class="form-label">Route</label>
                    <select class="form-select" id="route_id" name="route_id">
                        <option value="all" <?php echo $routeId == 'all' ? 'selected' : ''; ?>>All Routes</option>
                        <?php foreach ($routes as $route): ?>
                            <option value="<?php echo $route['id']; ?>" <?php echo $routeId == $route['id'] ? 'selected' : ''; ?>>
                                <?php echo $route['route_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="scheduled" <?php echo $status == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="departed" <?php echo $status == 'departed' ? 'selected' : ''; ?>>Departed</option>
                        <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="reports.php" class="btn btn-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card">
        <div class="card-header">
            <h5>Trip Reports</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Bus</th>
                            <th>Driver</th>
                            <th>Route</th>
                            <th>Status</th>
                            <th>Passengers</th>
                            <th>Revenue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($reports) > 0): ?>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($report['departure_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($report['scheduled_time'])); ?></td>
                                    <td><?php echo $report['bus_number']; ?></td>
                                    <td><?php echo $report['driver_name']; ?></td>
                                    <td><?php echo $report['route_name']; ?> (<?php echo $report['start_location']; ?> to <?php echo $report['end_location']; ?>)</td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($report['schedule_status']) {
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
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($report['schedule_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $report['passenger_count']; ?></td>
                                    <td>₱<?php echo number_format($report['revenue'], 2); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-action" data-bs-toggle="modal" data-bs-target="#viewReportModal<?php echo $report['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- View Report Modal -->
                                <div class="modal fade" id="viewReportModal<?php echo $report['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Trip Report Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Date:</label>
                                                    <p><?php echo date('F d, Y', strtotime($report['departure_date'])); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Time:</label>
                                                    <p><?php echo date('h:i A', strtotime($report['scheduled_time'])); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Bus:</label>
                                                    <p><?php echo $report['bus_number']; ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Driver:</label>
                                                    <p><?php echo $report['driver_name']; ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Route:</label>
                                                    <p><?php echo $report['route_name']; ?> (<?php echo $report['start_location']; ?> to <?php echo $report['end_location']; ?>)</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Status:</label>
                                                    <p>
                                                        <span class="badge <?php echo $statusClass; ?>">
                                                            <?php echo ucfirst($report['schedule_status']); ?>
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Passengers:</label>
                                                    <p><?php echo $report['passenger_count']; ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Revenue:</label>
                                                    <p>₱<?php echo number_format($report['revenue'], 2); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Notes:</label>
                                                    <p><?php echo nl2br($report['remarks']); ?></p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No reports found for the selected filters</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
