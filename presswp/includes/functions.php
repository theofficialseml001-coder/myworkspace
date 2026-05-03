<?php
/**
 * PressWP - WordPress Clone
 * includes/functions.php - Additional Helper Functions
 * Procedural PHP implementation
 */

/**
 * Sanitize Input
 * @param string $data
 * @return string
 */
function sanitize_input($data) {
    $conn = get_db_connection();
    $data = trim($data);
    $data = stripslashes($data);
    $data = mysqli_real_escape_string($conn, $data);
    mysqli_close($conn);
    return $data;
}

/**
 * Get Post by Slug
 * @param string $slug
 * @return array|null
 */
function get_post_by_slug($slug) {
    $conn = get_db_connection();
    $slug = mysqli_real_escape_string($conn, $slug);
    
    $sql = "SELECT * FROM posts WHERE slug = '$slug' AND status = 'publish'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $post = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        mysqli_close($conn);
        return $post;
    }
    
    mysqli_close($conn);
    return null;
}

/**
 * Get Recent Posts
 * @param int $limit
 * @return array
 */
function get_recent_posts($limit = 5) {
    $conn = get_db_connection();
    $limit = (int)$limit;
    
    $sql = "SELECT * FROM posts WHERE type = 'post' AND status = 'publish' ORDER BY created_at DESC LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    
    $posts = array();
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $posts[] = $row;
        }
        mysqli_free_result($result);
    }
    
    mysqli_close($conn);
    return $posts;
}

/**
 * Get Pages
 * @return array
 */
function get_pages() {
    $conn = get_db_connection();
    
    $sql = "SELECT * FROM posts WHERE type = 'page' AND status = 'publish' ORDER BY title ASC";
    $result = mysqli_query($conn, $sql);
    
    $pages = array();
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pages[] = $row;
        }
        mysqli_free_result($result);
    }
    
    mysqli_close($conn);
    return $pages;
}

/**
 * Get Plugin by Slug
 * @param string $slug
 * @return array|null
 */
function get_plugin_by_slug($slug) {
    $conn = get_db_connection();
    $slug = mysqli_real_escape_string($conn, $slug);
    
    $sql = "SELECT * FROM plugins WHERE slug = '$slug'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $plugin = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        mysqli_close($conn);
        return $plugin;
    }
    
    mysqli_close($conn);
    return null;
}

/**
 * Get All Active Plugins
 * @return array
 */
function get_active_plugins() {
    $conn = get_db_connection();
    
    $sql = "SELECT * FROM plugins WHERE is_active = 1 ORDER BY name ASC";
    $result = mysqli_query($conn, $sql);
    
    $plugins = array();
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $plugins[] = $row;
        }
        mysqli_free_result($result);
    }
    
    mysqli_close($conn);
    return $plugins;
}

/**
 * Format Date
 * @param string $date
 * @param string $format
 * @return string
 */
function format_date($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Get Excerpt
 * @param string $content
 * @param int $length
 * @return string
 */
function get_excerpt($content, $length = 150) {
    $content = strip_tags($content);
    if (strlen($content) <= $length) {
        return $content;
    }
    return substr($content, 0, $length) . '...';
}

/**
 * Generate Slug
 * @param string $string
 * @return string
 */
function generate_slug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Check Nonce (Security Token)
 * @param string $nonce
 * @param string $action
 * @return bool
 */
function verify_nonce($nonce, $action) {
    if (!isset($_SESSION['nonce_' . $action])) {
        return false;
    }
    return hash_equals($_SESSION['nonce_' . $action], $nonce);
}

/**
 * Generate Nonce
 * @param string $action
 * @return string
 */
function generate_nonce($action) {
    $token = bin2hex(random_bytes(32));
    $_SESSION['nonce_' . $action] = $token;
    return $token;
}

/**
 * Get User by ID
 * @param int $user_id
 * @return array|null
 */
function get_user_by_id($user_id) {
    $conn = get_db_connection();
    $user_id = (int)$user_id;
    
    $sql = "SELECT id, username, email, role, created_at FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        mysqli_close($conn);
        return $user;
    }
    
    mysqli_close($conn);
    return null;
}

/**
 * Count Posts by Type
 * @param string $type
 * @return int
 */
function count_posts_by_type($type = 'post') {
    $conn = get_db_connection();
    $type = mysqli_real_escape_string($conn, $type);
    
    $sql = "SELECT COUNT(*) as count FROM posts WHERE type = '$type' AND status = 'publish'";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        mysqli_close($conn);
        return (int)$row['count'];
    }
    
    mysqli_close($conn);
    return 0;
}

/**
 * Get Site URL
 * @param string $path
 * @return string
 */
function site_url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

/**
 * Get Admin URL
 * @param string $path
 * @return string
 */
function admin_url($path = '') {
    return SITE_URL . '/admin/' . ltrim($path, '/');
}

/**
 * Display Error Message
 * @param string $message
 */
function display_error($message) {
    echo '<div class="alert alert-danger">' . esc_html($message) . '</div>';
}

/**
 * Display Success Message
 * @param string $message
 */
function display_success($message) {
    echo '<div class="alert alert-success">' . esc_html($message) . '</div>';
}

?>
