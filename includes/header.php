<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Online Class Portal</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-bg: #f8f9fc;
            --sidebar-width: 250px;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .sidebar-brand {
            padding: 1.5rem 1rem;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .sidebar-brand:hover {
            color: white;
        }
        
        .sidebar hr {
            border-color: rgba(255,255,255,0.15);
            margin: 0 1rem;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        
        .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
            min-height: 100vh;
        }
        
        /* Topbar */
        .topbar {
            background: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.15);
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .card-header-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #e3e6f0;
        }
        
        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #5a5c69;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--secondary-color);
            text-transform: uppercase;
        }
        
        /* Course Cards */
        .course-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58,59,69,0.15);
        }
        
        .course-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 1rem 1.25rem;
        }
        
        .course-body {
            padding: 1.25rem;
        }
        
        /* Announcement Items */
        .announcement-item {
            padding: 1rem;
            border-left: 4px solid #4e73df;
            background: #f8f9fc;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        
        .announcement-item.pinned {
            border-left-color: #f6c23e;
            background: #fffbf0;
        }
        
        /* Mobile Responsive */
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
            
            #mobileToggle {
                display: block !important;
            }
        }
        
        #mobileToggle {
            display: none;
        }
        
        /* Loading Spinner */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <a href="dashboard.php" class="sidebar-brand">
            <i class="bi bi-mortarboard-fill"></i>
            <span>Class Portal</span>
        </a>
        <hr>
        
        <?php
        $current_page = basename($_SERVER['PHP_SELF'], '.php');
        ?>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="my-courses.php" class="nav-link <?php echo $current_page === 'my-courses' ? 'active' : ''; ?>">
                    <i class="bi bi-book"></i>
                    <span>My Courses</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="announcements.php" class="nav-link <?php echo $current_page === 'announcements' ? 'active' : ''; ?>">
                    <i class="bi bi-megaphone"></i>
                    <span>Announcements</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="messages.php" class="nav-link <?php echo $current_page === 'messages' ? 'active' : ''; ?>">
                    <i class="bi bi-chat-dots"></i>
                    <span>Messages</span>
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        $conn = getDBConnection();
                        $uid = $_SESSION['user_id'];
                        $unread = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM messages WHERE receiver_id = '$uid' AND is_read = 0"))['count'];
                        if ($unread > 0) {
                            echo '<span class="badge bg-danger ms-auto">' . $unread . '</span>';
                        }
                    }
                    ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="grades.php" class="nav-link <?php echo $current_page === 'grades' ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up"></i>
                    <span>Grades</span>
                </a>
            </li>
            
            <?php if ($_SESSION['user_type'] === 'instructor' || $_SESSION['user_type'] === 'admin'): ?>
                <li class="nav-item">
                    <a href="assessments.php" class="nav-link <?php echo $current_page === 'assessments' ? 'active' : ''; ?>">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Assessments</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if ($_SESSION['user_type'] === 'admin'): ?>
                <li class="nav-item mt-3">
                    <small class="text-white-50 px-3 text-uppercase" style="font-size: 0.75rem;">Admin</small>
                </li>
                <li class="nav-item">
                    <a href="admin.php" class="nav-link <?php echo $current_page === 'admin' ? 'active' : ''; ?>">
                        <i class="bi bi-gear"></i>
                        <span>Admin Panel</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        
        <div class="mt-auto p-3">
            <hr style="border-color: rgba(255,255,255,0.15);">
            <a href="logout.php" class="nav-link text-white-50 hover-white">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="d-flex justify-content-between align-items-center">
                <button class="btn btn-link d-lg-none" id="mobileToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                
                <div class="ms-auto d-flex align-items-center gap-3">
                    <!-- Notifications -->
                    <div class="dropdown">
                        <a href="#" class="text-secondary position-relative" data-bs-toggle="dropdown">
                            <i class="bi bi-bell fs-5"></i>
                            <?php
                            if (isset($_SESSION['user_id'])) {
                                $conn = getDBConnection();
                                $uid = $_SESSION['user_id'];
                                $count = mysqli_fetch_assoc(mysqli_query($conn, "
                                    SELECT COUNT(*) as count FROM (
                                        SELECT id FROM announcements a 
                                        WHERE (a.course_id IS NULL OR a.course_id IN (
                                            SELECT course_id FROM enrollments WHERE student_id = '$uid'
                                        ))
                                        AND a.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                                    ) as recent
                                "))['count'];
                                if ($count > 0) {
                                    echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">' . $count . '</span>';
                                }
                            }
                            ?>
                        </a>
                    </div>
                    
                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <div class="user-dropdown" data-bs-toggle="dropdown">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                            </div>
                            <div class="d-none d-md-block">
                                <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                                <small class="text-muted"><?php echo ucfirst($_SESSION['user_type']); ?></small>
                            </div>
                            <i class="bi bi-chevron-down"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main class="p-4">
