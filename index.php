<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Management System</title>
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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --info-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            overflow-x: hidden;
        }

        /* Modern Hero Section */
        .hero-section {
            position: relative;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.9), rgba(58, 12, 163, 0.9)), url('assets/images/bus-hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 150px 0 120px;
            margin-bottom: 60px;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            letter-spacing: -0.5px;
        }

        .hero-section p {
            font-size: 1.25rem;
            font-weight: 300;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Animated Shapes */
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

        /* Modern Cards */
        .portal-section {
            padding: 30px 0 60px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }

        .section-title h2 {
            font-weight: 600;
            font-size: 2.2rem;
            margin-bottom: 15px;
            color: var(--dark-color);
        }

        .section-title p {
            color: #6c757d;
            max-width: 700px;
            margin: 0 auto;
        }

        .card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: white;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-15px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .card-icon-wrapper {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05);
        }

        .card-icon {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }

        .user-card .card-icon {
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }

        .driver-card .card-icon {
            background: linear-gradient(135deg, #4cc9f0, #4895ef);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }

        .admin-card .card-icon {
            background: linear-gradient(135deg, #f72585, #b5179e);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 15px;
            text-align: center;
        }

        .card-text {
            color: #6c757d;
            margin-bottom: 25px;
            text-align: center;
        }

        .btn-portal {
            padding: 12px 25px;
            font-weight: 500;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            text-align: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
            border: none;
        }

        .btn-portal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            z-index: -1;
        }

        .btn-portal:hover::before {
            width: 100%;
        }

        .btn-portal:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #4cc9f0, #4895ef);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #f72585, #b5179e);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <!-- Animated shapes -->
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>

        <div class="container hero-content text-center">
            <h1>Bus Management System</h1>
            <p>Book bus tickets, find schedules, and manage travel easily from one secure portal.</p>
        </div>
    </div>

    <!-- Portal Section -->
    <section class="portal-section">
        <div class="container">
            <div class="section-title">
                <h2>Choose Your Portal</h2>
                <p>Select the appropriate portal based on your role in the system</p>
            </div>

            <div class="row justify-content-center g-4">
                <!-- User Portal Card -->
                <div class="col-md-4 mb-4">
                    <div class="card user-card h-100">
                        <div class="card-body d-flex flex-column align-items-center">
                            <div class="card-icon-wrapper">
                                <i class="fas fa-users card-icon"></i>
                            </div>
                            <h3 class="card-title">User Portal</h3>
                            <p class="card-text">Book travel, review your bookings, and manage your passenger profile.</p>
                            <div class="mt-auto w-100 text-center">
                                <a href="user/login_standalone.php" class="btn btn-primary btn-portal">
                                    <i class="fas fa-sign-in-alt me-2"></i> User Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Driver Portal Card -->
                <div class="col-md-4 mb-4">
                    <div class="card driver-card h-100">
                        <div class="card-body d-flex flex-column align-items-center">
                            <div class="card-icon-wrapper">
                                <i class="fas fa-id-card card-icon"></i>
                            </div>
                            <h3 class="card-title">Driver Portal</h3>
                            <p class="card-text">Sign in to review your assignments, routes, and trip updates.</p>
                            <div class="mt-auto w-100 text-center">
                                <a href="driver/login.php" class="btn btn-success btn-portal">
                                    <i class="fas fa-sign-in-alt me-2"></i> Driver Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Portal Card -->
                <div class="col-md-4 mb-4">
                    <div class="card admin-card h-100">
                        <div class="card-body d-flex flex-column align-items-center">
                            <div class="card-icon-wrapper">
                                <i class="fas fa-user-shield card-icon"></i>
                            </div>
                            <h3 class="card-title">Admin Portal</h3>
                            <p class="card-text">Access administrative tools to manage routes, buses, drivers, and reports.</p>
                            <div class="mt-auto w-100 text-center">
                                <a href="admin/login.php" class="btn btn-danger btn-portal">
                                    <i class="fas fa-sign-in-alt me-2"></i> Admin Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modern Footer -->
    <footer class="footer-section">
        <div class="footer-top">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="footer-about">
                            <h3 class="footer-title">Bus Management System</h3>
                            <p>Your trusted partner for safe and comfortable bus travel, providing efficient management solutions for all your transportation needs.</p>
                            <div class="social-links mt-4">
                                <a href="https://www.facebook.com/ps.kudenatti.9" class="social-icon" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://github.com/prabhu30-bgm" class="social-icon" target="_blank" rel="noopener noreferrer"><i class="fab fa-github"></i></a>
                                <a href="https://www.instagram.com/prabyaa_11/" class="social-icon" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                                <a href="https://www.linkedin.com/in/basavaprabhu-kudenatti/" class="social-icon" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h3 class="footer-title">Quick Links</h3>
                        <ul class="footer-links">
                            <li><a href="user/login_standalone.php"><i class="fas fa-angle-right me-2"></i>User Login</a></li>
                            <li><a href="user/register.php"><i class="fas fa-angle-right me-2"></i>Register</a></li>
                            <li><a href="driver/login.php"><i class="fas fa-angle-right me-2"></i>Driver Login</a></li>
                            <li><a href="admin/login.php"><i class="fas fa-angle-right me-2"></i>Admin Login</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h3 class="footer-title">Contact Us</h3>
                        <div class="footer-contact">
                            <div class="contact-item">
                                <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                                <div class="text">Mumbai, Maharashtra, India</div>
                            </div>
                            <div class="contact-item">
                                <div class="icon"><i class="fas fa-phone-alt"></i></div>
                                <div class="text">+91 98765 43210</div>
                            </div>
                            <div class="contact-item">
                                <div class="icon"><i class="fas fa-envelope"></i></div>
                                <div class="text">support@busmanagementsystem.com</div>
                            </div>
                            <div class="contact-item">
                                <div class="icon"><i class="fas fa-clock"></i></div>
                                <div class="text">Mon - Fri: 9:00 AM - 6:00 PM</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> Bus Management System. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <style>
        /* Modern Footer Styles */
        .footer-section {
            position: relative;
            margin-top: 80px;
        }

        .footer-top {
            background: linear-gradient(135deg, #2c3e50, #1a2530);
            color: #fff;
            padding: 70px 0 50px;
            border-top-left-radius: 50px;
            position: relative;
            z-index: 1;
        }

        .footer-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 50px;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
        }

        .footer-about p {
            margin-bottom: 30px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 15px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            display: block;
        }

        .footer-links a:hover {
            color: #fff;
            transform: translateX(5px);
        }

        .footer-contact .contact-item {
            display: flex;
            margin-bottom: 20px;
            align-items: flex-start;
        }

        .footer-contact .icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .footer-contact .text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
        }

        .footer-bottom {
            background: #1a2530;
            padding: 20px 0;
            text-align: center;
        }

        .copyright {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Add animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on scroll
            const cards = document.querySelectorAll('.card');

            // Function to check if element is in viewport
            function isInViewport(element) {
                const rect = element.getBoundingClientRect();
                return (
                    rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.9 &&
                    rect.bottom >= 0
                );
            }

            // Initial check for elements in viewport
            cards.forEach(card => {
                if (isInViewport(card)) {
                    card.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });

            // Check on scroll
            window.addEventListener('scroll', function() {
                cards.forEach(card => {
                    if (isInViewport(card) && !card.classList.contains('animate__animated')) {
                        card.classList.add('animate__animated', 'animate__fadeInUp');
                    }
                });
            });

            // Add hover effect to cards
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-15px)';
                    this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.1)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.08)';
                });
            });
        });
    </script>
</body>
</html>
