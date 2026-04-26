    <!-- Hero Section -->
    <?php if(isset($theme_settings['hero_title'])): ?>
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 mb-3"><?php echo htmlspecialchars($theme_settings['hero_title']); ?></h1>
            <p class="lead mb-4"><?php echo $site_description; ?></p>
            <a href="#content" class="btn btn-light btn-lg">Explore Content</a>
        </div>
    </section>
    <?php endif; ?>

    <!-- Main Content -->
    <section id="content" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h2 class="mb-4">Latest Posts</h2>
                    <div class="row">
                        <?php if(!empty($posts)): ?>
                            <?php foreach($posts as $post): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card post-card h-100">
                                    <?php if($post['featured_image']): ?>
                                    <img src="uploads/<?php echo $post['featured_image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                    <?php else: ?>
                                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-image fa-3x text-white"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></span>
                                        <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                                        <p class="card-text text-muted"><?php echo get_excerpt($post['content'], 100); ?></p>
                                    </div>
                                    <div class="card-footer bg-white border-0">
                                        <small class="text-muted">
                                            <i class="far fa-user"></i> <?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?> | 
                                            <i class="far fa-calendar"></i> <?php echo format_date($post['published_at']); ?> |
                                            <i class="far fa-eye"></i> <?php echo $post['views']; ?>
                                        </small>
                                        <a href="index.php?post=<?php echo $post['slug']; ?>" class="btn btn-outline-primary btn-sm float-end">Read More</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <p class="text-muted">No posts found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if(isset($total_pages) && $total_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="sidebar-widget">
                        <h5><i class="fas fa-folder"></i> Categories</h5>
                        <ul class="list-group list-group-flush">
                            <?php foreach($categories as $cat): ?>
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                                <a href="index.php?category=<?php echo $cat['slug']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                                <span class="badge bg-primary rounded-pill"><?php echo $cat['post_count']; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="sidebar-widget">
                        <h5><i class="fas fa-fire"></i> Popular Posts</h5>
                        <?php 
                        $popular_posts = get_popular_posts($conn, 5);
                        foreach($popular_posts as $ppost): 
                        ?>
                        <div class="mb-3">
                            <a href="index.php?post=<?php echo $ppost['slug']; ?>" class="text-decoration-none">
                                <h6 class="mb-1"><?php echo htmlspecialchars($ppost['title']); ?></h6>
                            </a>
                            <small class="text-muted"><i class="far fa-eye"></i> <?php echo $ppost['views']; ?> views</small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <?php if(isset($theme_settings['show_features']) && $theme_settings['show_features']): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Our Features</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-box text-center bg-white">
                        <i class="fas fa-rocket feature-icon"></i>
                        <h4>Fast Performance</h4>
                        <p class="text-muted">Optimized for speed and efficiency.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-box text-center bg-white">
                        <i class="fas fa-shield-alt feature-icon"></i>
                        <h4>Secure</h4>
                        <p class="text-muted">Built with security best practices.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-box text-center bg-white">
                        <i class="fas fa-mobile-alt feature-icon"></i>
                        <h4>Responsive</h4>
                        <p class="text-muted">Looks great on all devices.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Testimonials Section -->
    <?php if(isset($theme_settings['show_testimonials']) && $theme_settings['show_testimonials']): ?>
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">What People Say</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="testimonial-card">
                        <p class="fst-italic">"This CMS has transformed how we manage our content. Highly recommended!"</p>
                        <footer class="blockquote-footer">John Doe, <cite>CEO Company</cite></footer>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="testimonial-card">
                        <p class="fst-italic">"Easy to use, powerful features, and great support. What more could you ask for?"</p>
                        <footer class="blockquote-footer">Jane Smith, <cite>Web Developer</cite></footer>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="cta-section text-center">
        <div class="container">
            <h2 class="mb-3">Ready to Get Started?</h2>
            <p class="lead mb-4">Join thousands of satisfied users today.</p>
            <a href="admin.php" class="btn btn-light btn-lg">Contact Us</a>
        </div>
    </section>
