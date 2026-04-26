    <div class="main-content">
        <h2>Category: <?php echo htmlspecialchars($cat['name'] ?? $category_slug); ?></h2>
        <?php foreach($posts as $post): ?>
        <div class="post-item">
            <h3><a href="index.php?post=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
            <p><?php echo get_excerpt($post['content'], 150); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
