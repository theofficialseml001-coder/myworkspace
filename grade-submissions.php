<?php
/**
 * Grade Submissions
 * Allow instructors to grade student submissions
 */
require_once 'portal_config.php';
requireLogin();

if (!isInstructor() && !isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Grade Submissions';
$conn = getDBConnection();
$errors = [];
$success = false;

$assessment_id = isset($_GET['assessment_id']) ? (int)$_GET['assessment_id'] : 0;

// Verify assessment ownership
if ($assessment_id > 0) {
    $assessment = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT a.*, c.name as course_name, c.instructor_id 
        FROM assessments a 
        JOIN courses c ON a.course_id = c.id 
        WHERE a.id = '$assessment_id'
    "));
    
    if (!$assessment || ($assessment['instructor_id'] != $_SESSION['user_id'] && !isAdmin())) {
        setFlashMessage('error', 'You do not have permission to grade this assessment');
        header('Location: my-courses.php');
        exit();
    }
} else {
    setFlashMessage('error', 'Invalid assessment');
    header('Location: my-courses.php');
    exit();
}

// Handle grading
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'])) {
    $submission_id = (int)$_POST['submission_id'];
    $score = (int)$_POST['score'];
    $feedback = trim($_POST['feedback']);
    
    if ($score < 0 || $score > $assessment['max_score']) {
        $errors[] = "Score must be between 0 and {$assessment['max_score']}";
    }
    
    if (empty($errors)) {
        $feedback = mysqli_real_escape_string($conn, $feedback);
        $sql = "UPDATE submissions SET score = '$score', feedback = '$feedback', graded_at = NOW(), status = 'graded' 
                WHERE id = '$submission_id' AND assessment_id = '$assessment_id'";
        
        if (mysqli_query($conn, $sql)) {
            setFlashMessage('success', 'Submission graded successfully!');
            header('Location: grade-submissions.php?assessment_id=' . $assessment_id);
            exit();
        } else {
            $errors[] = "Failed to grade submission: " . mysqli_error($conn);
        }
    }
}

// Get all submissions for this assessment
$submissions = mysqli_query($conn, "
    SELECT s.*, u.full_name as student_name, u.email as student_email
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    WHERE s.assessment_id = '$assessment_id'
    ORDER BY s.submitted_at ASC
");

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-pencil"></i> Grade Submissions</h1>
    <a href="course-detail.php?id=<?php echo $assessment['course_id']; ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Course
    </a>
</div>

<div class="mb-4">
    <div class="content-card">
        <h5><?php echo htmlspecialchars($assessment['title']); ?></h5>
        <p class="text-muted mb-0">
            <?php echo ucfirst($assessment['type']); ?> | Max Score: <?php echo $assessment['max_score']; ?> | 
            Due: <?php echo formatDate($assessment['due_date'], 'M d, Y h:i A'); ?>
        </p>
    </div>
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

<?php
$flash = getFlashMessage();
if ($flash):
?>
<div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
    <?php echo htmlspecialchars($flash['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (mysqli_num_rows($submissions) === 0): ?>
<div class="content-card text-center py-5">
    <i class="bi bi-inbox fs-1 text-muted"></i>
    <h4 class="mt-3">No Submissions Yet</h4>
    <p class="text-muted">Students have not submitted any work for this assessment.</p>
</div>
<?php else: ?>
<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Submitted At</th>
                    <th>File</th>
                    <th>Comments</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($submission = mysqli_fetch_assoc($submissions)): ?>
                <tr>
                    <td>
                        <div class="fw-bold"><?php echo htmlspecialchars($submission['student_name']); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($submission['student_email']); ?></small>
                    </td>
                    <td><?php echo formatDate($submission['submitted_at'], 'M d, Y h:i A'); ?></td>
                    <td>
                        <?php if ($submission['file_path']): ?>
                        <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="bi bi-download"></i> Download
                        </a>
                        <?php else: ?>
                        <span class="text-muted">No file</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($submission['comments']): ?>
                        <button class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#commentModal<?php echo $submission['id']; ?>">
                            <i class="bi bi-chat"></i> View
                        </button>
                        
                        <!-- Comment Modal -->
                        <div class="modal fade" id="commentModal<?php echo $submission['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Student Comments</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php echo nl2br(htmlspecialchars($submission['comments'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($submission['score'] !== null): ?>
                        <span class="badge bg-success"><?php echo $submission['score']; ?> / <?php echo $assessment['max_score']; ?></span>
                        <?php else: ?>
                        <span class="badge bg-warning">Not Graded</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $submission['status'] === 'graded' ? 'bg-success' : 'bg-info'; ?>">
                            <?php echo ucfirst($submission['status']); ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#gradeModal<?php echo $submission['id']; ?>">
                            <i class="bi bi-pencil"></i> <?php echo $submission['score'] !== null ? 'Edit Grade' : 'Grade'; ?>
                        </button>
                        
                        <!-- Grade Modal -->
                        <div class="modal fade" id="gradeModal<?php echo $submission['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Grade Submission</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Score (0 - <?php echo $assessment['max_score']; ?>)</label>
                                                <input type="number" class="form-control" name="score" 
                                                       value="<?php echo $submission['score'] ?? ''; ?>" 
                                                       min="0" max="<?php echo $assessment['max_score']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Feedback</label>
                                                <textarea class="form-control" name="feedback" rows="4"><?php echo htmlspecialchars($submission['feedback'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Grade</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
