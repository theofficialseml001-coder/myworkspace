<?php
global $cms_db;
$slug = $_GET['post'];
$post = db_fetch(db_query("SELECT p.*, u.username FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.slug=? AND p.status='published'", [$slug]));

if (!$post) {
    include __DIR__ . '/404.php';
    exit;
}
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if ($post['featured_image']): ?>
            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($post['title']); ?>">
            <?php endif; ?>
            
            <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($post['title']); ?></h1>
            
            <div class="text-muted mb-4">
                <span><i class="far fa-user me-1"></i> <?php echo htmlspecialchars($post['username'] ?? 'Admin'); ?></span>
                <span class="mx-2">|</span>
                <span><i class="far fa-calendar me-1"></i> <?php echo date('F j, Y', strtotime($post['published_at'])); ?></span>
            </div>
            
            <div class="content">
                <?php echo apply_filters('the_content', $post['content']); ?>
            </div>
            
            <div class="mt-5 pt-4 border-top">
                <a href="index.php" class="btn btn-outline-primary">&larr; Back to Home</a>
            </div>
        </div>
    </div>
</div>
