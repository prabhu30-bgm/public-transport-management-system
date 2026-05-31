<?php
require_once '../auth/session.php';
require_once '../config/database.php';
requireDriver();

// Set page title
$page_title = 'My Schedule';

// Get driver ID from session
$driverId = $_SESSION['user_id'];

// Get filter parameters
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Get all schedules for the driver for the selected month
$sql = "SELECT s.*, b.bus_number, r.route_name, r.start_location, r.end_location
        FROM schedules s
        JOIN buses b ON s.bus_id = b.id
        JOIN routes r ON s.route_id = r.id
        WHERE s.driver_id = $driverId
        AND MONTH(s.departure_date) = $month
        AND YEAR(s.departure_date) = $year
        ORDER BY s.departure_date ASC, s.departure_time ASC";
$result = $conn->query($sql);
$schedules = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
}

// Group schedules by date
$schedulesByDate = [];
foreach ($schedules as $schedule) {
    $date = $schedule['departure_date'];
    if (!isset($schedulesByDate[$date])) {
        $schedulesByDate[$date] = [];
    }
    $schedulesByDate[$date][] = $schedule;
}

// Get month name
$monthName = date('F', mktime(0, 0, 0, $month, 1, $year));
?>

<?php
// Include header
include 'includes/header.php';
?>
<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">My Schedule</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-print"></i> Print Schedule
        </button>
    </div>
</div>

<!-- Month Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="" class="row g-3">
            <div class="col-md-4">
                <label for="month" class="form-label">Month</label>
                <select class="form-select" id="month" name="month">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $month == $i ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="year" class="form-label">Year</label>
                <select class="form-select" id="year" name="year">
                    <?php for ($i = date('Y') - 1; $i <= date('Y') + 1; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $year == $i ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">View Schedule</button>
            </div>
        </form>
    </div>
</div>

<!-- Schedule Display -->
<div class="card">
    <div class="card-header">
        <h5>Schedule for <?php echo $monthName . ' ' . $year; ?></h5>
    </div>
    <div class="card-body">
        <?php if (count($schedulesByDate) > 0): ?>
            <?php foreach ($schedulesByDate as $date => $daySchedules): ?>
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">
                        <?php echo date('l, F d, Y', strtotime($date)); ?>
                        <?php if ($date == date('Y-m-d')): ?>
                            <span class="badge bg-primary">Today</span>
                        <?php endif; ?>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Schedule ID</th>
                                    <th>Bus Number</th>
                                    <th>Route</th>
                                    <th>Departure Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($daySchedules as $schedule): ?>
                                    <tr>
                                        <td><?php echo $schedule['id']; ?></td>
                                        <td><?php echo $schedule['bus_number']; ?></td>
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
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <p class="mb-0">No schedules found for <?php echo $monthName . ' ' . $year; ?>.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
