<?php
include 'config.php';

// Ensure database is selected
if (!mysqli_select_db($conn, 'idea_validator')) {
    die("Database not available. Please run setup.php first.");
}

$message = '';
$error = '';
$form_data = array('title' => '', 'description' => '', 'submitter_name' => '', 'submitter_email' => '');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_idea'])) {
    $form_data['title'] = trim($_POST['title']);
    $form_data['description'] = trim($_POST['description']);
    $form_data['submitter_name'] = trim($_POST['submitter_name']);
    $form_data['submitter_email'] = trim($_POST['submitter_email']);
    
    $title = mysqli_real_escape_string($conn, $form_data['title']);
    $description = mysqli_real_escape_string($conn, $form_data['description']);
    $submitter_name = mysqli_real_escape_string($conn, $form_data['submitter_name']);
    $submitter_email = mysqli_real_escape_string($conn, $form_data['submitter_email']);
    
    // Validation
    if (empty($title) || empty($description) || empty($submitter_name) || empty($submitter_email)) {
        $error = "All fields are required.";
    } elseif (strlen($title) < 5 || strlen($title) > 255) {
        $error = "Title must be between 5 and 255 characters.";
    } elseif (strlen($description) < 20) {
        $error = "Description must be at least 20 characters.";
    } elseif (!filter_var($form_data['submitter_email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        // Insert idea
        $sql = "INSERT INTO ideas (title, description, submitter_name, submitter_email, votes, status) 
                VALUES ('$title', '$description', '$submitter_name', '$submitter_email', 0, 'pending')";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Your idea has been submitted successfully! It will appear after approval.";
            // Clear form data
            $form_data = array('title' => '', 'description' => '', 'submitter_name' => '', 'submitter_email' => '');
        } else {
            $error = "Error submitting idea: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Idea - Idea Validator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
                        <a class="nav-link active" href="submit.php"><i class="bi bi-plus-circle"></i> Submit Idea</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold"><i class="bi bi-plus-circle text-warning"></i> Submit Your Idea</h1>
                    <p class="lead text-muted">Share your website idea with the community!</p>
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

                <!-- Submission Form -->
                <div class="card shadow">
                    <div class="card-body p-4">
                        <form method="POST" action="submit.php">
                            <div class="mb-3">
                                <label for="title" class="form-label"><i class="bi bi-card-heading"></i> Idea Title</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       placeholder="Enter a catchy title for your idea" 
                                       value="<?php echo htmlspecialchars($form_data['title']); ?>" required>
                                <div class="form-text">Make it clear and concise (5-255 characters)</div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label"><i class="bi bi-card-text"></i> Description</label>
                                <textarea class="form-control" id="description" name="description" rows="6" 
                                          placeholder="Describe your idea in detail..." required><?php echo htmlspecialchars($form_data['description']); ?></textarea>
                                <div class="form-text">Explain what your website idea is about (minimum 20 characters)</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="submitter_name" class="form-label"><i class="bi bi-person"></i> Your Name</label>
                                    <input type="text" class="form-control" id="submitter_name" name="submitter_name" 
                                           placeholder="Your full name" 
                                           value="<?php echo htmlspecialchars($form_data['submitter_name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="submitter_email" class="form-label"><i class="bi bi-envelope"></i> Email Address</label>
                                    <input type="email" class="form-control" id="submitter_email" name="submitter_email" 
                                           placeholder="your@email.com" 
                                           value="<?php echo htmlspecialchars($form_data['submitter_email']); ?>" required>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" name="submit_idea" class="btn btn-primary btn-lg">
                                    <i class="bi bi-send"></i> Submit Idea
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Home
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-info-circle"></i> Submission Guidelines
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Be original and creative with your ideas</li>
                            <li>Provide clear and detailed descriptions</li>
                            <li>Use appropriate language</li>
                            <li>One idea per submission</li>
                            <li>Your idea will be reviewed before appearing publicly</li>
                        </ul>
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
</body>
</html>
