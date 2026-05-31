<?php
require_once '../auth/session.php';
require_once '../config/database.php';
require_once '../includes/validation.php';
requireDriver();

function verifyPassword($password, $storedHash) {
    if (password_verify($password, $storedHash)) {
        return true;
    }
    return $password === $storedHash;
}

// Get driver ID from session
$driverId = $_SESSION['user_id'];

// Get driver details
$sql = "SELECT * FROM drivers WHERE id = $driverId";
$result = $conn->query($sql);
$driver = $result->fetch_assoc();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $contact = $_POST['contact'];
        $license = $_POST['license'];
        $licenseClass = $_POST['license_class'];
        $licenseExpiry = $_POST['license_expiry'];

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
                    // Update driver profile
                    $updateSql = "UPDATE drivers SET 
                                name = '$name', 
                                email = '$email', 
                                contact_number = '$contact',
                                license_number = '$license',
                                license_class = '$licenseClass',
                                license_expiry = '$licenseExpiry'
                                WHERE id = $driverId";

                    if ($conn->query($updateSql) === TRUE) {
                        // Update session name
                        $_SESSION['name'] = $name;
                        $success = "Profile updated successfully.";

                        // Refresh driver data
                        $result = $conn->query($sql);
                        $driver = $result->fetch_assoc();
                    } else {
                        $error = "Error updating profile: " . $conn->error;
                    }
                }
            }
        }
    }

    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Verify current password
        if (!verifyPassword($currentPassword, $driver['password'])) {
            $error = "Current password is incorrect.";
        } else if ($newPassword != $confirmPassword) {
            $error = "New password and confirm password do not match.";
        } else {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateSql = "UPDATE drivers SET password = '$hashedPassword' WHERE id = $driverId";

            if ($conn->query($updateSql) === TRUE) {
                $success = "Password changed successfully.";
            } else {
                $error = "Error changing password: " . $conn->error;
            }
        }
    }
}

// Get driver statistics
$totalTripsSql = "SELECT COUNT(*) as count FROM schedules WHERE driver_id = $driverId";
$totalTripsResult = $conn->query($totalTripsSql);
$totalTrips = $totalTripsResult->fetch_assoc()['count'];

$completedTripsSql = "SELECT COUNT(*) as count FROM schedules WHERE driver_id = $driverId AND status IN ('departed', 'completed')";
$completedTripsResult = $conn->query($completedTripsSql);
$completedTrips = $completedTripsResult->fetch_assoc()['count'];

$upcomingTripsSql = "SELECT COUNT(*) as count FROM schedules WHERE driver_id = $driverId AND status = 'scheduled' AND departure_date >= CURDATE()";
$upcomingTripsResult = $conn->query($upcomingTripsSql);
$upcomingTrips = $upcomingTripsResult->fetch_assoc()['count'];
?>

<?php
// Include header
include 'includes/header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
    <h1 class="section-title">My Profile</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <span class="btn btn-sm btn-outline-primary"><i class="fas fa-user-circle me-2"></i>Welcome, <?php echo $_SESSION['name']; ?></span>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if (isset($success)): ?>
<div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
    <div class="d-flex align-items-center">
        <div class="flex-shrink-0 me-3">
            <i class="fas fa-check-circle fa-2x"></i>
        </div>
        <div class="flex-grow-1">
            <h5 class="alert-heading">Success!</h5>
            <p class="mb-0"><?php echo $success; ?></p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
    <div class="d-flex align-items-center">
        <div class="flex-shrink-0 me-3">
            <i class="fas fa-exclamation-circle fa-2x"></i>
        </div>
        <div class="flex-grow-1">
            <h5 class="alert-heading">Error!</h5>
            <p class="mb-0"><?php echo $error; ?></p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Driver Profile Overview -->
<div class="card profile-header mb-4 animate__animated animate__fadeIn">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-3 text-center mb-4 mb-md-0">
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                    <div class="status-indicator"></div>
                </div>
            </div>
            <div class="col-md-9">
                <h3 class="mb-3"><?php echo $driver['name']; ?></h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="profile-detail">
                            <i class="fas fa-id-card text-primary"></i>
                            <div>
                                <span class="detail-label">License Number</span>
                                <span class="detail-value"><?php echo $driver['license_number']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="profile-detail">
                            <i class="fas fa-car text-primary"></i>
                            <div>
                                <span class="detail-label">License Class</span>
                                <span class="detail-value"><?php echo $driver['license_class']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="profile-detail">
                            <i class="fas fa-calendar-times text-primary"></i>
                            <div>
                                <span class="detail-label">License Expiry</span>
                                <span class="detail-value"><?php echo date('d M Y', strtotime($driver['license_expiry'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="profile-detail">
                            <i class="fas fa-envelope text-primary"></i>
                            <div>
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?php echo $driver['email']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="profile-detail">
                            <i class="fas fa-phone text-primary"></i>
                            <div>
                                <span class="detail-label">Contact</span>
                                <span class="detail-value"><?php echo $driver['contact_number']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="profile-detail">
                            <i class="fas fa-user-check text-primary"></i>
                            <div>
                                <span class="detail-label">Status</span>
                                <span class="detail-value"><span class="badge bg-success"><?php echo ucfirst($driver['status']); ?></span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-4 mb-md-0">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-bus-alt"></i>
                </div>
                <h5 class="card-title">Total Trips</h5>
                <h2 class="card-text"><?php echo $totalTrips; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4 mb-md-0">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5 class="card-title">Completed Trips</h5>
                <h2 class="card-text"><?php echo $completedTrips; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
            <div class="card-body text-center">
                <div class="dashboard-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h5 class="card-title">Upcoming Trips</h5>
                <h2 class="card-text"><?php echo $upcomingTrips; ?></h2>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-header {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        margin: 0 auto;
    }

    .profile-avatar i {
        font-size: 80px;
        color: white;
    }

    .status-indicator {
        width: 25px;
        height: 25px;
        border-radius: 50%;
        background-color: #2ecc71;
        border: 3px solid white;
        position: absolute;
        bottom: 10px;
        right: 10px;
    }

    .profile-detail {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .profile-detail i {
        font-size: 1.5rem;
        margin-right: 15px;
        width: 30px;
        text-align: center;
    }

    .detail-label {
        display: block;
        font-size: 0.85rem;
        color: #6c757d;
    }

    .detail-value {
        display: block;
        font-weight: 500;
        font-size: 1.1rem;
    }
</style>

<div class="row">
    <!-- Profile Information -->
    <div class="col-md-6 mb-4">
        <div class="card animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2 text-primary"></i>Edit Profile</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $driver['name']; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                            <input type="text" class="form-control" id="username" value="<?php echo $driver['username']; ?>" readonly>
                        </div>
                        <div class="form-text"><i class="fas fa-info-circle me-1"></i>Username cannot be changed.</div>
                    </div>
                    <div class="mb-3">
                        <label for="license" class="form-label">License Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" class="form-control" id="license" name="license" 
                                   pattern="[A-Z]{2}[0-9]{2}\s[0-9]{11}" 
                                   placeholder="e.g., MH12 20110012345" 
                                   value="<?php echo $driver['license_number']; ?>" required>
                        </div>
                        <div class="form-text">Format: 2 letters (state), 2 digits (RTO), space, 11 digits</div>
                    </div>
                    <div class="mb-3">
                        <label for="license_class" class="form-label">License Class</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-car"></i></span>
                            <select class="form-select" id="license_class" name="license_class" required>
                                <option value="Heavy" <?php echo $driver['license_class'] == 'Heavy' ? 'selected' : ''; ?>>Heavy</option>
                                <option value="Medium" <?php echo $driver['license_class'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="Light" <?php echo $driver['license_class'] == 'Light' ? 'selected' : ''; ?>>Light</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="license_expiry" class="form-label">License Expiry Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-times"></i></span>
                            <input type="date" class="form-control" id="license_expiry" name="license_expiry" 
                                   value="<?php echo $driver['license_expiry']; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" class="form-control" id="contact" name="contact" value="<?php echo $driver['contact_number']; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $driver['email']; ?>" required>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="col-md-6 mb-4">
        <div class="card animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-key me-2 text-primary"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Information -->
        <div class="card mt-4 animate__animated animate__fadeInUp" style="animation-delay: 0.6s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Account Information</h5>
            </div>
            <div class="card-body">
                <div class="account-info-item">
                    <div class="info-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="info-content">
                        <span class="info-label">Account Created</span>
                        <span class="info-value"><?php echo date('F d, Y', strtotime($driver['created_at'])); ?></span>
                    </div>
                </div>
                <div class="account-info-item">
                    <div class="info-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div class="info-content">
                        <span class="info-label">Last Login</span>
                        <span class="info-value"><?php echo date('F d, Y h:i A'); ?></span>
                    </div>
                </div>
                <div class="account-info-item">
                    <div class="info-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="info-content">
                        <span class="info-label">License Number</span>
                        <span class="info-value"><?php echo $driver['license_number']; ?></span>
                    </div>
                </div>
                <div class="alert alert-info mt-3">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-info-circle fa-2x"></i>
                        </div>
                        <div>
                            <p class="mb-0">If you need to update your license information or have any account issues, please contact the administrator.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .account-info-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .account-info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(76, 201, 240, 0.1), rgba(72, 149, 239, 0.1));
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
    }

    .info-icon i {
        font-size: 1.2rem;
        color: var(--primary-color);
    }

    .info-label {
        display: block;
        font-size: 0.85rem;
        color: #6c757d;
    }

    .info-value {
        display: block;
        font-weight: 500;
        font-size: 1.1rem;
    }

    .input-group-text {
        background-color: transparent;
        border-right: none;
    }

    .input-group .form-control {
        border-left: none;
    }

    .toggle-password {
        cursor: pointer;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password visibility toggle
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

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
