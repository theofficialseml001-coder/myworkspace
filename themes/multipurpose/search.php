<?php
global $cms_db;
$search = sanitize_input($_GET['s']);
$posts = db_fetch_all(db_query("SELECT * FROM posts WHERE (title LIKE ? OR content LIKE ?) AND status='published' ORDER BY published_at DESC", ["%$search%", "%$search%"]));
?>
<div class="container py-5">
    <h1 class="mb-4">Search Results for "<?php echo htmlspecialchars($search); ?>"</h1>
    <?php if (empty($posts)): ?>
        <p class="text-muted">No results found.</p>
    <?php else: ?>
        <div class="row g-4">
        <?php foreach ($posts as $post): ?>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5><a href="?post=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h5>
                        <p class="text-muted"><?php echo htmlspecialchars(substr($post['excerpt'] ?: $post['content'], 0, 150)); ?>...</p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
