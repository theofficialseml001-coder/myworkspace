<?php
/**
 * Video Conference SFU Solution - API: Create Meeting
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$user = getCurrentUser();
$conn = getDBConnection();

// Get POST data
$title = sanitize($_POST['title'] ?? 'My Meeting');
$type = $_POST['type'] ?? 'instant';
$password = $_POST['password'] ?? null;
$scheduled_start = $_POST['scheduled_start'] ?? null;
$duration = (int)($_POST['duration'] ?? 60);

// Generate unique meeting ID
$meetingId = generateMeetingId();

// Validate input
if (empty($title)) {
    echo json_encode(['success' => false, 'error' => 'Meeting title is required']);
    exit;
}

// Prepare meeting settings
$allow_recording = isset($_POST['allow_recording']) ? 1 : 0;
$allow_screen_share = isset($_POST['allow_screen_share']) ? 1 : 0;
$allow_chat = isset($_POST['allow_chat']) ? 1 : 0;
$allow_file_share = isset($_POST['allow_file_share']) ? 1 : 0;
$allow_whiteboard = isset($_POST['allow_whiteboard']) ? 1 : 0;
$max_participants = (int)($_POST['max_participants'] ?? 100);
$is_public = isset($_POST['is_public']) ? 1 : 0;

// Hash password if provided
$hashed_password = $password ? password_hash($password, PASSWORD_DEFAULT) : null;

// Insert meeting into database
$stmt = mysqli_prepare($conn, "INSERT INTO meetings 
    (meeting_id, title, description, host_id, meeting_type, scheduled_start, duration, 
     password, is_public, allow_recording, allow_screen_share, allow_chat, 
     allow_file_share, allow_whiteboard, max_participants, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$status = ($type === 'instant') ? 'active' : 'waiting';

mysqli_stmt_bind_param($stmt, "ssissisiiiiiiiis", 
    $meetingId, $title, $description, $user['id'], $type, $scheduled_start, $duration,
    $hashed_password, $is_public, $allow_recording, $allow_screen_share, $allow_chat,
    $allow_file_share, $allow_whiteboard, $max_participants, $status
);

if (mysqli_stmt_execute($stmt)) {
    $meeting_db_id = mysqli_insert_id($conn);
    
    // Add host as participant
    $stmt = mysqli_prepare($conn, "INSERT INTO participants 
        (meeting_id, user_id, display_name, role, ip_address, user_agent) 
        VALUES (?, ?, ?, 'host', ?, ?)");
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    mysqli_stmt_bind_param($stmt, "iisss", $meeting_db_id, $user['id'], $user['full_name'], $ip, $user_agent);
    mysqli_stmt_execute($stmt);
    
    // Log activity
    $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
        VALUES (?, 'create_meeting', 'Created new meeting: {$meetingId}', ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $user['id'], $ip, $user_agent);
    mysqli_stmt_execute($stmt);
    
    // Create whiteboard session
    $stmt = mysqli_prepare($conn, "INSERT INTO whiteboard_sessions (meeting_id, created_by, board_data) 
        VALUES (?, ?, '{}')");
    mysqli_stmt_bind_param($stmt, "ii", $meeting_db_id, $user['id']);
    mysqli_stmt_execute($stmt);
    
    mysqli_close($conn);
    
    echo json_encode([
        'success' => true,
        'meeting' => [
            'id' => $meeting_db_id,
            'meeting_id' => $meetingId,
            'title' => $title,
            'type' => $type,
            'url' => APP_URL . '/meeting.php?id=' . $meeting_db_id,
            'join_url' => APP_URL . '/join.php?meeting=' . $meetingId
        ]
    ]);
} else {
    mysqli_close($conn);
    echo json_encode(['success' => false, 'error' => 'Failed to create meeting']);
}
?>
