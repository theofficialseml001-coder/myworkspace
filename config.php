<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cms_db');

// Site Configuration
define('SITE_URL', 'http://localhost/cms');
define('SITE_NAME', 'My CMS');
define('ADMIN_EMAIL', 'admin@example.com');

// Security
define('SECRET_KEY', 'your-secret-key-change-this-in-production');

// File Uploads
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB

// Themes
define('DEFAULT_THEME', 'multipurpose');

// Plugin Management (Admin Only)
define('PLUGIN_DIR', __DIR__ . '/plugins/');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('UTC');

// Create database connection (procedural MySQLi)
function get_db_connection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, "utf8mb4");
    return $conn;
}

$conn = get_db_connection();
?>
