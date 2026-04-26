<?php
/**
 * Profile - Online Class Portal
 * View and edit user profile
 */

require_once 'portal_config.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get user data
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    verifyCSRFToken($_POST['csrf_token']);
    
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $phone = sanitizeInput($_POST['phone']);
    $bio = sanitizeInput($_POST['bio']);
    
    if (!empty($first_name) && !empty($last_name)) {
        $first_name = mysqli_real_escape_string($conn, $first_name);
        $last_name = mysqli_real_escape_string($conn, $last_name);
        $phone = mysqli_real_escape_string($conn, $phone);
        $bio = mysqli_real_escape_string($conn, $bio);
        
        $sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', 
                phone = '$phone', bio = '$bio' WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $_SESSION['success_message'] = 'Profile updated successfully!';
            header('Location: profile.php');
            exit;
        } else {
            $_SESSION['error_message'] = 'Failed to update profile.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    verifyCSRFToken($_POST['csrf_token']);
    
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 8) {
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET password = '$hashed' WHERE id = '$user_id'";
                    
                    if (mysqli_query($conn, $sql)) {
                        $_SESSION['success_message'] = 'Password changed successfully!';
                        header('Location: profile.php');
                        exit;
                    } else {
                        $_SESSION['error_message'] = 'Failed to change password.';
                    }
                } else {
                    $_SESSION['error_message'] = 'Password must be at least 8 characters.';
                }
            } else {
                $_SESSION['error_message'] = 'New passwords do not match.';
            }
        } else {
            $_SESSION['error_message'] = 'Current password is incorrect.';
        }
    }
}

$page_title = 'My Profile';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-person-circle me-2"></i>My Profile</h2>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4">
            <div class="content-card text-center">
                <div class="user-avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                </div>
                <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                <p class="text-muted"><?php echo ucfirst($user['user_type']); ?></p>
                <p class="small text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                <hr>
                <p class="small mb-1"><i class="bi bi-calendar me-1"></i>Joined: <?php echo formatDate($user['created_at'], 'F d, Y'); ?></p>
                <?php if ($user['phone']): ?>
                    <p class="small mb-1"><i class="bi bi-phone me-1"></i><?php echo htmlspecialchars($user['phone']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Edit Profile -->
            <div class="content-card mb-4">
                <h5><i class="bi bi-pencil-square me-2"></i>Edit Profile</h5>
                <form method="POST" action="">
                    <?php generateCSRFToken(); ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name:</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name:</label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email:</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <small class="text-muted">Contact admin to change email</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone:</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bio:</label>
                        <textarea name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="content-card">
                <h5><i class="bi bi-key me-2"></i>Change Password</h5>
                <form method="POST" action="">
                    <?php generateCSRFToken(); ?>
                    <div class="mb-3">
                        <label class="form-label">Current Password:</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password:</label>
                            <input type="password" name="new_password" class="form-control" minlength="8" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm New Password:</label>
                            <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-warning">
                        <i class="bi bi-key-fill me-1"></i>Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
