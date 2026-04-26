<?php
/**
 * Admin Courses Management
 * Manage all courses in the system
 */
require_once 'portal_config.php';
requireLogin();

if (!isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Manage Courses';
$conn = getDBConnection();

// Handle delete course
if (isset($_POST['delete_course']) && verifyCSRFToken($_POST['csrf_token'])) {
    $course_id = (int)$_POST['course_id'];
    
    // Delete enrollments first
    mysqli_query($conn, "DELETE FROM enrollments WHERE course_id = '$course_id'");
    // Delete course materials
    mysqli_query($conn, "DELETE FROM course_materials WHERE course_id = '$course_id'");
    // Delete assessments
    mysqli_query($conn, "DELETE FROM assessments WHERE course_id = '$course_id'");
    // Delete announcements
    mysqli_query($conn, "DELETE FROM announcements WHERE course_id = '$course_id'");
    // Delete course
    $sql = "DELETE FROM courses WHERE id = '$course_id'";
    mysqli_query($conn, $sql);
    
    setFlashMessage('success', 'Course deleted successfully');
    header('Location: admin-courses.php');
    exit();
}

// Get all courses with instructor info
$courses_result = mysqli_query($conn, "
    SELECT c.*, u.full_name as instructor_name 
    FROM courses c 
    LEFT JOIN users u ON c.instructor_id = u.id 
    ORDER BY c.created_at DESC
");

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-book"></i> Course Management</h1>
    <a href="create-course.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create New Course
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

<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Code</th>
                    <th>Instructor</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                <?php
                $student_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM enrollments WHERE course_id = {$course['id']}"))['count'];
                ?>
                <tr>
                    <td><?php echo $course['id']; ?></td>
                    <td>
                        <div class="fw-bold"><?php echo htmlspecialchars($course['name']); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($course['description']); ?></small>
                    </td>
                    <td><span class="badge bg-info"><?php echo htmlspecialchars($course['code']); ?></span></td>
                    <td><?php echo $course['instructor_name'] ? htmlspecialchars($course['instructor_name']) : 'No Instructor'; ?></td>
                    <td><span class="badge bg-primary"><?php echo $student_count; ?> Students</span></td>
                    <td>
                        <span class="badge <?php echo $course['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $course['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td><?php echo formatDate($course['created_at'], 'M d, Y'); ?></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="edit-course.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this course? This will also delete all enrollments, materials, and assessments.');">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                <input type="hidden" name="delete_course" value="1">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
