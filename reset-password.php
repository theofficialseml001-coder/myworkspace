<?php
/**
 * Reset Password
 * Allow users to reset their password with a token
 */
require_once 'portal_config.php';

$page_title = 'Reset Password';
$conn = getDBConnection();
$errors = [];
$success = false;
$token_valid = false;

$token = isset($_GET['token']) ? $_GET['token'] : '';

// Verify token
if (!empty($token)) {
    $token = mysqli_real_escape_string($conn, $token);
    $result = mysqli_query($conn, "SELECT id, full_name FROM users WHERE reset_token = '$token' AND reset_token_expiry > NOW()");
    
    if (mysqli_num_rows($result) > 0) {
        $token_valid = true;
        $user = mysqli_fetch_assoc($result);
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Verify token again
    $token_check = mysqli_real_escape_string($conn, $token);
    $user_check = mysqli_query($conn, "SELECT id FROM users WHERE reset_token = '$token_check' AND reset_token_expiry > NOW()");
    
    if (mysqli_num_rows($user_check) === 0) {
        $errors[] = "Invalid or expired reset token";
    }
    
    if (empty($errors)) {
        $hashed_password = hashPassword($password);
        $sql = "UPDATE users SET password = '$hashed_password', reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = '$token_check'";
        
        if (mysqli_query($conn, $sql)) {
            $success = true;
        } else {
            $errors[] = "Failed to reset password: " . mysqli_error($conn);
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="content-card mt-5">
                <?php if ($success): ?>
                <div class="text-center py-4">
                    <i class="bi bi-check-circle-fill text-success fs-1"></i>
                    <h3 class="mt-3">Password Reset Successful!</h3>
                    <p class="text-muted">Your password has been updated successfully.</p>
                    <a href="login.php" class="btn btn-primary mt-3">
                        <i class="bi bi-box-arrow-in-right"></i> Login Now
                    </a>
                </div>
                <?php elseif (!$token_valid): ?>
                <div class="text-center py-4">
                    <i class="bi bi-x-circle-fill text-danger fs-1"></i>
                    <h3 class="mt-3">Invalid or Expired Link</h3>
                    <p class="text-muted">This password reset link is invalid or has expired.</p>
                    <a href="forgot-password.php" class="btn btn-primary mt-3">
                        <i class="bi bi-arrow-clockwise"></i> Request New Link
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock fs-1 text-primary"></i>
                    <h3 class="mt-3">Reset Password</h3>
                    <p class="text-muted">Enter your new password below.</p>
                </div>

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

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter new password" required autofocus>
                        <small class="text-muted">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm new password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-check-circle"></i> Reset Password
                    </button>
                </form>

                <div class="text-center">
                    <a href="login.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Back to Login
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
