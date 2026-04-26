    <header class="blog-header">
        <h1><?php echo $site_title; ?></h1>
        <p class="lead"><?php echo $site_description; ?></p>
    </header>
    <div class="main-content">
        <div class="row">
            <div class="col-md-8">
                <?php foreach($posts as $post): ?>
                <article class="post-item">
                    <div class="post-meta">
                        <i class="far fa-calendar"></i> <?php echo format_date($post['published_at']); ?> | 
                        <i class="far fa-user"></i> <?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?>
                    </div>
                    <h2><a href="index.php?post=<?php echo $post['slug']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                    <p class="text-muted"><?php echo get_excerpt($post['content'], 200); ?></p>
                    <a href="index.php?post=<?php echo $post['slug']; ?>" class="btn btn-outline-primary">Read More &rarr;</a>
                </article>
                <?php endforeach; ?>
            </div>
            <div class="col-md-4">
                <div class="sidebar-widget">
                    <h5>About</h5>
                    <p><?php echo $site_description; ?></p>
                </div>
                <div class="sidebar-widget">
                    <h5>Categories</h5>
                    <ul class="list-unstyled">
                        <?php foreach($categories as $cat): ?>
                        <li><a href="index.php?category=<?php echo $cat['slug']; ?>"><?php echo htmlspecialchars($cat['name']); ?> (<?php echo $cat['post_count']; ?>)</a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
