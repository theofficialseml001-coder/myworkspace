<?php
/**
 * PressWP - WordPress Clone
 * config.php - Database Configuration
 * Procedural PHP with MySQLi
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'presswp');

// Application Settings
define('SITE_URL', 'http://localhost/presswp');
define('ADMIN_EMAIL', 'admin@presswp.com');

// Error Reporting (Disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get Database Connection
 * @return mysqli|false
 */
function get_db_connection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, "utf8mb4");
    return $conn;
}

/**
 * Escape Output
 * @param string $data
 * @return string
 */
function esc_html($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if User is Admin
 * @return bool
 */
function is_admin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $conn = get_db_connection();
    $user_id = (int)$_SESSION['user_id'];
    
    $sql = "SELECT role FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        mysqli_close($conn);
        return ($row['role'] === 'admin');
    }
    
    mysqli_close($conn);
    return false;
}

/**
 * Get Current User
 * @return array|null
 */
function get_current_user() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $conn = get_db_connection();
    $user_id = (int)$_SESSION['user_id'];
    
    $sql = "SELECT id, username, email, role FROM users WHERE id = $user_id";
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
 * Redirect to URL
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Get Site Option
 * @param string $option_name
 * @return mixed
 */
function get_option($option_name) {
    $conn = get_db_connection();
    $option_name = mysqli_real_escape_string($conn, $option_name);
    
    $sql = "SELECT option_value FROM options WHERE option_name = '$option_name'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        mysqli_close($conn);
        return $row['option_value'];
    }
    
    mysqli_close($conn);
    return false;
}

/**
 * Update Site Option
 * @param string $option_name
 * @param string $option_value
 * @return bool
 */
function update_option($option_name, $option_value) {
    $conn = get_db_connection();
    $option_name = mysqli_real_escape_string($conn, $option_name);
    $option_value = mysqli_real_escape_string($conn, $option_value);
    
    $sql = "UPDATE options SET option_value = '$option_value' WHERE option_name = '$option_name'";
    $result = mysqli_query($conn, $sql);
    
    mysqli_close($conn);
    return $result;
}

?>
