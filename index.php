<?php
include 'config.php';

// Ensure database is selected
if (!mysqli_select_db($conn, 'idea_validator')) {
    die("Database not available. Please run setup.php first.");
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_idea'])) {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $submitter_name = mysqli_real_escape_string($conn, trim($_POST['submitter_name']));
    $submitter_email = mysqli_real_escape_string($conn, trim($_POST['submitter_email']));
    
    // Validation
    if (empty($title) || empty($description) || empty($submitter_name) || empty($submitter_email)) {
        $error = "All fields are required.";
    } elseif (strlen($title) < 5 || strlen($title) > 255) {
        $error = "Title must be between 5 and 255 characters.";
    } elseif (strlen($description) < 20) {
        $error = "Description must be at least 20 characters.";
    } elseif (!filter_var($submitter_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        // Insert idea
        $sql = "INSERT INTO ideas (title, description, submitter_name, submitter_email, votes, status) 
                VALUES ('$title', '$description', '$submitter_name', '$submitter_email', 0, 'approved')";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Your idea has been submitted successfully!";
        } else {
            $error = "Error submitting idea: " . mysqli_error($conn);
        }
    }
}

// Get all approved ideas sorted by votes
$sql = "SELECT * FROM ideas WHERE status = 'approved' ORDER BY votes DESC, created_at DESC";
$result = mysqli_query($conn, $sql);

$ideas = array();
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ideas[] = $row;
    }
    mysqli_free_result($result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idea Validator - Submit Your Ideas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .idea-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .idea-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .vote-btn {
            min-width: 100px;
        }
        .vote-count {
            font-size: 1.5rem;
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
                        <a class="nav-link active" href="index.php"><i class="bi bi-house"></i> Home</a>
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
        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold"><i class="bi bi-lightbulb text-warning"></i> Website Idea Validator</h1>
            <p class="lead text-muted">Submit your website ideas and let the community vote on them!</p>
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

        <!-- Ideas Grid -->
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4"><i class="bi bi-fire"></i> Top Ideas</h2>
            </div>
        </div>

        <?php if (count($ideas) > 0): ?>
            <div class="row g-4">
                <?php foreach ($ideas as $idea): ?>
                    <?php
                    // Check if user already voted
                    $voter_ip = $_SERVER['REMOTE_ADDR'];
                    $check_sql = "SELECT id FROM votes WHERE idea_id = " . intval($idea['id']) . " AND voter_ip = '$voter_ip'";
                    $check_result = mysqli_query($conn, $check_sql);
                    $has_voted = mysqli_num_rows($check_result) > 0;
                    if ($check_result) mysqli_free_result($check_result);
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card idea-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($idea['title']); ?></h5>
                                    <span class="badge bg-primary"><?php echo number_format($idea['votes']); ?> votes</span>
                                </div>
                                <p class="card-text text-muted"><?php echo nl2br(htmlspecialchars(substr($idea['description'], 0, 150))); ?><?php if (strlen($idea['description']) > 150) echo '...'; ?></p>
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($idea['submitter_name']); ?> | 
                                    <i class="bi bi-clock"></i> <?php echo date('M j, Y', strtotime($idea['created_at'])); ?>
                                </small>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-outline-primary vote-btn" 
                                            onclick="vote(<?php echo $idea['id']; ?>)" 
                                            <?php echo $has_voted ? 'disabled' : ''; ?>>
                                        <i class="bi bi-hand-thumbs-up"></i> 
                                        <?php echo $has_voted ? 'Voted' : 'Vote'; ?>
                                    </button>
                                    <a href="view.php?id=<?php echo $idea['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> No ideas submitted yet. Be the first to <a href="submit.php">submit an idea</a>!
                </div>
            </div>
        <?php endif; ?>
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
    </script>
</body>
</html>
