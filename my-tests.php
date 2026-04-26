<?php
/**
 * My Tests
 * View all tests for students and instructors
 */
require_once 'portal_config.php';
requireLogin();

$page_title = 'My Tests';
$conn = getDBConnection();
$student_id = $_SESSION['user_id'];

if (isStudent()) {
    // Get tests for courses the student is enrolled in
    $tests_result = mysqli_query($conn, "
        SELECT t.*, c.name as course_name, c.code as course_code,
               tr.score, tr.submitted_at, tr.status as attempt_status,
               CASE 
                   WHEN tr.id IS NOT NULL THEN 1 
                   WHEN t.end_datetime < NOW() THEN 2 
                   ELSE 0 
               END as status_order
        FROM tests t
        JOIN courses c ON t.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id AND e.student_id = '$student_id'
        LEFT JOIN test_results tr ON t.id = tr.test_id AND tr.student_id = '$student_id'
        WHERE e.status = 'active'
        ORDER BY status_order ASC, t.end_datetime ASC
    ");
} else {
    // Get tests for courses the instructor teaches
    $tests_result = mysqli_query($conn, "
        SELECT t.*, c.name as course_name, c.code as course_code,
               COUNT(tr.id) as attempt_count
        FROM tests t
        JOIN courses c ON t.course_id = c.id
        WHERE c.instructor_id = '$student_id'
        LEFT JOIN test_results tr ON t.id = tr.test_id
        GROUP BY t.id
        ORDER BY t.start_datetime ASC
    ");
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-file-earmark-text"></i> 
        <?php echo isStudent() ? 'My Tests & Quizzes' : 'Course Tests'; ?>
    </h1>
    <?php if (isInstructor() || isAdmin()): ?>
    <a href="my-courses.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Courses
    </a>
    <?php endif; ?>
</div>

<?php if (mysqli_num_rows($tests_result) === 0): ?>
<div class="content-card text-center py-5">
    <i class="bi bi-inbox fs-1 text-muted"></i>
    <h4 class="mt-3">No Tests Found</h4>
    <p class="text-muted">
        <?php echo isStudent() ? 'You have no tests at this time.' : 'You have not created any tests yet.'; ?>
    </p>
</div>
<?php else: ?>
<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Course</th>
                    <th>Duration</th>
                    <th>Total Marks</th>
                    <th>Available From</th>
                    <th>Due Date</th>
                    <?php if (isStudent()): ?>
                    <th>Your Score</th>
                    <th>Status</th>
                    <th>Action</th>
                    <?php else: ?>
                    <th>Attempts</th>
                    <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($test = mysqli_fetch_assoc($tests_result)): ?>
                <?php
                $now = time();
                $start = strtotime($test['start_datetime']);
                $end = strtotime($test['end_datetime']);
                $can_start = $start <= $now && $end > $now;
                $is_overdue = $end < $now;
                $is_upcoming = $start > $now;
                ?>
                <tr>
                    <td>
                        <div class="fw-bold"><?php echo htmlspecialchars($test['title']); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars(substr($test['description'], 0, 50)); ?>...</small>
                    </td>
                    <td>
                        <span class="badge bg-info"><?php echo htmlspecialchars($test['course_code']); ?></span>
                        <small class="d-block"><?php echo htmlspecialchars($test['course_name']); ?></small>
                    </td>
                    <td><span class="badge bg-secondary"><?php echo $test['duration_minutes']; ?> min</span></td>
                    <td><?php echo $test['total_marks']; ?> pts</td>
                    <td><?php echo formatDate($test['start_datetime'], 'M d, h:i A'); ?></td>
                    <td>
                        <?php if ($is_overdue): ?>
                        <span class="text-danger"><?php echo formatDate($test['end_datetime'], 'M d, h:i A'); ?></span>
                        <?php elseif ($is_upcoming): ?>
                        <span class="text-warning"><?php echo formatDate($test['end_datetime'], 'M d, h:i A'); ?></span>
                        <?php else: ?>
                        <span class="text-success"><?php echo formatDate($test['end_datetime'], 'M d, h:i A'); ?></span>
                        <?php endif; ?>
                    </td>
                    
                    <?php if (isStudent()): ?>
                    <td>
                        <?php if ($test['score'] !== null): ?>
                        <span class="badge bg-success"><?php echo $test['score']; ?> / <?php echo $test['total_marks']; ?></span>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        if ($test['attempt_status'] === 'completed'):
                            echo '<span class="badge bg-success">Completed</span>';
                        elseif ($test['attempt_status'] === 'in_progress'):
                            echo '<span class="badge bg-warning">In Progress</span>';
                        elseif ($is_overdue):
                            echo '<span class="badge bg-danger">Expired</span>';
                        elseif ($is_upcoming):
                            echo '<span class="badge bg-info">Upcoming</span>';
                        else:
                            echo '<span class="badge bg-primary">Available</span>';
                        endif;
                        ?>
                    </td>
                    <td>
                        <?php if ($test['attempt_status'] === 'in_progress'): ?>
                        <a href="take-test.php?id=<?php echo $test['id']; ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-play"></i> Continue
                        </a>
                        <?php elseif ($can_start && !$test['score']): ?>
                        <a href="take-test.php?id=<?php echo $test['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-play"></i> Start Test
                        </a>
                        <?php elseif ($test['score']): ?>
                        <a href="view-test-result.php?id=<?php echo $test['id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View Results
                        </a>
                        <?php elseif ($is_upcoming): ?>
                        <button class="btn btn-sm btn-secondary" disabled>
                            <i class="bi bi-clock"></i> Wait
                        </button>
                        <?php else: ?>
                        <button class="btn btn-sm btn-secondary" disabled>
                            <i class="bi bi-x-circle"></i> Expired
                        </button>
                        <?php endif; ?>
                    </td>
                    <?php else: ?>
                    <td>
                        <span class="badge bg-primary"><?php echo $test['attempt_count']; ?> attempts</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="test-results.php?test_id=<?php echo $test['id']; ?>" class="btn btn-outline-primary">
                                <i class="bi bi-graph-up"></i> Results
                            </a>
                            <a href="course-detail.php?id=<?php echo $test['course_id']; ?>" class="btn btn-outline-secondary">
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
