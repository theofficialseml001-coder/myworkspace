    <section class="py-4">
        <div class="container">
            <h2>Search: "<?php echo htmlspecialchars($search_query); ?>"</h2>
            <div class="row"><?php foreach($posts as $post): ?>
            <div class="col-md-4"><div class="news-grid-item"><div class="p-3"><h5><a href="index.php?post=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h5><p><?php echo get_excerpt($post['content'], 100); ?></p></div></div></div>
            <?php endforeach; ?></div>
        </div>
    </section>
