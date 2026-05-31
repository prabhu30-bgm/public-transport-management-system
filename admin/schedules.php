<?php
require_once '../auth/session.php';
require_once '../config/database.php';
requireAdmin();

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new schedule
    if (isset($_POST['add_schedule'])) {
        $busId = $_POST['bus_id'];
        $driverId = $_POST['driver_id'];
        $routeId = $_POST['route_id'];
        $departureTime = $_POST['departure_time'];
        $departureDate = $_POST['departure_date'];

        // Check if bus and driver are available at the specified time
        $checkBusSql = "SELECT * FROM schedules WHERE bus_id = $busId AND departure_date = '$departureDate' AND
                        (TIME(departure_time) <= TIME('$departureTime') + INTERVAL 1 HOUR AND
                            TIME(departure_time) >= TIME('$departureTime') - INTERVAL 1 HOUR)";
        $checkBusResult = $conn->query($checkBusSql);

        $checkDriverSql = "SELECT * FROM schedules WHERE driver_id = $driverId AND departure_date = '$departureDate' AND
                            (TIME(departure_time) <= TIME('$departureTime') + INTERVAL 1 HOUR AND
                            TIME(departure_time) >= TIME('$departureTime') - INTERVAL 1 HOUR)";
        $checkDriverResult = $conn->query($checkDriverSql);

        if ($checkBusResult->num_rows > 0) {
            $error = "Bus is already scheduled around this time. Please choose a different time or bus.";
        } else if ($checkDriverResult->num_rows > 0) {
            $error = "Driver is already scheduled around this time. Please choose a different time or driver.";
        } else {
            // Insert new schedule
            $sql = "INSERT INTO schedules (bus_id, driver_id, route_id, departure_time, departure_date)
                    VALUES ($busId, $driverId, $routeId, '$departureTime', '$departureDate')";

            if ($conn->query($sql) === TRUE) {
                $success = "Schedule added successfully.";
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }

    // Update schedule status
    if (isset($_POST['update_status'])) {
        $scheduleId = $_POST['schedule_id'];
        $status = $_POST['status'];

        $sql = "UPDATE schedules SET status = '$status' WHERE id = $scheduleId";

        if ($conn->query($sql) === TRUE) {
            $success = "Schedule status updated successfully.";
        } else {
            $error = "Error updating schedule status: " . $conn->error;
        }
    }

    // Delete schedule
    if (isset($_POST['delete_schedule'])) {
        $scheduleId = $_POST['schedule_id'];

        // Check if schedule has any trip reports
        $checkSql = "SELECT * FROM trip_reports WHERE schedule_id = $scheduleId";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows > 0) {
            $error = "Cannot delete schedule. Schedule has trip reports.";
        } else {
            $sql = "DELETE FROM schedules WHERE id = $scheduleId";

            if ($conn->query($sql) === TRUE) {
                $success = "Schedule deleted successfully.";
            } else {
                $error = "Error deleting schedule: " . $conn->error;
            }
        }
    }
}

// Get all active buses
$busesSql = "SELECT * FROM buses WHERE status = 'active' ORDER BY bus_number ASC";
$busesResult = $conn->query($busesSql);
$buses = [];

if ($busesResult->num_rows > 0) {
    while ($row = $busesResult->fetch_assoc()) {
        $buses[] = $row;
    }
}

// Get all active drivers
$driversSql = "SELECT * FROM drivers WHERE status = 'active' ORDER BY name ASC";
$driversResult = $conn->query($driversSql);
$drivers = [];

if ($driversResult->num_rows > 0) {
    while ($row = $driversResult->fetch_assoc()) {
        $drivers[] = $row;
    }
}

// Get all routes
$routesSql = "SELECT * FROM routes ORDER BY route_name ASC";
$routesResult = $conn->query($routesSql);
$routes = [];

if ($routesResult->num_rows > 0) {
    while ($row = $routesResult->fetch_assoc()) {
        $routes[] = $row;
    }
}

// Get schedules with filter
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$filterStatus = isset($_GET['filter_status']) ? $_GET['filter_status'] : 'all';

$schedulesSql = "SELECT s.*, b.bus_number, d.name as driver_name, r.route_name, r.start_location, r.end_location
                FROM schedules s
                JOIN buses b ON s.bus_id = b.id
                JOIN drivers d ON s.driver_id = d.id
                JOIN routes r ON s.route_id = r.id
                WHERE s.departure_date BETWEEN '$startDate' AND '$endDate'";

if ($filterStatus != 'all') {
    $schedulesSql .= " AND s.status = '$filterStatus'";
}

$schedulesSql .= " ORDER BY s.departure_date ASC, s.departure_time ASC";
$schedulesResult = $conn->query($schedulesSql);
$schedules = [];

if ($schedulesResult->num_rows > 0) {
    while ($row = $schedulesResult->fetch_assoc()) {
        $schedules[] = $row;
    }
}

$page_title = "Manage Schedules";
include 'includes/header.php';
?>

<!-- Page Content -->
<div class="container-fluid px-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-alt text-primary me-2"></i> Manage Schedules
        </h1>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
            <i class="fas fa-plus me-2"></i> Add New Schedule
        </button>
    </div>

    <!-- Alerts -->
    <?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                <div class="col-md-3">
                    <label for="filter_status" class="form-label">Status</label>
                    <select class="form-select" id="filter_status" name="filter_status">
                        <option value="all" <?php echo $filterStatus == 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="scheduled" <?php echo $filterStatus == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="departed" <?php echo $filterStatus == 'departed' ? 'selected' : ''; ?>>Departed</option>
                        <option value="completed" <?php echo $filterStatus == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $filterStatus == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="schedules.php" class="btn btn-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="card">
        <div class="card-header">
            <h5>Schedules from <?php echo date('F d, Y', strtotime($startDate)); ?> to <?php echo date('F d, Y', strtotime($endDate)); ?></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Bus</th>
                            <th>Driver</th>
                            <th>Route</th>
                            <th>Departure Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($schedules) > 0): ?>
                            <?php foreach ($schedules as $schedule): ?>
                                <tr>
                                    <td><?php echo $schedule['id']; ?></td>
                                    <td><?php echo $schedule['bus_number']; ?></td>
                                    <td><?php echo $schedule['driver_name']; ?></td>
                                    <td><?php echo $schedule['route_name']; ?> (<?php echo $schedule['start_location']; ?> to <?php echo $schedule['end_location']; ?>)</td>
                                    <td><?php echo date('h:i A', strtotime($schedule['departure_time'])); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($schedule['status']) {
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
                                            <?php echo ucfirst($schedule['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-warning btn-action" data-bs-toggle="modal" data-bs-target="#editScheduleModal<?php echo $schedule['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-action" data-bs-toggle="modal" data-bs-target="#deleteScheduleModal<?php echo $schedule['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Schedule Modal (Status Change) -->
                                <div class="modal fade" id="editScheduleModal<?php echo $schedule['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Schedule Status</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="post" action="">
                                                <div class="modal-body">
                                                    <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                                    <div class="mb-3">
                                                        <label for="status<?php echo $schedule['id']; ?>" class="form-label">Status</label>
                                                        <select class="form-select" id="status<?php echo $schedule['id']; ?>" name="status" required>
                                                            <option value="scheduled" <?php echo $schedule['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                                            <option value="departed" <?php echo $schedule['status'] == 'departed' ? 'selected' : ''; ?>>Departed</option>
                                                            <option value="completed" <?php echo $schedule['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                            <option value="cancelled" <?php echo $schedule['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Schedule Modal -->
                                <div class="modal fade" id="deleteScheduleModal<?php echo $schedule['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Schedule</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete this schedule?</p>
                                                <p><strong>Bus:</strong> <?php echo $schedule['bus_number']; ?></p>
                                                <p><strong>Driver:</strong> <?php echo $schedule['driver_name']; ?></p>
                                                <p><strong>Route:</strong> <?php echo $schedule['route_name']; ?></p>
                                                <p><strong>Departure:</strong> <?php echo date('F d, Y h:i A', strtotime($schedule['departure_date'] . ' ' . $schedule['departure_time'])); ?></p>
                                                <p class="text-danger">This action cannot be undone. If the schedule has any trip reports, the deletion will fail.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form method="post" action="">
                                                    <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                                    <button type="submit" name="delete_schedule" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No schedules found for the selected date and status</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bus_id" class="form-label">Bus</label>
                        <select class="form-select" id="bus_id" name="bus_id" required>
                            <option value="">Select Bus</option>
                            <?php foreach ($buses as $bus): ?>
                                <option value="<?php echo $bus['id']; ?>"><?php echo $bus['bus_number']; ?> (<?php echo $bus['model']; ?> - <?php echo $bus['capacity']; ?> seats)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="driver_id" class="form-label">Driver</label>
                        <select class="form-select" id="driver_id" name="driver_id" required>
                            <option value="">Select Driver</option>
                            <?php foreach ($drivers as $driver): ?>
                                <option value="<?php echo $driver['id']; ?>"><?php echo $driver['name']; ?> (<?php echo $driver['license_number']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="route_id" class="form-label">Route</label>
                        <select class="form-select" id="route_id" name="route_id" required>
                            <option value="">Select Route</option>
                            <?php foreach ($routes as $route): ?>
                                <option value="<?php echo $route['id']; ?>"><?php echo $route['route_name']; ?> (<?php echo $route['start_location']; ?> to <?php echo $route['end_location']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="departure_date" class="form-label">Departure Date</label>
                        <input type="date" class="form-control" id="departure_date" name="departure_date" min="<?php echo date('Y-m-d', strtotime('tomorrow')); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="departure_time" class="form-label">Departure Time</label>
                        <input type="time" class="form-control" id="departure_time" name="departure_time" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_schedule" class="btn btn-primary">Add Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
