    <div class="main-content">
        <h2>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h2>
        <?php foreach($posts as $post): ?>
        <div class="post-item">
            <h4><a href="index.php?post=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h4>
            <p><?php echo get_excerpt($post['content'], 150); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
