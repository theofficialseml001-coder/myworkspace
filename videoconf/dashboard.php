<?php
/**
 * Video Conference SFU Solution - Dashboard Page
 */

require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();
$conn = getDBConnection();

// Get user's meetings
$meetings = [];
$stmt = mysqli_prepare($conn, "SELECT * FROM meetings WHERE host_id = ? ORDER BY created_at DESC LIMIT 10");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $meetings[] = $row;
}

// Get upcoming meetings
$upcoming_meetings = [];
$stmt = mysqli_prepare($conn, "SELECT * FROM meetings WHERE host_id = ? AND scheduled_start > NOW() AND status = 'waiting' ORDER BY scheduled_start ASC LIMIT 5");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $upcoming_meetings[] = $row;
}

// Get user's subscription
$subscription = null;
$stmt = mysqli_prepare($conn, "SELECT sp.*, us.status as sub_status, us.expires_at FROM user_subscriptions us JOIN subscription_plans sp ON us.plan_id = sp.id WHERE us.user_id = ? AND us.status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$subscription = mysqli_fetch_assoc($result);

// If no subscription, get free plan
if (!$subscription) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM subscription_plans WHERE plan_type = 'free'");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $subscription = mysqli_fetch_assoc($result);
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #7c3aed;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 5px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .stat-card {
            border-radius: 15px;
            border: none;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .meeting-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .quick-action-btn {
            width: 150px;
            height: 150px;
            border-radius: 20px;
            border: none;
            transition: all 0.3s;
        }
        .quick-action-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3 d-none d-md-block">
                <div class="text-center mb-4">
                    <i class="bi bi-camera-video-fill text-white" style="font-size: 2.5rem;"></i>
                    <h5 class="text-white mt-2 fw-bold"><?php echo APP_NAME; ?></h5>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="meetings.php">
                            <i class="bi bi-calendar3"></i> Meetings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="recordings.php">
                            <i class="bi bi-record-circle"></i> Recordings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedule.php">
                            <i class="bi bi-calendar-plus"></i> Schedule
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacts.php">
                            <i class="bi bi-people"></i> Contacts
                        </a>
                    </li>
                    <?php if ($user['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/index.php">
                            <i class="bi bi-gear"></i> Admin Panel
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item mt-4">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person-circle"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="bi bi-gear-fill"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
                
                <div class="mt-auto pt-4">
                    <div class="card bg-white bg-opacity-10 border-0">
                        <div class="card-body p-3">
                            <small class="text-white-50">Your Plan</small>
                            <h6 class="text-white fw-bold"><?php echo htmlspecialchars($subscription['plan_name']); ?></h6>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar bg-white" role="progressbar" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Top Navbar -->
                <nav class="navbar navbar-light bg-white shadow-sm px-4 py-3">
                    <div class="d-flex align-items-center w-100">
                        <button class="btn btn-link d-md-none text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSidebar">
                            <i class="bi bi-list fs-4"></i>
                        </button>
                        <h4 class="mb-0 ms-2">Dashboard</h4>
                        <div class="ms-auto d-flex align-items-center gap-3">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMeetingModal">
                                <i class="bi bi-plus-circle"></i> New Meeting
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-link text-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-bell fs-5"></i>
                                    <span class="badge bg-danger position-absolute translate-middle">3</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">Notifications</h6></li>
                                    <li><a class="dropdown-item" href="#">Meeting reminder in 15 min</a></li>
                                    <li><a class="dropdown-item" href="#">New recording available</a></li>
                                    <li><a class="dropdown-item" href="#">John joined your meeting</a></li>
                                </ul>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-link text-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <img src="assets/uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" 
                                         class="rounded-circle" width="35" height="35" alt="Avatar">
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                                    <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
                
                <!-- Dashboard Content -->
                <div class="p-4">
                    <!-- Quick Actions -->
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <h5 class="fw-bold mb-3">Quick Actions</h5>
                        </div>
                        <div class="col-md-3 text-center">
                            <form action="meeting.php" method="GET">
                                <input type="hidden" name="action" value="start_instant">
                                <button type="submit" class="btn btn-primary quick-action-btn">
                                    <i class="bi bi-camera-video-fill fs-1"></i>
                                    <div class="mt-2 fw-bold">Start Instant Meeting</div>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-3 text-center">
                            <button class="btn btn-success quick-action-btn" data-bs-toggle="modal" data-bs-target="#joinMeetingModal">
                                <i class="bi bi-door-open-fill fs-1"></i>
                                <div class="mt-2 fw-bold">Join Meeting</div>
                            </button>
                        </div>
                        <div class="col-md-3 text-center">
                            <a href="schedule.php" class="btn btn-warning quick-action-btn text-white">
                                <i class="bi bi-calendar-plus-fill fs-1"></i>
                                <div class="mt-2 fw-bold">Schedule Meeting</div>
                            </a>
                        </div>
                        <div class="col-md-3 text-center">
                            <a href="share-screen.php" class="btn btn-info quick-action-btn text-white">
                                <i class="bi bi-display-fill fs-1"></i>
                                <div class="mt-2 fw-bold">Share Screen</div>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Stats Row -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card bg-primary text-white p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Total Meetings</h6>
                                        <h3 class="fw-bold mb-0"><?php echo count($meetings); ?></h3>
                                    </div>
                                    <i class="bi bi-calendar3 fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-success text-white p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Hours Streamed</h6>
                                        <h3 class="fw-bold mb-0">24.5</h3>
                                    </div>
                                    <i class="bi bi-clock-history fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-info text-white p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Recordings</h6>
                                        <h3 class="fw-bold mb-0">12</h3>
                                    </div>
                                    <i class="bi bi-record-circle fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-warning text-white p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Upcoming</h6>
                                        <h3 class="fw-bold mb-0"><?php echo count($upcoming_meetings); ?></h3>
                                    </div>
                                    <i class="bi bi-calendar-event fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Meetings -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Recent Meetings</h5>
                                <a href="meetings.php" class="btn btn-outline-primary btn-sm">View All</a>
                            </div>
                            <div class="card meeting-card">
                                <div class="card-body">
                                    <?php if (empty($meetings)): ?>
                                        <p class="text-muted text-center py-4">No meetings yet. Start your first meeting!</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Meeting ID</th>
                                                        <th>Title</th>
                                                        <th>Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($meetings as $meeting): ?>
                                                    <tr>
                                                        <td><code><?php echo htmlspecialchars($meeting['meeting_id']); ?></code></td>
                                                        <td><?php echo htmlspecialchars($meeting['title']); ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($meeting['created_at'])); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $meeting['status'] === 'active' ? 'success' : ($meeting['status'] === 'ended' ? 'secondary' : 'warning'); ?>">
                                                                <?php echo ucfirst($meeting['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="meeting.php?id=<?php echo $meeting['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-eye"></i> View
                                                            </a>
                                                            <?php if ($meeting['status'] !== 'ended'): ?>
                                                            <a href="meeting.php?id=<?php echo $meeting['id']; ?>&action=join" class="btn btn-sm btn-primary">
                                                                <i class="bi bi-play"></i> Join
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- New Meeting Modal -->
    <div class="modal fade" id="newMeetingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Start a New Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="api/create_meeting.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Meeting Title</label>
                            <input type="text" name="title" class="form-control" required placeholder="My Meeting">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meeting Type</label>
                            <select name="type" class="form-select">
                                <option value="instant">Instant Meeting</option>
                                <option value="scheduled">Scheduled Meeting</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password (Optional)</label>
                            <input type="text" name="password" class="form-control" placeholder="Leave empty for public meeting">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-camera-video"></i> Start Meeting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Join Meeting Modal -->
    <div class="modal fade" id="joinMeetingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Join a Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="meeting.php" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Meeting ID or Link</label>
                            <input type="text" name="meeting_id" class="form-control" required placeholder="Enter meeting ID">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-door-open"></i> Join Meeting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
