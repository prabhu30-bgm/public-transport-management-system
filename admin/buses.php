<?php
require_once '../auth/session.php';
require_once '../config/database.php';
requireAdmin();

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new bus
    if (isset($_POST['add_bus'])) {
        $busNumber = $_POST['bus_number'];
        $regNumber = $_POST['registration_number'];
        $capacity = $_POST['capacity'];
        $model = $_POST['model'];
        $status = $_POST['status'];

        // Check if bus number already exists
        $checkSql = "SELECT * FROM buses WHERE bus_number = '$busNumber'";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows > 0) {
            $error = "Bus number already exists. Please choose a different bus number.";
        } else {
            // Insert new bus
            $sql = "INSERT INTO buses (bus_number, registration_number, capacity, model, status)
                    VALUES ('$busNumber', '$regNumber', $capacity, '$model', '$status')";

            if ($conn->query($sql) === TRUE) {
                $success = "Bus added successfully.";
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }

    // Update bus status
    if (isset($_POST['update_status'])) {
        $busId = $_POST['bus_id'];
        $status = $_POST['status'];

        $sql = "UPDATE buses SET status = '$status' WHERE id = $busId";

        if ($conn->query($sql) === TRUE) {
            $success = "Bus status updated successfully.";
        } else {
            $error = "Error updating bus status: " . $conn->error;
        }
    }

    // Delete bus
    if (isset($_POST['delete_bus'])) {
        $busId = $_POST['bus_id'];

        // Check if bus has any schedules
        $checkSql = "SELECT * FROM schedules WHERE bus_id = $busId";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows > 0) {
            $error = "Cannot delete bus. Bus has assigned schedules.";
        } else {
            $sql = "DELETE FROM buses WHERE id = $busId";

            if ($conn->query($sql) === TRUE) {
                $success = "Bus deleted successfully.";
            } else {
                $error = "Error deleting bus: " . $conn->error;
            }
        }
    }
}

// Get all buses
$sql = "SELECT * FROM buses ORDER BY bus_number ASC";
$result = $conn->query($sql);
$buses = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $buses[] = $row;
    }
}
?>

<?php
$page_title = "Manage Buses";
include 'includes/header.php';
?>

<!-- Page Content -->
<div class="container-fluid px-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-bus text-primary me-2"></i> Manage Buses
        </h1>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addBusModal">
            <i class="fas fa-plus me-2"></i> Add New Bus
        </button>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Bus Status Cards -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Buses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $totalBusesSql = "SELECT COUNT(*) as total FROM buses";
                                $totalBusesResult = $conn->query($totalBusesSql);
                                $totalBuses = $totalBusesResult->fetch_assoc()['total'];
                                echo $totalBuses;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Buses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $activeBusesSql = "SELECT COUNT(*) as active FROM buses WHERE status = 'active'";
                                $activeBusesResult = $conn->query($activeBusesSql);
                                $activeBuses = $activeBusesResult->fetch_assoc()['active'];
                                echo $activeBuses;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Maintenance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $maintenanceBusesSql = "SELECT COUNT(*) as maintenance FROM buses WHERE status = 'maintenance'";
                                $maintenanceBusesResult = $conn->query($maintenanceBusesSql);
                                $maintenanceBuses = $maintenanceBusesResult->fetch_assoc()['maintenance'];
                                echo $maintenanceBuses;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wrench fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Inactive Buses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $inactiveBusesSql = "SELECT COUNT(*) as inactive FROM buses WHERE status = 'inactive'";
                                $inactiveBusesResult = $conn->query($inactiveBusesSql);
                                $inactiveBuses = $inactiveBusesResult->fetch_assoc()['inactive'];
                                echo $inactiveBuses;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

                <!-- Buses Table -->
                <div class="card">
                    <div class="card-header">
                        <h5>All Buses</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Bus Number</th>
                                        <th>Registration Number</th>
                                        <th>Capacity</th>
                                        <th>Model</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($buses) > 0): ?>
                                        <?php foreach ($buses as $bus): ?>
                                            <tr>
                                                <td><?php echo $bus['id']; ?></td>
                                                <td><?php echo $bus['bus_number']; ?></td>
                                                <td><?php echo $bus['registration_number']; ?></td>
                                                <td><?php echo $bus['capacity']; ?> seats</td>
                                                <td><?php echo $bus['model']; ?></td>
                                                <td>
                                                    <?php
                                                    $statusClass = '';
                                                    switch ($bus['status']) {
                                                        case 'active':
                                                            $statusClass = 'bg-success';
                                                            break;
                                                        case 'maintenance':
                                                            $statusClass = 'bg-warning';
                                                            break;
                                                        case 'inactive':
                                                            $statusClass = 'bg-danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo ucfirst($bus['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#viewBusModal<?php echo $bus['id']; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-warning btn-action" data-bs-toggle="modal" data-bs-target="#editBusModal<?php echo $bus['id']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger btn-action" data-bs-toggle="modal" data-bs-target="#deleteBusModal<?php echo $bus['id']; ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- View Bus Modal -->
                                            <div class="modal fade" id="viewBusModal<?php echo $bus['id']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Bus Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Bus Number:</label>
                                                                <p><?php echo $bus['bus_number']; ?></p>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Registration Number:</label>
                                                                <p><?php echo $bus['registration_number']; ?></p>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Capacity:</label>
                                                                <p><?php echo $bus['capacity']; ?> seats</p>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Model:</label>
                                                                <p><?php echo $bus['model']; ?></p>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Status:</label>
                                                                <p>
                                                                    <span class="badge <?php echo $statusClass; ?>">
                                                                        <?php echo ucfirst($bus['status']); ?>
                                                                    </span>
                                                                </p>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Added On:</label>
                                                                <p><?php echo date('F d, Y h:i A', strtotime($bus['created_at'])); ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Edit Bus Modal (Status Change) -->
                                            <div class="modal fade" id="editBusModal<?php echo $bus['id']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Update Bus Status</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="post" action="">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="bus_id" value="<?php echo $bus['id']; ?>">
                                                                <div class="mb-3">
                                                                    <label for="status" class="form-label">Status</label>
                                                                    <select class="form-select" id="status" name="status" required>
                                                                        <option value="active" <?php echo $bus['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                                        <option value="maintenance" <?php echo $bus['status'] == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                                                        <option value="inactive" <?php echo $bus['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
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

                                            <!-- Delete Bus Modal -->
                                            <div class="modal fade" id="deleteBusModal<?php echo $bus['id']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Delete Bus</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete bus <strong><?php echo $bus['bus_number']; ?></strong>?</p>
                                                            <p class="text-danger">This action cannot be undone. If the bus has any assigned schedules, the deletion will fail.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="post" action="">
                                                                <input type="hidden" name="bus_id" value="<?php echo $bus['id']; ?>">
                                                                <button type="submit" name="delete_bus" class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No buses found</td>
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

    <!-- Add Bus Modal -->
    <div class="modal fade" id="addBusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Bus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="bus_number" class="form-label">Bus Number</label>
                            <input type="text" class="form-control" id="bus_number" name="bus_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="registration_number" class="form-label">Registration Number</label>
                            <input type="text" class="form-control" id="registration_number" name="registration_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacity (Seats)</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" class="form-control" id="model" name="model" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_bus" class="btn btn-primary">Add Bus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
