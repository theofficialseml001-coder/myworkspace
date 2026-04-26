<?php
/**
 * Course Detail - Online Class Portal
 * View course details, materials, assessments, tests, announcements
 */

require_once 'portal_config.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$conn = getDBConnection();

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($course_id <= 0) {
    header('Location: my-courses.php');
    exit;
}

// Verify access
$can_access = false;
$course = null;

if ($user_type === 'admin') {
    $sql = "SELECT c.*, u.first_name as instructor_first, u.last_name as instructor_last
            FROM courses c
            INNER JOIN users u ON c.instructor_id = u.id
            WHERE c.id = '$course_id'";
    $result = mysqli_query($conn, $sql);
    $course = mysqli_fetch_assoc($result);
    $can_access = true;
} elseif ($user_type === 'instructor') {
    $sql = "SELECT c.*, u.first_name as instructor_first, u.last_name as instructor_last
            FROM courses c
            INNER JOIN users u ON c.instructor_id = u.id
            WHERE c.id = '$course_id' AND c.instructor_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $course = mysqli_fetch_assoc($result);
    $can_access = ($course !== null);
} else {
    $sql = "SELECT c.*, u.first_name as instructor_first, u.last_name as instructor_last, e.status as enrollment_status
            FROM courses c
            INNER JOIN users u ON c.instructor_id = u.id
            INNER JOIN enrollments e ON c.id = e.course_id
            WHERE c.id = '$course_id' AND e.student_id = '$user_id' AND e.status = 'enrolled'";
    $result = mysqli_query($conn, $sql);
    $course = mysqli_fetch_assoc($result);
    $can_access = ($course !== null);
}

if (!$can_access || !$course) {
    $_SESSION['error_message'] = 'You do not have access to this course.';
    header('Location: my-courses.php');
    exit;
}

// Get course materials
$materials = [];
$sql = "SELECT * FROM course_materials WHERE course_id = '$course_id' ORDER BY uploaded_at DESC";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $materials[] = $row;
}

// Get assessments
$assessments = [];
$sql = "SELECT * FROM assessments WHERE course_id = '$course_id' AND status = 'published' ORDER BY due_date ASC";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $assessments[] = $row;
}

// Get tests
$tests = [];
$sql = "SELECT * FROM tests WHERE course_id = '$course_id' AND status = 'published' ORDER BY start_datetime ASC";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $tests[] = $row;
}

// Get announcements
$announcements = [];
$sql = "SELECT a.*, u.first_name, u.last_name 
        FROM announcements a
        INNER JOIN users u ON a.posted_by = u.id
        WHERE a.course_id = '$course_id' OR a.course_id IS NULL
        ORDER BY a.is_pinned DESC, a.created_at DESC
        LIMIT 10";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $announcements[] = $row;
}

$page_title = $course['course_name'];
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="my-courses.php">My Courses</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($course['course_name']); ?></li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="bi bi-book me-2"></i><?php echo htmlspecialchars($course['course_name']); ?></h2>
                    <p class="text-muted mb-0">
                        <?php echo htmlspecialchars($course['course_code']); ?> | 
                        Instructor: <?php echo htmlspecialchars($course['instructor_first'] . ' ' . $course['instructor_last']); ?>
                    </p>
                </div>
                <?php if ($user_type === 'instructor'): ?>
                    <a href="manage-course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">
                        <i class="bi bi-gear me-1"></i>Manage Course
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Announcements -->
            <div class="content-card mb-4">
                <div class="card-header-custom">
                    <h5 class="mb-0"><i class="bi bi-megaphone me-2"></i>Announcements</h5>
                </div>
                <?php if (empty($announcements)): ?>
                    <p class="text-muted text-center py-3">No announcements yet.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcement-item <?php echo $announcement['is_pinned'] ? 'pinned' : ''; ?>">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">
                                    <?php if ($announcement['is_pinned']): ?>
                                        <i class="bi bi-pin-fill text-warning me-1"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($announcement['title']); ?>
                                </h6>
                                <small class="text-muted"><?php echo formatDate($announcement['created_at'], 'M d, Y'); ?></small>
                            </div>
                            <p class="mb-1 text-muted small"><?php echo nl2br(htmlspecialchars($announcement['message'])); ?></p>
                            <small class="text-muted">
                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Course Materials -->
            <div class="content-card mb-4">
                <div class="card-header-custom">
                    <h5 class="mb-0"><i class="bi bi-folder me-2"></i>Learning Materials</h5>
                </div>
                <?php if (empty($materials)): ?>
                    <p class="text-muted text-center py-3">No materials uploaded yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($materials as $material): ?>
                            <a href="<?php echo htmlspecialchars($material['file_path']); ?>" class="list-group-item list-group-item-action" download>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-earmark me-2"></i>
                                        <strong><?php echo htmlspecialchars($material['title']); ?></strong>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($material['description'] ?? ''); ?></small>
                                    </div>
                                    <span class="badge bg-secondary"><?php echo strtoupper(pathinfo($material['file_path'], PATHINFO_EXTENSION)); ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Assessments -->
            <div class="content-card mb-4">
                <div class="card-header-custom">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Assessments & Assignments</h5>
                </div>
                <?php if (empty($assessments)): ?>
                    <p class="text-muted text-center py-3">No assessments available.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Due Date</th>
                                    <th>Points</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assessments as $assessment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assessment['title']); ?></td>
                                        <td><span class="badge bg-info"><?php echo ucfirst($assessment['type']); ?></span></td>
                                        <td><?php echo formatDate($assessment['due_date'], 'M d, Y h:i A'); ?></td>
                                        <td><?php echo $assessment['total_points']; ?></td>
                                        <td>
                                            <?php if ($user_type === 'student'): ?>
                                                <a href="submit-assessment.php?id=<?php echo $assessment['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    Submit
                                                </a>
                                            <?php else: ?>
                                                <a href="view-submissions.php?id=<?php echo $assessment['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                    View Submissions
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tests -->
            <div class="content-card">
                <div class="card-header-custom">
                    <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Tests & Exams</h5>
                </div>
                <?php if (empty($tests)): ?>
                    <p class="text-muted text-center py-3">No tests scheduled.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Duration</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tests as $test): ?>
                                    <?php
                                    $now = date('Y-m-d H:i:s');
                                    $can_take = ($test['start_datetime'] <= $now && $test['end_datetime'] > $now);
                                    $upcoming = ($test['start_datetime'] > $now);
                                    $ended = ($test['end_datetime'] <= $now);
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($test['title']); ?></td>
                                        <td><?php echo $test['duration_minutes']; ?> min</td>
                                        <td><?php echo formatDate($test['start_datetime'], 'M d, h:i A'); ?></td>
                                        <td><?php echo formatDate($test['end_datetime'], 'M d, h:i A'); ?></td>
                                        <td>
                                            <?php if ($user_type === 'student'): ?>
                                                <?php if ($can_take): ?>
                                                    <a href="take-test.php?id=<?php echo $test['id']; ?>" class="btn btn-sm btn-success">
                                                        <i class="bi bi-play"></i> Start Test
                                                    </a>
                                                <?php elseif ($upcoming): ?>
                                                    <span class="badge bg-warning">Upcoming</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Ended</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="test-results.php?id=<?php echo $test['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                    Results
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Course Info -->
            <div class="content-card mb-4">
                <div class="card-header-custom">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Course Info</h5>
                </div>
                <div class="p-3">
                    <p class="mb-2"><strong>Credits:</strong> <?php echo $course['credits']; ?></p>
                    <p class="mb-2"><strong>Status:</strong> 
                        <span class="badge bg-<?php echo $course['status'] === 'active' ? 'success' : 'secondary'; ?>">
                            <?php echo ucfirst($course['status']); ?>
                        </span>
                    </p>
                    <?php if ($course['description']): ?>
                        <hr>
                        <p class="small text-muted mb-0"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="content-card">
                <div class="card-header-custom">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Quick Links</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="messages.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-chat-dots me-2"></i>Messages
                    </a>
                    <a href="grades.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-graph-up me-2"></i>My Grades
                    </a>
                    <?php if ($user_type === 'student'): ?>
                        <a href="browse-courses.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-search me-2"></i>Browse Courses
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
