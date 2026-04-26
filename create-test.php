<?php
/**
 * Create Test
 * Allow instructors to create tests/quizzes for their courses
 */
require_once 'portal_config.php';
requireLogin();

if (!isInstructor() && !isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Create Test';
$conn = getDBConnection();
$errors = [];
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Verify course ownership
if ($course_id > 0) {
    $course_check = mysqli_query($conn, "SELECT * FROM courses WHERE id = '$course_id'");
    $course = mysqli_fetch_assoc($course_check);
    
    if (!$course || ($course['instructor_id'] != $_SESSION['user_id'] && !isAdmin())) {
        setFlashMessage('error', 'You do not have permission to add tests to this course');
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
    $duration = (int)$_POST['duration'];
    $total_marks = (int)$_POST['total_marks'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $questions_json = $_POST['questions_json'];
    
    if (empty($title)) {
        $errors[] = "Test title is required";
    }
    if ($duration <= 0) {
        $errors[] = "Duration must be greater than 0";
    }
    if ($total_marks <= 0) {
        $errors[] = "Total marks must be greater than 0";
    }
    if (empty($start_date)) {
        $errors[] = "Start date is required";
    }
    if (empty($end_date)) {
        $errors[] = "End date is required";
    }
    
    if (empty($errors)) {
        $title = mysqli_real_escape_string($conn, $title);
        $description = mysqli_real_escape_string($conn, $description);
        $start_date = mysqli_real_escape_string($conn, $start_date);
        $end_date = mysqli_real_escape_string($conn, $end_date);
        
        $sql = "INSERT INTO tests (course_id, title, description, duration_minutes, total_marks, start_datetime, end_datetime, questions, created_by) 
                VALUES ('$course_id', '$title', '$description', '$duration', '$total_marks', '$start_date', '$end_date', '$questions_json', '{$_SESSION['user_id']}')";
        
        if (mysqli_query($conn, $sql)) {
            $test_id = mysqli_insert_id($conn);
            setFlashMessage('success', 'Test created successfully!');
            header('Location: course-detail.php?id=' . $course_id);
            exit();
        } else {
            $errors[] = "Failed to create test: " . mysqli_error($conn);
        }
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-file-earmark-text"></i> Create Test/Quiz</h1>
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
    <form method="POST" id="testForm">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        <input type="hidden" name="questions_json" id="questions_json" value="[]">
        
        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="title" class="form-label">Test Title *</label>
                <input type="text" class="form-control" id="title" name="title" 
                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="duration" class="form-label">Duration (minutes) *</label>
                <input type="number" class="form-control" id="duration" name="duration" 
                       value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : '60'; ?>" min="1" required>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Instructions/Description *</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="total_marks" class="form-label">Total Marks *</label>
                <input type="number" class="form-control" id="total_marks" name="total_marks" 
                       value="<?php echo isset($_POST['total_marks']) ? htmlspecialchars($_POST['total_marks']) : '100'; ?>" min="1" required>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="start_date" class="form-label">Start Date & Time *</label>
                <input type="datetime-local" class="form-control" id="start_date" name="start_date" 
                       value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>" required>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="end_date" class="form-label">End Date & Time *</label>
                <input type="datetime-local" class="form-control" id="end_date" name="end_date" 
                       value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>" required>
            </div>
        </div>
        
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            <strong>Note:</strong> You can add questions after creating the test. Click "Create Test" first, then add questions from the test detail page.
        </div>
        
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Create Test
            </button>
            <a href="course-detail.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
