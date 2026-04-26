<?php
/**
 * My Courses - Online Class Portal
 * View all enrolled/teaching courses
 */

require_once 'portal_config.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$conn = getDBConnection();

$courses = [];

if ($user_type === 'student') {
    $sql = "SELECT c.*, u.first_name, u.last_name, e.enrollment_date, e.status as enrollment_status
            FROM courses c
            INNER JOIN enrollments e ON c.id = e.course_id
            INNER JOIN users u ON c.instructor_id = u.id
            WHERE e.student_id = '$user_id'
            ORDER BY e.enrollment_date DESC";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
} else {
    $sql = "SELECT c.*, 
            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status = 'enrolled') as student_count
            FROM courses c
            WHERE c.instructor_id = '$user_id'
            ORDER BY c.created_at DESC";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}

$page_title = 'My Courses';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-book me-2"></i><?php echo $user_type === 'student' ? 'My Enrolled' : 'My Teaching'; ?> Courses</h2>
                <?php if ($user_type === 'instructor'): ?>
                    <a href="create-course.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Create Course
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (empty($courses)): ?>
        <div class="content-card text-center py-5">
            <i class="bi bi-book display-4 text-muted mb-3"></i>
            <h4>No courses found</h4>
            <p class="text-muted">
                <?php if ($user_type === 'student'): ?>
                    You are not enrolled in any courses yet.
                <?php else: ?>
                    You haven't created any courses yet.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="course-card h-100">
                        <div class="course-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($course['course_name']); ?></h5>
                                    <small class="text-muted"><?php echo htmlspecialchars($course['course_code']); ?></small>
                                </div>
                                <span class="badge bg-<?php echo $course['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($course['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="course-body">
                            <?php if ($user_type === 'student'): ?>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-person"></i>
                                    <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?>
                                </p>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-calendar"></i>
                                    Enrolled: <?php echo formatDate($course['enrollment_date'], 'M d, Y'); ?>
                                </p>
                            <?php else: ?>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-people"></i>
                                    <?php echo $course['student_count']; ?> students enrolled
                                </p>
                            <?php endif; ?>
                            <p class="text-muted small mb-3">
                                <?php echo htmlspecialchars(substr($course['description'] ?? 'No description available', 0, 100)); ?>...
                            </p>
                            <div class="d-grid gap-2">
                                <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>View Course
                                </a>
                                <?php if ($user_type === 'instructor'): ?>
                                    <a href="manage-course.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-gear me-1"></i>Manage
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
