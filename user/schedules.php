<?php
// Include database connection
require_once '../config/database.php';

// Set page title
$page_title = 'Bus Schedules';

// Get filter parameters
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$route_id = isset($_GET['route']) ? intval($_GET['route']) : 0;

// Check if available_seats column exists in schedules table
$checkColumnSql = "SHOW COLUMNS FROM schedules LIKE 'available_seats'";
$checkColumnResult = $conn->query($checkColumnSql);
$availableSeatsExists = ($checkColumnResult && $checkColumnResult->num_rows > 0);

// Build query based on filters
if ($availableSeatsExists) {
    $query = "SELECT s.id, s.departure_date, s.departure_time, s.status, s.available_seats,
              r.route_name, r.start_location, r.end_location, r.fare, r.estimated_time,
              b.bus_number, b.capacity
              FROM schedules s
              JOIN routes r ON s.route_id = r.id
              JOIN buses b ON s.bus_id = b.id
              WHERE s.departure_date >= CURDATE()
              AND s.status = 'scheduled'";
} else {
    // Fallback query without available_seats
    $query = "SELECT s.id, s.departure_date, s.departure_time, s.status, b.capacity as available_seats,
              r.route_name, r.start_location, r.end_location, r.fare, r.estimated_time,
              b.bus_number, b.capacity
              FROM schedules s
              JOIN routes r ON s.route_id = r.id
              JOIN buses b ON s.bus_id = b.id
              WHERE s.departure_date >= CURDATE()
              AND s.status = 'scheduled'";
}

$params = [];
$types = "";

if (!empty($from) && !empty($to)) {
    $query .= " AND r.start_location = ? AND r.end_location = ?";
    $params[] = $from;
    $params[] = $to;
    $types .= "ss";
}

if (!empty($date)) {
    $query .= " AND s.departure_date = ?";
    $params[] = $date;
    $types .= "s";
}

if ($route_id > 0) {
    $query .= " AND r.id = ?";
    $params[] = $route_id;
    $types .= "i";
}

$query .= " ORDER BY s.departure_date, s.departure_time";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
}
$stmt->close();

// Get all routes for filter
$routesSql = "SELECT id, route_name, start_location, end_location FROM routes ORDER BY route_name";
$routesResult = $conn->query($routesSql);
$routes = [];
if ($routesResult->num_rows > 0) {
    while ($row = $routesResult->fetch_assoc()) {
        $routes[] = $row;
    }
}

// Get all locations for filter
$locationsSql = "SELECT DISTINCT start_location FROM routes UNION SELECT DISTINCT end_location FROM routes ORDER BY start_location";
$locationsResult = $conn->query($locationsSql);
$locations = [];
if ($locationsResult->num_rows > 0) {
    while ($row = $locationsResult->fetch_assoc()) {
        $locations[] = $row['start_location'];
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Bus Schedules</h1>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Search Buses</h5>
    </div>
    <div class="card-body">
        <form method="get" action="" class="row g-3">
            <div class="col-md-3">
                <label for="from" class="form-label">From</label>
                <select class="form-select" id="from" name="from">
                    <option value="">All Starting Points</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?php echo $location; ?>" <?php echo $from === $location ? 'selected' : ''; ?>>
                            <?php echo $location; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="to" class="form-label">To</label>
                <select class="form-select" id="to" name="to">
                    <option value="">All Destinations</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?php echo $location; ?>" <?php echo $to === $location ? 'selected' : ''; ?>>
                            <?php echo $location; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date" class="form-label">Travel Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo $date; ?>" min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </form>
    </div>
</div>

<!-- Schedules Display -->
<div class="card">
    <div class="card-header">
        <h5>Available Buses</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($schedules)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Route</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Bus</th>
                            <th>Duration</th>
                            <th>Available Seats</th>
                            <th>Fare</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $schedule['route_name']; ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo $schedule['start_location']; ?> to <?php echo $schedule['end_location']; ?>
                                    </small>
                                </td>
                                <td><?php echo date('D, d M Y', strtotime($schedule['departure_date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($schedule['departure_time'])); ?></td>
                                <td><?php echo $schedule['bus_number']; ?></td>
                                <td><?php echo $schedule['estimated_time']; ?> mins</td>
                                <td>
                                    <?php
                                    $availableSeats = $schedule['available_seats'];
                                    $totalSeats = $schedule['capacity'];
                                    $seatClass = 'text-success';

                                    if ($availableSeats <= 5) {
                                        $seatClass = 'text-danger';
                                    } elseif ($availableSeats <= 10) {
                                        $seatClass = 'text-warning';
                                    }

                                    echo "<span class=\"{$seatClass}\">{$availableSeats}</span> / {$totalSeats}";
                                    ?>
                                </td>
                                <td>₹<?php echo number_format($schedule['fare'], 2); ?></td>
                                <td>
                                    <a href="booking.php?schedule=<?php echo $schedule['id']; ?>" class="btn btn-sm btn-primary">Book Now</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p class="mb-0">No schedules found for the selected criteria. Please try different search parameters.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Popular Routes Section -->
<div class="card mt-4">
    <div class="card-header">
        <h5>Popular Routes</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($routes as $index => $route): ?>
                <?php if ($index < 6): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $route['route_name']; ?></h5>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt text-danger"></i> <?php echo $route['start_location']; ?>
                                    <i class="fas fa-arrow-right mx-2"></i>
                                    <i class="fas fa-map-marker-alt text-success"></i> <?php echo $route['end_location']; ?>
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <a href="schedules.php?route=<?php echo $route['id']; ?>" class="btn btn-outline-primary w-100">View Schedules</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
