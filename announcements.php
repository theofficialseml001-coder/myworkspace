<?php
/**
 * Announcements - Online Class Portal
 * View all announcements (global and course-specific)
 */

require_once 'portal_config.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$conn = getDBConnection();

// Handle posting announcement (instructors/admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_announcement'])) {
    verifyCSRFToken($_POST['csrf_token']);
    
    if ($user_type === 'admin' || $user_type === 'instructor') {
        $title = sanitizeInput($_POST['title']);
        $message = sanitizeInput($_POST['message']);
        $course_id = isset($_POST['course_id']) && $_POST['course_id'] > 0 ? (int)$_POST['course_id'] : null;
        $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
        
        if (!empty($title) && !empty($message)) {
            $title = mysqli_real_escape_string($conn, $title);
            $message = mysqli_real_escape_string($conn, $message);
            
            $course_clause = $course_id ? "'$course_id'" : "NULL";
            
            $sql = "INSERT INTO announcements (posted_by, course_id, title, message, is_pinned, created_at) 
                    VALUES ('$user_id', $course_clause, '$title', '$message', '$is_pinned', NOW())";
            
            if (mysqli_query($conn, $sql)) {
                $_SESSION['success_message'] = 'Announcement posted successfully!';
            } else {
                $_SESSION['error_message'] = 'Failed to post announcement.';
            }
            header('Location: announcements.php');
            exit;
        }
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$course_filter = isset($_GET['course']) ? (int)$_GET['course'] : 0;

// Build query based on user type and filters
$where_clauses = [];

// Course access restriction
if ($user_type === 'student') {
    $where_clauses[] = "(a.course_id IS NULL OR a.course_id IN (
        SELECT course_id FROM enrollments WHERE student_id = '$user_id' AND status = 'enrolled'
    ))";
} elseif ($user_type === 'instructor') {
    $where_clauses[] = "(a.course_id IS NULL OR a.course_id IN (
        SELECT id FROM courses WHERE instructor_id = '$user_id'
    ))";
}

// Apply course filter
if ($course_filter > 0) {
    $where_clauses[] = "a.course_id = '$course_filter'";
} elseif ($filter === 'global') {
    $where_clauses[] = "a.course_id IS NULL";
} elseif ($filter === 'course') {
    $where_clauses[] = "a.course_id IS NOT NULL";
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$sql = "SELECT a.*, u.first_name, u.last_name, c.course_name, c.course_code
        FROM announcements a
        INNER JOIN users u ON a.posted_by = u.id
        LEFT JOIN courses c ON a.course_id = c.id
        $where_sql
        ORDER BY a.is_pinned DESC, a.created_at DESC";

$result = mysqli_query($conn, $sql);
$announcements = [];
while ($row = mysqli_fetch_assoc($result)) {
    $announcements[] = $row;
}

// Get user's courses for filter dropdown
$courses = [];
if ($user_type !== 'admin') {
    if ($user_type === 'student') {
        $sql = "SELECT c.id, c.course_name, c.course_code 
                FROM courses c
                INNER JOIN enrollments e ON c.id = e.course_id
                WHERE e.student_id = '$user_id' AND e.status = 'enrolled'
                ORDER BY c.course_name";
    } else {
        $sql = "SELECT id, course_name, course_code FROM courses 
                WHERE instructor_id = '$user_id' 
                ORDER BY course_name";
    }
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}

$page_title = 'Announcements';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-megaphone me-2"></i>Announcements</h2>
                <?php if ($user_type === 'admin' || $user_type === 'instructor'): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postModal">
                        <i class="bi bi-plus-circle me-1"></i>Post Announcement
                    </button>
                <?php endif; ?>
            </div>
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

    <!-- Filters -->
    <div class="content-card mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Filter by Type:</label>
                <select name="filter" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Announcements</option>
                    <option value="global" <?php echo $filter === 'global' ? 'selected' : ''; ?>>Global Only</option>
                    <option value="course" <?php echo $filter === 'course' ? 'selected' : ''; ?>>Course Specific</option>
                </select>
            </div>
            <?php if (!empty($courses)): ?>
                <div class="col-md-3">
                    <label class="form-label">Filter by Course:</label>
                    <select name="course" class="form-select" onchange="this.form.submit()">
                        <option value="0">All Courses</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $course_filter == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Announcements List -->
    <?php if (empty($announcements)): ?>
        <div class="content-card text-center py-5">
            <i class="bi bi-megaphone display-4 text-muted mb-3"></i>
            <h4>No announcements found</h4>
            <p class="text-muted">There are no announcements matching your criteria.</p>
        </div>
    <?php else: ?>
        <?php foreach ($announcements as $announcement): ?>
            <div class="content-card mb-3">
                <div class="announcement-item <?php echo $announcement['is_pinned'] ? 'pinned' : ''; ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="mb-1">
                                <?php if ($announcement['is_pinned']): ?>
                                    <i class="bi bi-pin-fill text-warning me-1"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($announcement['title']); ?>
                            </h5>
                            <?php if ($announcement['course_name']): ?>
                                <span class="badge bg-info">
                                    <i class="bi bi-book me-1"></i><?php echo htmlspecialchars($announcement['course_name']); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-primary">Global</span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted"><?php echo formatDate($announcement['created_at'], 'F d, Y h:i A'); ?></small>
                    </div>
                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($announcement['message'])); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-person-circle"></i>
                            <?php echo htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']); ?>
                        </small>
                        <?php if (($user_type === 'admin' || ($user_type === 'instructor' && $announcement['posted_by'] == $user_id)) && isset($_GET['show_actions'])): ?>
                            <div>
                                <a href="?delete=<?php echo $announcement['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Delete this announcement?')">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Post Announcement Modal -->
<?php if ($user_type === 'admin' || $user_type === 'instructor'): ?>
<div class="modal fade" id="postModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-megaphone me-2"></i>Post Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <?php generateCSRFToken(); ?>
                    <div class="mb-3">
                        <label class="form-label">Title:</label>
                        <input type="text" name="title" class="form-control" required maxlength="200">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message:</label>
                        <textarea name="message" class="form-control" rows="6" required></textarea>
                    </div>
                    <?php if (!empty($courses)): ?>
                        <div class="mb-3">
                            <label class="form-label">Course (optional):</label>
                            <select name="course_id" class="form-select">
                                <option value="">Global Announcement</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>">
                                        <?php echo htmlspecialchars($c['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Leave empty for global announcement</small>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_pinned" class="form-check-input" id="is_pinned">
                        <label class="form-check-label" for="is_pinned">Pin this announcement</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="post_announcement" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Post Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
