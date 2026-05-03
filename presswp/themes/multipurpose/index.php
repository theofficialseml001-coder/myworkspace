<?php
/**
 * Multipurpose Theme - PressWP
 * Professional theme for complex multipurpose websites
 */

require_once dirname(__DIR__) . '/config.php';

// Fetch posts
$conn = get_db_connection();
$sql = "SELECT * FROM posts WHERE type='post' AND status='publish' ORDER BY created_at DESC LIMIT 6";
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
    <title><?php echo esc_html($site_title); ?> - Multipurpose</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 100px 0;
        }
        .feature-box {
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            height: 100%;
        }
        .feature-box:hover { transform: translateY(-5px); }
        .service-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .service-card img { height: 200px; object-fit: cover; }
        .cta-section {
            background: var(--accent-color);
            color: white;
            padding: 60px 0;
        }
        footer {
            background: var(--primary-color);
            color: white;
            padding: 40px 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-layer-group me-2"></i><?php echo esc_html($site_title); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#portfolio">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#blog">Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <?php if (is_admin()): ?>
                    <li class="nav-item"><a class="nav-link btn btn-primary px-3" href="admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Professional Solutions for Your Business</h1>
            <p class="lead mb-4">We provide comprehensive services to help your business grow and succeed in the digital age.</p>
            <a href="#contact" class="btn btn-light btn-lg px-5">Get Started</a>
            <a href="#services" class="btn btn-outline-light btn-lg px-5 ms-2">Learn More</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5" id="services">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Our Services</h2>
                <p class="text-muted">Comprehensive solutions tailored to your needs</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box bg-white text-center">
                        <i class="fas fa-laptop-code fa-3x text-primary mb-3"></i>
                        <h4>Web Development</h4>
                        <p>Custom websites built with modern technologies.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box bg-white text-center">
                        <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                        <h4>Mobile Apps</h4>
                        <p>Native and cross-platform mobile applications.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box bg-white text-center">
                        <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                        <h4>Digital Marketing</h4>
                        <p>SEO, SEM, and social media marketing strategies.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section class="py-5 bg-light" id="portfolio">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Our Portfolio</h2>
                <p class="text-muted">Showcasing our best work</p>
            </div>
            <div class="row g-4">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="col-md-4">
                    <div class="card service-card">
                        <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                            <span class="text-white">Project <?php echo $i; ?></span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Project Title <?php echo $i; ?></h5>
                            <p class="card-text">Brief description of the project and its outcomes.</p>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section class="py-5" id="blog">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Latest News</h2>
                <p class="text-muted">Stay updated with our latest articles</p>
            </div>
            <div class="row g-4">
                <?php foreach ($posts as $post): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo esc_html($post['title']); ?></h5>
                            <p class="card-text"><?php echo esc_html(substr($post['content'], 0, 100)); ?>...</p>
                            <small class="text-muted"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section text-center" id="contact">
        <div class="container">
            <h2 class="mb-4">Ready to Start Your Project?</h2>
            <p class="lead mb-4">Contact us today for a free consultation.</p>
            <button class="btn btn-light btn-lg px-5">Contact Us</button>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>About Us</h5>
                    <p>We are a professional team dedicated to delivering excellence.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-white">Terms of Service</a></li>
                        <li><a href="#" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Connect With Us</h5>
                    <div>
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html($site_title); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
