<?php
/**
 * Browse Courses
 * Allow students to browse and enroll in available courses
 */
require_once 'portal_config.php';
requireLogin();

$page_title = 'Browse Courses';
$conn = getDBConnection();

// Get all active courses that the student is not enrolled in
$student_id = $_SESSION['user_id'];
$courses_result = mysqli_query($conn, "
    SELECT c.*, u.full_name as instructor_name,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrolled_count
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.id
    WHERE c.is_active = 1
    AND c.id NOT IN (SELECT course_id FROM enrollments WHERE student_id = '$student_id')
    ORDER BY c.created_at DESC
");

// Handle enrollment
if (isset($_POST['enroll']) && verifyCSRFToken($_POST['csrf_token'])) {
    $course_id = (int)$_POST['course_id'];
    
    // Check if already enrolled
    $check = mysqli_query($conn, "SELECT id FROM enrollments WHERE student_id = '$student_id' AND course_id = '$course_id'");
    if (mysqli_num_rows($check) === 0) {
        $sql = "INSERT INTO enrollments (student_id, course_id, enrolled_at, status) 
                VALUES ('$student_id', '$course_id', NOW(), 'active')";
        if (mysqli_query($conn, $sql)) {
            setFlashMessage('success', 'Successfully enrolled in the course!');
            header('Location: browse-courses.php');
            exit();
        } else {
            setFlashMessage('error', 'Failed to enroll: ' . mysqli_error($conn));
        }
    } else {
        setFlashMessage('info', 'You are already enrolled in this course');
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-grid"></i> Browse Available Courses</h1>
    <a href="my-courses.php" class="btn btn-primary">
        <i class="bi bi-book"></i> My Courses
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

<?php if (mysqli_num_rows($courses_result) === 0): ?>
<div class="content-card text-center py-5">
    <i class="bi bi-inbox fs-1 text-muted"></i>
    <h4 class="mt-3">No Available Courses</h4>
    <p class="text-muted">There are no courses available for enrollment at this time.</p>
</div>
<?php else: ?>
<div class="row">
    <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="course-card h-100">
            <div class="course-header">
                <h5 class="mb-1"><?php echo htmlspecialchars($course['name']); ?></h5>
                <small><?php echo htmlspecialchars($course['code']); ?></small>
            </div>
            <div class="course-body">
                <p class="text-muted small mb-3">
                    <?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...
                </p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <small class="text-muted">
                        <i class="bi bi-person"></i> <?php echo $course['instructor_name'] ? htmlspecialchars($course['instructor_name']) : 'TBD'; ?>
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-people"></i> <?php echo $course['enrolled_count']; ?> enrolled
                    </small>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    <button type="submit" name="enroll" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle"></i> Enroll Now
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
