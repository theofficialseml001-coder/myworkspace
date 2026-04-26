    <section class="py-5">
        <div class="container">
            <h2><?php echo htmlspecialchars($cat['name'] ?? $category_slug); ?></h2>
            <?php foreach($posts as $post): ?>
            <div class="card mb-3 news-card"><div class="card-body"><h5><a href="index.php?post=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h5><p><?php echo get_excerpt($post['content'], 150); ?></p></div></div>
            <?php endforeach; ?>
        </div>
    </section>
