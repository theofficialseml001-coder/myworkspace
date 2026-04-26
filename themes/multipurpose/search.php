    <section class="py-5">
        <div class="container">
            <h1 class="mb-4">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>
            <?php if(!empty($posts)): ?>
                <div class="row">
                <?php foreach($posts as $post): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5><a href="index.php?post=<?php echo $post['slug']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($post['title']); ?></a></h5>
                            <p class="text-muted"><?php echo get_excerpt($post['content'], 150); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">No results found.</p>
            <?php endif; ?>
        </div>
    </section>
