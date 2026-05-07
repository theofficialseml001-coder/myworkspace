<?php
/**
 * Video Conference SFU Solution - API: Leave Meeting
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

if (empty($meeting_id)) {
    echo json_encode(['success' => false, 'error' => 'Meeting ID is required']);
    exit;
}

// Get meeting info
$stmt = mysqli_prepare($conn, "SELECT id FROM meetings WHERE meeting_id = ?");
mysqli_stmt_bind_param($stmt, "s", $meeting_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$meeting = mysqli_fetch_assoc($result);

if (!$meeting) {
    echo json_encode(['success' => false, 'error' => 'Meeting not found']);
    exit;
}

// Update participant status to left
$stmt = mysqli_prepare($conn, "UPDATE participants SET status = 'left', left_at = NOW() 
    WHERE meeting_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $meeting['id'], $user['id']);

if (mysqli_stmt_execute($stmt)) {
    // Log activity
    $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description, ip_address) 
        VALUES (?, 'leave_meeting', 'Left meeting: {$meeting_id}', ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_stmt_bind_param($stmt, "is", $user['id'], $ip);
    mysqli_stmt_execute($stmt);
    
    // If host left, end the meeting for all
    $stmt = mysqli_prepare($conn, "SELECT host_id FROM meetings WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $meeting['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $meeting_data = mysqli_fetch_assoc($result);
    
    $host_left = false;
    if ($meeting_data['host_id'] == $user['id']) {
        $stmt = mysqli_prepare($conn, "UPDATE participants SET status = 'ended' WHERE meeting_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $meeting['id']);
        mysqli_stmt_execute($stmt);
        
        $stmt = mysqli_prepare($conn, "UPDATE meetings SET status = 'ended' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $meeting['id']);
        mysqli_stmt_execute($stmt);
        
        $host_left = true;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Left meeting successfully',
        'host_left' => $host_left
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to leave meeting']);
}

mysqli_close($conn);
?>
