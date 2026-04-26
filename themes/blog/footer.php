    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo $site_title; ?>. All rights reserved.</p>
            <?php if(is_admin()): ?><a href="admin.php" class="text-white">Admin</a><?php endif; ?>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
