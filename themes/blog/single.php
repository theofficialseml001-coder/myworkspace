    <div class="main-content">
        <article class="post-item">
            <div class="post-meta">
                <i class="far fa-calendar"></i> <?php echo format_date($post['published_at']); ?> | 
                <i class="far fa-user"></i> <?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?> |
                <i class="far fa-eye"></i> <?php echo $post['views']; ?> views
            </div>
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <hr>
            <div class="content"><?php echo $post['content']; ?></div>
        </article>
        <a href="index.php" class="btn btn-secondary">&larr; Back to Posts</a>
    </div>
