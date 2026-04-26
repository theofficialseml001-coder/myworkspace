<?php
/**
 * Create Assessment
 * Allow instructors to create assessments for their courses
 */
require_once 'portal_config.php';
requireLogin();

if (!isInstructor() && !isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Create Assessment';
$conn = getDBConnection();
$errors = [];
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Verify course ownership
if ($course_id > 0) {
    $course_check = mysqli_query($conn, "SELECT * FROM courses WHERE id = '$course_id'");
    $course = mysqli_fetch_assoc($course_check);
    
    if (!$course || ($course['instructor_id'] != $_SESSION['user_id'] && !isAdmin())) {
        setFlashMessage('error', 'You do not have permission to add assessments to this course');
        header('Location: my-courses.php');
        exit();
    }
} else {
    setFlashMessage('error', 'Invalid course');
    header('Location: my-courses.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $type = $_POST['type'];
    $max_score = (int)$_POST['max_score'];
    $due_date = $_POST['due_date'];
    
    if (empty($title)) {
        $errors[] = "Assessment title is required";
    }
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    if ($max_score <= 0) {
        $errors[] = "Maximum score must be greater than 0";
    }
    if (empty($due_date)) {
        $errors[] = "Due date is required";
    }
    
    if (empty($errors)) {
        $title = mysqli_real_escape_string($conn, $title);
        $description = mysqli_real_escape_string($conn, $description);
        $type = mysqli_real_escape_string($conn, $type);
        $due_date = mysqli_real_escape_string($conn, $due_date);
        
        $sql = "INSERT INTO assessments (course_id, title, description, type, max_score, due_date, created_by) 
                VALUES ('$course_id', '$title', '$description', '$type', '$max_score', '$due_date', '{$_SESSION['user_id']}')";
        
        if (mysqli_query($conn, $sql)) {
            $assessment_id = mysqli_insert_id($conn);
            setFlashMessage('success', 'Assessment created successfully!');
            header('Location: course-detail.php?id=' . $course_id);
            exit();
        } else {
            $errors[] = "Failed to create assessment: " . mysqli_error($conn);
        }
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-clipboard-check"></i> Create Assessment</h1>
    <a href="course-detail.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Course
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
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        
        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="title" class="form-label">Assessment Title *</label>
                <input type="text" class="form-control" id="title" name="title" 
                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="type" class="form-label">Type *</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="assignment" <?php echo (isset($_POST['type']) && $_POST['type'] === 'assignment') ? 'selected' : ''; ?>>Assignment</option>
                    <option value="quiz" <?php echo (isset($_POST['type']) && $_POST['type'] === 'quiz') ? 'selected' : ''; ?>>Quiz</option>
                    <option value="exam" <?php echo (isset($_POST['type']) && $_POST['type'] === 'exam') ? 'selected' : ''; ?>>Exam</option>
                    <option value="project" <?php echo (isset($_POST['type']) && $_POST['type'] === 'project') ? 'selected' : ''; ?>>Project</option>
                </select>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description/Instructions *</label>
            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="max_score" class="form-label">Maximum Score *</label>
                <input type="number" class="form-control" id="max_score" name="max_score" 
                       value="<?php echo isset($_POST['max_score']) ? htmlspecialchars($_POST['max_score']) : '100'; ?>" min="1" required>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="due_date" class="form-label">Due Date *</label>
                <input type="datetime-local" class="form-control" id="due_date" name="due_date" 
                       value="<?php echo isset($_POST['due_date']) ? htmlspecialchars($_POST['due_date']) : ''; ?>" required>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="draft">Draft</option>
                    <option value="published" selected>Published</option>
                </select>
            </div>
        </div>
        
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Create Assessment
            </button>
            <a href="course-detail.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
