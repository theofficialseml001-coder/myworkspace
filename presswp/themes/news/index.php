<?php
/**
 * News Theme - PressWP
 * Professional news portal theme
 */

require_once dirname(__DIR__) . '/config.php';

// Fetch posts
$conn = get_db_connection();
$sql = "SELECT * FROM posts WHERE type='post' AND status='publish' ORDER BY created_at DESC LIMIT 8";
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
    <title><?php echo esc_html($site_title); ?> - News Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --news-red: #c00;
            --news-dark: #1a1a1a;
            --news-gray: #f4f4f4;
        }
        body { font-family: 'Georgia', serif; background: var(--news-gray); }
        
        /* Top Bar */
        .news-top-bar {
            background: var(--news-dark);
            color: white;
            padding: 8px 0;
            font-size: 0.85rem;
        }
        
        /* Header */
        .news-header {
            background: white;
            padding: 30px 0;
            border-bottom: 4px solid var(--news-red);
        }
        .news-logo {
            font-family: 'Impact', sans-serif;
            font-size: 3rem;
            color: var(--news-dark);
            text-transform: uppercase;
            letter-spacing: -2px;
        }
        .news-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Navigation */
        .news-nav {
            background: var(--news-red);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .news-nav .nav-link {
            color: white !important;
            padding: 12px 18px;
            font-weight: bold;
            text-transform: uppercase;
            font-family: 'Arial', sans-serif;
        }
        .news-nav .nav-link:hover {
            background: rgba(255,255,255,0.2);
        }
        
        /* Breaking News */
        .breaking-news {
            background: var(--news-dark);
            color: white;
            padding: 10px 0;
        }
        .breaking-label {
            background: var(--news-red);
            padding: 5px 15px;
            font-weight: bold;
            text-transform: uppercase;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Main Story */
        .main-story {
            background: white;
            margin-bottom: 20px;
        }
        .main-story-img {
            height: 400px;
            background: linear-gradient(45deg, #333, #666);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
        .main-story h1 {
            font-size: 2.5rem;
            line-height: 1.2;
        }
        
        /* Article Cards */
        .article-card {
            background: white;
            border: none;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .article-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .article-category {
            color: var(--news-red);
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.8rem;
            font-family: 'Arial', sans-serif;
        }
        .article-title {
            font-size: 1.2rem;
            line-height: 1.3;
            margin: 10px 0;
        }
        .article-meta {
            color: #999;
            font-size: 0.8rem;
        }
        
        /* Sidebar */
        .sidebar-widget {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        .widget-title {
            border-bottom: 2px solid var(--news-red);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .trending-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .trending-number {
            color: var(--news-red);
            font-size: 1.5rem;
            font-weight: bold;
            margin-right: 10px;
        }
        
        /* Footer */
        footer {
            background: var(--news-dark);
            color: white;
            padding: 40px 0 20px;
        }
        
        .btn-news {
            background: var(--news-red);
            color: white;
            border: none;
        }
        .btn-news:hover {
            background: #a00;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="news-top-bar">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span id="current-date"></span>
                </div>
                <div class="col-md-6 text-end">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="news-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="news-logo mb-0"><?php echo esc_html($site_title); ?></h1>
                </div>
                <div class="col-md-4 text-end">
                    <div class="news-date" id="full-date"></div>
                    <?php if (is_admin()): ?>
                    <a href="admin/dashboard.php" class="btn btn-outline-danger btn-sm mt-2">Admin Panel</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg news-nav">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#newsNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="newsNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">World</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Politics</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Business</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Technology</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Sports</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Entertainment</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Health</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Opinion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breaking News -->
    <div class="breaking-news">
        <div class="container">
            <div class="d-flex align-items-center">
                <span class="breaking-label me-3">Breaking News</span>
                <marquee behavior="scroll" direction="left">Major developments in technology sector | Global markets show positive trends | Weather update: Storm system approaching the region</marquee>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-4">
        <div class="row">
            <!-- Main Story -->
            <div class="col-lg-8">
                <div class="main-story">
                    <div class="main-story-img">
                        <i class="fas fa-newspaper fa-5x"></i>
                    </div>
                    <div class="p-4">
                        <span class="article-category">Featured Story</span>
                        <h1>Headline News: Important Developments Today</h1>
                        <p class="lead">Stay informed with the latest breaking news and in-depth analysis from our team of expert journalists.</p>
                        <div class="article-meta">
                            <i class="far fa-clock"></i> Updated: <span id="update-time"></span>
                        </div>
                    </div>
                </div>

                <!-- Latest News Grid -->
                <h3 class="mb-3 border-bottom pb-2">Latest News</h3>
                <div class="row">
                    <?php foreach ($posts as $post): ?>
                    <div class="col-md-6">
                        <div class="article-card">
                            <div class="card-body">
                                <span class="article-category">News</span>
                                <h4 class="article-title"><?php echo esc_html($post['title']); ?></h4>
                                <p class="card-text"><?php echo esc_html(substr($post['content'], 0, 100)); ?>...</p>
                                <div class="article-meta">
                                    <i class="far fa-clock"></i> <?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Load More -->
                <div class="text-center mt-4">
                    <button class="btn btn-news px-5">Load More News</button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Trending -->
                <div class="sidebar-widget">
                    <h4 class="widget-title">Trending Now</h4>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <div class="trending-item">
                        <span class="trending-number"><?php echo $i; ?></span>
                        <a href="#" class="text-decoration-none text-dark">
                            Popular news headline that readers are clicking on today
                        </a>
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- Newsletter -->
                <div class="sidebar-widget bg-light">
                    <h4 class="widget-title">Newsletter</h4>
                    <p>Get the day's top headlines delivered to your inbox every morning.</p>
                    <form>
                        <input type="email" class="form-control mb-2" placeholder="Your email address">
                        <button type="submit" class="btn btn-news w-100">Subscribe</button>
                    </form>
                </div>

                <!-- Categories -->
                <div class="sidebar-widget">
                    <h4 class="widget-title">Categories</h4>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-secondary">World</span>
                        <span class="badge bg-secondary">Politics</span>
                        <span class="badge bg-secondary">Business</span>
                        <span class="badge bg-secondary">Tech</span>
                        <span class="badge bg-secondary">Sports</span>
                        <span class="badge bg-secondary">Health</span>
                        <span class="badge bg-secondary">Science</span>
                        <span class="badge bg-secondary">Entertainment</span>
                    </div>
                </div>

                <!-- Weather Widget -->
                <div class="sidebar-widget">
                    <h4 class="widget-title">Weather</h4>
                    <div class="text-center">
                        <i class="fas fa-sun fa-4x text-warning mb-3"></i>
                        <h2>72°F</h2>
                        <p class="text-muted">Sunny and clear</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h4><?php echo esc_html($site_title); ?></h4>
                    <p>Your trusted source for breaking news, analysis, and exclusive interviews.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Sections</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">World News</a></li>
                        <li><a href="#" class="text-white">Politics</a></li>
                        <li><a href="#" class="text-white">Business</a></li>
                        <li><a href="#" class="text-white">Technology</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contact</h5>
                    <p>
                        Newsroom: (555) 123-4567<br>
                        Tips: tips@newsportal.com<br>
                        Advertising: ads@newsportal.com
                    </p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo esc_html($site_title); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set current date
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
        document.getElementById('full-date').textContent = now.toLocaleDateString('en-US', options);
        document.getElementById('update-time').textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    </script>
</body>
</html>
