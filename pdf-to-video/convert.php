<?php
/**
 * Conversion Processing Page - PDF to Video Converter
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get conversion ID
$conversion_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($conversion_id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch conversion details
$sql = "SELECT * FROM conversions WHERE id = $conversion_id";
$result = mysqli_query($conn, $sql);
$conversion = mysqli_fetch_assoc($result);

if (!$conversion) {
    header("Location: index.php");
    exit;
}

// Check if processing should start
$status = $conversion['video_status'];
$processing_started = false;

if ($status === 'pending') {
    // Update status to processing
    $update_sql = "UPDATE conversions SET video_status = 'processing' WHERE id = $conversion_id";
    mysqli_query($conn, $update_sql);
    $status = 'processing';
    $processing_started = true;
    
    // Start background processing
    $process_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/process-conversion.php?id=' . $conversion_id;
    
    // Use shell_exec to trigger background process (non-blocking)
    if (function_exists('shell_exec')) {
        shell_exec("curl -s '{$process_url}' > /dev/null 2>&1 &");
    } else {
        // Fallback: redirect to processing script directly
        header("Location: process-conversion.php?id=" . $conversion_id);
        exit;
    }
}

// Refresh to get updated status
header("Refresh: 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Converting PDF to Video - PDF to Video Converter</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .progress-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .status-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .step-item {
            padding: 15px;
            border-left: 3px solid #dee2e6;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        .step-item.active {
            border-left-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .step-item.completed {
            border-left-color: #198754;
            background-color: #d1e7dd;
        }
        .step-item.failed {
            border-left-color: #dc3545;
            background-color: #f8d7da;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-file-earmark-play-fill me-2"></i>PDF to Video
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house-door me-1"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my-videos.php"><i class="bi bi-collection-play me-1"></i>My Videos</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5 text-center">
                            <h2 class="card-title mb-4">
                                <i class="bi bi-gear-wide-connected text-primary me-2"></i>
                                Converting Your PDF
                            </h2>
                            
                            <p class="lead text-muted mb-4">
                                <?php echo htmlspecialchars($conversion['original_filename']); ?>
                            </p>
                            
                            <!-- Status Icon -->
                            <div class="status-icon">
                                <?php if ($status === 'completed'): ?>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                <?php elseif ($status === 'failed'): ?>
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                <?php else: ?>
                                    <i class="bi bi-hourglass-split text-primary animate-pulse"></i>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Status Text -->
                            <h4 class="mb-3">
                                <?php if ($status === 'completed'): ?>
                                    <span class="text-success">Conversion Complete!</span>
                                <?php elseif ($status === 'failed'): ?>
                                    <span class="text-danger">Conversion Failed</span>
                                <?php else: ?>
                                    <span class="text-primary">Processing Your Video...</span>
                                <?php endif; ?>
                            </h4>
                            
                            <?php if ($status === 'failed' && $conversion['error_message']): ?>
                                <div class="alert alert-danger text-start">
                                    <strong>Error:</strong> <?php echo htmlspecialchars($conversion['error_message']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Progress Steps -->
                            <div class="text-start mt-4">
                                <div class="step-item <?php echo ($status !== 'pending') ? 'completed' : ($processing_started ? 'active' : ''); ?>">
                                    <i class="bi bi-cloud-upload me-2"></i>
                                    <strong>Step 1:</strong> File Uploaded
                                </div>
                                <div class="step-item <?php echo ($status === 'processing' || $status === 'completed') ? 'active' : ''; ?>">
                                    <i class="bi bi-file-text me-2"></i>
                                    <strong>Step 2:</strong> Extracting Text from PDF
                                </div>
                                <div class="step-item <?php echo ($status === 'processing' || $status === 'completed') ? 'active' : ''; ?>">
                                    <i class="bi bi-microphone me-2"></i>
                                    <strong>Step 3:</strong> Generating Audio Narration
                                </div>
                                <div class="step-item <?php echo ($status === 'processing' || $status === 'completed') ? 'active' : ''; ?>">
                                    <i class="bi bi-images me-2"></i>
                                    <strong>Step 4:</strong> Creating Slide Images
                                </div>
                                <div class="step-item <?php echo ($status === 'processing' || $status === 'completed') ? 'active' : ''; ?>">
                                    <i class="bi bi-film me-2"></i>
                                    <strong>Step 5:</strong> Rendering Final Video
                                </div>
                                <div class="step-item <?php echo ($status === 'completed') ? 'completed' : ''; ?>">
                                    <i class="bi bi-check-all me-2"></i>
                                    <strong>Step 6:</strong> Video Ready!
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="mt-5">
                                <?php if ($status === 'completed'): ?>
                                    <a href="view-video.php?id=<?php echo $conversion['id']; ?>" class="btn btn-success btn-lg me-2">
                                        <i class="bi bi-play-circle me-2"></i>Watch Video
                                    </a>
                                    <a href="index.php" class="btn btn-outline-primary btn-lg">
                                        <i class="bi bi-plus-circle me-2"></i>Convert Another
                                    </a>
                                <?php elseif ($status === 'failed'): ?>
                                    <a href="index.php" class="btn btn-primary btn-lg">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Try Again
                                    </a>
                                <?php else: ?>
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-3 text-muted">This may take a few minutes. Please wait...</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> PDF to Video Converter. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-refresh if still processing
        <?php if ($status === 'processing'): ?>
        setTimeout(function() {
            location.reload();
        }, 5000); // Refresh every 5 seconds
        <?php endif; ?>
    </script>
</body>
</html>
