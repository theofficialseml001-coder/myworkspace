<?php
/**
 * Main Index Page - PDF to Video Converter
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get settings from database
$max_file_size = getSetting($conn, 'max_file_size', 10) * 1024 * 1024; // Convert to bytes
$allowed_extensions = explode(',', getSetting($conn, 'allowed_extensions', 'pdf'));
$video_width = getSetting($conn, 'video_width', 1280);
$video_height = getSetting($conn, 'video_height', 720);

$message = '';
$message_type = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
    $upload_dir = __DIR__ . '/uploads/';
    
    // Validate file
    if ($_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        $message = 'File upload error. Please try again.';
        $message_type = 'danger';
    } elseif ($_FILES['pdf_file']['size'] > $max_file_size) {
        $message = 'File size exceeds maximum allowed size of ' . getSetting($conn, 'max_file_size', 10) . ' MB.';
        $message_type = 'danger';
    } else {
        $file_ext = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, array_map('trim', $allowed_extensions))) {
            $message = 'Invalid file type. Only PDF files are allowed.';
            $message_type = 'danger';
        } else {
            // Generate unique filename and move uploaded file
            $stored_filename = generateUniqueFilename($_FILES['pdf_file']['name']);
            $upload_path = $upload_dir . $stored_filename;
            
            if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $upload_path)) {
                // Insert into database
                $user_ip = getUserIP();
                $original_filename = mysqli_real_escape_string($conn, $_FILES['pdf_file']['name']);
                $stored_filename = mysqli_real_escape_string($conn, $stored_filename);
                $file_path = mysqli_real_escape_string($conn, $upload_path);
                
                $sql = "INSERT INTO conversions (user_ip, original_filename, stored_filename, file_path, video_status) 
                        VALUES ('$user_ip', '$original_filename', '$stored_filename', '$file_path', 'pending')";
                
                if (mysqli_query($conn, $sql)) {
                    $conversion_id = mysqli_insert_id($conn);
                    header("Location: convert.php?id=" . $conversion_id);
                    exit;
                } else {
                    $message = 'Database error. Please try again.';
                    $message_type = 'danger';
                    unlink($upload_path);
                }
            } else {
                $message = 'Failed to save file. Please try again.';
                $message_type = 'danger';
            }
        }
    }
}

// Get recent conversions
$recent_sql = "SELECT * FROM conversions WHERE video_status = 'completed' ORDER BY created_at DESC LIMIT 10";
$recent_result = mysqli_query($conn, $recent_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF to Video Converter - Create Videos from PDF Documents</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-file-earmark-play-fill me-2"></i>PDF to Video
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="bi bi-house-door me-1"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my-videos.php"><i class="bi bi-collection-play me-1"></i>My Videos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php"><i class="bi bi-gear me-1"></i>Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-5 bg-gradient-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-3">
                        <i class="bi bi-file-earmark-pdf-fill me-2"></i>Convert PDF to Video
                    </h1>
                    <p class="lead mb-4">Transform your PDF documents into engaging videos with automatic text-to-speech narration. No external APIs required - runs entirely on your server!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Upload Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <h2 class="card-title text-center mb-4">
                                <i class="bi bi-cloud-upload-alt text-primary me-2"></i>Upload Your PDF
                            </h2>
                            
                            <?php if ($message): ?>
                                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                    <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                    <?php echo htmlspecialchars($message); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <div class="mb-4">
                                    <label for="pdf_file" class="form-label fw-semibold">Select PDF File</label>
                                    <input type="file" class="form-control form-control-lg" id="pdf_file" name="pdf_file" accept=".pdf" required>
                                    <div class="form-text">Maximum file size: <?php echo getSetting($conn, 'max_file_size', 10); ?> MB</div>
                                    <div class="invalid-feedback">Please select a PDF file.</div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-magic me-2"></i>Convert to Video
                                    </button>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <div class="row text-center">
                                <div class="col-md-4 mb-3">
                                    <div class="feature-box">
                                        <i class="bi bi-shield-check text-success display-6 mb-3"></i>
                                        <h5 class="fw-semibold">100% Private</h5>
                                        <p class="text-muted small">Your files stay on your server. No external uploads.</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="feature-box">
                                        <i class="bi bi-speedometer2 text-primary display-6 mb-3"></i>
                                        <h5 class="fw-semibold">Fast Processing</h5>
                                        <p class="text-muted small">Quick conversion using server-side tools.</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="feature-box">
                                        <i class="bi bi-speaker text-info display-6 mb-3"></i>
                                        <h5 class="fw-semibold">Auto Narration</h5>
                                        <p class="text-muted small">Built-in text-to-speech for voiceovers.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Videos Section -->
    <?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
    <section class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center mb-5">
                <i class="bi bi-collection-play text-primary me-2"></i>Recent Videos
            </h2>
            <div class="row">
                <?php while ($video = mysqli_fetch_assoc($recent_result)): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <?php if ($video['thumbnail_path'] && file_exists($video['thumbnail_path'])): ?>
                            <img src="<?php echo htmlspecialchars($video['thumbnail_path']); ?>" class="card-img-top" alt="Video thumbnail" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-film text-white display-4"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title text-truncate"><?php echo htmlspecialchars($video['video_title'] ?: $video['original_filename']); ?></h5>
                            <p class="card-text text-muted small">
                                <i class="bi bi-clock me-1"></i><?php echo formatDuration($video['duration_seconds']); ?> | 
                                <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($video['created_at'])); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="view-video.php?id=<?php echo $video['id']; ?>" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-play-circle me-1"></i>Watch Video
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- How It Works Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">How It Works</h2>
            <div class="row">
                <div class="col-md-3 text-center mb-4">
                    <div class="step-circle bg-primary text-white mx-auto mb-3">1</div>
                    <h5 class="fw-semibold">Upload PDF</h5>
                    <p class="text-muted small">Select your PDF document</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="step-circle bg-primary text-white mx-auto mb-3">2</div>
                    <h5 class="fw-semibold">Extract Text</h5>
                    <p class="text-muted small">We extract text from your PDF</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="step-circle bg-primary text-white mx-auto mb-3">3</div>
                    <h5 class="fw-semibold">Generate Voice</h5>
                    <p class="text-muted small">Create audio narration</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="step-circle bg-primary text-white mx-auto mb-3">4</div>
                    <h5 class="fw-semibold">Create Video</h5>
                    <p class="text-muted small">Combine slides with audio</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> PDF to Video Converter. All rights reserved.</p>
            <p class="small text-muted mt-2">Powered by FFmpeg, eSpeak, and PHP</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
