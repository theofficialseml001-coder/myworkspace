<?php
include 'config.php';

// Ensure database is selected
if (!mysqli_select_db($conn, 'idea_validator')) {
    die("Database not available. Please run setup.php first.");
}

// Get idea ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$idea_id = intval($_GET['id']);

// Get idea details
$sql = "SELECT * FROM ideas WHERE id = $idea_id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit;
}

$idea = mysqli_fetch_assoc($result);
mysqli_free_result($result);

// Check if user already voted
$voter_ip = $_SERVER['REMOTE_ADDR'];
$check_sql = "SELECT id FROM votes WHERE idea_id = $idea_id AND voter_ip = '$voter_ip'";
$check_result = mysqli_query($conn, $check_sql);
$has_voted = mysqli_num_rows($check_result) > 0;
if ($check_result) mysqli_free_result($check_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($idea['title']); ?> - Idea Validator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .vote-count-large {
            font-size: 3rem;
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-lightbulb"></i> Idea Validator
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="submit.php"><i class="bi bi-plus-circle"></i> Submit Idea</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Back Button -->
                <a href="index.php" class="btn btn-outline-secondary mb-4">
                    <i class="bi bi-arrow-left"></i> Back to All Ideas
                </a>

                <!-- Idea Card -->
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="mb-0"><i class="bi bi-lightbulb"></i> <?php echo htmlspecialchars($idea['title']); ?></h2>
                            <span class="badge bg-light text-primary fs-6">
                                <i class="bi bi-fire"></i> <?php echo number_format($idea['votes']); ?> votes
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Vote Section -->
                        <div class="text-center mb-4 p-3 bg-light rounded">
                            <div class="vote-count-large"><?php echo number_format($idea['votes']); ?></div>
                            <p class="text-muted mb-3">Total Votes</p>
                            <button class="btn btn-primary btn-lg vote-btn" 
                                    onclick="vote(<?php echo $idea['id']; ?>)" 
                                    <?php echo $has_voted ? 'disabled' : ''; ?>>
                                <i class="bi bi-hand-thumbs-up"></i> 
                                <?php echo $has_voted ? 'You Voted' : 'Vote for this Idea'; ?>
                            </button>
                        </div>

                        <!-- Description -->
                        <h4><i class="bi bi-card-text"></i> Description</h4>
                        <p class="lead"><?php echo nl2br(htmlspecialchars($idea['description'])); ?></p>

                        <!-- Submitter Info -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <h5><i class="bi bi-person-circle"></i> Submitted By</h5>
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($idea['submitter_name']); ?></p>
                            <p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($idea['submitter_email']); ?></p>
                        </div>

                        <!-- Date Info -->
                        <div class="mt-3 text-muted">
                            <small>
                                <i class="bi bi-clock"></i> Submitted on <?php echo date('F j, Y \a\t g:i A', strtotime($idea['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-<?php echo $idea['status'] == 'approved' ? 'success' : ($idea['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                <?php echo ucfirst($idea['status']); ?>
                            </span>
                            <button class="btn btn-outline-primary btn-sm" onclick="vote(<?php echo $idea['id']; ?>)" <?php echo $has_voted ? 'disabled' : ''; ?>>
                                <i class="bi bi-hand-thumbs-up"></i> Vote Now
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Share Section -->
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-share"></i> Share This Idea
                    </div>
                    <div class="card-body">
                        <p class="mb-2">Share this idea with your friends to get more votes!</p>
                        <div class="d-flex gap-2">
                            <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('Check out this idea: ' . $idea['title']); ?>&url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                               class="btn btn-outline-info" target="_blank">
                                <i class="bi bi-twitter"></i> Twitter
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                               class="btn btn-outline-primary" target="_blank">
                                <i class="bi bi-facebook"></i> Facebook
                            </a>
                            <button class="btn btn-outline-secondary" onclick="copyLink()">
                                <i class="bi bi-link-45deg"></i> Copy Link
                            </button>
                        </div>
                    </div>
                </div>
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
    <script>
        function vote(ideaId) {
            fetch('vote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'idea_id=' + ideaId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error voting');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while voting');
            });
        }

        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                alert('Link copied to clipboard!');
            }, function(err) {
                alert('Could not copy link: ' + err);
            });
        }
    </script>
</body>
</html>
