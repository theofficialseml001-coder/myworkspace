<?php
/**
 * Blog Theme - PressWP
 * Clean, minimalist theme for blogging platforms
 */

require_once dirname(__DIR__) . '/config.php';

// Fetch posts
$conn = get_db_connection();
$sql = "SELECT * FROM posts WHERE type='post' AND status='publish' ORDER BY created_at DESC";
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
    <title><?php echo esc_html($site_title); ?> - Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --blog-primary: #1a1a2e;
            --blog-accent: #e94560;
            --blog-bg: #f8f9fa;
        }
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: var(--blog-bg);
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Merriweather', serif;
        }
        .blog-header {
            background: white;
            border-bottom: 3px solid var(--blog-accent);
            padding: 40px 0;
            margin-bottom: 40px;
        }
        .blog-title {
            color: var(--blog-primary);
            font-weight: 700;
        }
        .post-card {
            background: white;
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        .post-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
        .post-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
        }
        .post-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .read-more {
            color: var(--blog-accent);
            text-decoration: none;
            font-weight: 600;
        }
        .read-more:hover {
            text-decoration: underline;
        }
        .sidebar-widget {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .widget-title {
            color: var(--blog-primary);
            border-bottom: 2px solid var(--blog-accent);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        footer {
            background: var(--blog-primary);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--blog-accent);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="blog-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="blog-title display-5"><?php echo esc_html($site_title); ?></h1>
                    <p class="text-muted mb-0">Thoughts, stories, and ideas</p>
                </div>
                <div class="col-md-4 text-end">
                    <?php if (is_admin()): ?>
                    <a href="admin/dashboard.php" class="btn btn-outline-primary">Admin Panel</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <!-- Posts Column -->
            <div class="col-lg-8">
                <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="author-avatar me-3">
                                <?php echo strtoupper(substr($post['title'], 0, 1)); ?>
                            </div>
                            <div class="post-meta">
                                <span>By Admin</span>
                                <span class="mx-2">•</span>
                                <span><?php echo date('F d, Y', strtotime($post['created_at'])); ?></span>
                            </div>
                        </div>
                        <h2 class="mb-3"><?php echo esc_html($post['title']); ?></h2>
                        <div class="post-content">
                            <p><?php echo esc_html(substr($post['content'], 0, 300)); ?>...</p>
                        </div>
                        <a href="#" class="read-more mt-3 d-inline-block">Read More →</a>
                    </div>
                </article>
                <?php endforeach; ?>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- About Widget -->
                <div class="sidebar-widget">
                    <h4 class="widget-title">About</h4>
                    <p>Welcome to our blog! We share insights, tutorials, and stories about technology, life, and everything in between.</p>
                </div>

                <!-- Categories Widget -->
                <div class="sidebar-widget">
                    <h4 class="widget-title">Categories</h4>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="#" class="text-decoration-none">Technology</a> <span class="badge bg-secondary">12</span></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Lifestyle</a> <span class="badge bg-secondary">8</span></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Travel</a> <span class="badge bg-secondary">5</span></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Food</a> <span class="badge bg-secondary">7</span></li>
                    </ul>
                </div>

                <!-- Newsletter Widget -->
                <div class="sidebar-widget">
                    <h4 class="widget-title">Newsletter</h4>
                    <p>Subscribe to get the latest posts delivered to your inbox.</p>
                    <form>
                        <div class="mb-2">
                            <input type="email" class="form-control" placeholder="Your email">
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo esc_html($site_title); ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
