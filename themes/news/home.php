    <section class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <?php if(!empty($posts)): $featured = $posts[0]; ?>
                    <div class="featured-card mb-4">
                        <img src="uploads/<?php echo $featured['featured_image'] ?? ''; ?>" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect fill=%22%23ccc%22 width=%22100%22 height=%22100%22/></svg>'">
                        <div class="featured-overlay">
                            <span class="category-badge"><?php echo htmlspecialchars($featured['category_name'] ?? 'News'); ?></span>
                            <h2 class="mt-2"><?php echo htmlspecialchars($featured['title']); ?></h2>
                            <p><?php echo get_excerpt($featured['content'], 150); ?></p>
                            <a href="index.php?post=<?php echo $featured['slug']; ?>" class="btn btn-danger">Read Full Story</a>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row">
                        <?php foreach(array_slice($posts, 1, 4) as $post): ?>
                        <div class="col-md-6">
                            <div class="news-grid-item">
                                <img src="uploads/<?php echo $post['featured_image'] ?? ''; ?>" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect fill=%22%23ddd%22 width=%22100%22 height=%22100%22/></svg>'">
                                <div class="p-3">
                                    <span class="category-badge"><?php echo htmlspecialchars($post['category_name'] ?? 'News'); ?></span>
                                    <h5 class="mt-2"><a href="index.php?post=<?php echo $post['slug']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($post['title']); ?></a></h5>
                                    <small class="text-muted"><i class="far fa-clock"></i> <?php echo format_date($post['published_at']); ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-white p-3 mb-3">
                        <h5 class="border-bottom pb-2"><i class="fas fa-fire text-danger"></i> Most Read</h5>
                        <?php $popular = get_popular_posts($conn, 5); foreach($popular as $ppost): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <a href="index.php?post=<?php echo $ppost['slug']; ?>" class="text-decoration-none text-dark"><h6><?php echo htmlspecialchars($ppost['title']); ?></h6></a>
                            <small class="text-muted"><i class="far fa-eye"></i> <?php echo $ppost['views']; ?> reads</small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="bg-white p-3">
                        <h5 class="border-bottom pb-2">Categories</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach($categories as $cat): ?>
                            <a href="index.php?category=<?php echo $cat['slug']; ?>" class="btn btn-outline-secondary btn-sm"><?php echo htmlspecialchars($cat['name']); ?> (<?php echo $cat['post_count']; ?>)</a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
