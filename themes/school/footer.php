    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4"><h5><?php echo $site_title; ?></h5><p>Educating tomorrow's leaders today.</p></div>
                <div class="col-md-4"><h5>Quick Links</h5><ul class="list-unstyled"><?php foreach($categories as $cat): ?><li><a href="index.php?category=<?php echo $cat['slug']; ?>" class="text-white"><?php echo htmlspecialchars($cat['name']); ?></a></li><?php endforeach; ?></ul></div>
                <div class="col-md-4"><h5>Contact</h5><p>123 Education Lane<br>City, State 12345<br>Phone: (555) 123-4567</p></div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.2);">
            <p class="text-center mb-0">&copy; <?php echo date('Y'); ?> <?php echo $site_title; ?>. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
