<?php
require_once '../auth/session.php';
require_once '../config/database.php';
requireAdmin();

// Start output buffering
ob_start();

// Process issue status update
if (isset($_POST['update_issue']) && isset($_POST['issue_id'])) {
    $issueId = $_POST['issue_id'];
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $adminRemarks = isset($_POST['admin_remarks']) ? $_POST['admin_remarks'] : '';
    
    // Update issue status
    $adminRemarks = $conn->real_escape_string($adminRemarks);
    $updateSql = "UPDATE issue_reports SET status = '$status', admin_remarks = '$adminRemarks' WHERE id = $issueId";
    
    if ($conn->query($updateSql)) {
        header("Location: issues.php?updated=success");
        exit();
    } else {
        header("Location: issues.php?error=update_failed");
        exit();
    }
}

include 'includes/header.php';

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$driverId = isset($_GET['driver_id']) ? $_GET['driver_id'] : 'all';
$issueType = isset($_GET['issue_type']) ? $_GET['issue_type'] : 'all';

// Get all drivers for filter dropdown
$driversSql = "SELECT * FROM drivers ORDER BY name ASC";
$driversResult = $conn->query($driversSql);
$drivers = [];

if ($driversResult->num_rows > 0) {
    while ($row = $driversResult->fetch_assoc()) {
        $drivers[] = $row;
    }
}

// Build the query for issue reports
$issuesSql = "SELECT ir.*, s.id as schedule_id, d.name as driver_name, r.route_name, b.bus_number
                FROM issue_reports ir
                JOIN schedules s ON ir.schedule_id = s.id
                JOIN drivers d ON ir.driver_id = d.id
                JOIN routes r ON s.route_id = r.id
                JOIN buses b ON s.bus_id = b.id
                WHERE 1=1";

if ($status != 'all') {
    $issuesSql .= " AND ir.status = '$status'";
}

if ($driverId != 'all') {
    $issuesSql .= " AND ir.driver_id = $driverId";
}

if ($issueType != 'all') {
    $issuesSql .= " AND ir.issue_type = '$issueType'";
}

$issuesSql .= " ORDER BY ir.reported_at DESC";
$issuesResult = $conn->query($issuesSql);
$issues = [];

if ($issuesResult->num_rows > 0) {
    while ($row = $issuesResult->fetch_assoc()) {
        $issues[] = $row;
    }
}

// Get issue types for filter dropdown
$issueTypes = [
    'puncture' => 'Tire Puncture',
    'mechanical' => 'Mechanical Failure',
    'fuel' => 'Fuel Issues',
    'accident' => 'Accident/Collision',
    'traffic' => 'Heavy Traffic',
    'weather' => 'Weather Conditions',
    'passenger' => 'Passenger-Related Issue',
    'other' => 'Other'
];

// Get statistics
$totalIssues = count($issues);
$pendingIssues = 0;
$acknowledgedIssues = 0;
$resolvedIssues = 0;

foreach ($issues as $issue) {
    if ($issue['status'] == 'pending') {
        $pendingIssues++;
    } else if ($issue['status'] == 'acknowledged') {
        $acknowledgedIssues++;
    } else if ($issue['status'] == 'resolved') {
        $resolvedIssues++;
    }
}

$pendingPercentage = $totalIssues > 0 ? round(($pendingIssues / $totalIssues) * 100) : 0;
$acknowledgedPercentage = $totalIssues > 0 ? round(($acknowledgedIssues / $totalIssues) * 100) : 0;
$resolvedPercentage = $totalIssues > 0 ? round(($resolvedIssues / $totalIssues) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Reports - Bus Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5>Bus Management System</h5>
                        <p class="text-muted">Admin Panel</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="drivers.php">
                                <i class="fas fa-user"></i> Drivers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="buses.php">
                                <i class="fas fa-bus"></i> Buses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="routes.php">
                                <i class="fas fa-route"></i> Routes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="schedules.php">
                                <i class="fas fa-calendar-alt"></i> Schedules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="issues.php">
                                <i class="fas fa-exclamation-triangle"></i> Issues
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link text-danger" href="../auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Issue Reports</h1>
                </div>

                <?php if (isset($_GET['updated']) && $_GET['updated'] == 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> The issue status has been updated.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] == 'update_failed'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> Failed to update the issue status. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Filter Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" action="" class="row g-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All</option>
                                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="acknowledged" <?php echo $status == 'acknowledged' ? 'selected' : ''; ?>>Acknowledged</option>
                                    <option value="resolved" <?php echo $status == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="driver_id" class="form-label">Driver</label>
                                <select class="form-select" id="driver_id" name="driver_id">
                                    <option value="all" <?php echo $driverId == 'all' ? 'selected' : ''; ?>>All Drivers</option>
                                    <?php foreach ($drivers as $driver): ?>
                                        <option value="<?php echo $driver['id']; ?>" <?php echo $driverId == $driver['id'] ? 'selected' : ''; ?>>
                                            <?php echo $driver['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="issue_type" class="form-label">Issue Type</label>
                                <select class="form-select" id="issue_type" name="issue_type">
                                    <option value="all" <?php echo $issueType == 'all' ? 'selected' : ''; ?>>All Types</option>
                                    <?php foreach ($issueTypes as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $issueType == $key ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="issues.php" class="btn btn-secondary ms-2">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Issues</h5>
                                <h2 class="card-text"><?php echo $totalIssues; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Pending</h5>
                                <h2 class="card-text"><?php echo $pendingIssues; ?> (<?php echo $pendingPercentage; ?>%)</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center">
                                <h5 class="card-title">Acknowledged</h5>
                                <h2 class="card-text"><?php echo $acknowledgedIssues; ?> (<?php echo $acknowledgedPercentage; ?>%)</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Resolved</h5>
                                <h2 class="card-text"><?php echo $resolvedIssues; ?> (<?php echo $resolvedPercentage; ?>%)</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Issues Table -->
                <div class="card">
                    <div class="card-header">
                        <h5>Issue Reports</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Reported At</th>
                                        <th>Driver</th>
                                        <th>Bus</th>
                                        <th>Route</th>
                                        <th>Issue Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($issues) > 0): ?>
                                        <?php foreach ($issues as $issue): ?>
                                            <tr>
                                                <td><?php echo $issue['id']; ?></td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($issue['reported_at'])); ?></td>
                                                <td><?php echo $issue['driver_name']; ?></td>
                                                <td><?php echo $issue['bus_number']; ?></td>
                                                <td><?php echo $issue['route_name']; ?></td>
                                                <td>
                                                    <?php 
                                                    $issueTypeLabel = isset($issueTypes[$issue['issue_type']]) ? $issueTypes[$issue['issue_type']] : $issue['issue_type'];
                                                    $badgeClass = '';
                                                    
                                                    switch ($issue['issue_type']) {
                                                        case 'puncture':
                                                        case 'mechanical':
                                                        case 'fuel':
                                                            $badgeClass = 'bg-danger';
                                                            break;
                                                        case 'accident':
                                                            $badgeClass = 'bg-dark';
                                                            break;
                                                        case 'traffic':
                                                        case 'weather':
                                                            $badgeClass = 'bg-warning text-dark';
                                                            break;
                                                        case 'passenger':
                                                            $badgeClass = 'bg-info text-dark';
                                                            break;
                                                        default:
                                                            $badgeClass = 'bg-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $issueTypeLabel; ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = '';
                                                    switch ($issue['status']) {
                                                        case 'pending':
                                                            $statusClass = 'bg-danger';
                                                            break;
                                                        case 'acknowledged':
                                                            $statusClass = 'bg-warning text-dark';
                                                            break;
                                                        case 'resolved':
                                                            $statusClass = 'bg-success';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($issue['status']); ?></span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewIssueModal<?php echo $issue['id']; ?>">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                            
                                            <!-- View Issue Modal -->
                                            <div class="modal fade" id="viewIssueModal<?php echo $issue['id']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Issue Report #<?php echo $issue['id']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <h6>Issue Details</h6>
                                                                    <p><strong>Reported At:</strong> <?php echo date('M d, Y h:i A', strtotime($issue['reported_at'])); ?></p>
                                                                    <p><strong>Issue Type:</strong> <span class="badge <?php echo $badgeClass; ?>"><?php echo $issueTypeLabel; ?></span></p>
                                                                    <p><strong>Current Status:</strong> <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($issue['status']); ?></span></p>
                                                                    <p><strong>Location:</strong> <?php echo $issue['location'] ? $issue['location'] : 'Not specified'; ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6>Trip Details</h6>
                                                                    <p><strong>Schedule ID:</strong> <?php echo $issue['schedule_id']; ?></p>
                                                                    <p><strong>Driver:</strong> <?php echo $issue['driver_name']; ?></p>
                                                                    <p><strong>Bus:</strong> <?php echo $issue['bus_number']; ?></p>
                                                                    <p><strong>Route:</strong> <?php echo $issue['route_name']; ?></p>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <h6>Issue Description</h6>
                                                                <div class="p-3 bg-light rounded">
                                                                    <?php echo nl2br($issue['issue_description']); ?>
                                                                </div>
                                                            </div>
                                                            
                                                            <?php if ($issue['admin_remarks']): ?>
                                                            <div class="mb-3">
                                                                <h6>Admin Remarks</h6>
                                                                <div class="p-3 bg-light rounded">
                                                                    <?php echo nl2br($issue['admin_remarks']); ?>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                            
                                                            <form method="post" action="">
                                                                <input type="hidden" name="issue_id" value="<?php echo $issue['id']; ?>">
                                                                
                                                                <div class="mb-3">
                                                                    <label for="status<?php echo $issue['id']; ?>" class="form-label">Update Status</label>
                                                                    <select class="form-select" id="status<?php echo $issue['id']; ?>" name="status">
                                                                        <option value="pending" <?php echo $issue['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                        <option value="acknowledged" <?php echo $issue['status'] == 'acknowledged' ? 'selected' : ''; ?>>Acknowledged</option>
                                                                        <option value="resolved" <?php echo $issue['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                                                    </select>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="admin_remarks<?php echo $issue['id']; ?>" class="form-label">Admin Remarks</label>
                                                                    <textarea class="form-control" id="admin_remarks<?php echo $issue['id']; ?>" name="admin_remarks" rows="3"><?php echo $issue['admin_remarks']; ?></textarea>
                                                                </div>
                                                                
                                                                <div class="d-grid">
                                                                    <button type="submit" name="update_issue" class="btn btn-primary">Update Issue</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No issue reports found</td>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
