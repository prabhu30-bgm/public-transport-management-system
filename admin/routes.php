<?php
require_once '../auth/session.php';
require_once '../config/database.php';
requireAdmin();
include 'includes/header.php';
// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new route
    if (isset($_POST['add_route'])) {
        $routeName = $_POST['route_name'];
        $startLocation = $_POST['start_location'];
        $endLocation = $_POST['end_location'];
        $distance = $_POST['distance'];
        $estimatedTime = $_POST['estimated_time'];
        $fare = $_POST['fare'];

        // Check if route already exists
        $checkSql = "SELECT * FROM routes WHERE route_name = '$routeName'";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows > 0) {
            $error = "Route name already exists. Please choose a different name.";
        } else {
            // Insert new route
            $sql = "INSERT INTO routes (route_name, start_location, end_location, distance, estimated_time, fare)
                    VALUES ('$routeName', '$startLocation', '$endLocation', $distance, $estimatedTime, $fare)";

            if ($conn->query($sql) === TRUE) {
                $success = "Route added successfully.";
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }

    // Update route
    if (isset($_POST['update_route'])) {
        $routeId = $_POST['route_id'];
        $routeName = $_POST['route_name'];
        $startLocation = $_POST['start_location'];
        $endLocation = $_POST['end_location'];
        $distance = $_POST['distance'];
        $estimatedTime = $_POST['estimated_time'];
        $fare = $_POST['fare'];

        // Check if route name already exists for other routes
        $checkSql = "SELECT * FROM routes WHERE route_name = '$routeName' AND id != $routeId";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows > 0) {
            $error = "Route name already exists. Please choose a different name.";
        } else {
            // Update route
            $sql = "UPDATE routes SET
                    route_name = '$routeName',
                    start_location = '$startLocation',
                    end_location = '$endLocation',
                    distance = $distance,
                    estimated_time = $estimatedTime,
                    fare = $fare
                    WHERE id = $routeId";

            if ($conn->query($sql) === TRUE) {
                $success = "Route updated successfully.";
            } else {
                $error = "Error updating route: " . $conn->error;
            }
        }
    }

    // Delete route
    if (isset($_POST['delete_route'])) {
        $routeId = $_POST['route_id'];

        // Check if route has any schedules
        $checkSql = "SELECT * FROM schedules WHERE route_id = $routeId";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows > 0) {
            $error = "Cannot delete route. Route has assigned schedules.";
        } else {
            $sql = "DELETE FROM routes WHERE id = $routeId";

            if ($conn->query($sql) === TRUE) {
                $success = "Route deleted successfully.";
            } else {
                $error = "Error deleting route: " . $conn->error;
            }
        }
    }
}

// Get all routes
$sql = "SELECT * FROM routes ORDER BY id ASC";
$result = $conn->query($sql);
$routes = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $routes[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Routes - Bus Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5>Bus Management System</h5>
                        <p class="text-muted">Admin Panel</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="drivers.php">
                                <i class="fas fa-user"></i> Drivers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="buses.php">
                                <i class="fas fa-bus"></i> Buses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="routes.php">
                                <i class="fas fa-route"></i> Routes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="schedules.php">
                                <i class="fas fa-calendar-alt"></i> Schedules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="issues.php">
                                <i class="fas fa-exclamation-triangle"></i> Issues
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link text-danger" href="../auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Routes</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRouteModal">
                        <i class="fas fa-plus"></i> Add New Route
                    </button>
                </div>

                <!-- Alerts -->
                <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Routes Table -->
                <div class="card">
                    <div class="card-header">
                        <h5>All Routes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Route Name</th>
                                        <th>Start Location</th>
                                        <th>End Location</th>
                                        <th>Distance (km)</th>
                                        <th>Est. Time (min)</th>
                                        <th>Fare (₹)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($routes) > 0): ?>
                                        <?php foreach ($routes as $route): ?>
                                            <tr>
                                                <td><?php echo $route['id']; ?></td>
                                                <td><?php echo $route['route_name']; ?></td>
                                                <td><?php echo $route['start_location']; ?></td>
                                                <td><?php echo $route['end_location']; ?></td>
                                                <td><?php echo $route['distance']; ?></td>
                                                <td><?php echo $route['estimated_time']; ?></td>
                                                <td>₹<?php echo number_format($route['fare'], 2); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-warning btn-action" data-bs-toggle="modal" data-bs-target="#editRouteModal<?php echo $route['id']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger btn-action" data-bs-toggle="modal" data-bs-target="#deleteRouteModal<?php echo $route['id']; ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Edit Route Modal -->
                                            <div class="modal fade" id="editRouteModal<?php echo $route['id']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Route</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="post" action="">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="route_id" value="<?php echo $route['id']; ?>">
                                                                <div class="mb-3">
                                                                    <label for="route_name<?php echo $route['id']; ?>" class="form-label">Route Name</label>
                                                                    <input type="text" class="form-control" id="route_name<?php echo $route['id']; ?>" name="route_name" value="<?php echo $route['route_name']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="start_location<?php echo $route['id']; ?>" class="form-label">Start Location</label>
                                                                    <input type="text" class="form-control" id="start_location<?php echo $route['id']; ?>" name="start_location" value="<?php echo $route['start_location']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="end_location<?php echo $route['id']; ?>" class="form-label">End Location</label>
                                                                    <input type="text" class="form-control" id="end_location<?php echo $route['id']; ?>" name="end_location" value="<?php echo $route['end_location']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="distance<?php echo $route['id']; ?>" class="form-label">Distance (km)</label>
                                                                    <input type="number" step="0.01" class="form-control" id="distance<?php echo $route['id']; ?>" name="distance" value="<?php echo $route['distance']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="estimated_time<?php echo $route['id']; ?>" class="form-label">Estimated Time (minutes)</label>
                                                                    <input type="number" class="form-control" id="estimated_time<?php echo $route['id']; ?>" name="estimated_time" value="<?php echo $route['estimated_time']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="fare<?php echo $route['id']; ?>" class="form-label">Fare (₹)</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-text">₹</span>
                                                                        <input type="number" step="0.01" class="form-control" id="fare<?php echo $route['id']; ?>" name="fare" value="<?php echo $route['fare']; ?>" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" name="update_route" class="btn btn-primary">Update Route</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Delete Route Modal -->
                                            <div class="modal fade" id="deleteRouteModal<?php echo $route['id']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Delete Route</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete route <strong><?php echo $route['route_name']; ?></strong>?</p>
                                                            <p class="text-danger">This action cannot be undone. If the route has any assigned schedules, the deletion will fail.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="post" action="">
                                                                <input type="hidden" name="route_id" value="<?php echo $route['id']; ?>">
                                                                <button type="submit" name="delete_route" class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No routes found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Route Modal -->
    <div class="modal fade" id="addRouteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Route</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="route_name" class="form-label">Route Name</label>
                            <input type="text" class="form-control" id="route_name" name="route_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="start_location" class="form-label">Start Location</label>
                            <input type="text" class="form-control" id="start_location" name="start_location" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_location" class="form-label">End Location</label>
                            <input type="text" class="form-control" id="end_location" name="end_location" required>
                        </div>
                        <div class="mb-3">
                            <label for="distance" class="form-label">Distance (km)</label>
                            <input type="number" step="0.01" class="form-control" id="distance" name="distance" required>
                        </div>
                        <div class="mb-3">
                            <label for="estimated_time" class="form-label">Estimated Time (minutes)</label>
                            <input type="number" class="form-control" id="estimated_time" name="estimated_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="fare" class="form-label">Fare (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" class="form-control" id="fare" name="fare" value="100.00" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_route" class="btn btn-primary">Add Route</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
