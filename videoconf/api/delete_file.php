<?php
/**
 * Video Conference SFU Solution - API: Delete File
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
$file_id = (int)($_POST['file_id'] ?? 0);

if ($file_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'File ID is required']);
    exit;
}

// Get file info and verify ownership/permissions
$stmt = mysqli_prepare($conn, "SELECT sf.*, m.host_id FROM shared_files sf 
    JOIN meetings m ON sf.meeting_id = m.id 
    WHERE sf.id = ?");
mysqli_stmt_bind_param($stmt, "i", $file_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$file = mysqli_fetch_assoc($result);

if (!$file) {
    echo json_encode(['success' => false, 'error' => 'File not found']);
    exit;
}

// Only uploader or meeting host can delete
if ($file['user_id'] != $user['id'] && $file['host_id'] != $user['id']) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized to delete this file']);
    exit;
}

// Delete file from server
if (file_exists($file['file_path'])) {
    unlink($file['file_path']);
}

// Delete from database
$stmt = mysqli_prepare($conn, "DELETE FROM shared_files WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $file_id);

if (mysqli_stmt_execute($stmt)) {
    // Log activity
    $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description, ip_address) 
        VALUES (?, 'delete_file', 'Deleted file ID: {$file_id}', ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_stmt_bind_param($stmt, "is", $user['id'], $ip);
    mysqli_stmt_execute($stmt);
    
    echo json_encode([
        'success' => true,
        'message' => 'File deleted successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete file']);
}

mysqli_close($conn);
?>
