<?php
/**
 * Dashboard - Online Class Portal
 * Main dashboard for all user types (admin, instructor, student)
 */

require_once 'portal_config.php';

// Require login
requireLogin();

// Get user data
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$user_name = $_SESSION['user_name'];

$conn = getDBConnection();

// Get statistics based on user type
$stats = [];

if ($user_type === 'student') {
    // Get enrolled courses count
    $sql = "SELECT COUNT(*) as count FROM enrollments WHERE student_id = '$user_id' AND status = 'enrolled'";
    $result = mysqli_query($conn, $sql);
    $stats['courses'] = mysqli_fetch_assoc($result)['count'];
    
    // Get pending assignments
    $sql = "SELECT COUNT(*) as count FROM assessments a
            INNER JOIN enrollments e ON a.course_id = e.course_id
            WHERE e.student_id = '$user_id' 
            AND a.status = 'published'
            AND a.due_date > NOW()";
    $result = mysqli_query($conn, $sql);
    $stats['assignments'] = mysqli_fetch_assoc($result)['count'];
    
    // Get unread messages
    $sql = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = '$user_id' AND is_read = 0";
    $result = mysqli_query($conn, $sql);
    $stats['messages'] = mysqli_fetch_assoc($result)['count'];
    
    // Get upcoming tests
    $sql = "SELECT COUNT(*) as count FROM tests t
            INNER JOIN enrollments e ON t.course_id = e.course_id
            WHERE e.student_id = '$user_id'
            AND t.status = 'published'
            AND t.start_datetime <= NOW()
            AND t.end_datetime > NOW()";
    $result = mysqli_query($conn, $sql);
    $stats['tests'] = mysqli_fetch_assoc($result)['count'];
    
} elseif ($user_type === 'instructor') {
    // Get teaching courses count
    $sql = "SELECT COUNT(*) as count FROM courses WHERE instructor_id = '$user_id' AND status = 'active'";
    $result = mysqli_query($conn, $sql);
    $stats['courses'] = mysqli_fetch_assoc($result)['count'];
    
    // Get total students
    $sql = "SELECT COUNT(DISTINCT e.student_id) as count 
            FROM enrollments e
            INNER JOIN courses c ON e.course_id = c.id
            WHERE c.instructor_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $stats['students'] = mysqli_fetch_assoc($result)['count'];
    
    // Get pending submissions to grade
    $sql = "SELECT COUNT(*) as count FROM submissions s
            INNER JOIN assessments a ON s.assessment_id = a.id
            INNER JOIN courses c ON a.course_id = c.id
            WHERE c.instructor_id = '$user_id'
            AND s.status = 'submitted'";
    $result = mysqli_query($conn, $sql);
    $stats['submissions'] = mysqli_fetch_assoc($result)['count'];
    
    // Get unread messages
    $sql = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = '$user_id' AND is_read = 0";
    $result = mysqli_query($conn, $sql);
    $stats['messages'] = mysqli_fetch_assoc($result)['count'];
    
} else {
    // Admin stats
    $sql = "SELECT COUNT(*) as count FROM users WHERE user_type = 'student'";
    $result = mysqli_query($conn, $sql);
    $stats['students'] = mysqli_fetch_assoc($result)['count'];
    
    $sql = "SELECT COUNT(*) as count FROM users WHERE user_type = 'instructor'";
    $result = mysqli_query($conn, $sql);
    $stats['instructors'] = mysqli_fetch_assoc($result)['count'];
    
    $sql = "SELECT COUNT(*) as count FROM courses WHERE status = 'active'";
    $result = mysqli_query($conn, $sql);
    $stats['courses'] = mysqli_fetch_assoc($result)['count'];
    
    $sql = "SELECT COUNT(*) as count FROM messages WHERE is_read = 0";
    $result = mysqli_query($conn, $sql);
    $stats['messages'] = mysqli_fetch_assoc($result)['count'];
}

// Get recent announcements
$announcements = [];
if ($user_type === 'student' || $user_type === 'instructor') {
    if ($user_type === 'student') {
        $sql = "SELECT a.*, u.first_name, u.last_name, c.course_name 
                FROM announcements a
                INNER JOIN users u ON a.posted_by = u.id
                LEFT JOIN courses c ON a.course_id = c.id
                WHERE a.status = 'active'
                AND (a.course_id IS NULL OR a.course_id IN (
                    SELECT course_id FROM enrollments WHERE student_id = '$user_id'
                ))
                ORDER BY a.is_pinned DESC, a.created_at DESC LIMIT 5";
    } else {
        $sql = "SELECT a.*, u.first_name, u.last_name, c.course_name 
                FROM announcements a
                INNER JOIN users u ON a.posted_by = u.id
                LEFT JOIN courses c ON a.course_id = c.id
                WHERE a.status = 'active'
                ORDER BY a.is_pinned DESC, a.created_at DESC LIMIT 5";
    }
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $announcements[] = $row;
    }
}

// Get recent courses
$courses = [];
if ($user_type === 'student') {
    $sql = "SELECT c.*, u.first_name as instructor_first, u.last_name as instructor_last
            FROM courses c
            INNER JOIN users u ON c.instructor_id = u.id
            INNER JOIN enrollments e ON c.id = e.course_id
            WHERE e.student_id = '$user_id' AND c.status = 'active'
            ORDER BY c.created_at DESC LIMIT 4";
} elseif ($user_type === 'instructor') {
    $sql = "SELECT c.* FROM courses c
            WHERE c.instructor_id = '$user_id' AND c.status = 'active'
            ORDER BY c.created_at DESC LIMIT 4";
} else {
    $sql = "SELECT c.*, u.first_name as instructor_first, u.last_name as instructor_last
            FROM courses c
            INNER JOIN users u ON c.instructor_id = u.id
            WHERE c.status = 'active'
            ORDER BY c.created_at DESC LIMIT 4";
}
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = $row;
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Online Class Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fc;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand i {
            font-size: 2rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        
        .top-navbar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .card-header-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .announcement-item {
            padding: 15px;
            border-left: 4px solid #667eea;
            background: #f8f9fa;
            border-radius: 0 10px 10px 0;
            margin-bottom: 15px;
        }
        
        .announcement-item.pinned {
            border-left-color: #f6c23e;
            background: #fffbf0;
        }
        
        .course-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        
        .course-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: white;
        }
        
        .course-body {
            padding: 20px;
        }
        
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #667eea;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-mortarboard-fill"></i>
            <h4 class="mt-2 mb-0">ClassPortal</h4>
        </div>
        
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active">
                <i class="bi bi-speedometer2"></i>Dashboard
            </a>
            
            <?php if ($user_type === 'admin'): ?>
                <a href="admin-users.php">
                    <i class="bi bi-people"></i>Users
                </a>
                <a href="admin-courses.php">
                    <i class="bi bi-book"></i>Courses
                </a>
            <?php elseif ($user_type === 'instructor'): ?>
                <a href="my-courses.php">
                    <i class="bi bi-book"></i>My Courses
                </a>
                <a href="create-assessment.php">
                    <i class="bi bi-clipboard-check"></i>Assessments
                </a>
                <a href="create-test.php">
                    <i class="bi bi-file-earmark-text"></i>Tests
                </a>
                <a href="grade-submissions.php">
                    <i class="bi bi-pencil-square"></i>Grade Submissions
                </a>
            <?php else: ?>
                <a href="my-courses.php">
                    <i class="bi bi-book"></i>My Courses
                </a>
                <a href="my-assessments.php">
                    <i class="bi bi-clipboard-check"></i>Assessments
                </a>
                <a href="my-tests.php">
                    <i class="bi bi-file-earmark-text"></i>Tests
                </a>
                <a href="my-grades.php">
                    <i class="bi bi-graph-up"></i>My Grades
                </a>
            <?php endif; ?>
            
            <a href="announcements.php">
                <i class="bi bi-megaphone"></i>Announcements
            </a>
            <a href="messages.php">
                <i class="bi bi-chat-dots"></i>Messages
                <?php if ($stats['messages'] > 0): ?>
                    <span class="badge bg-danger float-end"><?php echo $stats['messages']; ?></span>
                <?php endif; ?>
            </a>
            <a href="profile.php">
                <i class="bi bi-person"></i>Profile
            </a>
            <a href="logout.php">
                <i class="bi bi-box-arrow-right"></i>Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="mobile-toggle me-3" id="mobileToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h4 class="mb-0">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h4>
                    <p class="text-muted mb-0 small"><?php echo ucfirst($user_type); ?></p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="messages.php" class="position-relative text-muted">
                    <i class="bi bi-bell" style="font-size: 1.3rem;"></i>
                    <?php if ($stats['messages'] > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                            <?php echo $stats['messages']; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center gap-2" 
                            type="button" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                             style="width: 40px; height: 40px;">
                            <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                        </div>
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($user_name); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <?php if ($user_type === 'student'): ?>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-book"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['courses']; ?></div>
                        <div class="stat-label">Enrolled Courses</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f6c23e 0%, #f39c12 100%);">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['assignments']; ?></div>
                        <div class="stat-label">Pending Assignments</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #e74a3b 0%, #c0392b 100%);">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['tests']; ?></div>
                        <div class="stat-label">Active Tests</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['messages']; ?></div>
                        <div class="stat-label">Unread Messages</div>
                    </div>
                </div>
            <?php elseif ($user_type === 'instructor'): ?>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-book"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['courses']; ?></div>
                        <div class="stat-label">My Courses</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['students']; ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f6c23e 0%, #f39c12 100%);">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['submissions']; ?></div>
                        <div class="stat-label">To Grade</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['messages']; ?></div>
                        <div class="stat-label">Unread Messages</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['students']; ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['instructors']; ?></div>
                        <div class="stat-label">Instructors</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f6c23e 0%, #f39c12 100%);">
                            <i class="bi bi-book"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['courses']; ?></div>
                        <div class="stat-label">Active Courses</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #e74a3b 0%, #c0392b 100%);">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['messages']; ?></div>
                        <div class="stat-label">Unread Messages</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Announcements Section -->
        <?php if (!empty($announcements)): ?>
        <div class="content-card">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-megaphone me-2"></i>Recent Announcements</h5>
                <a href="announcements.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
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
                    <p class="mb-1 text-muted small"><?php echo htmlspecialchars(substr($announcement['message'], 0, 150)); ?><?php echo strlen($announcement['message']) > 150 ? '...' : ''; ?></p>
                    <small class="text-muted">
                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']); ?>
                        <?php if ($announcement['course_name']): ?>
                            <i class="bi bi-book ms-2"></i> <?php echo htmlspecialchars($announcement['course_name']); ?>
                        <?php endif; ?>
                    </small>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- My Courses Section -->
        <?php if (!empty($courses)): ?>
        <div class="content-card">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-book me-2"></i><?php echo $user_type === 'student' ? 'My' : 'Recent'; ?> Courses</h5>
                <a href="my-courses.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="row g-4">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="course-card">
                            <div class="course-header">
                                <h5 class="mb-1"><?php echo htmlspecialchars($course['course_name']); ?></h5>
                                <small><?php echo htmlspecialchars($course['course_code']); ?></small>
                            </div>
                            <div class="course-body">
                                <?php if ($user_type === 'student' && isset($course['instructor_first'])): ?>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-person"></i> 
                                        <?php echo htmlspecialchars($course['instructor_first'] . ' ' . $course['instructor_last']); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="text-muted small mb-3">
                                    <?php echo htmlspecialchars(substr($course['description'] ?? 'No description', 0, 80)); ?>...
                                </p>
                                <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-primary w-100">
                                    View Course
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        document.getElementById('mobileToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('mobileToggle');
            
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
</body>
</html>
