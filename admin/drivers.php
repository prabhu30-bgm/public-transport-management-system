<?php
require_once '../auth/session.php';
require_once '../config/database.php';
require_once '../includes/validation.php';
requireAdmin();

// Set page title
$page_title = 'Manage Drivers';

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new driver
    if (isset($_POST['add_driver'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $name = $_POST['name'];
        $license = $_POST['license'];
        $licenseClass = $_POST['license_class'];
        $licenseExpiry = $_POST['license_expiry'];
        $contact = $_POST['contact'];
        $email = $_POST['email'];

        // Validate license format
        $licenseValidation = Validation::validateDriverLicense($license);
        if (!$licenseValidation['valid']) {
            $error = $licenseValidation['message'];
        } else {
            // Validate license class
            $classValidation = Validation::validateLicenseClass($licenseClass);
            if (!$classValidation['valid']) {
                $error = $classValidation['message'];
            } else {
                // Validate license expiry
                $expiryValidation = Validation::validateLicenseExpiry($licenseExpiry);
                if (!$expiryValidation['valid']) {
                    $error = $expiryValidation['message'];
                } else {
                    // Check if username already exists
                    $checkSql = "SELECT * FROM drivers WHERE username = '$username'";
                    $checkResult = $conn->query($checkSql);

                    if ($checkResult->num_rows > 0) {
                        $error = "Username already exists. Please choose a different username.";
                    } else {
                        // Insert new driver
                        $sql = "INSERT INTO drivers (username, password, name, license_number, license_class, license_expiry, contact_number, email)
                                VALUES ('$username', '$password', '$name', '$license', '$licenseClass', '$licenseExpiry', '$contact', '$email')";

                        if ($conn->query($sql) === TRUE) {
                            $success = "Driver added successfully.";
                        } else {
                            $error = "Error: " . $sql . "<br>" . $conn->error;
                        }
                    }
                }
            }
        }
    }

    // Update driver status
    if (isset($_POST['update_status'])) {
        $driverId = $_POST['driver_id'];
        $status = $_POST['status'];

        $sql = "UPDATE drivers SET status = '$status' WHERE id = $driverId";

        if ($conn->query($sql) === TRUE) {
            $success = "Driver status updated successfully.";
        } else {
            $error = "Error updating driver status: " . $conn->error;
        }
    }

    // Delete driver
    if (isset($_POST['delete_driver'])) {
        $driverId = $_POST['driver_id'];

        // Check if driver has any schedules
        $checkSql = "SELECT * FROM schedules WHERE driver_id = $driverId";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows > 0) {
            $error = "Cannot delete driver. Driver has assigned schedules.";
        } else {
            $sql = "DELETE FROM drivers WHERE id = $driverId";

            if ($conn->query($sql) === TRUE) {
                $success = "Driver deleted successfully.";
            } else {
                $error = "Error deleting driver: " . $conn->error;
            }
        }
    }
}

// Get all drivers
$sql = "SELECT * FROM drivers ORDER BY id ASC";
$result = $conn->query($sql);
$drivers = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $drivers[] = $row;
    }
}

// Include header
include 'includes/header.php';
?>
<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Drivers</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDriverModal">
        <i class="fas fa-plus"></i> Add New Driver
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

<!-- Drivers Table -->
<div class="card">
    <div class="card-header">
        <h5>All Drivers</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>License Number</th>
                        <th>License Class</th>
                        <th>License Expiry</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($drivers) > 0): ?>
                        <?php foreach ($drivers as $driver): ?>
                            <tr>
                                <td><?php echo $driver['id']; ?></td>
                                <td><?php echo $driver['name']; ?></td>
                                <td><?php echo $driver['username']; ?></td>
                                <td><?php echo $driver['license_number']; ?></td>
                                <td><?php echo isset($driver['license_class']) ? $driver['license_class'] : 'Heavy'; ?></td>
                                <td><?php echo isset($driver['license_expiry']) ? date('d M Y', strtotime($driver['license_expiry'])) : date('d M Y', strtotime('+1 year')); ?></td>
                                <td><?php echo $driver['contact_number']; ?></td>
                                <td><?php echo $driver['email']; ?></td>
                                <td>
                                    <span class="badge <?php echo $driver['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo ucfirst($driver['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#viewDriverModal<?php echo $driver['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning btn-action" data-bs-toggle="modal" data-bs-target="#editDriverModal<?php echo $driver['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-action" data-bs-toggle="modal" data-bs-target="#deleteDriverModal<?php echo $driver['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

            <!-- View Driver Modal -->
            <div class="modal fade" id="viewDriverModal<?php echo $driver['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Driver Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Name:</label>
                                <p><?php echo $driver['name']; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Username:</label>
                                <p><?php echo $driver['username']; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">License Number:</label>
                                <p><?php echo $driver['license_number']; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">License Class:</label>
                                <p><?php echo isset($driver['license_class']) ? $driver['license_class'] : 'Heavy'; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">License Expiry:</label>
                                <p><?php echo isset($driver['license_expiry']) ? date('d M Y', strtotime($driver['license_expiry'])) : date('d M Y', strtotime('+1 year')); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Contact Number:</label>
                                <p><?php echo $driver['contact_number']; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email:</label>
                                <p><?php echo $driver['email']; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p>
                                    <span class="badge <?php echo $driver['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo ucfirst($driver['status']); ?>
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Created At:</label>
                                <p><?php echo date('F d, Y h:i A', strtotime($driver['created_at'])); ?></p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Driver Modal (Status Change) -->
            <div class="modal fade" id="editDriverModal<?php echo $driver['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Driver Status</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post" action="">
                            <div class="modal-body">
                                <input type="hidden" name="driver_id" value="<?php echo $driver['id']; ?>">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active" <?php echo $driver['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $driver['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
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

            <!-- Delete Driver Modal -->
            <div class="modal fade" id="deleteDriverModal<?php echo $driver['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Delete Driver</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete driver <strong><?php echo $driver['name']; ?></strong>?</p>
                            <p class="text-danger">This action cannot be undone. If the driver has any assigned schedules, the deletion will fail.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form method="post" action="">
                                <input type="hidden" name="driver_id" value="<?php echo $driver['id']; ?>">
                                <button type="submit" name="delete_driver" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No drivers found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Driver Modal -->
<div class="modal fade" id="addDriverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Driver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="license" class="form-label">License Number</label>
                        <input type="text" class="form-control" id="license" name="license" 
                               pattern="[A-Z]{2}[0-9]{2}\s[0-9]{11}" 
                               placeholder="e.g., MH12 20110012345" required>
                        <div class="form-text">Format: 2 letters (state), 2 digits (RTO), space, 11 digits</div>
                    </div>
                    <div class="mb-3">
                        <label for="license_class" class="form-label">License Class</label>
                        <select class="form-select" id="license_class" name="license_class" required>
                            <option value="">Select License Class</option>
                            <option value="Heavy">Heavy</option>
                            <option value="Medium">Medium</option>
                            <option value="Light">Light</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="license_expiry" class="form-label">License Expiry Date</label>
                        <input type="date" class="form-control" id="license_expiry" name="license_expiry" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="contact" name="contact" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_driver" class="btn btn-primary">Add Driver</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- License Validation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const licenseInput = document.getElementById('license');
    const licenseClassSelect = document.getElementById('license_class');
    const licenseExpiryInput = document.getElementById('license_expiry');

    // Set minimum date for expiry to today
    const today = new Date().toISOString().split('T')[0];
    licenseExpiryInput.min = today;

    // License number validation
    licenseInput.addEventListener('input', function(e) {
        // Convert to uppercase
        this.value = this.value.toUpperCase();
        
        // Remove any non-alphanumeric characters except space
        this.value = this.value.replace(/[^A-Z0-9\s]/g, '');
        
        // Ensure proper spacing
        if (this.value.length > 4 && this.value[4] !== ' ') {
            this.value = this.value.slice(0, 4) + ' ' + this.value.slice(4);
        }
    });

    // License class validation
    licenseClassSelect.addEventListener('change', function() {
        if (this.value === '') {
            this.setCustomValidity('Please select a license class');
        } else {
            this.setCustomValidity('');
        }
    });

    // License expiry validation
    licenseExpiryInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const today = new Date();
        
        if (selectedDate < today) {
            this.setCustomValidity('Expiry date cannot be in the past');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
