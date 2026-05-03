<?php
/**
 * School Theme - PressWP
 * Educational institution website theme
 */

require_once dirname(__DIR__) . '/config.php';

// Fetch posts
$conn = get_db_connection();
$sql = "SELECT * FROM posts WHERE type='post' AND status='publish' ORDER BY created_at DESC LIMIT 4";
$result = mysqli_query($conn, $sql);
$posts = array();
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    mysqli_free_result($result);
}
mysqli_close($conn);

$site_title = get_option('site_title');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($site_title); ?> - School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --school-blue: #003366;
            --school-gold: #fbb917;
            --school-light: #f8f9fa;
        }
        body { font-family: 'Arial', sans-serif; }
        
        /* Top Bar */
        .top-bar {
            background: var(--school-blue);
            color: white;
            padding: 10px 0;
            font-size: 0.9rem;
        }
        
        /* Main Header */
        .school-header {
            background: white;
            padding: 20px 0;
            border-bottom: 4px solid var(--school-gold);
        }
        .school-logo {
            font-size: 2rem;
            font-weight: bold;
            color: var(--school-blue);
        }
        
        /* Navigation */
        .school-nav {
            background: var(--school-blue);
        }
        .school-nav .nav-link {
            color: white !important;
            padding: 15px 20px;
        }
        .school-nav .nav-link:hover {
            background: var(--school-gold);
            color: var(--school-blue) !important;
        }
        
        /* Hero Slider */
        .hero-slider {
            background: linear-gradient(rgba(0,51,102,0.8), rgba(0,51,102,0.8)), url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 400"><rect fill="%23003366" width="1200" height="400"/></svg>');
            background-size: cover;
            color: white;
            padding: 100px 0;
        }
        
        /* Stats Section */
        .stats-section {
            background: var(--school-gold);
            padding: 50px 0;
        }
        .stat-box {
            text-align: center;
            color: var(--school-blue);
        }
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
        }
        
        /* Programs */
        .program-card {
            border: 2px solid var(--school-blue);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
        }
        .program-card:hover {
            box-shadow: 0 10px 30px rgba(0,51,102,0.2);
        }
        .program-header {
            background: var(--school-blue);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        /* News Section */
        .news-section {
            background: var(--school-light);
            padding: 60px 0;
        }
        .news-card {
            background: white;
            border-left: 4px solid var(--school-gold);
            margin-bottom: 20px;
        }
        
        /* Quick Links */
        .quick-links {
            background: var(--school-blue);
            color: white;
            padding: 40px 0;
        }
        .quick-links a {
            color: white;
            display: block;
            padding: 5px 0;
        }
        
        /* Footer */
        footer {
            background: #002244;
            color: white;
            padding: 30px 0;
        }
        
        .btn-school {
            background: var(--school-gold);
            color: var(--school-blue);
            font-weight: bold;
            border: none;
        }
        .btn-school:hover {
            background: #e5a815;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <i class="fas fa-phone me-2"></i> (555) 123-4567
                    <span class="mx-2">|</span>
                    <i class="fas fa-envelope me-2"></i> info@school.edu
                </div>
                <div class="col-md-6 text-end">
                    <a href="#" class="text-white me-3">Portal Login</a>
                    <a href="#" class="text-white">Careers</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="school-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="school-logo">
                        <i class="fas fa-graduation-cap me-2"></i>
                        <?php echo esc_html($site_title); ?>
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <button class="btn btn-school px-4">Apply Now</button>
                    <?php if (is_admin()): ?>
                    <a href="admin/dashboard.php" class="btn btn-outline-primary ms-2">Admin</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg school-nav">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#schoolNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="schoolNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Academics</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Admissions</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Athletics</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">News</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-slider text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Excellence in Education Since 1985</h1>
            <p class="lead mb-4">Nurturing young minds and building future leaders</p>
            <button class="btn btn-school btn-lg px-5">Virtual Tour</button>
            <button class="btn btn-outline-light btn-lg px-5 ms-2">Request Info</button>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 stat-box">
                    <div class="stat-number">1,200</div>
                    <div>Students Enrolled</div>
                </div>
                <div class="col-md-3 stat-box">
                    <div class="stat-number">85</div>
                    <div>Certified Teachers</div>
                </div>
                <div class="col-md-3 stat-box">
                    <div class="stat-number">98%</div>
                    <div>Graduation Rate</div>
                </div>
                <div class="col-md-3 stat-box">
                    <div class="stat-number">25+</div>
                    <div>Sports & Activities</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Academic Programs -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="color: var(--school-blue);">Academic Programs</h2>
                <p class="text-muted">Comprehensive education for every stage</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="program-card">
                        <div class="program-header">
                            <i class="fas fa-child fa-3x"></i>
                            <h4 class="mt-3">Elementary</h4>
                        </div>
                        <div class="p-4">
                            <p>Building strong foundations in core subjects with a focus on creativity and critical thinking.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="program-card">
                        <div class="program-header">
                            <i class="fas fa-book fa-3x"></i>
                            <h4 class="mt-3">Middle School</h4>
                        </div>
                        <div class="p-4">
                            <p>Preparing students for academic success with challenging curriculum and extracurricular activities.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="program-card">
                        <div class="program-header">
                            <i class="fas fa-user-graduate fa-3x"></i>
                            <h4 class="mt-3">High School</h4>
                        </div>
                        <div class="p-4">
                            <p>College preparatory programs with AP courses and career readiness training.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section class="news-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="color: var(--school-blue);">School News & Announcements</h2>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <?php foreach ($posts as $post): ?>
                    <div class="card news-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title"><?php echo esc_html($post['title']); ?></h5>
                                <small class="text-muted"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></small>
                            </div>
                            <p class="card-text"><?php echo esc_html(substr($post['content'], 0, 150)); ?>...</p>
                            <a href="#" class="btn btn-sm btn-outline-primary">Read More</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Upcoming Events</h5>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Oct 15</strong><br>Parent-Teacher Conference
                            </li>
                            <li class="list-group-item">
                                <strong>Oct 20</strong><br>Fall Sports Day
                            </li>
                            <li class="list-group-item">
                                <strong>Nov 1</strong><br>Science Fair
                            </li>
                            <li class="list-group-item">
                                <strong>Nov 15</strong><br>Thanksgiving Concert
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Links -->
    <section class="quick-links">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <a href="#">Calendar</a>
                    <a href="#">Lunch Menu</a>
                    <a href="#">Staff Directory</a>
                    <a href="#">Employment</a>
                </div>
                <div class="col-md-3">
                    <h5>Resources</h5>
                    <a href="#">Library</a>
                    <a href="#">Counseling</a>
                    <a href="#">Technology</a>
                    <a href="#">Safety</a>
                </div>
                <div class="col-md-3">
                    <h5>Community</h5>
                    <a href="#">PTA</a>
                    <a href="#">Volunteers</a>
                    <a href="#">Donations</a>
                    <a href="#">Partnerships</a>
                </div>
                <div class="col-md-3">
                    <h5>Contact Us</h5>
                    <p>
                        123 Education Lane<br>
                        Cityville, ST 12345<br>
                        Phone: (555) 123-4567<br>
                        Email: info@school.edu
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo esc_html($site_title); ?>. All rights reserved.</p>
            <p class="mb-0 small">Accredited by the Regional Education Board</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
