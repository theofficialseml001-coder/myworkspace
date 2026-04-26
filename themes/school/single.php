    <section class="py-5">
        <div class="container">
            <article>
                <span class="badge bg-warning text-dark mb-2"><?php echo htmlspecialchars($post['category_name'] ?? 'News'); ?></span>
                <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                <p class="text-muted"><i class="far fa-calendar"></i> <?php echo format_date($post['published_at']); ?></p>
                <hr>
                <div><?php echo $post['content']; ?></div>
            </article>
        </div>
    </section>
