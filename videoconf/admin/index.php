<?php
/**
 * Video Conference SFU Solution - Admin Dashboard
 */

require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = getCurrentUser();
$conn = getDBConnection();

// Check if user has admin role (you can modify this check based on your needs)
$is_admin = ($user['role'] === 'admin' || $user['email'] === 'admin@example.com');

if (!$is_admin) {
    redirect('../dashboard.php?error=Access denied');
}

// Get system statistics
$stats = [];

// Total users
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM users");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats['total_users'] = mysqli_fetch_assoc($result)['count'];

// Total meetings
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM meetings");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats['total_meetings'] = mysqli_fetch_assoc($result)['count'];

// Active meetings
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM meetings WHERE status = 'active'");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats['active_meetings'] = mysqli_fetch_assoc($result)['count'];

// Total recordings
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM recordings");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats['total_recordings'] = mysqli_fetch_assoc($result)['count'];

// Total storage used (in GB)
$stmt = mysqli_prepare($conn, "SELECT SUM(file_size) as total FROM recordings");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$stats['storage_used_gb'] = round(($data['total'] ?? 0) / (1024 * 1024 * 1024), 2);

// Active subscriptions
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM user_subscriptions WHERE status = 'active'");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stats['active_subscriptions'] = mysqli_fetch_assoc($result)['count'];

// Monthly revenue (if you have payment integration)
$stmt = mysqli_prepare($conn, "SELECT SUM(sp.price) as revenue FROM user_subscriptions us 
    JOIN subscription_plans sp ON us.plan_id = sp.id 
    WHERE us.status = 'active' AND DATE_FORMAT(us.created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$stats['monthly_revenue'] = $data['revenue'] ?? 0;

// Recent users
$stmt = mysqli_prepare($conn, "SELECT id, full_name, email, created_at FROM users ORDER BY created_at DESC LIMIT 10");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$recent_users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $recent_users[] = $row;
}

// Recent meetings
$stmt = mysqli_prepare($conn, "SELECT m.*, u.full_name as host_name FROM meetings m 
    JOIN users u ON m.host_id = u.id 
    ORDER BY m.created_at DESC LIMIT 10");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$recent_meetings = [];
while ($row = mysqli_fetch_assoc($result)) {
    $recent_meetings[] = $row;
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        }
        .admin-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 5px;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .stat-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar p-3">
                <div class="mb-4 px-2">
                    <h4 class="text-white mb-0">
                        <i class="bi bi-camera-video-fill"></i> <?php echo APP_NAME; ?>
                    </h4>
                    <small class="text-muted">Admin Panel</small>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link active" href="index.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="users.php">
                        <i class="bi bi-people-fill me-2"></i> Users
                    </a>
                    <a class="nav-link" href="meetings.php">
                        <i class="bi bi-camera-video me-2"></i> Meetings
                    </a>
                    <a class="nav-link" href="recordings.php">
                        <i class="bi bi-record-circle me-2"></i> Recordings
                    </a>
                    <a class="nav-link" href="subscriptions.php">
                        <i class="bi bi-credit-card me-2"></i> Subscriptions
                    </a>
                    <a class="nav-link" href="settings.php">
                        <i class="bi bi-gear-fill me-2"></i> Settings
                    </a>
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.1);">
                    <a class="nav-link" href="../dashboard.php">
                        <i class="bi bi-arrow-left me-2"></i> Back to App
                    </a>
                    <a class="nav-link" href="../logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 bg-light">
                <!-- Top Bar -->
                <div class="bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Dashboard Overview</h5>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted">Welcome, <?php echo htmlspecialchars($user['full_name']); ?></span>
                        <img src="<?php echo $user['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name']); ?>" 
                             class="rounded-circle" width="40" height="40" alt="Avatar">
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4">
                    <!-- Stats Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Total Users</h6>
                                            <h3 class="mb-0"><?php echo number_format($stats['total_users']); ?></h3>
                                        </div>
                                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-people-fill"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Total Meetings</h6>
                                            <h3 class="mb-0"><?php echo number_format($stats['total_meetings']); ?></h3>
                                            <small class="text-success">
                                                <i class="bi bi-arrow-up"></i> <?php echo $stats['active_meetings']; ?> active
                                            </small>
                                        </div>
                                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-camera-video-fill"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Recordings</h6>
                                            <h3 class="mb-0"><?php echo number_format($stats['total_recordings']); ?></h3>
                                            <small class="text-muted"><?php echo $stats['storage_used_gb']; ?> GB used</small>
                                        </div>
                                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-record-circle-fill"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Subscriptions</h6>
                                            <h3 class="mb-0"><?php echo number_format($stats['active_subscriptions']); ?></h3>
                                            <small class="text-success">$<?php echo number_format($stats['monthly_revenue'], 2); ?> this month</small>
                                        </div>
                                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                                            <i class="bi bi-credit-card-fill"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 py-3">
                                    <h6 class="mb-0">Recent Users</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Joined</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_users as $u): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 py-3">
                                    <h6 class="mb-0">Recent Meetings</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Host</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_meetings as $m): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($m['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($m['host_name']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $m['status'] === 'active' ? 'success' : ($m['status'] === 'ended' ? 'secondary' : 'warning'); ?>">
                                                            <?php echo ucfirst($m['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
