<?php
require_once '../auth/session.php';
require_once '../config/database.php';
requireAdmin();

// Set page title
$page_title = 'Passenger Management';

// Process status toggle
if (isset($_POST['toggle_status']) && isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);
    
    // Get current status
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user) {
        $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
        $updateStmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newStatus, $userId);
        if ($updateStmt->execute()) {
            header("Location: users.php?status_updated=success");
            exit();
        } else {
            header("Location: users.php?error=update_failed");
            exit();
        }
    }
}

// Process user deletion
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);
    
    $deleteStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $deleteStmt->bind_param("i", $userId);
    if ($deleteStmt->execute()) {
        header("Location: users.php?deleted=success");
        exit();
    } else {
        header("Location: users.php?error=delete_failed");
        exit();
    }
}

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT * FROM users";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " WHERE name LIKE ? OR email LIKE ? OR username LIKE ? OR phone LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
    $types = "ssss";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();

include 'includes/header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2"><i class="fas fa-users text-primary me-2"></i>Passenger Management</h1>
</div>

<!-- Alerts -->
<?php if (isset($_GET['status_updated'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>Success!</strong> Passenger status has been updated successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>Deleted!</strong> Passenger account has been removed.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Error!</strong> Something went wrong. Please try again.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Search Card -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="get" action="" class="row g-3 align-items-center">
            <div class="col-md-9">
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search passengers by name, email, username or phone...">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button>
                <?php if (!empty($search)): ?>
                <a href="users.php" class="btn btn-secondary"><i class="fas fa-undo"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Passengers Table -->
<div class="card shadow">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 text-primary fw-bold">Passenger Accounts</h5>
        <span class="badge bg-primary rounded-pill"><?php echo count($users); ?> Passengers</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Registered Date</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-2">
                                            <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                                        </div>
                                        <span class="fw-semibold text-gray-800"><?php echo htmlspecialchars($u['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['phone']); ?></td>
                                <td>
                                    <?php if ($u['status'] === 'active'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d M Y, h:i A', strtotime($u['created_at'])); ?></td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-2 justify-content-end">
                                        <!-- Toggle Status Form -->
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $u['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success'; ?>" title="<?php echo $u['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                <?php if ($u['status'] === 'active'): ?>
                                                    <i class="fas fa-user-slash me-1"></i> Deactivate
                                                <?php else: ?>
                                                    <i class="fas fa-user-check me-1"></i> Activate
                                                <?php endif; ?>
                                            </button>
                                        </form>

                                        <!-- Delete Trigger Button -->
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?php echo $u['id']; ?>" title="Delete Account">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteUserModal<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to permanently delete the account of <strong><?php echo htmlspecialchars($u['name']); ?></strong>?</p>
                                                    <p class="text-danger small"><i class="fas fa-info-circle me-1"></i> This action is irreversible. All of this user's booking history and tickets will also be deleted due to database integrity constraints.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancel</button>
                                                    <form method="post" action="" style="display:inline;">
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-danger"><i class="fas fa-trash-alt me-1"></i>Permanently Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-users-slash text-muted mb-3" style="font-size: 3rem;"></i>
                                    <p class="mb-0 text-muted">No passengers found match the criteria.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
