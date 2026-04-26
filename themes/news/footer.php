    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4"><h5><?php echo $site_title; ?></h5><p>Delivering news that matters, 24/7.</p></div>
                <div class="col-md-4"><h5>Sections</h5><ul class="list-unstyled"><?php foreach($categories as $cat): ?><li><a href="index.php?category=<?php echo $cat['slug']; ?>" class="text-white"><?php echo htmlspecialchars($cat['name']); ?></a></li><?php endforeach; ?></ul></div>
                <div class="col-md-4"><h5>Follow Us</h5><a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a><a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a><a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a></div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.2);">
            <p class="text-center mb-0">&copy; <?php echo date('Y'); ?> <?php echo $site_title; ?>. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
