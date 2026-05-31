<?php
require_once '../auth/session.php';
require_once '../config/database.php';
requireAdmin();

// Set page title
$page_title = 'Booking Management';

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build the query for bookings
$bookingsSql = "SELECT b.*, u.name as user_name, u.email, u.phone,
                s.departure_date, s.departure_time,
                r.route_name, r.start_location, r.end_location,
                bs.bus_number
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN schedules s ON b.schedule_id = s.id
                JOIN routes r ON s.route_id = r.id
                JOIN buses bs ON s.bus_id = bs.id
                WHERE s.departure_date BETWEEN '$startDate' AND '$endDate'";

if ($status != 'all') {
    $bookingsSql .= " AND b.status = '$status'";
}

$bookingsSql .= " ORDER BY b.id ASC, s.departure_date DESC, s.departure_time ASC";
$bookingsResult = $conn->query($bookingsSql);
$bookings = [];

if ($bookingsResult->num_rows > 0) {
    while ($row = $bookingsResult->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Booking Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

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
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="confirmed" <?php echo $status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bookings Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Route</th>
                        <th>Journey Date</th>
                        <th>Seats</th>
                        <th>Available Seats</th>
                        <th>Total Seats</th>
                        <th>Total Fare</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td>
                                    <?php echo $booking['user_name']; ?><br>
                                    <small class="text-muted">
                                        <?php echo $booking['email']; ?><br>
                                        <?php echo $booking['phone']; ?>
                                    </small>
                                </td>
                                <td>
                                    <?php echo $booking['route_name']; ?><br>
                                    <small class="text-muted">
                                        <?php echo $booking['start_location']; ?> to <?php echo $booking['end_location']; ?>
                                    </small>
                                </td>
                                <td>
                                    <?php echo date('d M Y', strtotime($booking['departure_date'])); ?><br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($booking['departure_time'])); ?></small>
                                </td>
                                <td><?php echo $booking['seats']; ?></td>
                                <?php
                                // Fetch available seats and total seats for this schedule
                                $seatSql = "SELECT available_seats, b.capacity FROM schedules s JOIN buses b ON s.bus_id = b.id WHERE s.id = ?";
                                $seatStmt = $conn->prepare($seatSql);
                                $seatStmt->bind_param("i", $booking['schedule_id']);
                                $seatStmt->execute();
                                $seatStmt->bind_result($available_seats, $total_seats);
                                $seatStmt->fetch();
                                $seatStmt->close();
                                ?>
                                <td><?php echo $available_seats; ?></td>
                                <td><?php echo $total_seats; ?></td>
                                <td>₹<?php echo number_format($booking['total_fare'], 2); ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    switch ($booking['status']) {
                                        case 'confirmed':
                                            $statusClass = 'bg-success';
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'bg-danger';
                                            break;
                                        case 'completed':
                                            $statusClass = 'bg-info';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewBookingModal<?php echo $booking['id']; ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>

                            <!-- View Booking Modal -->
                            <div class="modal fade" id="viewBookingModal<?php echo $booking['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Booking Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>User Information</h6>
                                                    <p><strong>Name:</strong> <?php echo $booking['user_name']; ?></p>
                                                    <p><strong>Email:</strong> <?php echo $booking['email']; ?></p>
                                                    <p><strong>Phone:</strong> <?php echo $booking['phone']; ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Journey Details</h6>
                                                    <p><strong>Route:</strong> <?php echo $booking['route_name']; ?></p>
                                                    <p><strong>From:</strong> <?php echo $booking['start_location']; ?></p>
                                                    <p><strong>To:</strong> <?php echo $booking['end_location']; ?></p>
                                                    <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($booking['departure_date'])); ?></p>
                                                    <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($booking['departure_time'])); ?></p>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Booking Information</h6>
                                                    <p><strong>Booking ID:</strong> <?php echo $booking['id']; ?></p>
                                                    <p><strong>Seats:</strong> <?php echo $booking['seats']; ?></p>
                                                    <p><strong>Total Fare:</strong> ₹<?php echo number_format($booking['total_fare'], 2); ?></p>
                                                    <p><strong>Payment Method:</strong> <?php echo ucfirst($booking['payment_method']); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Status Information</h6>
                                                    <p><strong>Status:</strong> <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($booking['status']); ?></span></p>
                                                    <p><strong>Booking Date:</strong> <?php echo date('d M Y, h:i A', strtotime($booking['booking_date'])); ?></p>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h6>Bus Seats</h6>
                                                    <p><strong>Available Seats:</strong> <?php echo $available_seats; ?> / <strong>Total Seats:</strong> <?php echo $total_seats; ?></p>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h6>Passenger Details</h6>
                                                    <ul>
                                                    <?php
                                                    $ticketsSql = "SELECT passenger_name, passenger_age, passenger_gender, seat_number FROM tickets WHERE booking_id = ?";
                                                    $ticketsStmt = $conn->prepare($ticketsSql);
                                                    $ticketsStmt->bind_param("i", $booking['id']);
                                                    $ticketsStmt->execute();
                                                    $ticketsResult = $ticketsStmt->get_result();
                                                    if ($ticketsResult->num_rows > 0) {
                                                        while ($ticket = $ticketsResult->fetch_assoc()) {
                                                            echo "<li><strong>" . htmlspecialchars($ticket['passenger_name']) . "</strong> (Age: " . htmlspecialchars($ticket['passenger_age']) . ", Gender: " . htmlspecialchars($ticket['passenger_gender']) . ", Seat: " . htmlspecialchars($ticket['seat_number']) . ")</li>";
                                                        }
                                                    } else {
                                                        echo "<li>No passenger details found.</li>";
                                                    }
                                                    $ticketsStmt->close();
                                                    ?>
                                                    </ul>
                                                </div>
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
                            <td colspan="8" class="text-center">No bookings found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?> 