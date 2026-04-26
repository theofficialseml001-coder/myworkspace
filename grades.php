<?php
/**
 * Grades - Online Class Portal
 * View grades for students, manage grades for instructors
 */

require_once 'portal_config.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$conn = getDBConnection();

// Handle grade submission (instructors only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_grade']) && $user_type === 'instructor') {
    verifyCSRFToken($_POST['csrf_token']);
    
    $submission_id = (int)$_POST['submission_id'];
    $grade = (float)$_POST['grade'];
    $feedback = sanitizeInput($_POST['feedback']);
    
    if ($submission_id > 0 && $grade >= 0) {
        $feedback = mysqli_real_escape_string($conn, $feedback);
        
        // Verify instructor owns the assessment
        $sql = "SELECT a.total_points FROM submissions s
                INNER JOIN assessments a ON s.assessment_id = a.id
                INNER JOIN courses c ON a.course_id = c.id
                WHERE s.id = '$submission_id' AND c.instructor_id = '$user_id'";
        $result = mysqli_query($conn, $sql);
        $assessment = mysqli_fetch_assoc($result);
        
        if ($assessment) {
            $sql = "UPDATE submissions 
                    SET grade = '$grade', feedback = '$feedback', status = 'graded', graded_at = NOW()
                    WHERE id = '$submission_id'";
            
            if (mysqli_query($conn, $sql)) {
                $_SESSION['success_message'] = 'Grade submitted successfully!';
            } else {
                $_SESSION['error_message'] = 'Failed to submit grade.';
            }
        }
        header('Location: grades.php');
        exit;
    }
}

$grades = [];

if ($user_type === 'student') {
    // Get student's grades
    $sql = "SELECT s.*, a.title as assessment_title, a.total_points, a.type,
            c.course_name, c.course_code,
            u.first_name as instructor_first, u.last_name as instructor_last
            FROM submissions s
            INNER JOIN assessments a ON s.assessment_id = a.id
            INNER JOIN courses c ON a.course_id = c.id
            INNER JOIN users u ON c.instructor_id = u.id
            WHERE s.student_id = '$user_id'
            ORDER BY s.submitted_at DESC";
    
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $grades[] = $row;
    }
    
    // Calculate overall stats
    $total_graded = 0;
    $total_earned = 0;
    $total_possible = 0;
    foreach ($grades as $g) {
        if ($g['status'] === 'graded' && $g['grade'] !== null) {
            $total_graded++;
            $total_earned += $g['grade'];
            $total_possible += $g['total_points'];
        }
    }
    $average = $total_possible > 0 ? round(($total_earned / $total_possible) * 100, 2) : 0;
    
} elseif ($user_type === 'instructor') {
    // Get submissions for instructor's courses
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';
    
    $status_filter = '';
    if ($filter === 'pending') {
        $status_filter = "AND s.status = 'submitted'";
    } elseif ($filter === 'graded') {
        $status_filter = "AND s.status = 'graded'";
    } elseif ($filter === 'all') {
        $status_filter = '';
    }
    
    $sql = "SELECT s.*, a.title as assessment_title, a.total_points, a.type, a.due_date,
            c.course_name, c.course_code,
            u.first_name, u.last_name
            FROM submissions s
            INNER JOIN assessments a ON s.assessment_id = a.id
            INNER JOIN courses c ON a.course_id = c.id
            INNER JOIN users u ON s.student_id = u.id
            WHERE c.instructor_id = '$user_id' $status_filter
            ORDER BY s.submitted_at DESC";
    
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $grades[] = $row;
    }
    
    // Count pending
    $pending_count = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) as count FROM submissions s
        INNER JOIN assessments a ON s.assessment_id = a.id
        INNER JOIN courses c ON a.course_id = c.id
        WHERE c.instructor_id = '$user_id' AND s.status = 'submitted'
    "))['count'];
    
} else {
    // Admin view - all submissions
    $sql = "SELECT s.*, a.title as assessment_title, a.total_points,
            c.course_name,
            student.first_name as student_first, student.last_name as student_last,
            instructor.first_name as instructor_first, instructor.last_name as instructor_last
            FROM submissions s
            INNER JOIN assessments a ON s.assessment_id = a.id
            INNER JOIN courses c ON a.course_id = c.id
            INNER JOIN users student ON s.student_id = student.id
            INNER JOIN users instructor ON c.instructor_id = instructor.id
            ORDER BY s.submitted_at DESC
            LIMIT 100";
    
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $grades[] = $row;
    }
}

$page_title = 'Grades';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-graph-up me-2"></i>
                <?php 
                if ($user_type === 'student') echo 'My Grades';
                elseif ($user_type === 'instructor') echo 'Grade Submissions';
                else echo 'All Grades';
                ?>
            </h2>
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

    <?php if ($user_type === 'student'): ?>
        <!-- Student Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_graded; ?></div>
                    <div class="stat-label">Graded Assessments</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f6c23e 0%, #f39c12 100%);">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_earned; ?> / <?php echo $total_possible; ?></div>
                    <div class="stat-label">Total Points</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);">
                        <i class="bi bi-percent"></i>
                    </div>
                    <div class="stat-number"><?php echo $average; ?>%</div>
                    <div class="stat-label">Overall Average</div>
                </div>
            </div>
        </div>
    <?php elseif ($user_type === 'instructor'): ?>
        <!-- Instructor Filters -->
        <div class="content-card mb-4">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a href="?filter=pending" class="nav-link <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                        <i class="bi bi-clock me-1"></i>Pending
                        <?php if ($pending_count > 0): ?>
                            <span class="badge bg-danger ms-1"><?php echo $pending_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?filter=graded" class="nav-link <?php echo $filter === 'graded' ? 'active' : ''; ?>">
                        <i class="bi bi-check-lg me-1"></i>Graded
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?filter=all" class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>">
                        <i class="bi bi-list-ul me-1"></i>All
                    </a>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Grades Table -->
    <div class="content-card">
        <?php if (empty($grades)): ?>
            <p class="text-muted text-center py-5">
                <?php if ($user_type === 'student'): ?>
                    No graded assessments yet.
                <?php elseif ($user_type === 'instructor'): ?>
                    No submissions found.
                <?php else: ?>
                    No submissions found.
                <?php endif; ?>
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <?php if ($user_type !== 'student'): ?>
                                <th>Student</th>
                            <?php endif; ?>
                            <th>Assessment</th>
                            <?php if ($user_type === 'student'): ?>
                                <th>Course</th>
                            <?php endif; ?>
                            <th>Type</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $g): ?>
                            <tr>
                                <?php if ($user_type !== 'student'): ?>
                                    <td>
                                        <?php if (isset($g['first_name'])): ?>
                                            <?php echo htmlspecialchars($g['first_name'] . ' ' . $g['last_name']); ?>
                                        <?php elseif (isset($g['student_first'])): ?>
                                            <?php echo htmlspecialchars($g['student_first'] . ' ' . $g['student_last']); ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <strong><?php echo htmlspecialchars($g['assessment_title']); ?></strong>
                                    <?php if ($user_type !== 'student' && isset($g['course_name'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($g['course_name']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <?php if ($user_type === 'student'): ?>
                                    <td><?php echo htmlspecialchars($g['course_name']); ?></td>
                                <?php endif; ?>
                                <td><span class="badge bg-info"><?php echo ucfirst($g['type']); ?></span></td>
                                <td><?php echo formatDate($g['submitted_at'], 'M d, h:i A'); ?></td>
                                <td>
                                    <?php if ($g['status'] === 'submitted'): ?>
                                        <span class="badge bg-warning">Submitted</span>
                                    <?php elseif ($g['status'] === 'graded'): ?>
                                        <span class="badge bg-success">Graded</span>
                                    <?php elseif ($g['status'] === 'late'): ?>
                                        <span class="badge bg-danger">Late</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo ucfirst($g['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($g['status'] === 'graded' && $g['grade'] !== null): ?>
                                        <strong class="<?php echo ($g['grade'] / $g['total_points']) >= 0.7 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $g['grade']; ?> / <?php echo $g['total_points']; ?>
                                        </strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user_type === 'student' && $g['status'] === 'graded'): ?>
                                        <button class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="modal" data-bs-target="#feedbackModal<?php echo $g['id']; ?>">
                                            <i class="bi bi-chat-left-text"></i> Feedback
                                        </button>
                                    <?php elseif ($user_type === 'instructor' && $g['status'] === 'submitted'): ?>
                                        <button class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" data-bs-target="#gradeModal<?php echo $g['id']; ?>">
                                            <i class="bi bi-pencil-square"></i> Grade
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Feedback Modal (Student) -->
                            <?php if ($user_type === 'student' && $g['status'] === 'graded'): ?>
                            <div class="modal fade" id="feedbackModal<?php echo $g['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Feedback: <?php echo htmlspecialchars($g['assessment_title']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Grade:</strong> <?php echo $g['grade']; ?> / <?php echo $g['total_points']; ?></p>
                                            <hr>
                                            <p><?php echo nl2br(htmlspecialchars($g['feedback'] ?? 'No feedback provided')); ?></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Grade Modal (Instructor) -->
                            <?php if ($user_type === 'instructor' && $g['status'] === 'submitted'): ?>
                            <div class="modal fade" id="gradeModal<?php echo $g['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Grade: <?php echo htmlspecialchars($g['assessment_title']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="">
                                            <div class="modal-body">
                                                <?php generateCSRFToken(); ?>
                                                <input type="hidden" name="submission_id" value="<?php echo $g['id']; ?>">
                                                <p><strong>Student:</strong> <?php echo htmlspecialchars($g['first_name'] . ' ' . $g['last_name']); ?></p>
                                                <p><strong>Max Points:</strong> <?php echo $g['total_points']; ?></p>
                                                <div class="mb-3">
                                                    <label class="form-label">Grade:</label>
                                                    <input type="number" name="grade" class="form-control" 
                                                           min="0" max="<?php echo $g['total_points']; ?>" 
                                                           step="0.01" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Feedback:</label>
                                                    <textarea name="feedback" class="form-control" rows="4"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="submit_grade" class="btn btn-primary">
                                                    Submit Grade
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
