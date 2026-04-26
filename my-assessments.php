<?php
/**
 * My Assessments
 * View all assessments for students and instructors
 */
require_once 'portal_config.php';
requireLogin();

$page_title = 'My Assessments';
$conn = getDBConnection();
$student_id = $_SESSION['user_id'];

// Handle assessment submission (redirect to submit page)
if (isset($_GET['submit']) && isset($_GET['assessment_id'])) {
    header('Location: submit-assessment.php?id=' . (int)$_GET['assessment_id']);
    exit();
}

if (isStudent()) {
    // Get assessments for courses the student is enrolled in
    $assessments_result = mysqli_query($conn, "
        SELECT a.*, c.name as course_name, c.code as course_code,
               s.score, s.submitted_at, s.status as submission_status,
               CASE 
                   WHEN s.id IS NOT NULL THEN 1 
                   WHEN a.due_date < NOW() THEN 2 
                   ELSE 0 
               END as status_order
        FROM assessments a
        JOIN courses c ON a.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id AND e.student_id = '$student_id'
        LEFT JOIN submissions s ON a.id = s.assessment_id AND s.student_id = '$student_id'
        WHERE e.status = 'active'
        ORDER BY status_order ASC, a.due_date ASC
    ");
} else {
    // Get assessments for courses the instructor teaches
    $assessments_result = mysqli_query($conn, "
        SELECT a.*, c.name as course_name, c.code as course_code,
               COUNT(s.id) as submission_count
        FROM assessments a
        JOIN courses c ON a.course_id = c.id
        WHERE c.instructor_id = '$student_id'
        LEFT JOIN submissions s ON a.id = s.assessment_id
        GROUP BY a.id
        ORDER BY a.due_date ASC
    ");
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-clipboard-check"></i> 
        <?php echo isStudent() ? 'My Assessments' : 'Course Assessments'; ?>
    </h1>
    <?php if (isInstructor() || isAdmin()): ?>
    <a href="my-courses.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Courses
    </a>
    <?php endif; ?>
</div>

<?php if (mysqli_num_rows($assessments_result) === 0): ?>
<div class="content-card text-center py-5">
    <i class="bi bi-inbox fs-1 text-muted"></i>
    <h4 class="mt-3">No Assessments Found</h4>
    <p class="text-muted">
        <?php echo isStudent() ? 'You have no assessments at this time.' : 'You have not created any assessments yet.'; ?>
    </p>
</div>
<?php else: ?>
<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Assessment</th>
                    <th>Course</th>
                    <th>Type</th>
                    <th>Due Date</th>
                    <th>Max Score</th>
                    <?php if (isStudent()): ?>
                    <th>Your Score</th>
                    <th>Status</th>
                    <th>Action</th>
                    <?php else: ?>
                    <th>Submissions</th>
                    <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($assessment = mysqli_fetch_assoc($assessments_result)): ?>
                <tr>
                    <td>
                        <div class="fw-bold"><?php echo htmlspecialchars($assessment['title']); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars(substr($assessment['description'], 0, 50)); ?>...</small>
                    </td>
                    <td>
                        <span class="badge bg-info"><?php echo htmlspecialchars($assessment['course_code']); ?></span>
                        <small class="d-block"><?php echo htmlspecialchars($assessment['course_name']); ?></small>
                    </td>
                    <td><span class="badge bg-secondary"><?php echo ucfirst($assessment['type']); ?></span></td>
                    <td>
                        <?php 
                        $due = strtotime($assessment['due_date']);
                        $now = time();
                        $color = $due < $now ? 'danger' : 'primary';
                        ?>
                        <span class="text-<?php echo $color; ?>">
                            <?php echo formatDate($assessment['due_date'], 'M d, Y h:i A'); ?>
                        </span>
                    </td>
                    <td><?php echo $assessment['max_score']; ?> pts</td>
                    
                    <?php if (isStudent()): ?>
                    <td>
                        <?php if ($assessment['score'] !== null): ?>
                        <span class="badge bg-success"><?php echo $assessment['score']; ?> / <?php echo $assessment['max_score']; ?></span>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        if ($assessment['submission_status'] === 'submitted'):
                            echo '<span class="badge bg-success">Submitted</span>';
                        elseif ($assessment['submission_status'] === 'graded'):
                            echo '<span class="badge bg-primary">Graded</span>';
                        elseif ($due < $now):
                            echo '<span class="badge bg-danger">Overdue</span>';
                        else:
                            echo '<span class="badge bg-warning">Pending</span>';
                        endif;
                        ?>
                    </td>
                    <td>
                        <?php if ($due >= $now && !$assessment['score']): ?>
                        <a href="submit-assessment.php?id=<?php echo $assessment['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-upload"></i> Submit
                        </a>
                        <?php elseif ($assessment['score']): ?>
                        <a href="view-submission.php?id=<?php echo $assessment['id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <?php endif; ?>
                    </td>
                    <?php else: ?>
                    <td>
                        <span class="badge bg-primary"><?php echo $assessment['submission_count']; ?> submissions</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="grade-submissions.php?assessment_id=<?php echo $assessment['id']; ?>" class="btn btn-outline-primary">
                                <i class="bi bi-pencil"></i> Grade
                            </a>
                            <a href="course-detail.php?id=<?php echo $assessment['course_id']; ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
