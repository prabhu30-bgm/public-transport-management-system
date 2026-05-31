<?php
// Include database connection and session
require_once '../config/database.php';
require_once '../auth/user_session.php';

// Require user login
requireUser();

// Set page title
$page_title = 'My Bookings';

// Get user ID
$user_id = $_SESSION['user_id'];

// Check if viewing a specific booking
$booking_id = isset($_GET['booking']) ? intval($_GET['booking']) : 0;

// Process booking cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $cancel_booking_id = intval($_POST['booking_id']);
    
    // Check if booking belongs to user
    $checkBookingSql = "SELECT b.id, b.schedule_id, b.seats, s.departure_date, s.departure_time 
                        FROM bookings b
                        JOIN schedules s ON b.schedule_id = s.id
                        WHERE b.id = ? AND b.user_id = ? AND b.status = 'confirmed'";
    $checkBookingStmt = $conn->prepare($checkBookingSql);
    $checkBookingStmt->bind_param("ii", $cancel_booking_id, $user_id);
    $checkBookingStmt->execute();
    $checkBookingResult = $checkBookingStmt->get_result();
    
    if ($checkBookingResult->num_rows === 1) {
        $bookingData = $checkBookingResult->fetch_assoc();
        $schedule_id = $bookingData['schedule_id'];
        $seats = $bookingData['seats'];
        
        // Check if departure time is at least 2 hours away
        $departure_datetime = $bookingData['departure_date'] . ' ' . $bookingData['departure_time'];
        $current_datetime = date('Y-m-d H:i:s');
        $time_difference = strtotime($departure_datetime) - strtotime($current_datetime);
        
        if ($time_difference < 7200) { // 2 hours = 7200 seconds
            $cancel_error = "Bookings can only be cancelled at least 2 hours before departure.";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Update booking status
                $updateBookingSql = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
                $updateBookingStmt = $conn->prepare($updateBookingSql);
                $updateBookingStmt->bind_param("i", $cancel_booking_id);
                $updateBookingStmt->execute();
                $updateBookingStmt->close();
                
                // Update available seats
                $updateSeatsSql = "UPDATE schedules SET available_seats = available_seats + ? WHERE id = ?";
                $updateSeatsStmt = $conn->prepare($updateSeatsSql);
                $updateSeatsStmt->bind_param("ii", $seats, $schedule_id);
                $updateSeatsStmt->execute();
                $updateSeatsStmt->close();

                // Ensure available_seats does not exceed bus capacity
                $capSeatsSql = "UPDATE schedules s JOIN buses b ON s.bus_id = b.id SET s.available_seats = LEAST(s.available_seats, b.capacity) WHERE s.id = ?";
                $capSeatsStmt = $conn->prepare($capSeatsSql);
                $capSeatsStmt->bind_param("i", $schedule_id);
                $capSeatsStmt->execute();
                $capSeatsStmt->close();
                
                // Commit transaction
                $conn->commit();
                
                $cancel_success = "Booking cancelled successfully. Your refund will be processed according to our refund policy.";
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $cancel_error = "Cancellation failed: " . $e->getMessage();
            }
        }
    } else {
        $cancel_error = "Invalid booking or booking cannot be cancelled.";
    }
    $checkBookingStmt->close();
}

// Get booking details if viewing a specific booking
if ($booking_id > 0) {
    // Get booking details
    $bookingDetailsSql = "SELECT b.id, b.schedule_id, b.seats, b.total_fare, b.booking_date, b.status, b.payment_method,
                          s.departure_date, s.departure_time,
                          r.route_name, r.start_location, r.end_location, r.estimated_time,
                          bs.bus_number
                          FROM bookings b
                          JOIN schedules s ON b.schedule_id = s.id
                          JOIN routes r ON s.route_id = r.id
                          JOIN buses bs ON s.bus_id = bs.id
                          WHERE b.id = ? AND b.user_id = ?";
    $bookingDetailsStmt = $conn->prepare($bookingDetailsSql);
    $bookingDetailsStmt->bind_param("ii", $booking_id, $user_id);
    $bookingDetailsStmt->execute();
    $bookingDetailsResult = $bookingDetailsStmt->get_result();
    
    if ($bookingDetailsResult->num_rows === 1) {
        $bookingDetails = $bookingDetailsResult->fetch_assoc();
        
        // Get ticket details
        $ticketsSql = "SELECT * FROM tickets WHERE booking_id = ?";
        $ticketsStmt = $conn->prepare($ticketsSql);
        $ticketsStmt->bind_param("i", $booking_id);
        $ticketsStmt->execute();
        $ticketsResult = $ticketsStmt->get_result();
        
        $tickets = [];
        if ($ticketsResult->num_rows > 0) {
            while ($ticket = $ticketsResult->fetch_assoc()) {
                $tickets[] = $ticket;
            }
        }
        $ticketsStmt->close();
    } else {
        // Booking not found or doesn't belong to user
        header("Location: bookings.php?error=invalid_booking");
        exit();
    }
    $bookingDetailsStmt->close();
} else {
    // Get all bookings for user
    $bookingsSql = "SELECT b.id, b.schedule_id, b.seats, b.total_fare, b.booking_date, b.status,
                    s.departure_date, s.departure_time,
                    r.route_name, r.start_location, r.end_location
                    FROM bookings b
                    JOIN schedules s ON b.schedule_id = s.id
                    JOIN routes r ON s.route_id = r.id
                    WHERE b.user_id = ?
                    ORDER BY b.booking_date DESC";
    $bookingsStmt = $conn->prepare($bookingsSql);
    $bookingsStmt->bind_param("i", $user_id);
    $bookingsStmt->execute();
    $bookingsResult = $bookingsStmt->get_result();
    
    $bookings = [];
    if ($bookingsResult->num_rows > 0) {
        while ($booking = $bookingsResult->fetch_assoc()) {
            $bookings[] = $booking;
        }
    }
    $bookingsStmt->close();
}

// Include header
include 'includes/header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $booking_id > 0 ? 'Booking Details' : 'My Bookings'; ?></h1>
    <?php if ($booking_id > 0): ?>
        <a href="bookings.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to All Bookings
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Booking successful! Your tickets have been confirmed.
    </div>
<?php endif; ?>

<?php if (isset($cancel_success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $cancel_success; ?>
    </div>
<?php endif; ?>

<?php if (isset($cancel_error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $cancel_error; ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_booking'): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> The requested booking was not found or does not belong to you.
    </div>
<?php endif; ?>

<?php if ($booking_id > 0 && isset($bookingDetails)): ?>
    <!-- Booking Details View -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Journey Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Booking ID</h6>
                            <p><?php echo $bookingDetails['id']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Booking Date</h6>
                            <p><?php echo date('d M Y, h:i A', strtotime($bookingDetails['booking_date'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Route</h6>
                            <p><?php echo $bookingDetails['route_name']; ?></p>
                            <p class="text-muted small">
                                <i class="fas fa-map-marker-alt text-danger"></i> <?php echo $bookingDetails['start_location']; ?> 
                                <i class="fas fa-arrow-right mx-2"></i> 
                                <i class="fas fa-map-marker-alt text-success"></i> <?php echo $bookingDetails['end_location']; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Journey Date & Time</h6>
                            <p><?php echo date('D, d M Y', strtotime($bookingDetails['departure_date'])); ?></p>
                            <p class="text-muted small"><?php echo date('h:i A', strtotime($bookingDetails['departure_time'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Bus Number</h6>
                            <p><?php echo $bookingDetails['bus_number']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Estimated Journey Time</h6>
                            <p><?php echo $bookingDetails['estimated_time']; ?> minutes</p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Number of Seats</h6>
                            <p><?php echo $bookingDetails['seats']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Total Fare</h6>
                            <p>₹<?php echo number_format($bookingDetails['total_fare'], 2); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Payment Method</h6>
                            <p><?php echo ucfirst($bookingDetails['payment_method']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Status</h6>
                            <p>
                                <?php
                                $statusClass = '';
                                switch ($bookingDetails['status']) {
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
                                <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($bookingDetails['status']); ?></span>
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($bookingDetails['status'] === 'confirmed'): ?>
                        <?php
                        $departure_datetime = $bookingDetails['departure_date'] . ' ' . $bookingDetails['departure_time'];
                        $current_datetime = date('Y-m-d H:i:s');
                        $time_difference = strtotime($departure_datetime) - strtotime($current_datetime);
                        $can_cancel = $time_difference >= 7200; // 2 hours = 7200 seconds
                        ?>
                        
                        <?php if ($can_cancel): ?>
                            <div class="mt-3">
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelBookingModal">
                                    <i class="fas fa-times-circle"></i> Cancel Booking
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle"></i> Bookings can only be cancelled at least 2 hours before departure.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Passenger Details</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($tickets)): ?>
                        <?php foreach ($tickets as $index => $ticket): ?>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Passenger <?php echo $index + 1; ?></h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Name:</strong> <?php echo $ticket['passenger_name']; ?></p>
                                    <p><strong>Age:</strong> <?php echo $ticket['passenger_age']; ?></p>
                                    <p><strong>Gender:</strong> <?php echo ucfirst($ticket['passenger_gender']); ?></p>
                                    <p><strong>Seat Number:</strong> <?php echo $ticket['seat_number']; ?></p>
                                    <p><strong>Ticket Number:</strong> <?php echo $ticket['ticket_number']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p class="mb-0">No passenger details available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cancel Booking Modal -->
    <div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this booking?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                    <p><strong>Refund Policy:</strong> Cancellations made at least 2 hours before departure are eligible for a full refund.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <form method="post" action="">
                        <input type="hidden" name="booking_id" value="<?php echo $bookingDetails['id']; ?>">
                        <button type="submit" name="cancel_booking" class="btn btn-danger">Confirm Cancellation</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Bookings List View -->
    <div class="card">
        <div class="card-header">
            <h5>Your Booking History</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($bookings)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Route</th>
                                <th>Journey Date</th>
                                <th>Seats</th>
                                <th>Total Fare</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td>
                                        <?php echo $booking['route_name']; ?><br>
                                        <small class="text-muted"><?php echo $booking['start_location']; ?> to <?php echo $booking['end_location']; ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($booking['departure_date'])); ?><br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($booking['departure_time'])); ?></small>
                                    </td>
                                    <td><?php echo $booking['seats']; ?></td>
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
                                        <a href="bookings.php?booking=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">You haven't made any bookings yet.</p>
                </div>
                <div class="text-center mt-3">
                    <a href="schedules.php" class="btn btn-primary">Browse Schedules</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php
// Include footer
include 'includes/footer.php';
?>
