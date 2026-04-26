    <section class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <article class="bg-white p-4">
                        <span class="category-badge"><?php echo htmlspecialchars($post['category_name'] ?? 'News'); ?></span>
                        <h1 class="my-3"><?php echo htmlspecialchars($post['title']); ?></h1>
                        <p class="text-muted"><i class="far fa-user"></i> <?php echo htmlspecialchars($post['author_name'] ?? 'Staff'); ?> | <i class="far fa-calendar"></i> <?php echo format_date($post['published_at']); ?> | <i class="far fa-eye"></i> <?php echo $post['views']; ?> views</p>
                        <hr>
                        <div class="content"><?php echo $post['content']; ?></div>
                    </article>
                </div>
                <div class="col-lg-4">
                    <div class="bg-white p-3">
                        <h5>Share This Story</h5>
                        <button class="btn btn-outline-primary btn-sm"><i class="fab fa-facebook"></i> Facebook</button>
                        <button class="btn btn-outline-info btn-sm"><i class="fab fa-twitter"></i> Twitter</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
