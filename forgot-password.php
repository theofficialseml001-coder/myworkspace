<?php
/**
 * Forgot Password
 * Allow users to reset their password via email
 */
require_once 'portal_config.php';

$page_title = 'Forgot Password';
$conn = getDBConnection();
$errors = [];
$success = false;
$email_sent = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'])) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($errors)) {
        $email = mysqli_real_escape_string($conn, $email);
        $result = mysqli_query($conn, "SELECT id, full_name FROM users WHERE email = '$email'");
        
        if (mysqli_num_rows($result) > 0) {
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $sql = "UPDATE users SET reset_token = '$reset_token', reset_token_expiry = '$reset_expiry' WHERE email = '$email'";
            mysqli_query($conn, $sql);
            
            // In a real application, send email with reset link
            // For demo purposes, we'll show the token
            $reset_link = SITE_URL . '/reset-password.php?token=' . $reset_token;
            $email_sent = true;
            $demo_token = $reset_link;
        } else {
            // Don't reveal if email exists or not (security best practice)
            $email_sent = true;
            $demo_token = "If the email exists, a reset link would be sent.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="content-card mt-5">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock fs-1 text-primary"></i>
                    <h3 class="mt-3">Forgot Password?</h3>
                    <p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                <?php if ($email_sent): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    <strong>Password reset link generated!</strong>
                    <p class="mb-0 mt-2 small">
                        In production, this would be sent via email.<br>
                        <strong>Demo Link:</strong> <a href="<?php echo htmlspecialchars($demo_token); ?>"><?php echo htmlspecialchars($demo_token); ?></a>
                    </p>
                </div>
                <div class="text-center mt-3">
                    <a href="login.php" class="btn btn-primary">Back to Login</a>
                </div>
                <?php else: ?>
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
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               placeholder="Enter your email" required autofocus>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-send"></i> Send Reset Link
                    </button>
                </form>

                <div class="text-center">
                    <a href="login.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
