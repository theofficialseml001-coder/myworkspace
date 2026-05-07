<?php
/**
 * Video Conference SFU Solution - API: Mute/Unmute Participant
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
$meeting_id = sanitize($_POST['meeting_id'] ?? '');
$participant_id = (int)($_POST['participant_id'] ?? 0);
$action = sanitize($_POST['action'] ?? ''); // mute, unmute, disable_video, enable_video

if (empty($meeting_id) || $participant_id <= 0 || empty($action)) {
    echo json_encode(['success' => false, 'error' => 'Meeting ID, participant ID, and action are required']);
    exit;
}

// Validate action
$allowed_actions = ['mute', 'unmute', 'disable_video', 'enable_video', 'remove'];
if (!in_array($action, $allowed_actions)) {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

// Get meeting info
$stmt = mysqli_prepare($conn, "SELECT id, host_id FROM meetings WHERE meeting_id = ?");
mysqli_stmt_bind_param($stmt, "s", $meeting_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$meeting = mysqli_fetch_assoc($result);

if (!$meeting) {
    echo json_encode(['success' => false, 'error' => 'Meeting not found']);
    exit;
}

// Get current user's role in the meeting
$stmt = mysqli_prepare($conn, "SELECT role FROM participants 
    WHERE meeting_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $meeting['id'], $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$current_participant = mysqli_fetch_assoc($result);

if (!$current_participant) {
    echo json_encode(['success' => false, 'error' => 'You are not a participant in this meeting']);
    exit;
}

// Only host or co-host can mute others
if ($current_participant['role'] !== 'host' && $current_participant['role'] !== 'co-host') {
    echo json_encode(['success' => false, 'error' => 'Only host or co-host can perform this action']);
    exit;
}

// Get target participant info
$stmt = mysqli_prepare($conn, "SELECT p.*, u.id as user_account_id FROM participants p 
    LEFT JOIN users u ON p.user_id = u.id 
    WHERE p.meeting_id = ? AND p.id = ?");
mysqli_stmt_bind_param($stmt, "ii", $meeting['id'], $participant_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$target_participant = mysqli_fetch_assoc($result);

if (!$target_participant) {
    echo json_encode(['success' => false, 'error' => 'Participant not found']);
    exit;
}

// Cannot modify host
if ($target_participant['role'] === 'host') {
    echo json_encode(['success' => false, 'error' => 'Cannot modify host settings']);
    exit;
}

switch ($action) {
    case 'mute':
        // Send WebSocket signal to mute (handled client-side)
        // For now, just log the action
        break;
        
    case 'unmute':
        // Note: Can only request unmute, cannot force it
        break;
        
    case 'disable_video':
        // Send WebSocket signal to disable video
        break;
        
    case 'enable_video':
        // Send WebSocket signal to enable video
        break;
        
    case 'remove':
        // Remove participant from meeting
        $stmt = mysqli_prepare($conn, "UPDATE participants SET status = 'removed' 
            WHERE meeting_id = ? AND id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $meeting['id'], $participant_id);
        mysqli_stmt_execute($stmt);
        break;
}

// Log activity
$stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description, ip_address) 
    VALUES (?, 'moderate_participant', '{$action} participant {$participant_id} in meeting: {$meeting_id}', ?)");
$ip = $_SERVER['REMOTE_ADDR'];
mysqli_stmt_bind_param($stmt, "is", $user['id'], $ip);
mysqli_stmt_execute($stmt);

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'message' => ucfirst(str_replace('_', ' ', $action)) . ' action performed successfully',
    'action' => $action,
    'participant_id' => $participant_id
]);
?>
