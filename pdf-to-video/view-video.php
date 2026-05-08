<?php
/**
 * View Video Page - PDF to Video Converter
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get video ID
$video_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($video_id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch video details
$sql = "SELECT * FROM conversions WHERE id = $video_id AND video_status = 'completed'";
$result = mysqli_query($conn, $sql);
$video = mysqli_fetch_assoc($result);

if (!$video || !file_exists($video['video_path'])) {
    header("Location: index.php");
    exit;
}

// Get related videos
$related_sql = "SELECT * FROM conversions WHERE video_status = 'completed' AND id != $video_id ORDER BY created_at DESC LIMIT 4";
$related_result = mysqli_query($conn, $related_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($video['video_title']); ?> - PDF to Video Converter</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .video-player-container {
            background: #000;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .video-info {
            padding: 30px;
            background: #fff;
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

    <!-- Video Player Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-9">
                    <!-- Video Player -->
                    <div class="video-player-container mb-4">
                        <video class="w-100" controls autoplay>
                            <source src="<?php echo htmlspecialchars($video['video_path']); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    
                    <!-- Video Information -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body video-info">
                            <h1 class="h3 mb-3"><?php echo htmlspecialchars($video['video_title']); ?></h1>
                            
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-clock me-2"></i>Duration
                                    </p>
                                    <p class="fw-semibold"><?php echo formatDuration($video['duration_seconds']); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-calendar me-2"></i>Created
                                    </p>
                                    <p class="fw-semibold"><?php echo date('F d, Y', strtotime($video['created_at'])); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-file-earmark-pdf me-2"></i>Original File
                                    </p>
                                    <p class="fw-semibold text-truncate"><?php echo htmlspecialchars($video['original_filename']); ?></p>
                                </div>
                            </div>
                            
                            <?php if ($video['script_content']): ?>
                            <hr>
                            <h5 class="mb-3">
                                <i class="bi bi-file-text me-2"></i>Extracted Content
                            </h5>
                            <div class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                                <pre class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars(substr($video['script_content'], 0, 5000)); ?><?php echo strlen($video['script_content']) > 5000 ? '...' : ''; ?></pre>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Action Buttons -->
                            <div class="mt-4">
                                <a href="<?php echo htmlspecialchars($video['video_path']); ?>" download class="btn btn-primary me-2">
                                    <i class="bi bi-download me-2"></i>Download Video
                                </a>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar - Related Videos -->
                <div class="col-lg-3">
                    <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-collection-play text-primary me-2"></i>Related Videos
                            </h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if ($related_result && mysqli_num_rows($related_result) > 0): ?>
                                <?php while ($related = mysqli_fetch_assoc($related_result)): ?>
                                <a href="view-video.php?id=<?php echo $related['id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 text-truncate"><?php echo htmlspecialchars($related['video_title']); ?></h6>
                                        <small><?php echo formatDuration($related['duration_seconds']); ?></small>
                                    </div>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($related['created_at'])); ?></small>
                                </a>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="list-group-item text-muted">No other videos yet</div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="my-videos.php" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-grid me-1"></i>View All Videos
                            </a>
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
</body>
</html>
