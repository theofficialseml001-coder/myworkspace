<?php
global $cms_db;
$posts = db_fetch_all(db_query("SELECT * FROM posts WHERE post_type='post' AND status='published' ORDER BY published_at DESC LIMIT 6"));
$pages = db_fetch_all(db_query("SELECT * FROM posts WHERE post_type='page' AND status='published' LIMIT 3"));
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3"><?php echo get_option('site_title'); ?></h1>
        <p class="lead mb-4"><?php echo get_option('site_description'); ?></p>
        <a href="?page=contact" class="btn btn-light btn-lg me-2">Get Started</a>
        <a href="#features" class="btn btn-outline-light btn-lg">Learn More</a>
    </div>
</section>

<!-- Features Section -->
<section class="section" id="features">
    <div class="container">
        <div class="section-title">
            <h2>Our Services</h2>
            <p class="text-muted">Professional solutions for your business needs</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-box bg-white text-center">
                    <i class="fas fa-rocket fa-3x text-primary mb-3"></i>
                    <h4>Fast Performance</h4>
                    <p class="text-muted">Optimized for speed and efficiency to deliver the best user experience.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box bg-white text-center">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h4>Secure & Reliable</h4>
                    <p class="text-muted">Enterprise-grade security with regular updates and monitoring.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box bg-white text-center">
                    <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                    <h4>Responsive Design</h4>
                    <p class="text-muted">Perfect display on all devices from desktop to mobile.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Latest Posts Section -->
<section class="section bg-light">
    <div class="container">
        <div class="section-title">
            <h2>Latest News</h2>
            <p class="text-muted">Stay updated with our latest articles</p>
        </div>
        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <?php if ($post['featured_image']): ?>
                    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <?php else: ?>
                    <div style="height:200px;background:#e9ecef;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars(substr($post['excerpt'] ?: $post['content'], 0, 100)); ?>...</p>
                        <a href="?post=<?php echo $post['slug']; ?>" class="btn btn-outline-primary">Read More</a>
                    </div>
                    <div class="card-footer text-muted small">
                        <i class="far fa-calendar"></i> <?php echo date('M j, Y', strtotime($post['published_at'])); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section">
    <div class="container text-center">
        <h2 class="mb-4">Ready to Get Started?</h2>
        <p class="lead text-muted mb-4">Contact us today to learn more about how we can help your business grow.</p>
        <a href="?page=contact" class="btn btn-primary btn-lg">Contact Us Now</a>
    </div>
</section>
