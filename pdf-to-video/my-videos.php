<?php
/**
 * My Videos Page - PDF to Video Converter
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get user's IP for filtering
$user_ip = getUserIP();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query
$where_clause = "WHERE 1=1";

if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $where_clause .= " AND (original_filename LIKE '%$search_escaped%' OR video_title LIKE '%$search_escaped%')";
}

if ($status_filter !== 'all') {
    $status_escaped = mysqli_real_escape_string($conn, $status_filter);
    $where_clause .= " AND video_status = '$status_escaped'";
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM conversions $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total_videos = $count_row['total'];
$total_pages = ceil($total_videos / $per_page);

// Get videos
$videos_sql = "SELECT * FROM conversions $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$videos_result = mysqli_query($conn, $videos_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Videos - PDF to Video Converter</title>
    
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
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house-door me-1"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="my-videos.php"><i class="bi bi-collection-play me-1"></i>My Videos</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-8">
                    <h1 class="mb-3">
                        <i class="bi bi-collection-play text-primary me-2"></i>My Videos
                    </h1>
                    <p class="text-muted">Manage and view all your converted videos</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>New Conversion
                    </a>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" placeholder="Search by filename or title..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-search me-1"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Videos Grid -->
            <?php if ($videos_result && mysqli_num_rows($videos_result) > 0): ?>
            <div class="row">
                <?php while ($video = mysqli_fetch_assoc($videos_result)): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <!-- Thumbnail/Status -->
                        <div class="position-relative" style="height: 160px; background: #000;">
                            <?php if ($video['video_status'] === 'completed' && $video['thumbnail_path'] && file_exists($video['thumbnail_path'])): ?>
                                <img src="<?php echo htmlspecialchars($video['thumbnail_path']); ?>" class="card-img-top h-100" style="object-fit: cover;" alt="Thumbnail">
                            <?php else: ?>
                                <div class="d-flex h-100 align-items-center justify-content-center">
                                    <?php if ($video['video_status'] === 'processing'): ?>
                                        <i class="bi bi-gear-wide-connected text-warning display-4 animate-spin"></i>
                                    <?php elseif ($video['video_status'] === 'failed'): ?>
                                        <i class="bi bi-x-circle-fill text-danger display-4"></i>
                                    <?php else: ?>
                                        <i class="bi bi-hourglass-split text-muted display-4"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Duration Badge -->
                            <?php if ($video['video_status'] === 'completed' && $video['duration_seconds'] > 0): ?>
                            <span class="badge bg-dark position-absolute bottom-0 end-0 m-2">
                                <?php echo formatDuration($video['duration_seconds']); ?>
                            </span>
                            <?php endif; ?>
                            
                            <!-- Status Badge -->
                            <span class="badge position-absolute top-0 start-0 m-2 bg-<?php 
                                echo $video['video_status'] === 'completed' ? 'success' : 
                                    ($video['video_status'] === 'processing' ? 'warning' : 
                                    ($video['video_status'] === 'failed' ? 'danger' : 'secondary')); 
                            ?>">
                                <?php echo ucfirst($video['video_status']); ?>
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <h6 class="card-title text-truncate" title="<?php echo htmlspecialchars($video['video_title'] ?: $video['original_filename']); ?>">
                                <?php echo htmlspecialchars($video['video_title'] ?: $video['original_filename']); ?>
                            </h6>
                            <p class="card-text small text-muted mb-2">
                                <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($video['created_at'])); ?>
                            </p>
                        </div>
                        
                        <div class="card-footer bg-white border-0">
                            <?php if ($video['video_status'] === 'completed'): ?>
                                <a href="view-video.php?id=<?php echo $video['id']; ?>" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="bi bi-play-circle me-1"></i>Watch
                                </a>
                            <?php elseif ($video['video_status'] === 'processing' || $video['video_status'] === 'pending'): ?>
                                <a href="convert.php?id=<?php echo $video['id']; ?>" class="btn btn-outline-warning btn-sm w-100">
                                    <i class="bi bi-hourglass-split me-1"></i>Processing
                                </a>
                            <?php else: ?>
                                <a href="convert.php?id=<?php echo $video['id']; ?>" class="btn btn-outline-danger btn-sm w-100">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Retry
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Video pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">Previous</a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php elseif (abs($i - $page) == 3): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-collection display-1 text-muted"></i>
                <h3 class="mt-3">No videos found</h3>
                <p class="text-muted">
                    <?php if (!empty($search) || $status_filter !== 'all'): ?>
                        Try adjusting your search or filter criteria
                    <?php else: ?>
                        Start by converting your first PDF to video!
                    <?php endif; ?>
                </p>
                <a href="index.php" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle me-2"></i>Create New Video
                </a>
            </div>
            <?php endif; ?>
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
