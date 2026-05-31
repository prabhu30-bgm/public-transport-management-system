<?php
// Include database connection and session
require_once '../config/database.php';
require_once '../auth/user_session.php';

// Require user login
requireUser();

// Set page title
$page_title = 'Book Ticket';

// Get schedule ID from URL
$schedule_id = isset($_GET['schedule']) ? intval($_GET['schedule']) : 0;

// Check if available_seats column exists in schedules table
$checkColumnSql = "SHOW COLUMNS FROM schedules LIKE 'available_seats'";
$checkColumnResult = $conn->query($checkColumnSql);
$availableSeatsExists = ($checkColumnResult && $checkColumnResult->num_rows > 0);

// Check if schedule exists
if ($availableSeatsExists) {
    $scheduleSql = "SELECT s.id, s.departure_date, s.departure_time, s.status, s.available_seats,
                    r.route_name, r.start_location, r.end_location, r.fare, r.estimated_time,
                    b.bus_number, b.capacity
                    FROM schedules s
                    JOIN routes r ON s.route_id = r.id
                    JOIN buses b ON s.bus_id = b.id
                    WHERE s.id = ? AND s.status = 'scheduled' AND s.departure_date >= CURDATE()";
} else {
    // Fallback query without available_seats
    $scheduleSql = "SELECT s.id, s.departure_date, s.departure_time, s.status, b.capacity as available_seats,
                    r.route_name, r.start_location, r.end_location, r.fare, r.estimated_time,
                    b.bus_number, b.capacity
                    FROM schedules s
                    JOIN routes r ON s.route_id = r.id
                    JOIN buses b ON s.bus_id = b.id
                    WHERE s.id = ? AND s.status = 'scheduled' AND s.departure_date >= CURDATE()";
}

$stmt = $conn->prepare($scheduleSql);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Schedule not found or not available
    header("Location: schedules.php?error=invalid_schedule");
    exit();
}

$schedule = $result->fetch_assoc();
$stmt->close();

// Process booking form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_ticket'])) {
    // Get form data
    $seats = intval($_POST['seats']);
    $total_fare = $seats * $schedule['fare'];
    $payment_method = $_POST['payment_method'];
    $user_id = $_SESSION['user_id'];

    // Validate seats
    $errors = [];

    if ($seats <= 0) {
        $errors[] = "Please select at least 1 seat.";
    }

    if ($seats > $schedule['available_seats']) {
        $errors[] = "Not enough seats available. Only {$schedule['available_seats']} seats left.";
    }

    // Get passenger details
    $passenger_names = $_POST['passenger_name'];
    $passenger_ages = $_POST['passenger_age'];
    $passenger_genders = $_POST['passenger_gender'];

    // Validate passenger details
    if (count($passenger_names) !== $seats) {
        $errors[] = "Please provide details for all passengers.";
    }

    // Validate passenger ages
    foreach ($passenger_ages as $age) {
        if (!is_numeric($age) || $age < 0) {
            $errors[] = "Invalid age: Age cannot be negative or empty.";
            break;
        }
    }

    // If no errors, create booking
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert booking
            $bookingSql = "INSERT INTO bookings (user_id, schedule_id, seats, total_fare, payment_method) VALUES (?, ?, ?, ?, ?)";
            $bookingStmt = $conn->prepare($bookingSql);
            $bookingStmt->bind_param("iiids", $user_id, $schedule_id, $seats, $total_fare, $payment_method);
            $bookingStmt->execute();
            $booking_id = $conn->insert_id;
            $bookingStmt->close();

            // Insert tickets for each passenger
            for ($i = 0; $i < $seats; $i++) {
                $passenger_name = $passenger_names[$i];
                $passenger_age = $passenger_ages[$i];
                $passenger_gender = $passenger_genders[$i];
                $seat_number = "A" . ($i + 1); // Simple seat numbering
                $ticket_number = "TKT" . time() . rand(1000, 9999) . ($i + 1);

                $ticketSql = "INSERT INTO tickets (booking_id, passenger_name, passenger_age, passenger_gender, seat_number, ticket_number) VALUES (?, ?, ?, ?, ?, ?)";
                $ticketStmt = $conn->prepare($ticketSql);
                $ticketStmt->bind_param("isisss", $booking_id, $passenger_name, $passenger_age, $passenger_gender, $seat_number, $ticket_number);
                $ticketStmt->execute();
                $ticketStmt->close();
            }

            // Update available seats if column exists
            if ($availableSeatsExists) {
                $updateSeatsSql = "UPDATE schedules SET available_seats = available_seats - ? WHERE id = ?";
                $updateSeatsStmt = $conn->prepare($updateSeatsSql);
                $updateSeatsStmt->bind_param("ii", $seats, $schedule_id);
                $updateSeatsStmt->execute();
                $updateSeatsStmt->close();
            }

            // Commit transaction
            $conn->commit();

            // Redirect to booking confirmation
            header("Location: bookings.php?booking={$booking_id}&status=success");
            exit();

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Booking failed: " . $e->getMessage();
        }
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Book Ticket</h1>
</div>

<?php if (isset($errors) && !empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Booking Form -->
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Passenger Details</h5>
            </div>
            <div class="card-body">
                <form method="post" action="" id="bookingForm">
                    <div class="mb-3">
                        <label for="seats" class="form-label">Number of Seats</label>
                        <select class="form-select" id="seats" name="seats" required onchange="updatePassengerForms()">
                            <?php for ($i = 1; $i <= min(6, $schedule['available_seats']); $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div id="passengerForms">
                        <!-- Passenger forms will be dynamically added here -->
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="book_ticket" class="btn btn-primary">Book Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Journey Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Route</h6>
                    <p class="mb-1"><?php echo $schedule['route_name']; ?></p>
                    <p class="text-muted small">
                        <i class="fas fa-map-marker-alt text-danger"></i> <?php echo $schedule['start_location']; ?>
                        <i class="fas fa-arrow-right mx-2"></i>
                        <i class="fas fa-map-marker-alt text-success"></i> <?php echo $schedule['end_location']; ?>
                    </p>
                </div>

                <div class="mb-3">
                    <h6>Date & Time</h6>
                    <p class="mb-1"><?php echo date('D, d M Y', strtotime($schedule['departure_date'])); ?></p>
                    <p class="text-muted small"><?php echo date('h:i A', strtotime($schedule['departure_time'])); ?></p>
                </div>

                <div class="mb-3">
                    <h6>Bus</h6>
                    <p class="mb-1"><?php echo $schedule['bus_number']; ?></p>
                    <p class="text-muted small">Estimated journey time: <?php echo $schedule['estimated_time']; ?> mins</p>
                </div>

                <div class="mb-3">
                    <h6>Fare</h6>
                    <p class="mb-1">₹<?php echo number_format($schedule['fare'], 2); ?> per seat</p>
                    <p class="text-muted small">Total fare will be calculated based on number of seats</p>
                </div>

                <div class="mb-3">
                    <h6>Available Seats</h6>
                    <p class="mb-1"><?php echo $schedule['available_seats']; ?> / <?php echo $schedule['capacity']; ?></p>
                </div>

                <div class="alert alert-info mb-0">
                    <p class="mb-0"><i class="fas fa-info-circle"></i> Please provide accurate passenger details for a smooth journey.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for dynamic passenger forms -->
<script>
    function updatePassengerForms() {
        const seatsCount = parseInt(document.getElementById('seats').value);
        const passengerFormsContainer = document.getElementById('passengerForms');
        passengerFormsContainer.innerHTML = '';

        for (let i = 0; i < seatsCount; i++) {
            const passengerForm = document.createElement('div');
            passengerForm.className = 'card mb-3';
            passengerForm.innerHTML = `
                <div class="card-body">
                    <h6>Passenger ${i + 1}</h6>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="passenger_name[]" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Age</label>
                        <input type="number" class="form-control" name="passenger_age[]" min="0" required 
                            oninput="validateAge(this)" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="passenger_gender[]" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            `;
            passengerFormsContainer.appendChild(passengerForm);
        }
    }

    // Add age validation function
    function validateAge(input) {
        if (input.value < 0) {
            input.value = 0;
            alert("Age cannot be negative!");
        }
    }

    // Initialize passenger forms
    document.addEventListener('DOMContentLoaded', function() {
        updatePassengerForms();
    });
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
