    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><?php echo $site_title; ?></h5>
                    <p><?php echo $site_description; ?></p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <?php foreach($menu_items as $item): ?>
                        <li><a href="<?php echo $item['url'] ?: 'index.php?post=' . $item['post_slug']; ?>"><?php echo $item['label']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Categories</h5>
                    <ul class="list-unstyled">
                        <?php foreach($categories as $cat): ?>
                        <li><a href="index.php?category=<?php echo $cat['slug']; ?>"><?php echo $cat['name']; ?> (<?php echo $cat['post_count']; ?>)</a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.2);">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $site_title; ?>. All rights reserved.</p>
                <?php if(is_admin()): ?>
                <a href="admin.php" class="btn btn-sm btn-outline-light">Admin Panel</a>
                <?php endif; ?>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
