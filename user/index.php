<?php
// Include database connection
require_once '../config/database.php';

// Set page title
$page_title = 'Home';

// Include header
include 'includes/header.php';

// ---------------------------------------------------
// CHECK IF bookings TABLE EXISTS
// ---------------------------------------------------
$bookingsTableExists = false;

$checkBookingsTable = $conn->query("SHOW TABLES LIKE 'bookings'");

if ($checkBookingsTable && $checkBookingsTable->num_rows > 0) {
    $bookingsTableExists = true;
}

// ---------------------------------------------------
// GET POPULAR ROUTES
// ---------------------------------------------------

if ($bookingsTableExists) {

    // WITH bookings table
    $popularRoutesSql = "
        SELECT 
            r.id,
            r.route_name,
            r.start_location,
            r.end_location,
            0 as fare,
            COUNT(b.id) as booking_count
        FROM routes r
        LEFT JOIN schedules s ON r.id = s.route_id
        LEFT JOIN bookings b ON s.id = b.schedule_id
        GROUP BY r.id
        ORDER BY booking_count DESC
        LIMIT 6
    ";

} else {

    // WITHOUT bookings table
    $popularRoutesSql = "
        SELECT 
            r.id,
            r.route_name,
            r.start_location,
            r.end_location,
            0 as fare,
            0 as booking_count
        FROM routes r
        ORDER BY r.id DESC
        LIMIT 6
    ";
}

$popularRoutesResult = $conn->query($popularRoutesSql);

$popularRoutes = [];

if ($popularRoutesResult && $popularRoutesResult->num_rows > 0) {

    while ($row = $popularRoutesResult->fetch_assoc()) {

        $popularRoutes[] = $row;
    }
}

// ---------------------------------------------------
// CHECK available_seats COLUMN
// ---------------------------------------------------

$availableSeatsExists = false;

$checkColumnSql = "SHOW COLUMNS FROM schedules LIKE 'available_seats'";

$checkColumnResult = $conn->query($checkColumnSql);

if ($checkColumnResult && $checkColumnResult->num_rows > 0) {
    $availableSeatsExists = true;
}

// ---------------------------------------------------
// GET UPCOMING SCHEDULES
// ---------------------------------------------------

if ($availableSeatsExists) {

    $upcomingSchedulesSql = "
        SELECT 
            s.id,
            s.departure_date,
            s.departure_time,
            r.route_name,
            r.start_location,
            r.end_location,
            0 as fare,
            b.bus_number,
            s.available_seats
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses b ON s.bus_id = b.id
        WHERE s.departure_date >= CURDATE()
        AND s.status = 'scheduled'
        ORDER BY s.departure_date, s.departure_time
        LIMIT 6
    ";

} else {

    $upcomingSchedulesSql = "
        SELECT 
            s.id,
            s.departure_date,
            s.departure_time,
            r.route_name,
            r.start_location,
            r.end_location,
            0 as fare,
            b.bus_number,
            b.capacity as available_seats
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses b ON s.bus_id = b.id
        WHERE s.departure_date >= CURDATE()
        AND s.status = 'scheduled'
        ORDER BY s.departure_date, s.departure_time
        LIMIT 6
    ";
}

$upcomingSchedulesResult = $conn->query($upcomingSchedulesSql);

$upcomingSchedules = [];

if ($upcomingSchedulesResult && $upcomingSchedulesResult->num_rows > 0) {

    while ($row = $upcomingSchedulesResult->fetch_assoc()) {

        $upcomingSchedules[] = $row;
    }
}
?>

<!-- Hero Section with Search -->
<div class="hero-section">
    <!-- Animated shapes for visual interest -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0 animate__animated animate__fadeInLeft">
                <h1>Book Your Bus Tickets</h1>
                <p class="lead">Travel safely and comfortably with our premium bus service. Enjoy hassle-free booking and a smooth journey experience.</p>
                <div class="hero-buttons">
                    <a href="schedules.php" class="btn btn-light btn-lg me-3 mb-3 mb-md-0">
                        <i class="fas fa-calendar-alt me-2"></i> View Schedules
                    </a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i> Register Now
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 animate__animated animate__fadeInRight">
                <div class="search-box">
                    <h4>Find Your Route</h4>
                    <form action="schedules.php" method="get">
                        <div class="mb-4">
                            <label for="from" class="form-label">From</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <select class="form-select" id="from" name="from" required>
                                    <option value="">Select Starting Point</option>
                                    <?php
                                    $locationsSql = "SELECT DISTINCT start_location FROM routes ORDER BY start_location";
                                    $locationsResult = $conn->query($locationsSql);
                                    if ($locationsResult && $locationsResult->num_rows > 0) {
                                        while ($location = $locationsResult->fetch_assoc()) {
                                            echo "<option value=\"" . $location['start_location'] . "\">" . $location['start_location'] . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="to" class="form-label">To</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <select class="form-select" id="to" name="to" required>
                                    <option value="">Select Destination</option>
                                    <?php
                                    $locationsSql = "SELECT DISTINCT end_location FROM routes ORDER BY end_location";
                                    $locationsResult = $conn->query($locationsSql);
                                    if ($locationsResult && $locationsResult->num_rows > 0) {
                                        while ($location = $locationsResult->fetch_assoc()) {
                                            echo "<option value=\"" . $location['end_location'] . "\">" . $location['end_location'] . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="date" class="form-label">Travel Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                <input type="date" class="form-control" id="date" name="date" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i> Search Buses
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Hero section animated shapes */
    .shape {
        position: absolute;
        opacity: 0.1;
        border-radius: 50%;
        background: white;
    }

    .shape-1 {
        width: 150px;
        height: 150px;
        top: 20%;
        left: 10%;
        animation: float 8s ease-in-out infinite;
    }

    .shape-2 {
        width: 100px;
        height: 100px;
        bottom: 20%;
        right: 10%;
        animation: float 6s ease-in-out infinite;
    }

    .shape-3 {
        width: 70px;
        height: 70px;
        bottom: 30%;
        left: 20%;
        animation: float 10s ease-in-out infinite;
    }

    @keyframes float {
        0% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
        100% { transform: translateY(0) rotate(0deg); }
    }

    .hero-buttons {
        margin-top: 2rem;
    }

    .input-group-text {
        background-color: transparent;
        border-right: none;
    }

    .input-group .form-select,
    .input-group .form-control {
        border-left: none;
    }
</style>

<div class="container">
    <!-- Popular Routes Section -->
    <section class="mb-5 mt-5 pt-3">
        <h2 class="section-title">Popular Routes</h2>
        <div class="row g-4">
            <?php
            $animationDelay = 0;
            foreach ($popularRoutes as $route):
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 route-card animate__animated animate__fadeInUp" style="animation-delay: <?php echo $animationDelay; ?>s;">
                    <div class="card-body">
                        <div class="route-icon"><i class="fas fa-bus"></i></div>
                        <h5 class="card-title">
                            <?php echo $route['start_location']; ?>
                            <i class="fas fa-arrow-right mx-2 text-primary"></i>
                            <?php echo $route['end_location']; ?>
                        </h5>
                        <div class="route-details">
                            <div class="route-detail">
                                <i class="fas fa-route me-2 text-primary"></i> Route: <span><?php echo $route['route_name']; ?></span>
                            </div>
                            <div class="route-detail">
                                <i class="fas fa-money-bill-wave me-2 text-primary"></i> Fare: <span>₹<?php echo number_format($route['fare'], 2); ?></span>
                            </div>
                        </div>
                        <a href="schedules.php?route=<?php echo $route['id']; ?>" class="btn btn-primary mt-3 w-100">
                            <i class="fas fa-calendar-alt me-2"></i> View Schedules
                        </a>
                    </div>
                </div>
            </div>
            <?php
            $animationDelay += 0.2;
            endforeach;
            ?>
            <?php if (empty($popularRoutes)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No routes available at the moment. Please check back later.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <style>
        .route-card {
            transition: all 0.3s ease;
            border-radius: 15px;
            overflow: hidden;
            border: none;
        }

        .route-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .route-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--success-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            display: inline-block;
        }

        .route-details {
            background-color: rgba(67, 97, 238, 0.05);
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }

        .route-detail {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .route-detail:last-child {
            margin-bottom: 0;
        }

        .route-detail span {
            font-weight: 500;
            margin-left: 5px;
        }
    </style>

    <!-- Upcoming Schedules Section -->
    <section class="mb-5 mt-5 pt-3">
        <h2 class="section-title">Upcoming Schedules</h2>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Route</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Bus</th>
                                <th>Available Seats</th>
                                <th>Fare</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingSchedules as $schedule): ?>
                            <tr class="animate__animated animate__fadeIn" style="animation-delay: 0.1s;">
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium"><?php echo $schedule['route_name']; ?></span>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt text-danger"></i> <?php echo $schedule['start_location']; ?>
                                            <i class="fas fa-arrow-right mx-1"></i>
                                            <i class="fas fa-map-marker-alt text-success"></i> <?php echo $schedule['end_location']; ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-calendar-alt text-primary me-2"></i>
                                        <?php echo date('d M Y', strtotime($schedule['departure_date'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-clock text-primary me-2"></i>
                                        <?php echo date('h:i A', strtotime($schedule['departure_time'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-bus text-primary me-2"></i>
                                        <?php echo $schedule['bus_number']; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-chair text-primary me-2"></i>
                                        <span class="<?php echo $schedule['available_seats'] < 5 ? 'text-danger fw-bold' : ''; ?>">
                                            <?php echo $schedule['available_seats']; ?> seats
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-rupee-sign text-primary me-2"></i>
                                        <?php echo number_format($schedule['fare'], 2); ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="booking.php?schedule=<?php echo $schedule['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-ticket-alt me-1"></i> Book Now
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($upcomingSchedules)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 2.5rem;"></i>
                                        <p class="mb-0">No upcoming schedules available at the moment.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center mt-4">
            <a href="schedules.php" class="btn btn-primary">
                <i class="fas fa-calendar-alt me-2"></i> View All Schedules
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="mb-5 mt-5 pt-3">
        <h2 class="section-title">Why Choose Us</h2>
        <div class="row g-4">
            <div class="col-md-4 mb-4">
                <div class="card h-100 feature-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                    <div class="card-body text-center">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                        </div>
                        <h5 class="card-title mt-4">Safety First</h5>
                        <p class="card-text text-muted">Your safety is our top priority. All our buses are regularly maintained and driven by experienced drivers.</p>
                        <div class="feature-hover-content">
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle me-2"></i> Regular vehicle maintenance</li>
                                <li><i class="fas fa-check-circle me-2"></i> Experienced drivers</li>
                                <li><i class="fas fa-check-circle me-2"></i> Safety equipment on board</li>
                                <li><i class="fas fa-check-circle me-2"></i> 24/7 support team</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 feature-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                    <div class="card-body text-center">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon">
                                <i class="fas fa-couch"></i>
                            </div>
                        </div>
                        <h5 class="card-title mt-4">Comfort</h5>
                        <p class="card-text text-muted">Enjoy a comfortable journey with spacious seating, air conditioning, and modern amenities.</p>
                        <div class="feature-hover-content">
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle me-2"></i> Spacious seating arrangement</li>
                                <li><i class="fas fa-check-circle me-2"></i> Air conditioning</li>
                                <li><i class="fas fa-check-circle me-2"></i> Entertainment systems</li>
                                <li><i class="fas fa-check-circle me-2"></i> Clean and sanitized interiors</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 feature-card animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
                    <div class="card-body text-center">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <h5 class="card-title mt-4">Punctuality</h5>
                        <p class="card-text text-muted">We value your time. Our buses are known for their punctuality and reliability.</p>
                        <div class="feature-hover-content">
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle me-2"></i> On-time departures</li>
                                <li><i class="fas fa-check-circle me-2"></i> Real-time tracking</li>
                                <li><i class="fas fa-check-circle me-2"></i> Optimized routes</li>
                                <li><i class="fas fa-check-circle me-2"></i> Timely notifications</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .feature-card {
            transition: all 0.3s ease;
            border-radius: 15px;
            overflow: hidden;
            border: none;
            position: relative;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .feature-icon-wrapper {
            width: 90px;
            height: 90px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .feature-icon {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--success-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }

        .feature-hover-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            margin-top: 15px;
        }

        .feature-card:hover .feature-hover-content {
            max-height: 200px;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }

        .feature-list li {
            margin-bottom: 8px;
            padding: 8px 0;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.05);
        }

        .feature-list li:last-child {
            margin-bottom: 0;
            border-bottom: none;
        }
    </style>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
