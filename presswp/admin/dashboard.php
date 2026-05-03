<?php
/**
 * PressWP Admin Dashboard
 * Main admin panel - Only accessible by administrators
 */

require_once '../config.php';

// Check if user is admin
if (!is_admin()) {
    redirect('login.php');
}

$current_user = get_current_user();
$conn = get_db_connection();

// Get statistics
$posts_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM posts"))['count'];
$users_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$plugins_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM plugins"))['count'];

// Get active theme
$active_theme = get_option('active_theme');

// Handle theme change (admin only)
if (isset($_POST['change_theme']) && is_admin()) {
    $new_theme = mysqli_real_escape_string($conn, $_POST['theme']);
    update_option('active_theme', $new_theme);
    $message = "Theme changed successfully!";
}

// Get plugins (view only for non-admins, but we already checked admin above)
$plugins_result = mysqli_query($conn, "SELECT * FROM plugins");
$plugins = array();
while ($row = mysqli_fetch_assoc($plugins_result)) {
    $plugins[] = $row;
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PressWP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --admin-sidebar-width: 250px;
            --admin-primary: #2c3e50;
            --admin-accent: #3498db;
        }
        body { background: #f8f9fa; }
        
        .sidebar {
            width: var(--admin-sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--admin-primary);
            color: white;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 20px;
            font-size: 1.5rem;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        .sidebar-menu li a {
            display: block;
            padding: 15px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 4px solid var(--admin-accent);
        }
        .sidebar-menu li a i {
            width: 25px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: var(--admin-sidebar-width);
            padding: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--admin-primary);
        }
        .stat-label {
            color: #6c757d;
        }
        
        .content-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .card-header-custom {
            padding: 20px;
            border-bottom: 1px solid #eee;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .card-body-custom {
            padding: 20px;
        }
        
        .plugin-item {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .plugin-active {
            color: #28a745;
        }
        .locked-badge {
            background: #dc3545;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-cog me-2"></i>PressWP Admin
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li><a href="#"><i class="fas fa-file-alt"></i>Posts</a></li>
            <li><a href="#"><i class="fas fa-file"></i>Pages</a></li>
            <li><a href="#"><i class="fas fa-palette"></i>Themes</a></li>
            <li><a href="#"><i class="fas fa-plug"></i>Plugins</a></li>
            <li><a href="#"><i class="fas fa-users"></i>Users</a></li>
            <li><a href="#"><i class="fas fa-cog"></i>Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard</h2>
            <div class="d-flex align-items-center">
                <span class="me-3">Welcome, <?php echo esc_html($current_user['username']); ?></span>
                <a href="../index.php" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
            </div>
        </div>

        <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary text-white">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-number"><?php echo $posts_count; ?></div>
                    <div class="stat-label">Total Posts</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success text-white">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $users_count; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info text-white">
                        <i class="fas fa-plug"></i>
                    </div>
                    <div class="stat-number"><?php echo $plugins_count; ?></div>
                    <div class="stat-label">Active Plugins</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning text-white">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="stat-number"><?php echo ucfirst($active_theme); ?></div>
                    <div class="stat-label">Active Theme</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Theme Selection -->
        <div class="row">
            <div class="col-md-6">
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="fas fa-palette me-2"></i>Change Theme
                    </div>
                    <div class="card-body-custom">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Select Theme</label>
                                <select name="theme" class="form-select">
                                    <option value="multipurpose" <?php echo ($active_theme == 'multipurpose') ? 'selected' : ''; ?>>Multipurpose (Business)</option>
                                    <option value="blog" <?php echo ($active_theme == 'blog') ? 'selected' : ''; ?>>Blog Platform</option>
                                    <option value="school" <?php echo ($active_theme == 'school') ? 'selected' : ''; ?>>School Website</option>
                                    <option value="news" <?php echo ($active_theme == 'news') ? 'selected' : ''; ?>>News Portal</option>
                                </select>
                            </div>
                            <button type="submit" name="change_theme" class="btn btn-primary">
                                <i class="fas fa-sync-alt"></i> Change Theme
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </div>
                    <div class="card-body-custom">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary"><i class="fas fa-plus"></i> New Post</button>
                            <button class="btn btn-outline-primary"><i class="fas fa-upload"></i> Upload Media</button>
                            <button class="btn btn-outline-primary"><i class="fas fa-user-plus"></i> Add User</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plugins Section (View Only - Locked for Admin Management) -->
        <div class="content-card">
            <div class="card-header-custom">
                <i class="fas fa-plug me-2"></i>Installed Plugins
                <span class="locked-badge ms-2"><i class="fas fa-lock"></i> Admin Only</span>
            </div>
            <div class="card-body-custom">
                <div class="alert alert-warning">
                    <i class="fas fa-lock me-2"></i>
                    <strong>Plugin Management Locked:</strong> Only administrators can modify plugin settings. 
                    All plugins are pre-installed and managed at the system level.
                </div>
                <?php foreach ($plugins as $plugin): ?>
                <div class="plugin-item">
                    <div>
                        <strong><?php echo esc_html($plugin['name']); ?></strong>
                        <p class="mb-0 text-muted small"><?php echo esc_html($plugin['description']); ?></p>
                    </div>
                    <div>
                        <?php if ($plugin['is_active']): ?>
                        <span class="plugin-active"><i class="fas fa-check-circle"></i> Active</span>
                        <?php else: ?>
                        <span class="text-muted"><i class="fas fa-times-circle"></i> Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="content-card">
            <div class="card-header-custom">
                <i class="fas fa-history me-2"></i>Recent Activity
            </div>
            <div class="card-body-custom">
                <p class="text-muted">System initialized. All plugins are active and configured.</p>
                <ul class="list-unstyled">
                    <li class="py-2 border-bottom">
                        <i class="fas fa-check text-success me-2"></i>
                        System plugins activated
                        <small class="text-muted float-end">Just now</small>
                    </li>
                    <li class="py-2 border-bottom">
                        <i class="fas fa-palette text-primary me-2"></i>
                        Theme '<?php echo ucfirst($active_theme); ?>' activated
                        <small class="text-muted float-end">Current session</small>
                    </li>
                    <li class="py-2">
                        <i class="fas fa-user text-info me-2"></i>
                        Admin logged in
                        <small class="text-muted float-end">Current session</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
