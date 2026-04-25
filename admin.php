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
