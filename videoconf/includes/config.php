<?php
/**
 * Video Conference SFU Solution - Configuration File
 * Procedural PHP with MySQLi
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'videoconf_db');

// Application Configuration
define('APP_NAME', 'VideoConf Pro');
define('APP_URL', 'http://localhost/videoconf');
define('TIMEZONE', 'UTC');

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/assets/uploads/');
define('RECORDING_DIR', __DIR__ . '/assets/uploads/recordings/');
define('FILE_SHARE_DIR', __DIR__ . '/assets/uploads/files/');
define('AVATAR_DIR', __DIR__ . '/assets/uploads/avatars/');
define('MAX_FILE_SIZE', 104857600); // 100MB

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// Timezone
date_default_timezone_set(TIMEZONE);

// Database Connection (Procedural MySQLi)
function getDBConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, "utf8mb4");
    return $conn;
}

// Helper Functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    return $user;
}

function generateMeetingId($length = 10) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $meetingId = '';
    for ($i = 0; $i < $length; $i++) {
        $meetingId .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $meetingId;
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

?>
