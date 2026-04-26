    <section class="py-5">
        <div class="container">
            <h1 class="mb-4">Category: <?php echo htmlspecialchars($cat['name'] ?? $category_slug); ?></h1>
            <div class="row">
            <?php if(!empty($posts)): ?>
                <?php foreach($posts as $post): ?>
                <div class="col-md-6 mb-4">
                    <div class="card post-card">
                        <div class="card-body">
                            <h5><a href="index.php?post=<?php echo $post['slug']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($post['title']); ?></a></h5>
                            <p class="text-muted"><?php echo get_excerpt($post['content'], 100); ?></p>
                            <small class="text-muted"><?php echo format_date($post['published_at']); ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No posts in this category.</p>
            <?php endif; ?>
            </div>
        </div>
    </section>
