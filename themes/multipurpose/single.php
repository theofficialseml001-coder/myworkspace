    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <article>
                        <?php if($post['featured_image']): ?>
                        <img src="uploads/<?php echo $post['featured_image']; ?>" class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        <?php endif; ?>
                        <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></span>
                        <h1 class="mb-3"><?php echo htmlspecialchars($post['title']); ?></h1>
                        <div class="text-muted mb-4">
                            <i class="far fa-user"></i> <?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?> | 
                            <i class="far fa-calendar"></i> <?php echo format_date($post['published_at']); ?> |
                            <i class="far fa-eye"></i> <?php echo $post['views']; ?> views
                        </div>
                        <div class="content">
                            <?php echo $post['content']; ?>
                        </div>
                    </article>
                </div>
                <div class="col-lg-4">
                    <div class="sidebar-widget">
                        <h5>Share This Post</h5>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-primary"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="btn btn-outline-info"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="btn btn-outline-secondary"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
