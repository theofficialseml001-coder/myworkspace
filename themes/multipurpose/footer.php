    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5><?php echo get_option('site_title'); ?></h5>
                    <p class="text-muted"><?php echo get_option('site_description'); ?></p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="?page=about">About Us</a></li>
                        <li><a href="?page=services">Services</a></li>
                        <li><a href="?page=contact">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <p class="text-muted">
                        <i class="fas fa-envelope me-2"></i> <?php echo get_option('admin_email'); ?><br>
                        <i class="fas fa-map-marker-alt me-2"></i> Your Address Here
                    </p>
                    <div class="mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.2);">
            <div class="text-center text-muted">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo get_option('site_title'); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
