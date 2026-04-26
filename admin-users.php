<?php
/**
 * Admin Users Management
 * Manage all users in the system
 */
require_once 'portal_config.php';
requireLogin();

if (!isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Manage Users';
$conn = getDBConnection();

// Handle delete user
if (isset($_POST['delete_user']) && verifyCSRFToken($_POST['csrf_token'])) {
    $user_id = (int)$_POST['user_id'];
    if ($user_id !== $_SESSION['user_id']) {
        $sql = "DELETE FROM users WHERE id = '$user_id'";
        mysqli_query($conn, $sql);
        setFlashMessage('success', 'User deleted successfully');
        header('Location: admin-users.php');
        exit();
    }
}

// Handle update user role
if (isset($_POST['update_role']) && verifyCSRFToken($_POST['csrf_token'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = mysqli_real_escape_string($conn, $_POST['user_type']);
    $allowed_roles = ['admin', 'instructor', 'student'];
    
    if (in_array($new_role, $allowed_roles)) {
        $sql = "UPDATE users SET user_type = '$new_role' WHERE id = '$user_id'";
        mysqli_query($conn, $sql);
        setFlashMessage('success', 'User role updated successfully');
        header('Location: admin-users.php');
        exit();
    }
}

// Get all users
$users_result = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-people"></i> User Management</h1>
    <a href="register.php" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Add New User
    </a>
</div>

<?php
$flash = getFlashMessage();
if ($flash):
?>
<div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
    <?php echo htmlspecialchars($flash['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar" style="width: 35px; height: 35px; font-size: 0.9rem;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <?php echo htmlspecialchars($user['full_name']); ?>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <select name="user_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="student" <?php echo $user['user_type'] === 'student' ? 'selected' : ''; ?>>Student</option>
                                <option value="instructor" <?php echo $user['user_type'] === 'instructor' ? 'selected' : ''; ?>>Instructor</option>
                                <option value="admin" <?php echo $user['user_type'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <input type="hidden" name="update_role" value="1">
                        </form>
                    </td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td><?php echo formatDate($user['created_at'], 'M d, Y'); ?></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="delete_user" value="1">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
