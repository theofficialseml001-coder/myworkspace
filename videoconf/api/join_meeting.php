<?php
/**
 * Video Conference SFU Solution - API: Join Meeting
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in (optional for public meetings)
$is_logged_in = isLoggedIn();
$user = $is_logged_in ? getCurrentUser() : null;
$conn = getDBConnection();

// Get POST data
$meeting_id = sanitize($_POST['meeting_id'] ?? '');
$password = $_POST['password'] ?? '';
$display_name = sanitize($_POST['display_name'] ?? ($user ? $user['full_name'] : 'Guest'));

if (empty($meeting_id)) {
    echo json_encode(['success' => false, 'error' => 'Meeting ID is required']);
    exit;
}

// Get meeting info
$stmt = mysqli_prepare($conn, "SELECT m.*, u.full_name as host_name FROM meetings m 
    JOIN users u ON m.host_id = u.id WHERE m.meeting_id = ?");
mysqli_stmt_bind_param($stmt, "s", $meeting_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$meeting = mysqli_fetch_assoc($result);

if (!$meeting) {
    echo json_encode(['success' => false, 'error' => 'Meeting not found']);
    exit;
}

// Check meeting status
if ($meeting['status'] !== 'active') {
    echo json_encode(['success' => false, 'error' => 'Meeting is not active']);
    exit;
}

// Check password if required
if ($meeting['password'] && !password_verify($password, $meeting['password'])) {
    echo json_encode(['success' => false, 'error' => 'Incorrect meeting password']);
    exit;
}

// Check participant limit
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM participants 
    WHERE meeting_id = ? AND status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $meeting['id']);
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$count_data = mysqli_fetch_assoc($count_result);

if ($count_data['count'] >= $meeting['max_participants']) {
    echo json_encode(['success' => false, 'error' => 'Meeting is full']);
    exit;
}

// Determine user role
$role = 'participant';
if ($user && $meeting['host_id'] == $user['id']) {
    $role = 'host';
} elseif ($user) {
    // Check if user is a co-host
    $stmt = mysqli_prepare($conn, "SELECT role FROM invitations 
        WHERE meeting_id = ? AND (email = ? OR user_id = ?) AND role = 'co-host'");
    mysqli_stmt_bind_param($stmt, "isi", $meeting['id'], $user ? $user['email'] : '', $user ? $user['id'] : 0);
    mysqli_stmt_execute($stmt);
    $invite_result = mysqli_stmt_get_result($stmt);
    if (mysqli_fetch_assoc($invite_result)) {
        $role = 'co-host';
    }
}

// Add participant
$user_id = $user ? $user['id'] : null;
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

$stmt = mysqli_prepare($conn, "INSERT INTO participants 
    (meeting_id, user_id, display_name, role, ip_address, user_agent, status) 
    VALUES (?, ?, ?, ?, ?, ?, 'active')");
mysqli_stmt_bind_param($stmt, "iissss", $meeting['id'], $user_id, $display_name, $role, $ip, $user_agent);

if (mysqli_stmt_execute($stmt)) {
    $participant_id = mysqli_insert_id($conn);
    
    // Log activity
    if ($user) {
        $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description, ip_address) 
            VALUES (?, 'join_meeting', 'Joined meeting: {$meeting_id}', ?)");
        mysqli_stmt_bind_param($stmt, "is", $user['id'], $ip);
        mysqli_stmt_execute($stmt);
    }
    
    echo json_encode([
        'success' => true,
        'participant' => [
            'id' => $participant_id,
            'meeting_id' => $meeting['id'],
            'display_name' => $display_name,
            'role' => $role,
            'user_id' => $user_id
        ],
        'meeting' => [
            'title' => $meeting['title'],
            'host_name' => $meeting['host_name'],
            'allow_recording' => $meeting['allow_recording'],
            'allow_screen_share' => $meeting['allow_screen_share'],
            'allow_chat' => $meeting['allow_chat'],
            'allow_whiteboard' => $meeting['allow_whiteboard']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to join meeting']);
}

mysqli_close($conn);
?>
