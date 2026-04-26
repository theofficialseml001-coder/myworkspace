<?php
/**
 * Create Course
 * Allow instructors and admins to create new courses
 */
require_once 'portal_config.php';
requireLogin();

if (!isInstructor() && !isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Create Course';
$conn = getDBConnection();
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'])) {
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $description = trim($_POST['description']);
    $instructor_id = $_SESSION['user_id'];
    
    if (empty($name)) {
        $errors[] = "Course name is required";
    }
    if (empty($code)) {
        $errors[] = "Course code is required";
    } else {
        // Check if code already exists
        $code_check = mysqli_real_escape_string($conn, $code);
        $existing = mysqli_query($conn, "SELECT id FROM courses WHERE code = '$code_check'");
        if (mysqli_num_rows($existing) > 0) {
            $errors[] = "Course code already exists";
        }
    }
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    
    if (empty($errors)) {
        $name = mysqli_real_escape_string($conn, $name);
        $code = mysqli_real_escape_string($conn, $code);
        $description = mysqli_real_escape_string($conn, $description);
        
        $sql = "INSERT INTO courses (name, code, description, instructor_id, is_active) 
                VALUES ('$name', '$code', '$description', '$instructor_id', 1)";
        
        if (mysqli_query($conn, $sql)) {
            $course_id = mysqli_insert_id($conn);
            setFlashMessage('success', 'Course created successfully!');
            header('Location: course-detail.php?id=' . $course_id);
            exit();
        } else {
            $errors[] = "Failed to create course: " . mysqli_error($conn);
        }
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-plus-circle"></i> Create New Course</h1>
    <a href="my-courses.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Courses
    </a>
</div>

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

<div class="content-card">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Course Name *</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="code" class="form-label">Course Code *</label>
                <input type="text" class="form-control" id="code" name="code" 
                       value="<?php echo isset($_POST['code']) ? htmlspecialchars($_POST['code']) : ''; ?>" 
                       placeholder="e.g., CS101" required>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description *</label>
            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>
        
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Create Course
            </button>
            <a href="my-courses.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
