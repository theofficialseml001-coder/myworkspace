<?php
/**
 * Video Conference SFU Solution - API: Delete Recording
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
$recording_id = (int)($_POST['recording_id'] ?? 0);

if ($recording_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Recording ID is required']);
    exit;
}

// Get recording info and verify ownership
$stmt = mysqli_prepare($conn, "SELECT r.*, m.host_id FROM recordings r 
    JOIN meetings m ON r.meeting_id = m.id 
    WHERE r.id = ?");
mysqli_stmt_bind_param($stmt, "i", $recording_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$recording = mysqli_fetch_assoc($result);

if (!$recording) {
    echo json_encode(['success' => false, 'error' => 'Recording not found']);
    exit;
}

// Only host can delete recordings
if ($recording['host_id'] != $user['id']) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized to delete this recording']);
    exit;
}

// Delete file from server
if (file_exists($recording['file_path'])) {
    unlink($recording['file_path']);
}

// Delete from database
$stmt = mysqli_prepare($conn, "DELETE FROM recordings WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $recording_id);

if (mysqli_stmt_execute($stmt)) {
    // Log activity
    $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description, ip_address) 
        VALUES (?, 'delete_recording', 'Deleted recording ID: {$recording_id}', ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_stmt_bind_param($stmt, "is", $user['id'], $ip);
    mysqli_stmt_execute($stmt);
    
    echo json_encode([
        'success' => true,
        'message' => 'Recording deleted successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete recording']);
}

mysqli_close($conn);
?>
