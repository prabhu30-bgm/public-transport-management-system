<?php
// Include database connection and required files
require_once '../config/database.php';
require_once '../auth/user_session.php';
require_once '../includes/validation.php';

// Redirect if already logged in
redirectIfLoggedIn();

// Set page title
$page_title = 'Register';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !Validation::validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid request";
    } else {
        // Get and sanitize form data
        $username = Validation::sanitizeInput($_POST['username']);
        $password = $_POST['password']; // Don't sanitize password before validation
        $confirm_password = $_POST['confirm_password'];
        $name = Validation::sanitizeInput($_POST['name']);
        $email = Validation::sanitizeInput($_POST['email']);
        $phone = Validation::sanitizeInput($_POST['phone']);
        $address = Validation::sanitizeInput($_POST['address']);
        
        // Validate form data
        $errors = [];
        
        // Validate username
        $usernameValidation = Validation::validateUsername($username);
        if ($usernameValidation !== true) {
            $errors[] = $usernameValidation;
        }

        // Check if username already exists
        $checkUsername = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkUsername->bind_param("s", $username);
        $checkUsername->execute();
        $checkUsername->store_result();
        if ($checkUsername->num_rows > 0) {
            $errors[] = "Username already exists. Please choose a different username.";
        }
        $checkUsername->close();
        
        // Validate email
        $emailValidation = Validation::validateEmail($email);
        if ($emailValidation !== true) {
            $errors[] = $emailValidation;
        }

        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();
        if ($checkEmail->num_rows > 0) {
            $errors[] = "Email already exists. Please use a different email address.";
        }
        $checkEmail->close();
        
        // Validate phone
        $phoneValidation = Validation::validatePhone($phone);
        if ($phoneValidation !== true) {
            $errors[] = $phoneValidation;
        }
        
        // Validate password
        $passwordValidation = Validation::validatePassword($password);
        if ($passwordValidation !== true) {
            $errors = array_merge($errors, $passwordValidation);
        }
        
        // Check if passwords match
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
        
        // Validate name (at least 2 words, only letters and spaces)
        if (!preg_match("/^[a-zA-Z]+(?: [a-zA-Z]+)+$/", $name)) {
            $errors[] = "Please enter your full name (first and last name).";
        }
        
        // Validate address (minimum length)
        if (strlen($address) < 10) {
            $errors[] = "Please enter a valid address (minimum 10 characters).";
        }
        
        // Check rate limiting for registration attempts
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!Validation::checkRateLimit($ip, 'register', 3, 3600)) { // 3 attempts per hour
            $errors[] = "Too many registration attempts. Please try again later.";
        }
        
        // If no errors, insert user into database
        if (empty($errors)) {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $insertUser = $conn->prepare("INSERT INTO users (username, password, name, email, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
            $insertUser->bind_param("ssssss", $username, $hashed_password, $name, $email, $phone, $address);
            
            if ($insertUser->execute()) {
                // Registration successful, redirect to login page
                header("Location: login_standalone.php?registered=success");
                exit();
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
            $insertUser->close();
        }
    }
}

// Generate CSRF token for the form
$csrf_token = Validation::generateCSRFToken();

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Create an Account</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <p class="text-muted mb-4">
                        <small><span class="text-danger">*</span> Indicates required fields</small>
                    </p>
                    
                    <form method="post" action="" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                    required pattern="^[a-zA-Z]+(?: [a-zA-Z]+)+$"
                                    title="Please enter your full name (first and last name)">
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" 
                                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                    required pattern="[a-zA-Z0-9_]{4,20}"
                                    title="Username must be 4-20 characters long and can only contain letters, numbers, and underscores">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                    value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                                    required pattern="[0-9]{10,15}"
                                    title="Phone number must be between 10 and 15 digits">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" name="address" rows="3" 
                                required minlength="10"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" 
                                    required minlength="8"
                                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}"
                                    title="Password must be at least 8 characters long and include uppercase, lowercase, number, and special character">
                                <div class="password-strength mt-2"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <!-- <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">I agree to the <a href="#">Terms and Conditions</a> <span class="text-danger">*</span></label>
                        </div> -->
                        
                        <div class="d-grid">
                            <button type="submit" name="register" class="btn btn-primary">Register</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    Already have an account? <a href="login_standalone.php">Login here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.querySelector('.password-strength');
    let strength = 0;
    let message = '';
    
    // Length check
    if (password.length >= 8) strength++;
    // Uppercase check
    if (/[A-Z]/.test(password)) strength++;
    // Lowercase check
    if (/[a-z]/.test(password)) strength++;
    // Number check
    if (/[0-9]/.test(password)) strength++;
    // Special character check
    if (/[!@#$%^&*()\-_=+{};:,<.>]/.test(password)) strength++;
    
    switch(strength) {
        case 0:
        case 1:
            message = '<div class="alert alert-danger">Very Weak</div>';
            break;
        case 2:
            message = '<div class="alert alert-warning">Weak</div>';
            break;
        case 3:
            message = '<div class="alert alert-info">Medium</div>';
            break;
        case 4:
            message = '<div class="alert alert-primary">Strong</div>';
            break;
        case 5:
            message = '<div class="alert alert-success">Very Strong</div>';
            break;
    }
    
    strengthDiv.innerHTML = message;
});

// Confirm password validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    if (this.value !== password) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
