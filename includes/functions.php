<?php
/**
 * CMS Helper Functions
 * Procedural PHP helper functions for the CMS
 */

// Security: Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Security: Check if user is admin
function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Security: Require login
function require_login() {
    if (!is_logged_in()) {
        header('Location: admin.php?action=login');
        exit;
    }
}

// Security: Require admin
function require_admin() {
    if (!is_admin()) {
        header('Location: index.php?error=access_denied');
        exit;
    }
}

// Get current user
function get_current_user_data($conn) {
    if (!is_logged_in()) {
        return null;
    }
    
    $user_id = (int)$_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Sanitize input
function sanitize_input($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim(htmlspecialchars($data)));
}

// Generate slug from title
function generate_slug($title) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    return $slug;
}

// Get option value
function get_option($conn, $option_name, $default = '') {
    $sql = "SELECT option_value FROM options WHERE option_name = '" . sanitize_input($option_name) . "'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['option_value'];
    }
    
    return $default;
}

// Update option value
function update_option($conn, $option_name, $option_value) {
    $sql = "UPDATE options SET option_value = '" . sanitize_input($option_value) . "' 
            WHERE option_name = '" . sanitize_input($option_name) . "'";
    return mysqli_query($conn, $sql);
}

// Get active theme
function get_active_theme($conn) {
    $sql = "SELECT * FROM themes WHERE is_active = 1 LIMIT 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    // Default to multipurpose
    $sql = "SELECT * FROM themes WHERE slug = 'multipurpose' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Get all themes
function get_all_themes($conn) {
    $sql = "SELECT * FROM themes ORDER BY name";
    $result = mysqli_query($conn, $sql);
    
    $themes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $themes[] = $row;
    }
    
    return $themes;
}

// Activate theme
function activate_theme($conn, $theme_slug) {
    // Deactivate all themes first
    mysqli_query($conn, "UPDATE themes SET is_active = 0");
    
    // Activate selected theme
    $sql = "UPDATE themes SET is_active = 1 WHERE slug = '" . sanitize_input($theme_slug) . "'";
    return mysqli_query($conn, $sql);
}

// Get all plugins (admin only function)
function get_all_plugins($conn) {
    $sql = "SELECT * FROM plugins ORDER BY name";
    $result = mysqli_query($conn, $sql);
    
    $plugins = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $plugins[] = $row;
    }
    
    return $plugins;
}

// Toggle plugin status (admin only)
function toggle_plugin($conn, $plugin_slug) {
    $sql = "UPDATE plugins SET is_active = NOT is_active WHERE slug = '" . sanitize_input($plugin_slug) . "'";
    return mysqli_query($conn, $sql);
}

// Get posts with pagination
function get_posts($conn, $limit = 10, $offset = 0, $status = 'published', $post_type = null) {
    $sql = "SELECT p.*, u.display_name as author_name, c.name as category_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = '" . sanitize_input($status) . "'";
    
    if ($post_type) {
        $sql .= " AND p.post_type = '" . sanitize_input($post_type) . "'";
    }
    
    $sql .= " ORDER BY p.published_at DESC LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($conn, $sql);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    return $posts;
}

// Get single post by slug
function get_post_by_slug($conn, $slug) {
    $sql = "SELECT p.*, u.display_name as author_name, c.name as category_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.slug = '" . sanitize_input($slug) . "'";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Get post count
function get_post_count($conn, $status = 'published') {
    $sql = "SELECT COUNT(*) as count FROM posts WHERE status = '" . sanitize_input($status) . "'";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    
    return 0;
}

// Get categories
function get_categories($conn, $parent_id = 0) {
    $sql = "SELECT c.*, COUNT(p.id) as post_count 
            FROM categories c 
            LEFT JOIN posts p ON c.id = p.category_id AND p.status = 'published'
            WHERE c.parent_id = $parent_id
            GROUP BY c.id ORDER BY c.name";
    
    $result = mysqli_query($conn, $sql);
    
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    
    return $categories;
}

// Get menu items
function get_menu_items($conn, $menu_slug = 'main-menu') {
    $sql = "SELECT mi.*, p.slug as post_slug 
            FROM menu_items mi 
            JOIN menus m ON mi.menu_id = m.id 
            LEFT JOIN posts p ON mi.post_id = p.id 
            WHERE m.slug = '" . sanitize_input($menu_slug) . "' 
            ORDER BY mi.position";
    
    $result = mysqli_query($conn, $sql);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    return $items;
}

// Format date
function format_date($date, $format = null) {
    global $conn;
    
    if (!$format) {
        $format = get_option($conn, 'date_format', 'F j, Y');
    }
    
    return date($format, strtotime($date));
}

// Truncate text
function truncate_text($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

// Get excerpt from content
function get_excerpt($content, $length = 150) {
    $excerpt = strip_tags($content);
    return truncate_text($excerpt, $length);
}

// Upload file
function upload_file($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred'];
    }
    
    // Check file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    // Check file type
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = UPLOAD_DIR . $filename;
    
    // Create uploads directory if it doesn't exist
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    }
    
    return ['success' => false, 'message' => 'Failed to save file'];
}

// Save media to database
function save_media($conn, $filename, $original_name, $mime_type, $file_size, $filepath, $uploaded_by, $alt_text = '') {
    $sql = "INSERT INTO media (filename, original_name, mime_type, file_size, filepath, uploaded_by, alt_text) 
            VALUES ('" . sanitize_input($filename) . "', 
                    '" . sanitize_input($original_name) . "', 
                    '" . sanitize_input($mime_type) . "', 
                    $file_size, 
                    '" . sanitize_input($filepath) . "', 
                    $uploaded_by, 
                    '" . sanitize_input($alt_text) . "')";
    
    if (mysqli_query($conn, $sql)) {
        return mysqli_insert_id($conn);
    }
    
    return false;
}

// Increment post views
function increment_post_views($conn, $post_id) {
    $sql = "UPDATE posts SET views = views + 1 WHERE id = $post_id";
    return mysqli_query($conn, $sql);
}

// Search posts
function search_posts($conn, $query, $limit = 10) {
    $query = sanitize_input($query);
    $sql = "SELECT p.*, u.display_name as author_name, c.name as category_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'published' 
            AND (p.title LIKE '%$query%' OR p.content LIKE '%$query%' OR p.excerpt LIKE '%$query%')
            ORDER BY p.published_at DESC 
            LIMIT $limit";
    
    $result = mysqli_query($conn, $sql);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    return $posts;
}

// Get recent posts
function get_recent_posts($conn, $limit = 5) {
    return get_posts($conn, $limit, 0, 'published');
}

// Get popular posts by views
function get_popular_posts($conn, $limit = 5) {
    $sql = "SELECT p.*, u.display_name as author_name, c.name as category_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'published' 
            ORDER BY p.views DESC 
            LIMIT $limit";
    
    $result = mysqli_query($conn, $sql);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    return $posts;
}

// Check plugin permissions (admin only)
function can_manage_plugins() {
    return is_admin();
}

// Get plugin by slug
function get_plugin_by_slug($conn, $slug) {
    $sql = "SELECT * FROM plugins WHERE slug = '" . sanitize_input($slug) . "'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

?>
