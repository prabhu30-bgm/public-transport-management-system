            </main>
        </div>
    </div>

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
                                <div class="social-links mt-4">
                                    <a href="https://www.facebook.com/ps.kudenatti.9" class="social-icon" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                                    <a href="https://github.com/prabhu30-bgm" class="social-icon" target="_blank" rel="noopener noreferrer"><i class="fab fa-github"></i></a>
                                    <a href="https://www.instagram.com/prabyaa_11/" class="social-icon" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                                    <a href="https://www.linkedin.com/in/basavaprabhu-kudenatti/" class="social-icon" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h3 class="footer-title">Quick Links</h3>
                        <ul class="footer-links">
                            <li><a href="index.php"><i class="fas fa-angle-right me-2"></i>Home</a></li>
                            <li><a href="schedules.php"><i class="fas fa-angle-right me-2"></i>Schedules</a></li>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
                            <li><a href="bookings.php"><i class="fas fa-angle-right me-2"></i>My Bookings</a></li>
                            <li><a href="profile.php"><i class="fas fa-angle-right me-2"></i>My Profile</a></li>
                            <?php else: ?>
                            <li><a href="login.php"><i class="fas fa-angle-right me-2"></i>Login</a></li>
                            <li><a href="register.php"><i class="fas fa-angle-right me-2"></i>Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h3 class="footer-title">Contact Us</h3>
                        <div class="footer-contact">
                            <div class="contact-item">
                                <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                                <div class="text">Belagavi, Karnataka, India</div>
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

        .footer-top::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiBwYXR0ZXJuVW5pdHM9InVzZXJTcGFjZU9uVXNlIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSgxMzUpIj48cmVjdCBpZD0icGF0dGVybi1iZyIgd2lkdGg9IjQwMCUiIGhlaWdodD0iNDAwJSIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjAyKSI+PC9yZWN0PjxjaXJjbGUgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjA1KSIgY3g9IjIwIiBjeT0iMjAiIHI9IjEiPjwvY2lyY2xlPjwvcGF0dGVybj48L2RlZnM+PHJlY3QgZmlsbD0idXJsKCNwYXR0ZXJuKSIgaGVpZ2h0PSIxMDAlIiB3aWR0aD0iMTAwJSI+PC9yZWN0Pjwvc3ZnPg==');
            opacity: 0.5;
            z-index: -1;
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

        @media (max-width: 768px) {
            .footer-top {
                padding: 50px 0 30px;
                border-top-left-radius: 30px;
            }

            .footer-title {
                margin-top: 20px;
            }
        }
        .social-links {
            position: relative;
            z-index: 9999;
            }

        .social-icon {
            position: relative;
            z-index: 9999;
            pointer-events: auto;
            cursor: pointer;
            }

        .social-icon i {
            pointer-events: none;
        }   
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript for Sidebar Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileToggle = document.querySelector('.navbar-toggler');

            // Check if sidebar state is saved in localStorage
            const sidebarCollapsed = localStorage.getItem('userSidebarCollapsed') === 'true';

            // Apply initial state
            if (sidebar && sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                if (mainContent) {
                    mainContent.classList.add('expanded');
                }
            }

            // Toggle sidebar on button click (desktop)
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    if (mainContent) {
                        mainContent.classList.toggle('expanded');
                    }

                    // Save state to localStorage
                    localStorage.setItem('userSidebarCollapsed', sidebar.classList.contains('collapsed'));
                });
            }

            // Toggle sidebar on mobile
            if (mobileToggle && sidebar) {
                mobileToggle.addEventListener('click', function() {
                    if (window.innerWidth < 992) { // Only on mobile
                        sidebar.classList.toggle('show');
                    }
                });
            }

            // Hide sidebar when clicking on a link (mobile)
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992 && sidebar) {
                        const bsCollapse = bootstrap.Collapse.getInstance(document.querySelector('.navbar-collapse'));
                        if (bsCollapse) {
                            bsCollapse.hide();
                        }
                        sidebar.classList.remove('show');
                    }
                });
            });

            // Hide sidebar when clicking outside (mobile)
            document.addEventListener('click', function(event) {
                if (sidebar && window.innerWidth < 992 &&
                    !sidebar.contains(event.target) &&
                    mobileToggle && !mobileToggle.contains(event.target) &&
                    sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    const bsCollapse = bootstrap.Collapse.getInstance(document.querySelector('.navbar-collapse'));
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });

            // Adjust on window resize
            window.addEventListener('resize', function() {
                if (sidebar && window.innerWidth >= 992) {
                    sidebar.classList.remove('show');
                    const bsCollapse = bootstrap.Collapse.getInstance(document.querySelector('.navbar-collapse'));
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });
        });
    </script>
</body>
</html>
