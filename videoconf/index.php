<?php
/**
 * Video Conference SFU Solution - Main Index Page
 * Advanced Bootstrap 5 Frontend
 */

require_once 'includes/config.php';

// Redirect to dashboard if logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Professional Video Conferencing</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #7c3aed;
            --accent-color: #06b6d4;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,165.3C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }
        
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .feature-icon i {
            font-size: 2.5rem;
            color: white;
        }
        
        .use-case-badge {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            padding: 8px 16px;
            border-radius: 20px;
            margin: 5px;
            display: inline-block;
        }
        
        .cta-button {
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.4);
        }
        
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-camera-video-fill text-primary"></i>
                <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#use-cases">Use Cases</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-primary" href="login.php">Login</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary" href="register.php">Sign Up Free</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 text-white">
                    <h1 class="display-3 fw-bold mb-4">
                        Next-Generation<br>
                        <span style="color: #06b6d4;">Video Conferencing</span><br>
                        Platform
                    </h1>
                    <p class="lead mb-4">
                        Experience crystal-clear HD video meetings with advanced features like 
                        real-time whiteboard, screen sharing, file sharing, group chat, and recording. 
                        Perfect for business, education, telehealth, and more.
                    </p>
                    <div class="d-flex gap-3 mb-5">
                        <a href="register.php" class="btn btn-light btn-lg cta-button">
                            <i class="bi bi-rocket-takeoff"></i> Start Free Meeting
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-lg cta-button">
                            <i class="bi bi-play-circle"></i> Learn More
                        </a>
                    </div>
                    <div class="d-flex gap-4">
                        <div>
                            <h3 class="fw-bold mb-0">10M+</h3>
                            <small>Active Users</small>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0">50M+</h3>
                            <small>Meetings Hosted</small>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0">99.9%</h3>
                            <small>Uptime SLA</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 600'%3E%3Crect fill='%234f46e5' opacity='0.1' width='800' height='600'/%3E%3Crect x='100' y='100' width='600' height='400' rx='20' fill='white' opacity='0.9'/%3E%3Crect x='120' y='120' width='400' height='300' rx='10' fill='%234f46e5' opacity='0.2'/%3E%3Ccircle cx='200' cy='200' r='40' fill='%237c3aed' opacity='0.5'/%3E%3Ccircle cx='300' cy='200' r='40' fill='%2306b6d4' opacity='0.5'/%3E%3Ccircle cx='400' cy='200' r='40' fill='%234f46e5' opacity='0.5'/%3E%3Crect x='540' y='120' width='160' height='120' rx='10' fill='%237c3aed' opacity='0.3'/%3E%3Crect x='540' y='260' width='160' height='120' rx='10' fill='%2306b6d4' opacity='0.3'/%3E%3Crect x='120' y='440' width='580' height='60' rx='10' fill='%234f46e5' opacity='0.1'/%3E%3C/svg%3E" 
                         alt="Video Conference Interface" 
                         class="img-fluid rounded-4 shadow-lg"
                         style="animation: float 3s ease-in-out infinite;">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Powerful Features</h2>
                <p class="lead text-muted">Everything you need for professional video conferencing</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="feature-icon">
                            <i class="bi bi-camera-video"></i>
                        </div>
                        <h4>HD Video Conferencing</h4>
                        <p class="text-muted">Crystal-clear 1080p HD video with adaptive bitrate streaming for smooth experience even on low bandwidth.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="feature-icon">
                            <i class="bi bi-display"></i>
                        </div>
                        <h4>Screen Sharing</h4>
                        <p class="text-muted">Share your entire screen, specific applications, or browser tabs with participants in real-time.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="feature-icon">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <h4>Interactive Whiteboard</h4>
                        <p class="text-muted">Collaborate in real-time with drawing tools, shapes, text, and annotations on a shared whiteboard.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="feature-icon">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h4>Group Chat</h4>
                        <p class="text-muted">Send messages, emojis, and files in public or private chats during meetings.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="feature-icon">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                        </div>
                        <h4>File Sharing</h4>
                        <p class="text-muted">Share documents, images, and files up to 100MB directly in the meeting chat.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="feature-icon">
                            <i class="bi bi-record-circle"></i>
                        </div>
                        <h4>Cloud Recording</h4>
                        <p class="text-muted">Record meetings to the cloud with automatic transcription and easy sharing options.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="feature-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h4>Instant Meetings</h4>
                        <p class="text-muted">Start meetings instantly with unique meeting IDs or schedule recurring meetings in advance.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4>Up to 1000 Participants</h4>
                        <p class="text-muted">Host large-scale events, webinars, and conferences with support for up to 1000 attendees.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="feature-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h4>Enterprise Security</h4>
                        <p class="text-muted">End-to-end encryption, waiting rooms, meeting passwords, and role-based access control.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Use Cases Section -->
    <section id="use-cases" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Perfect For Every Use Case</h2>
                <p class="lead text-muted">Versatile platform designed for multiple industries</p>
            </div>
            <div class="row g-3 justify-content-center">
                <div class="col-auto">
                    <span class="use-case-badge"><i class="bi bi-briefcase"></i> Business Meetings</span>
                </div>
                <div class="col-auto">
                    <span class="use-case-badge"><i class="bi bi-mortarboard"></i> Online Education</span>
                </div>
                <div class="col-auto">
                    <span class="use-case-badge"><i class="bi bi-heart-pulse"></i> Telehealth</span>
                </div>
                <div class="col-auto">
                    <span class="use-case-badge"><i class="bi bi-person-workspace"></i> Remote Work</span>
                </div>
                <div class="col-auto">
                    <span class="use-case-badge"><i class="bi bi-people-fill"></i> Social Media</span>
                </div>
                <div class="col-auto">
                    <span class="use-case-badge"><i class="bi bi-handshake"></i> Consultations</span>
                </div>
                <div class="col-auto">
                    <span class="use-case-badge"><i class="bi bi-camera"></i> Live Classes</span>
                </div>
                <div class="col-auto">
                    <span class="use-case-badge"><i class="bi bi-mic"></i> Interviews</span>
                </div>
                <div class="col-auto">
                    <span class="use-case-badge"><i class="bi bi-search"></i> Inspections</span>
                </div>
                <div class="col-auto">
                    <span class="use-case-badge"><i class="bi bi-heart"></i> Dating</span>
                </div>
                <div class="col-auto">
                    <span class="use-case-badge"><i class="bi bi-chat-square-text"></i> Group Discussions</span>
                </div>
                <div class="col-auto">
                    <span class="use-case-badge"><i class="bi bi-broadcast"></i> Webinars</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">10M+</div>
                        <p class="text-muted mb-0">Registered Users</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">50M+</div>
                        <p class="text-muted mb-0">Meetings Hosted</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">100+</div>
                        <p class="text-muted mb-0">Countries Served</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">99.9%</div>
                        <p class="text-muted mb-0">Uptime Guarantee</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Simple, Transparent Pricing</h2>
                <p class="lead text-muted">Choose the perfect plan for your needs</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="card-title">Free</h5>
                            <div class="display-6 fw-bold my-3">$0</div>
                            <small class="text-muted">Forever free</small>
                            <hr>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> 100 Participants</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> 40 min Duration</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Screen Sharing</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Group Chat</li>
                                <li class="mb-2 text-muted"><i class="bi bi-x-circle-fill"></i> Cloud Recording</li>
                            </ul>
                            <a href="register.php" class="btn btn-outline-primary w-100 mt-3">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 border-primary border-2 shadow">
                        <div class="card-body p-4">
                            <span class="badge bg-primary mb-2">Popular</span>
                            <h5 class="card-title">Basic</h5>
                            <div class="display-6 fw-bold my-3">$9.99<small class="fs-6 text-muted">/mo</small></div>
                            <hr>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> 200 Participants</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> 2 hr Duration</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> HD Video</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Whiteboard</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> 10 Recordings</li>
                            </ul>
                            <a href="register.php" class="btn btn-primary w-100 mt-3">Start Free Trial</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="card-title">Pro</h5>
                            <div class="display-6 fw-bold my-3">$19.99<small class="fs-6 text-muted">/mo</small></div>
                            <hr>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> 500 Participants</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> 4 hr Duration</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Full HD 1080p</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Analytics</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> 50 Recordings</li>
                            </ul>
                            <a href="register.php" class="btn btn-outline-primary w-100 mt-3">Start Free Trial</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="card-title">Enterprise</h5>
                            <div class="display-6 fw-bold my-3">$49.99<small class="fs-6 text-muted">/mo</small></div>
                            <hr>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> 1000 Participants</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> 8 hr Duration</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Unlimited Recording</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> SSO & SAML</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Dedicated Support</li>
                            </ul>
                            <a href="register.php" class="btn btn-outline-primary w-100 mt-3">Contact Sales</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
        <div class="container py-5 text-center text-white">
            <h2 class="display-4 fw-bold mb-4">Ready to Get Started?</h2>
            <p class="lead mb-4">Join millions of users worldwide and start hosting professional video meetings today.</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="register.php" class="btn btn-light btn-lg cta-button">
                    <i class="bi bi-rocket-takeoff"></i> Sign Up Free
                </a>
                <a href="login.php" class="btn btn-outline-light btn-lg cta-button">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-camera-video-fill text-primary"></i>
                        <?php echo APP_NAME; ?>
                    </h5>
                    <p class="text-muted">Professional video conferencing solution for businesses, educators, and individuals worldwide.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-white"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-twitter fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-linkedin fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-instagram fs-5"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Product</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Features</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Pricing</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Security</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Enterprise</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Resources</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Documentation</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">API Reference</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Blog</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Support</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Company</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Careers</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Contact</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> support@videoconf.com</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i> +1 (555) 123-4567</li>
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> San Francisco, CA</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <div class="text-center text-muted">
                <small>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
    </style>
</body>
</html>
