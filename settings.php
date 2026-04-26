<?php
/**
 * Settings
 * User settings page for profile customization
 */
require_once 'portal_config.php';
requireLogin();

$page_title = 'Settings';
$conn = getDBConnection();
$errors = [];
$success = false;
$user_id = $_SESSION['user_id'];

// Get user data
$user = getUserData($user_id);

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'])) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_notifications') {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $course_updates = isset($_POST['course_updates']) ? 1 : 0;
        $message_alerts = isset($_POST['message_alerts']) ? 1 : 0;
        
        $sql = "UPDATE users SET email_notifications = '$email_notifications', 
                course_updates = '$course_updates', message_alerts = '$message_alerts' 
                WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $sql)) {
            setFlashMessage('success', 'Notification settings updated successfully!');
            header('Location: settings.php');
            exit();
        } else {
            $errors[] = "Failed to update settings: " . mysqli_error($conn);
        }
    } elseif ($action === 'update_privacy') {
        $profile_visibility = $_POST['profile_visibility'] ?? 'public';
        $show_email = isset($_POST['show_email']) ? 1 : 0;
        
        $sql = "UPDATE users SET profile_visibility = '$profile_visibility', show_email = '$show_email' 
                WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $sql)) {
            setFlashMessage('success', 'Privacy settings updated successfully!');
            header('Location: settings.php');
            exit();
        } else {
            $errors[] = "Failed to update settings: " . mysqli_error($conn);
        }
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-gear"></i> Settings</h1>
    <a href="profile.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Profile
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

<?php if (!empty($errors)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Notification Settings -->
    <div class="col-md-6 mb-4">
        <div class="content-card h-100">
            <h5><i class="bi bi-bell"></i> Notification Preferences</h5>
            <hr>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="update_notifications">
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications"
                           <?php echo $user['email_notifications'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="email_notifications">
                        Email Notifications
                    </label>
                    <small class="text-muted d-block">Receive email updates about course activities</small>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="course_updates" name="course_updates"
                           <?php echo $user['course_updates'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="course_updates">
                        Course Updates
                    </label>
                    <small class="text-muted d-block">Get notified about new materials and announcements</small>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="message_alerts" name="message_alerts"
                           <?php echo $user['message_alerts'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="message_alerts">
                        Message Alerts
                    </label>
                    <small class="text-muted d-block">Receive notifications for new messages</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Notification Settings
                </button>
            </form>
        </div>
    </div>
    
    <!-- Privacy Settings -->
    <div class="col-md-6 mb-4">
        <div class="content-card h-100">
            <h5><i class="bi bi-shield-lock"></i> Privacy Settings</h5>
            <hr>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="update_privacy">
                
                <div class="mb-3">
                    <label for="profile_visibility" class="form-label">Profile Visibility</label>
                    <select class="form-select" id="profile_visibility" name="profile_visibility">
                        <option value="public" <?php echo ($user['profile_visibility'] ?? 'public') === 'public' ? 'selected' : ''; ?>>Public - Everyone can see your profile</option>
                        <option value="course" <?php echo ($user['profile_visibility'] ?? 'public') === 'course' ? 'selected' : ''; ?>>Course Members Only</option>
                        <option value="private" <?php echo ($user['profile_visibility'] ?? 'public') === 'private' ? 'selected' : ''; ?>>Private - Only you can see your profile</option>
                    </select>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="show_email" name="show_email"
                           <?php echo $user['show_email'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="show_email">
                        Show Email Address
                    </label>
                    <small class="text-muted d-block">Allow others to see your email address</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Privacy Settings
                </button>
            </form>
        </div>
    </div>
    
    <!-- Account Info -->
    <div class="col-md-12 mb-4">
        <div class="content-card">
            <h5><i class="bi bi-info-circle"></i> Account Information</h5>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted d-block">Member Since</small>
                    <strong><?php echo formatDate($user['created_at'], 'F d, Y'); ?></strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Account Type</small>
                    <strong><?php echo ucfirst($user['user_type']); ?></strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Last Login</small>
                    <strong><?php echo $user['last_login'] ? formatDate($user['last_login'], 'M d, Y h:i A') : 'N/A'; ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
