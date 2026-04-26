<?php
/**
 * Submit Assessment - Online Class Portal
 * Students submit their assessment work
 */

require_once 'portal_config.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

if ($user_type !== 'student') {
    header('Location: dashboard.php');
    exit;
}

$conn = getDBConnection();
$assessment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($assessment_id <= 0) {
    header('Location: my-courses.php');
    exit;
}

// Get assessment details and verify student is enrolled
$sql = "SELECT a.*, c.course_name, c.course_code
        FROM assessments a
        INNER JOIN courses c ON a.course_id = c.id
        INNER JOIN enrollments e ON c.id = e.course_id
        WHERE a.id = '$assessment_id' AND e.student_id = '$user_id' AND e.status = 'enrolled'";
$result = mysqli_query($conn, $sql);
$assessment = mysqli_fetch_assoc($result);

if (!$assessment) {
    $_SESSION['error_message'] = 'Assessment not found or you do not have access.';
    header('Location: my-courses.php');
    exit;
}

// Check if already submitted
$sql = "SELECT * FROM submissions WHERE assessment_id = '$assessment_id' AND student_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$existing_submission = mysqli_fetch_assoc($result);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assessment'])) {
    verifyCSRFToken($_POST['csrf_token']);
    
    $comments = sanitizeInput($_POST['comments']);
    $file_path = null;
    
    // Handle file upload
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/assessments/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            $_SESSION['error_message'] = 'Invalid file type. Allowed: ' . implode(', ', $allowed_exts);
        } else {
            $new_filename = 'sub_' . $user_id . '_' . $assessment_id . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $upload_path)) {
                $file_path = $upload_path;
            } else {
                $_SESSION['error_message'] = 'Failed to upload file.';
            }
        }
    }
    
    if (!isset($_SESSION['error_message'])) {
        $comments = mysqli_real_escape_string($conn, $comments);
        $file_path = $file_path ? mysqli_real_escape_string($conn, $file_path) : NULL;
        
        $now = date('Y-m-d H:i:s');
        $is_late = ($now > $assessment['due_date']) ? 1 : 0;
        $status = $is_late ? 'late' : 'submitted';
        
        if ($existing_submission) {
            // Update existing submission
            $file_update = $file_path ? ", file_path = '$file_path'" : '';
            $sql = "UPDATE submissions 
                    SET comments = '$comments', file_path = COALESCE('$file_path', file_path), 
                        status = '$status', submitted_at = NOW()
                    WHERE assessment_id = '$assessment_id' AND student_id = '$user_id'";
        } else {
            // Create new submission
            $sql = "INSERT INTO submissions (assessment_id, student_id, file_path, comments, status, submitted_at)
                    VALUES ('$assessment_id', '$user_id', '$file_path', '$comments', '$status', NOW())";
        }
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success_message'] = 'Assessment submitted successfully!';
            header('Location: course-detail.php?id=' . $assessment['course_id']);
            exit;
        } else {
            $_SESSION['error_message'] = 'Failed to submit assessment.';
        }
    }
}

$page_title = 'Submit: ' . $assessment['title'];
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="my-courses.php">My Courses</a></li>
                    <li class="breadcrumb-item"><a href="course-detail.php?id=<?php echo $assessment['course_id']; ?>"><?php echo htmlspecialchars($assessment['course_name']); ?></a></li>
                    <li class="breadcrumb-item active">Submit Assessment</li>
                </ol>
            </nav>
            <h2><i class="bi bi-upload me-2"></i>Submit Assessment</h2>
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

    <div class="row">
        <div class="col-lg-8">
            <div class="content-card">
                <h4><?php echo htmlspecialchars($assessment['title']); ?></h4>
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($assessment['instructions'] ?? '')); ?></p>
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <strong>Type:</strong><br>
                        <span class="badge bg-info"><?php echo ucfirst($assessment['type']); ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Total Points:</strong><br>
                        <?php echo $assessment['total_points']; ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Due Date:</strong><br>
                        <?php echo formatDate($assessment['due_date'], 'F d, Y h:i A'); ?>
                        <?php if (strtotime($assessment['due_date']) < time()): ?>
                            <span class="badge bg-danger ms-1">Overdue</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($existing_submission): ?>
                <div class="content-card border-warning">
                    <h5 class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Previous Submission Found</h5>
                    <p>You have already submitted this assessment. Submitting again will replace your previous submission.</p>
                    <hr>
                    <p><strong>Submitted:</strong> <?php echo formatDate($existing_submission['submitted_at'], 'F d, Y h:i A'); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-<?php echo $existing_submission['status'] === 'graded' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($existing_submission['status']); ?>
                        </span>
                    </p>
                    <?php if ($existing_submission['status'] === 'graded'): ?>
                        <p><strong>Grade:</strong> <?php echo $existing_submission['grade']; ?> / <?php echo $existing_submission['total_points']; ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <form method="POST" enctype="multipart/form-data">
                    <?php generateCSRFToken(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Upload File:</label>
                        <input type="file" name="submission_file" class="form-control" accept=".pdf,.doc,.docx,.txt,.zip,.rar">
                        <small class="text-muted">Allowed formats: PDF, DOC, DOCX, TXT, ZIP, RAR</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Comments (optional):</label>
                        <textarea name="comments" class="form-control" rows="4" placeholder="Any comments for the instructor..."><?php echo htmlspecialchars($existing_submission['comments'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" name="submit_assessment" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Submit Assessment
                        </button>
                        <a href="course-detail.php?id=<?php echo $assessment['course_id']; ?>" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card">
                <h5><i class="bi bi-info-circle me-2"></i>Submission Guidelines</h5>
                <ul class="small text-muted mb-0">
                    <li>Ensure your file is properly named</li>
                    <li>Submit before the due date to avoid late penalties</li>
                    <li>You can resubmit if needed (latest submission counts)</li>
                    <li>Contact your instructor if you encounter issues</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
