<?php
// Start session
session_start();
require_once '../includes/validation.php';

// Redirect if already logged in as admin
if(isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Generate CSRF token
$csrf_token = Validation::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Bus Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary-color: #f72585;
            --secondary-color: #b5179e;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --info-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            margin: 2rem;
            position: relative;
            z-index: 1;
            animation: fadeIn 1s ease-in-out;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .login-header {
            background: linear-gradient(135deg, #f72585, #b5179e);
            color: white;
            padding: 30px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header h2 {
            font-weight: 600;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            z-index: 0;
        }

        .login-body {
            padding: 40px 30px;
        }

        .admin-logo-wrapper {
            width: 90px;
            height: 90px;
            margin: 0 auto 25px;
            position: relative;
        }

        .admin-logo {
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
            color: var(--primary-color);
            font-size: 2.5rem;
            position: relative;
            z-index: 1;
        }

        .admin-logo i {
            background: linear-gradient(135deg, #f72585, #b5179e);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 8px;
            color: #555;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            height: 50px;
            border: 1px solid #e0e0e0;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(247, 37, 133, 0.15);
            border-color: var(--primary-color);
            background-color: #fff;
        }

        .input-group {
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            box-shadow: 0 5px 15px rgba(247, 37, 133, 0.15);
        }

        .input-group-text {
            border-radius: 8px 0 0 8px;
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-right: none;
            padding: 0 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
            border-left: none;
        }

        .input-group:has(button) .form-control {
            border-radius: 0;
        }

        .btn-toggle-password {
            border-radius: 0 8px 8px 0;
            border: 1px solid #e0e0e0;
            border-left: none;
            background-color: #f8f9fa;
            color: var(--primary-color);
            width: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-toggle-password:hover {
            background-color: #e9ecef;
        }

        .form-check {
            margin-bottom: 20px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin-top: 0.2rem;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            font-size: 0.95rem;
            cursor: pointer;
            padding-left: 5px;
            color: #555;
        }

        .login-btn {
            background: linear-gradient(135deg, #f72585, #b5179e);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 20px;
            width: 100%;
            font-weight: 500;
            font-size: 1rem;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(247, 37, 133, 0.3);
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            z-index: -1;
        }

        .login-btn:hover::before {
            width: 100%;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(247, 37, 133, 0.4);
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.95rem;
            color: #555;
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-footer a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            border: none;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .alert-success {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-warning {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--warning-color);
            border-left: 4px solid var(--warning-color);
        }

        .alert-danger {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--warning-color);
            border-left: 4px solid var(--warning-color);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container animate__animated animate__fadeIn">
        <div class="login-card">
            <div class="login-header">
                <h2>Admin Login</h2>
            </div>
            <div class="login-body">
                <div class="text-center mb-4">
                    <div class="admin-logo-wrapper">
                        <div class="admin-logo">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <p class="text-muted">Welcome back! Log in to manage system settings, routes, drivers, and reports.</p>
                </div>

                <?php
                // Display error message if any
                if(isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']);
                }
                ?>

                <form action="../auth/login.php" method="post">
                    <input type="hidden" name="role" value="admin">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" 
                                placeholder="Enter your username" required 
                                pattern="[a-zA-Z0-9_]{4,20}"
                                title="Username must be 4-20 characters long and can only contain letters, numbers, and underscores">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" 
                                placeholder="Enter your password" required
                                minlength="6"
                                pattern=".{6,}"
                                title="Password must be at least 6 characters long">
                            <button class="btn-toggle-password" type="button" id="togglePassword">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    <!-- <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div> -->
                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                </form>

                <div class="login-footer">
                    <p>&copy; <?php echo date('Y'); ?> Bus Management System</p>
                    <a href="../index.php" class="d-inline-block mt-2">
                        <i class="fas fa-home me-1"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            togglePassword.addEventListener('click', function() {
                // Toggle the password visibility
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            });

            // Add animation to form elements
            const formElements = document.querySelectorAll('.form-control, .form-check, .login-btn');
            formElements.forEach((element, index) => {
                element.classList.add('animate__animated', 'animate__fadeInUp');
                element.style.animationDelay = `${0.1 + (index * 0.1)}s`;
            });

            // Focus effect on input groups
            const inputGroups = document.querySelectorAll('.input-group');
            inputGroups.forEach(group => {
                const input = group.querySelector('.form-control');
                input.addEventListener('focus', () => {
                    group.style.boxShadow = '0 5px 15px rgba(247, 37, 133, 0.15)';
                });
                input.addEventListener('blur', () => {
                    group.style.boxShadow = '0 3px 10px rgba(0, 0, 0, 0.05)';
                });
            });
        });
    </script>
</body>
</html>
