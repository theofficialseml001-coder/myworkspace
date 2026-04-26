<?php
global $cms_db;
$slug = $_GET['category'];
$category = db_fetch(db_query("SELECT * FROM terms WHERE slug=?", [$slug]));
if ($category) {
    $posts = db_fetch_all(db_query("SELECT p.* FROM posts p INNER JOIN term_relationships tr ON p.id = tr.object_id WHERE tr.term_id = ? AND p.status='published'", [$category['id']]));
}
?>
<div class="container py-5">
    <h1 class="mb-4"><?php echo $category ? htmlspecialchars($category['name']) : 'Category'; ?></h1>
    <?php if (empty($posts)): ?>
        <p class="text-muted">No posts in this category.</p>
    <?php else: ?>
        <div class="row g-4">
        <?php foreach ($posts as $post): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5><a href="?post=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h5>
                        <p class="text-muted"><?php echo htmlspecialchars(substr($post['excerpt'] ?: $post['content'], 0, 100)); ?>...</p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
