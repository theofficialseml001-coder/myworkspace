<?php
/**
 * Core Functions & World-Class Architecture
 * Procedural PHP | MySQLi | Hook System | Security | CPT | Audit Log
 */

// Start Session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

// ============================================================================
// 1. DATABASE ABSTRACTION & SECURITY LAYER
// ============================================================================

global $cms_db;
$cms_db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($cms_db->connect_error) {
    die("Connection failed: " . $cms_db->connect_error);
}

$cms_db->set_charset("utf8mb4");

/**
 * Safe Query Execution (Prevents SQL Injection)
 */
function db_query($sql, $params = []) {
    global $cms_db;
    
    if (empty($params)) {
        return $cms_db->query($sql);
    }

    $stmt = $cms_db->prepare($sql);
    if (!$stmt) {
        error_log("DB Prepare Error: " . $cms_db->error);
        return false;
    }

    $types = '';
    $values = [];
    foreach ($params as $param) {
        if (is_int($param)) $types .= 'i';
        elseif (is_float($param)) $types .= 'd';
        else $types .= 's';
        $values[] = $param;
    }

    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    return $stmt;
}

function db_fetch($result) {
    if ($result instanceof mysqli_stmt) {
        $res = $result->get_result();
        return $res->fetch_assoc();
    }
    return $result->fetch_assoc();
}

function db_fetch_all($result) {
    if ($result instanceof mysqli_stmt) {
        $res = $result->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

// ============================================================================
// 2. HOOK SYSTEM (ACTIONS & FILTERS)
// ============================================================================

global $cms_hooks;
$cms_hooks = ['actions' => [], 'filters' => []];

function add_action($tag, $function_to_add, $priority = 10) {
    global $cms_hooks;
    $cms_hooks['actions'][$tag][$priority][] = $function_to_add;
    ksort($cms_hooks['actions'][$tag]);
}

function add_filter($tag, $function_to_add, $priority = 10) {
    global $cms_hooks;
    $cms_hooks['filters'][$tag][$priority][] = $function_to_add;
    ksort($cms_hooks['filters'][$tag]);
}

function do_action($tag, ...$args) {
    global $cms_hooks;
    if (!isset($cms_hooks['actions'][$tag])) return;
    foreach ($cms_hooks['actions'][$tag] as $priority_group) {
        foreach ($priority_group as $function) {
            call_user_func_array($function, $args);
        }
    }
}

function apply_filters($tag, $value, ...$args) {
    global $cms_hooks;
    if (!isset($cms_hooks['filters'][$tag])) return $value;
    foreach ($cms_hooks['filters'][$tag] as $priority_group) {
        foreach ($priority_group as $function) {
            array_unshift($args, $value);
            $value = call_user_func_array($function, $args);
        }
    }
    return $value;
}

// ============================================================================
// 3. NONCE SECURITY SYSTEM (CSRF Protection)
// ============================================================================

function create_nonce($action = -1) {
    $tick = ceil(time() / 43200);
    $uid = session_id() ?: '0';
    return substr(md5($tick . $action . $uid . DB_SECRET), 0, 10);
}

function verify_nonce($nonce, $action = -1) {
    if (empty($nonce)) return false;
    return $nonce === create_nonce($action);
}

function wp_nonce_field($action = -1, $name = '_wpnonce') {
    $token = create_nonce($action);
    echo "<input type='hidden' name='$name' value='$token'>";
}

// ============================================================================
// 4. CUSTOM POST TYPES API
// ============================================================================

global $cms_post_types;
$cms_post_types = ['post', 'page'];

function register_post_type($type, $args = []) {
    global $cms_post_types;
    if (!in_array($type, $cms_post_types)) {
        $cms_post_types[] = $type;
    }
}

function get_post_types() {
    global $cms_post_types;
    return $cms_post_types;
}

// ============================================================================
// 5. ASSET MANAGEMENT (Enqueue System)
// ============================================================================

global $cms_assets;
$cms_assets = ['scripts' => [], 'styles' => []];

function enqueue_script($handle, $src, $deps = [], $ver = '1.0', $in_footer = true) {
    global $cms_assets;
    $cms_assets['scripts'][$handle] = compact('handle', 'src', 'deps', 'ver', 'in_footer');
}

function enqueue_style($handle, $src, $deps = [], $ver = '1.0') {
    global $cms_assets;
    $cms_assets['styles'][$handle] = compact('handle', 'src', 'deps', 'ver');
}

function print_scripts() {
    global $cms_assets;
    foreach ($cms_assets['scripts'] as $script) {
        echo "<script src='{$script['src']}'></script>\n";
    }
}

function print_styles() {
    global $cms_assets;
    foreach ($cms_assets['styles'] as $style) {
        echo "<link rel='stylesheet' href='{$style['src']}'>\n";
    }
}

// ============================================================================
// 6. AUDIT LOGGING
// ============================================================================

function log_audit_event($action, $user_id, $details = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $sql = "INSERT INTO audit_logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())";
    db_query($sql, [$user_id, $action, $details, $ip]);
}

// ============================================================================
// 7. USER & PERMISSION HELPERS
// ============================================================================

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return defined('IS_ADMIN') && IS_ADMIN;
}

function current_user_can($capability) {
    if (!isset($_SESSION['user_id'])) return false;
    $role = $_SESSION['user_role'] ?? 'subscriber';
    if ($capability == 'manage_plugins' || $capability == 'edit_themes' || $capability == 'manage_options') {
        return $role === 'admin';
    }
    return true;
}

function get_current_user_data() {
    if (!is_logged_in()) return null;
    global $cms_db;
    $id = intval($_SESSION['user_id']);
    $result = $cms_db->query("SELECT * FROM users WHERE id = $id");
    return $result ? $result->fetch_assoc() : null;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// ============================================================================
// 8. CONTENT HELPERS
// ============================================================================

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function get_option($key, $default = '') {
    global $cms_db;
    $key = $cms_db->real_escape_string($key);
    $result = $cms_db->query("SELECT option_value FROM options WHERE option_name = '$key'");
    if ($row = $result->fetch_assoc()) {
        return $row['option_value'];
    }
    return $default;
}

function update_option($key, $value) {
    global $cms_db;
    $key = $cms_db->real_escape_string($key);
    $value = $cms_db->real_escape_string($value);
    
    $check = $cms_db->query("SELECT id FROM options WHERE option_name = '$key'");
    if ($check->num_rows > 0) {
        $cms_db->query("UPDATE options SET option_value = '$value' WHERE option_name = '$key'");
    } else {
        $cms_db->query("INSERT INTO options (option_name, option_value) VALUES ('$key', '$value')");
    }
}

// ============================================================================
// 9. REVISION SYSTEM
// ============================================================================

function save_post_revision($post_id, $user_id) {
    global $cms_db;
    $post_id = intval($post_id);
    $user_id = intval($user_id);
    
    $result = $cms_db->query("SELECT * FROM posts WHERE id = $post_id");
    if (!$result || $result->num_rows === 0) return false;
    
    $post = $result->fetch_assoc();
    
    $sql = "INSERT INTO post_revisions (post_id, title, content, excerpt, author_id, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    return db_query($sql, [$post_id, $post['title'], $post['content'], $post['excerpt'], $user_id]);
}

function get_post_revisions($post_id) {
    $sql = "SELECT * FROM post_revisions WHERE post_id = ? ORDER BY created_at DESC";
    return db_fetch_all(db_query($sql, [intval($post_id)]));
}

function restore_revision($revision_id) {
    global $cms_db;
    $revision_id = intval($revision_id);
    
    $result = $cms_db->query("SELECT * FROM post_revisions WHERE id = $revision_id");
    if (!$result || $result->num_rows === 0) return false;
    
    $rev = $result->fetch_assoc();
    
    $sql = "UPDATE posts SET title = ?, content = ?, excerpt = ? WHERE id = ?";
    return db_query($sql, [$rev['title'], $rev['content'], $rev['excerpt'], $rev['post_id']]);
}

// ============================================================================
// 10. MULTI-LANGUAGE SUPPORT
// ============================================================================

global $cms_current_lang;
$cms_current_lang = get_option('default_language', 'en');

function set_language($lang) {
    global $cms_current_lang;
    $allowed = ['en', 'es', 'fr', 'de', 'ar', 'zh'];
    if (in_array($lang, $allowed)) {
        $cms_current_lang = $lang;
        update_option('current_language', $lang);
    }
}

function __($text, $domain = 'default') {
    return $text;
}

function _e($text, $domain = 'default') {
    echo __($text, $domain);
}

// ============================================================================
// DEFAULT HOOKS INITIALIZATION
// ============================================================================

add_action('init', 'setup_default_post_types');
function setup_default_post_types() {
    register_post_type('post', ['label' => 'Posts']);
    register_post_type('page', ['label' => 'Pages']);
    register_post_type('product', ['label' => 'Products']);
}

add_action('send_headers', 'send_security_headers');
function send_security_headers() {
    if (!headers_sent()) {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
    }
}
