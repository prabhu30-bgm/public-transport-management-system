</main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Initialize Bootstrap components (except modals) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all tooltips
            var tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(function(tooltip) {
                new bootstrap.Tooltip(tooltip);
            });

            // Initialize all popovers
            var popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
            popovers.forEach(function(popover) {
                new bootstrap.Popover(popover);
            });
        });
    </script>

    <!-- Custom JavaScript for Sidebar Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileToggle = document.querySelector('.navbar-toggler');

            // Check if sidebar state is saved in localStorage
            const sidebarCollapsed = localStorage.getItem('driverSidebarCollapsed') === 'true';

            // Apply initial state
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            }

            // Toggle sidebar on button click (desktop)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');

                    // Save state to localStorage
                    localStorage.setItem('driverSidebarCollapsed', sidebar.classList.contains('collapsed'));
                });
            }

            // Toggle sidebar on mobile
            if (mobileToggle) {
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
                    if (window.innerWidth < 992) {
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
                if (window.innerWidth < 992 &&
                    !sidebar.contains(event.target) &&
                    !mobileToggle.contains(event.target) &&
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
                if (window.innerWidth >= 992) {
                    sidebar.classList.remove('show');
                    const bsCollapse = bootstrap.Collapse.getInstance(document.querySelector('.navbar-collapse'));
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });
        });
    </script>

    <!-- Real-time clock script with animation -->
    <script>
        function updateClock() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const formattedHours = (hours % 12) || 12;
            const formattedTime = `${formattedHours}:${minutes}:${seconds} ${ampm}`;

            // Format date for sidebar
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const dayName = days[now.getDay()];
            const monthName = months[now.getMonth()];
            const date = now.getDate();
            const year = now.getFullYear();
            const formattedDate = `${dayName}, ${monthName} ${date}, ${year}`;

            // Update navbar clock
            const clockElement = document.getElementById('currentTime');
            if (clockElement) {
                // Add animation when seconds change
                if (clockElement.textContent !== formattedTime) {
                    clockElement.classList.add('time-update');
                    setTimeout(() => {
                        clockElement.classList.remove('time-update');
                    }, 500);
                }
                clockElement.textContent = formattedTime;
            }

            // Update sidebar date and time
            const sidebarDateElement = document.getElementById('sidebarDate');
            if (sidebarDateElement) {
                sidebarDateElement.textContent = formattedDate;
            }

            const sidebarTimeElement = document.getElementById('sidebarTime');
            if (sidebarTimeElement) {
                sidebarTimeElement.textContent = formattedTime;
            }
        }

        // Update clock every second
        setInterval(updateClock, 1000);

        // Initial call
        document.addEventListener('DOMContentLoaded', function() {
            updateClock();

            // Auto-dismiss alerts after 10 seconds
            const alerts = document.querySelectorAll('.alert:not(.alert-info)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const closeButton = alert.querySelector('.btn-close');
                    if (closeButton) {
                        closeButton.click();
                    }
                }, 10000);
            });
        });
    </script>

    <style>
        /* Time update animation */
        @keyframes timeUpdate {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); color: var(--primary); }
            100% { transform: scale(1); }
        }

        .time-update {
            animation: timeUpdate 0.5s ease;
        }
    </style>
</body>
</html>
