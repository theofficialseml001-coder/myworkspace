<?php
include 'config.php';

// Ensure database is selected
if (!mysqli_select_db($conn, 'idea_validator')) {
    die("Database not available. Please run setup.php first.");
}

$message = '';
$error = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $idea_id = intval($_POST['idea_id']);
        $sql = "UPDATE ideas SET status = 'approved' WHERE id = $idea_id";
        if (mysqli_query($conn, $sql)) {
            $message = "Idea approved successfully!";
        } else {
            $error = "Error approving idea: " . mysqli_error($conn);
        }
    } elseif (isset($_POST['reject'])) {
        $idea_id = intval($_POST['idea_id']);
        $sql = "UPDATE ideas SET status = 'rejected' WHERE id = $idea_id";
        if (mysqli_query($conn, $sql)) {
            $message = "Idea rejected successfully!";
        } else {
            $error = "Error rejecting idea: " . mysqli_error($conn);
        }
    }
}

// Get all pending ideas
$sql = "SELECT * FROM ideas WHERE status = 'pending' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$pending_ideas = array();
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pending_ideas[] = $row;
    }
    mysqli_free_result($result);
}

// Get all approved ideas
$sql = "SELECT * FROM ideas WHERE status = 'approved' ORDER BY votes DESC, created_at DESC";
$result = mysqli_query($conn, $sql);

$approved_ideas = array();
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $approved_ideas[] = $row;
    }
    mysqli_free_result($result);
}

// Get all rejected ideas
$sql = "SELECT * FROM ideas WHERE status = 'rejected' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$rejected_ideas = array();
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $rejected_ideas[] = $row;
    }
    mysqli_free_result($result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Idea Validator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="admin.php">
                <i class="bi bi-gear"></i> Admin Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house"></i> View Site</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold"><i class="bi bi-gear text-warning"></i> Admin Dashboard</h1>
            <p class="lead text-muted">Manage and moderate submitted ideas</p>
        </div>

        <!-- Alerts -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3><i class="bi bi-hourglass-split"></i> <?php echo count($pending_ideas); ?></h3>
                        <p class="mb-0">Pending Ideas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3><i class="bi bi-check-circle"></i> <?php echo count($approved_ideas); ?></h3>
                        <p class="mb-0">Approved Ideas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3><i class="bi bi-x-circle"></i> <?php echo count($rejected_ideas); ?></h3>
                        <p class="mb-0">Rejected Ideas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Ideas -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0"><i class="bi bi-hourglass-split"></i> Pending Ideas (<?php echo count($pending_ideas); ?>)</h4>
            </div>
            <div class="card-body">
                <?php if (count($pending_ideas) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Submitter</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_ideas as $idea): ?>
                                    <tr>
                                        <td><?php echo $idea['id']; ?></td>
                                        <td><?php echo htmlspecialchars($idea['title']); ?></td>
                                        <td><?php echo htmlspecialchars($idea['submitter_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($idea['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="idea_id" value="<?php echo $idea['id']; ?>">
                                                <button type="submit" name="approve" class="btn btn-sm btn-success" onclick="return confirm('Approve this idea?')">
                                                    <i class="bi bi-check"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="idea_id" value="<?php echo $idea['id']; ?>">
                                                <button type="submit" name="reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject this idea?')">
                                                    <i class="bi bi-x"></i> Reject
                                                </button>
                                            </form>
                                            <a href="view.php?id=<?php echo $idea['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No pending ideas to review.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Approved Ideas -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="bi bi-check-circle"></i> Approved Ideas (<?php echo count($approved_ideas); ?>)</h4>
            </div>
            <div class="card-body">
                <?php if (count($approved_ideas) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Votes</th>
                                    <th>Submitter</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approved_ideas as $idea): ?>
                                    <tr>
                                        <td><?php echo $idea['id']; ?></td>
                                        <td><?php echo htmlspecialchars($idea['title']); ?></td>
                                        <td><span class="badge bg-primary"><?php echo number_format($idea['votes']); ?></span></td>
                                        <td><?php echo htmlspecialchars($idea['submitter_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($idea['created_at'])); ?></td>
                                        <td>
                                            <a href="view.php?id=<?php echo $idea['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No approved ideas yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rejected Ideas -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="bi bi-x-circle"></i> Rejected Ideas (<?php echo count($rejected_ideas); ?>)</h4>
            </div>
            <div class="card-body">
                <?php if (count($rejected_ideas) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Submitter</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rejected_ideas as $idea): ?>
                                    <tr>
                                        <td><?php echo $idea['id']; ?></td>
                                        <td><?php echo htmlspecialchars($idea['title']); ?></td>
                                        <td><?php echo htmlspecialchars($idea['submitter_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($idea['created_at'])); ?></td>
                                        <td>
                                            <a href="view.php?id=<?php echo $idea['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No rejected ideas.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Idea Validator. Built with Bootstrap & PHP.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

    <!-- Posts Management -->
    <?php if ($action === 'posts'): ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>All Posts</span>
            <a href="admin.php?action=post_new" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Post</a>
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                <?php
                $all_posts = get_posts($conn, 100, 0, 'any');
                foreach($all_posts as $post):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                    <td><?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?></td>
                    <td><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></td>
                    <td><span class="badge bg-<?php echo $post['status'] === 'published' ? 'success' : 'secondary'; ?>"><?php echo $post['status']; ?></span></td>
                    <td><?php echo format_date($post['published_at'] ?? $post['created_at']); ?></td>
                    <td>
                        <a href="admin.php?action=post_edit&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <?php if(is_admin()): ?>
                        <a href="admin.php?action=post_delete&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this post?')"><i class="fas fa-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- New/Edit Post Form -->
    <?php if ($action === 'post_new' || $action === 'post_edit'): ?>
    <?php
    $post = null;
    if ($action === 'post_edit' && isset($_GET['id'])) {
        $post_id = (int)$_GET['id'];
        $result = mysqli_query($conn, "SELECT * FROM posts WHERE id=$post_id");
        $post = mysqli_fetch_assoc($result);
    }
    ?>
    <form method="POST" action="admin.php?action=post_save" enctype="multipart/form-data">
        <?php if($post): ?><input type="hidden" name="post_id" value="<?php echo $post['id']; ?>"><?php endif; ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug (optional)</label>
                            <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($post['slug'] ?? ''); ?>" placeholder="auto-generated">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" class="form-control" rows="15"><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">Publish</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" <?php echo ($post['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Post Type</label>
                            <select name="post_type" class="form-select">
                                <option value="post" <?php echo ($post['post_type'] ?? '') === 'post' ? 'selected' : ''; ?>>Post</option>
                                <option value="page" <?php echo ($post['post_type'] ?? '') === 'page' ? 'selected' : ''; ?>>Page</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><?php echo $post ? 'Update' : 'Publish'; ?></button>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header">Category</div>
                    <div class="card-body">
                        <select name="category_id" class="form-select">
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($post['category_id'] ?? 1) == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header">Featured Image</div>
                    <div class="card-body">
                        <?php if($post && $post['featured_image']): ?>
                        <img src="uploads/<?php echo $post['featured_image']; ?>" class="img-fluid mb-2 rounded">
                        <?php endif; ?>
                        <input type="file" name="featured_image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Excerpt</div>
                    <div class="card-body">
                        <textarea name="excerpt" class="form-control" rows="3"><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>

    <!-- Themes Management (Admin Only) -->
    <?php if ($action === 'themes' && is_admin()): ?>
    <div class="row">
        <?php foreach($themes as $theme): ?>
        <div class="col-md-4 mb-4">
            <div class="card theme-card <?php echo $theme['is_active'] ? 'active' : ''; ?>">
                <div style="height: 200px; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-image fa-4x text-muted"></i>
                </div>
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($theme['name']); ?></h5>
                    <p class="text-muted small"><?php echo htmlspecialchars($theme['description']); ?></p>
                    <p class="small"><strong>Version:</strong> <?php echo $theme['version']; ?> | <strong>Author:</strong> <?php echo $theme['author']; ?></p>
                    <?php if($theme['is_active']): ?>
                    <span class="badge bg-success">Active</span>
                    <?php else: ?>
                    <a href="admin.php?action=theme_activate&slug=<?php echo $theme['slug']; ?>" class="btn btn-primary btn-sm">Activate</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Plugins Management (Admin Only) -->
    <?php if ($action === 'plugins' && is_admin()): ?>
    <div class="alert alert-info"><i class="fas fa-info-circle"></i> All plugins are admin-managed only. Non-admin users cannot modify plugin settings.</div>
    <?php foreach($plugins as $plugin): ?>
    <div class="plugin-item">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5><?php echo htmlspecialchars($plugin['name']); ?> <?php if($plugin['admin_only']): ?><span class="admin-badge">Admin Only</span><?php endif; ?></h5>
                <p class="text-muted mb-1"><?php echo htmlspecialchars($plugin['description']); ?></p>
                <small><strong>Version:</strong> <?php echo $plugin['version']; ?> | <strong>Author:</strong> <?php echo $plugin['author']; ?></small>
            </div>
            <div>
                <a href="admin.php?action=plugin_toggle&slug=<?php echo $plugin['slug']; ?>" class="btn btn-<?php echo $plugin['is_active'] ? 'success' : 'secondary'; ?>">
                    <?php echo $plugin['is_active'] ? 'Active' : 'Inactive'; ?>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Settings (Admin Only) -->
    <?php if ($action === 'settings' && is_admin()): ?>
    <form method="POST" action="admin.php?action=settings_save">
        <div class="card">
            <div class="card-header">General Settings</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Site Title</label>
                    <input type="text" name="setting_site_title" class="form-control" value="<?php echo htmlspecialchars(get_option($conn, 'site_title')); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Site Description</label>
                    <textarea name="setting_site_description" class="form-control" rows="3"><?php echo htmlspecialchars(get_option($conn, 'site_description')); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Posts Per Page</label>
                    <input type="number" name="setting_posts_per_page" class="form-control" value="<?php echo get_option($conn, 'posts_per_page', 10); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Allow Comments</label>
                    <select name="setting_allow_comments" class="form-select">
                        <option value="1" <?php echo get_option($conn, 'allow_comments', 1) ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?php echo !get_option($conn, 'allow_comments', 1) ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </div>
    </form>
    <?php endif; ?>

    <!-- Pages, Media, Users, Comments placeholders -->
    <?php if (in_array($action, ['pages', 'media', 'users', 'comments'])): ?>
    <div class="alert alert-info">
        <h5><i class="fas fa-info-circle"></i> <?php echo ucfirst($action); ?> Management</h5>
        <p>This section is under development. Admin-only access is enforced for users management.</p>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
