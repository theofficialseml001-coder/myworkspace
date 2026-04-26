<?php
/**
 * My Grades
 * View grades for students
 */
require_once 'portal_config.php';
requireLogin();

if (!isStudent()) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'My Grades';
$conn = getDBConnection();
$student_id = $_SESSION['user_id'];

// Get all courses with grades
$grades_result = mysqli_query($conn, "
    SELECT c.id as course_id, c.name as course_name, c.code as course_code,
           u.full_name as instructor_name,
           COUNT(a.id) as total_assessments,
           COUNT(s.id) as submitted_count,
           COALESCE(SUM(s.score), 0) as total_score,
           COALESCE(SUM(a.max_score), 0) as max_possible_score,
           CASE 
               WHEN COALESCE(SUM(a.max_score), 0) > 0 
               THEN ROUND((COALESCE(SUM(s.score), 0) / SUM(a.max_score)) * 100, 2)
               ELSE 0 
           END as percentage
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN users u ON c.instructor_id = u.id
    LEFT JOIN assessments a ON c.id = a.course_id
    LEFT JOIN submissions s ON a.id = s.assessment_id AND s.student_id = e.student_id
    WHERE e.student_id = '$student_id' AND e.status = 'active'
    GROUP BY c.id
    ORDER BY c.name ASC
");

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-graph-up"></i> My Grades</h1>
    <a href="dashboard.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
</div>

<?php if (mysqli_num_rows($grades_result) === 0): ?>
<div class="content-card text-center py-5">
    <i class="bi bi-inbox fs-1 text-muted"></i>
    <h4 class="mt-3">No Grades Available</h4>
    <p class="text-muted">You are not enrolled in any courses yet.</p>
    <a href="browse-courses.php" class="btn btn-primary mt-2">
        <i class="bi bi-grid"></i> Browse Courses
    </a>
</div>
<?php else: ?>
<div class="row">
    <?php while ($grade = mysqli_fetch_assoc($grades_result)): ?>
    <?php
    $percentage = $grade['percentage'];
    if ($percentage >= 90) $badge_color = 'success';
    elseif ($percentage >= 80) $badge_color = 'primary';
    elseif ($percentage >= 70) $badge_color = 'info';
    elseif ($percentage >= 60) $badge_color = 'warning';
    else $badge_color = 'danger';
    
    $letter_grade = '';
    if ($percentage >= 90) $letter_grade = 'A';
    elseif ($percentage >= 80) $letter_grade = 'B';
    elseif ($percentage >= 70) $letter_grade = 'C';
    elseif ($percentage >= 60) $letter_grade = 'D';
    else $letter_grade = 'F';
    ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="content-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($grade['course_name']); ?></h5>
                    <small class="text-muted"><?php echo htmlspecialchars($grade['course_code']); ?></small>
                </div>
                <span class="badge bg-<?php echo $badge_color; ?> fs-6">
                    <?php echo $letter_grade; ?> (<?php echo $percentage; ?>%)
                </span>
            </div>
            
            <p class="text-muted small mb-3">
                Instructor: <?php echo $grade['instructor_name'] ? htmlspecialchars($grade['instructor_name']) : 'TBD'; ?>
            </p>
            
            <div class="progress mb-3" style="height: 10px;">
                <div class="progress-bar bg-<?php echo $badge_color; ?>" role="progressbar" 
                     style="width: <?php echo $percentage; ?>%;" 
                     aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            
            <div class="row text-center">
                <div class="col-6 border-end">
                    <small class="text-muted d-block">Total Score</small>
                    <strong><?php echo round($grade['total_score']); ?> / <?php echo round($grade['max_possible_score']); ?></strong>
                </div>
                <div class="col-6">
                    <small class="text-muted d-block">Submitted</small>
                    <strong><?php echo $grade['submitted_count']; ?> / <?php echo $grade['total_assessments']; ?></strong>
                </div>
            </div>
            
            <div class="mt-3 pt-3 border-top">
                <a href="course-detail.php?id=<?php echo $grade['course_id']; ?>" class="btn btn-sm btn-outline-primary w-100">
                    <i class="bi bi-eye"></i> View Course Details
                </a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<!-- Overall Summary -->
<?php
mysqli_data_seek($grades_result, 0);
$total_score = 0;
$total_max = 0;
while ($g = mysqli_fetch_assoc($grades_result)) {
    $total_score += $g['total_score'];
    $total_max += $g['max_possible_score'];
}
$overall_percentage = $total_max > 0 ? round(($total_score / $total_max) * 100, 2) : 0;
?>
<div class="content-card mt-4">
    <h5><i class="bi bi-pie-chart"></i> Overall Performance</h5>
    <div class="row align-items-center mt-3">
        <div class="col-md-8">
            <div class="progress" style="height: 20px;">
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: <?php echo $overall_percentage; ?>%;" 
                     aria-valuenow="<?php echo $overall_percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <h3 class="mb-0"><?php echo $overall_percentage; ?>%</h3>
            <small class="text-muted">Overall Average</small>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
