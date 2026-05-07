<?php
/**
 * Video Conference SFU Solution - Join Meeting Page (Public/Guest Access)
 */

require_once 'includes/config.php';

$meeting_id = sanitize($_GET['meeting'] ?? '');
$error = '';

if (empty($meeting_id)) {
    redirect('index.php');
}

$conn = getDBConnection();

// Get meeting info
$stmt = mysqli_prepare($conn, "SELECT m.*, u.full_name as host_name FROM meetings m 
    JOIN users u ON m.host_id = u.id WHERE m.meeting_id = ?");
mysqli_stmt_bind_param($stmt, "s", $meeting_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$meeting = mysqli_fetch_assoc($result);

if (!$meeting) {
    $error = 'Meeting not found';
} elseif ($meeting['status'] !== 'active') {
    $error = 'This meeting is not currently active';
}

mysqli_close($conn);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $display_name = sanitize($_POST['display_name'] ?? 'Guest');
    $password = $_POST['password'] ?? '';
    
    // Store in session for later use
    $_SESSION['guest_name'] = $display_name;
    $_SESSION['guest_password'] = $password;
    
    redirect('meeting_join.php?meeting=' . $meeting_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Meeting - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .join-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .join-card {
            max-width: 450px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="join-page">
        <div class="card join-card border-0">
            <div class="card-body p-5">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <a href="index.php" class="btn btn-primary w-100">
                        <i class="bi bi-house-door"></i> Back to Home
                    </a>
                <?php else: ?>
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-camera-video-fill text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h2 class="fw-bold mb-2">Join Meeting</h2>
                        <p class="text-muted"><?php echo htmlspecialchars($meeting['title']); ?></p>
                        <p class="small text-muted">Hosted by <?php echo htmlspecialchars($meeting['host_name']); ?></p>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="display_name" class="form-label">Your Name</label>
                            <input type="text" class="form-control form-control-lg" id="display_name" name="display_name" 
                                   placeholder="Enter your name" required autofocus>
                        </div>

                        <?php if ($meeting['password']): ?>
                        <div class="mb-3">
                            <label for="password" class="form-label">Meeting Password</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" 
                                   placeholder="Enter meeting password" required>
                        </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="audioOff" name="audio_off">
                                <label class="form-check-label" for="audio_off">
                                    Start with microphone off
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="videoOff" name="video_off" checked>
                                <label class="form-check-label" for="video_off">
                                    Start with camera off
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right"></i> Join Meeting
                        </button>

                        <div class="text-center">
                            <a href="login.php" class="text-decoration-none">Already have an account? Sign In</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
