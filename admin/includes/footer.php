</main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <?php if (isset($initializeCharts) && $initializeCharts): ?>
    <script>
        // Function to initialize all charts
        function initializeCharts() {
            // Set Chart.js defaults
            Chart.defaults.font.family = "'Segoe UI', 'Helvetica Neue', Arial, sans-serif";
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#6c757d';
            Chart.defaults.plugins.legend.position = 'bottom';
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            Chart.defaults.plugins.tooltip.padding = 10;
            Chart.defaults.plugins.tooltip.cornerRadius = 4;
            Chart.defaults.plugins.tooltip.titleFont = { weight: 'bold' };

            // Initialize Trips Chart
            const tripsCtx = document.getElementById('tripsChart').getContext('2d');
            const tripsChart = new Chart(tripsCtx, {
                type: 'line',
                data: {
                    labels: <?php if (isset($dayLabels)) echo json_encode($dayLabels); else echo "['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']"; ?>,
                    datasets: [{
                        label: 'Trips',
                        data: <?php if (isset($tripsByDay)) echo json_encode($tripsByDay); else echo "[12, 19, 15, 17, 14, 10, 13]"; ?>,
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        borderColor: '#4e73df',
                        borderWidth: 2,
                        pointBackgroundColor: '#4e73df',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#4e73df',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Initialize Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusData = <?php
                if (isset($statusCounts)) {
                    $labels = array_map('ucfirst', array_keys($statusCounts));
                    $data = array_values($statusCounts);
                    echo '{ labels: ' . json_encode($labels) . ', data: ' . json_encode($data) . ' }';
                } else {
                    echo '{ labels: ["Scheduled", "Departed", "Completed", "Cancelled"], data: [15, 8, 12, 3] }';
                }
            ?>;

            const statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusData.labels,
                    datasets: [{
                        data: statusData.data,
                        backgroundColor: [
                            '#4e73df', // Primary - Scheduled
                            '#1cc88a', // Success - Departed
                            '#36b9cc', // Info - Completed
                            '#e74a3b'  // Danger - Cancelled
                        ],
                        borderWidth: 0,
                        hoverOffset: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    }
                }
            });

            // Initialize Issue Types Chart
            const issueTypesCtx = document.getElementById('issueTypesChart').getContext('2d');
            const issueData = <?php
                if (isset($issueTypes)) {
                    $labels = array_map(function($key) {
                        return ucfirst(str_replace('_', ' ', $key));
                    }, array_keys($issueTypes));
                    $data = array_values($issueTypes);
                    echo '{ labels: ' . json_encode($labels) . ', data: ' . json_encode($data) . ' }';
                } else {
                    echo '{ labels: ["Puncture", "Mechanical", "Fuel", "Accident", "Traffic", "Weather", "Passenger", "Other"], data: [3, 5, 2, 1, 7, 4, 3, 2] }';
                }
            ?>;

            const issueTypesChart = new Chart(issueTypesCtx, {
                type: 'bar',
                data: {
                    labels: issueData.labels,
                    datasets: [{
                        label: 'Issue Count',
                        data: issueData.data,
                        backgroundColor: [
                            '#e74a3b', // Danger
                            '#e74a3b', // Danger
                            '#e74a3b', // Danger
                            '#5a5c69', // Dark
                            '#f6c23e', // Warning
                            '#f6c23e', // Warning
                            '#36b9cc', // Info
                            '#858796'  // Secondary
                        ],
                        borderWidth: 0,
                        borderRadius: 4,
                        maxBarThickness: 25
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        // Initialize charts if they exist
        if (document.getElementById('tripsChart')) {
            initializeCharts();
        }
    </script>
    <?php endif; ?>
</body>
</html>
