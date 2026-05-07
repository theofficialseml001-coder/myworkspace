<?php
/**
 * Video Conference SFU Solution - API: Get Recordings
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$meeting_id = sanitize($_GET['meeting_id'] ?? '');
$limit = (int)($_GET['limit'] ?? 20);
$offset = (int)($_GET['offset'] ?? 0);

$user = getCurrentUser();
$conn = getDBConnection();

// Build query based on whether meeting_id is provided
if (!empty($meeting_id)) {
    // Get specific meeting recordings
    $stmt = mysqli_prepare($conn, "SELECT r.*, m.title as meeting_title, m.meeting_id 
        FROM recordings r 
        JOIN meetings m ON r.meeting_id = m.id 
        WHERE r.meeting_id = (SELECT id FROM meetings WHERE meeting_id = ?) 
        AND r.status = 'completed'
        ORDER BY r.created_at DESC 
        LIMIT ? OFFSET ?");
    mysqli_stmt_bind_param($stmt, "sii", $meeting_id, $limit, $offset);
} else {
    // Get all user's recordings (hosted meetings)
    $stmt = mysqli_prepare($conn, "SELECT r.*, m.title as meeting_title, m.meeting_id 
        FROM recordings r 
        JOIN meetings m ON r.meeting_id = m.id 
        WHERE m.host_id = ? 
        AND r.status = 'completed'
        ORDER BY r.created_at DESC 
        LIMIT ? OFFSET ?");
    mysqli_stmt_bind_param($stmt, "iii", $user['id'], $limit, $offset);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$recordings = [];
while ($rec = mysqli_fetch_assoc($result)) {
    $recordings[] = [
        'id' => $rec['id'],
        'meeting_id' => $rec['meeting_id'],
        'meeting_title' => $rec['meeting_title'],
        'file_path' => $rec['file_path'],
        'file_size' => $rec['file_size'],
        'file_type' => $rec['file_type'],
        'duration' => $rec['duration'],
        'status' => $rec['status'],
        'created_at' => $rec['created_at'],
        'download_url' => APP_URL . '/' . $rec['file_path']
    ];
}

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'recordings' => $recordings,
    'count' => count($recordings)
]);
?>
