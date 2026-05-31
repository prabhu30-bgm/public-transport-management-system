<?php
// Include database connection and session
require_once '../config/database.php';
require_once '../auth/user_session.php';

// Require user login
requireUser();

// Set page title
$page_title = 'My Profile';

// Get user ID
$user_id = $_SESSION['user_id'];

// Get user details
$userSql = "SELECT * FROM users WHERE id = ?";
$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 1) {
    $user = $userResult->fetch_assoc();
} else {
    // User not found
    header("Location: ../auth/logout.php");
    exit();
}
$userStmt->close();

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validate form data
    $errors = [];
    
    // Check if email already exists (for other users)
    $checkEmailSql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $checkEmailStmt = $conn->prepare($checkEmailSql);
    $checkEmailStmt->bind_param("si", $email, $user_id);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();
    if ($checkEmailStmt->num_rows > 0) {
        $errors[] = "Email already exists. Please use a different email address.";
    }
    $checkEmailStmt->close();
    
    // If no errors, update profile
    if (empty($errors)) {
        $updateProfileSql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $updateProfileStmt = $conn->prepare($updateProfileSql);
        $updateProfileStmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
        
        if ($updateProfileStmt->execute()) {
            // Update session name
            $_SESSION['name'] = $name;
            
            $success = "Profile updated successfully.";
            
            // Refresh user data
            $userStmt = $conn->prepare($userSql);
            $userStmt->bind_param("i", $user_id);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $user = $userResult->fetch_assoc();
            $userStmt->close();
        } else {
            $errors[] = "Profile update failed. Please try again.";
        }
        $updateProfileStmt->close();
    }
}

// Process password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    // Get form data
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validate form data
    $password_errors = [];
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $password_errors[] = "Current password is incorrect.";
    }
    
    // Check if new password matches confirmation
    if ($new_password !== $confirm_password) {
        $password_errors[] = "New passwords do not match.";
    }
    
    // Check password length
    if (strlen($new_password) < 6) {
        $password_errors[] = "New password must be at least 6 characters long.";
    }
    
    // If no errors, update password
    if (empty($password_errors)) {
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $updatePasswordSql = "UPDATE users SET password = ? WHERE id = ?";
        $updatePasswordStmt = $conn->prepare($updatePasswordSql);
        $updatePasswordStmt->bind_param("si", $hashed_password, $user_id);
        
        if ($updatePasswordStmt->execute()) {
            $password_success = "Password changed successfully.";
        } else {
            $password_errors[] = "Password change failed. Please try again.";
        }
        $updatePasswordStmt->close();
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">My Profile</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Profile Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Profile Information</h5>
            </div>
            <div class="card-body">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" readonly>
                        <div class="form-text">Username cannot be changed.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo $user['address']; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="created_at" class="form-label">Member Since</label>
                        <input type="text" class="form-control" id="created_at" value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" readonly>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Change Password -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Change Password</h5>
            </div>
            <div class="card-body">
                <?php if (isset($password_success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $password_success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($password_errors) && !empty($password_errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($password_errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>
        
        <!-- Account Actions -->
        <div class="card">
            <div class="card-header">
                <h5>Account Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="bookings.php" class="btn btn-outline-primary">
                        <i class="fas fa-ticket-alt me-2"></i> View My Bookings
                    </a>
                    <a href="../auth/logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
