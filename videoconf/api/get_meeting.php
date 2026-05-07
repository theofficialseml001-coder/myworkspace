<?php
/**
 * Video Conference SFU Solution - API: Get Meeting Info
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$meeting_id = sanitize($_GET['meeting_id'] ?? '');

if (empty($meeting_id)) {
    echo json_encode(['success' => false, 'error' => 'Meeting ID is required']);
    exit;
}

$user = getCurrentUser();
$conn = getDBConnection();

// Get meeting info
$stmt = mysqli_prepare($conn, "SELECT m.*, u.full_name as host_name, u.email as host_email 
    FROM meetings m 
    JOIN users u ON m.host_id = u.id 
    WHERE m.meeting_id = ?");
mysqli_stmt_bind_param($stmt, "s", $meeting_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$meeting = mysqli_fetch_assoc($result);

if (!$meeting) {
    echo json_encode(['success' => false, 'error' => 'Meeting not found']);
    exit;
}

// Check if user has access
if ($meeting['host_id'] != $user['id'] && !$meeting['is_public']) {
    // Check if user is invited
    $stmt = mysqli_prepare($conn, "SELECT id FROM invitations 
        WHERE meeting_id = ? AND (email = ? OR user_id = ?) AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, "isi", $meeting['id'], $user['email'], $user['id']);
    mysqli_stmt_execute($stmt);
    $invite_result = mysqli_stmt_get_result($stmt);
    
    if (!mysqli_fetch_assoc($invite_result)) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }
}

// Get participants count
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM participants 
    WHERE meeting_id = ? AND status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $meeting['id']);
mysqli_stmt_execute($stmt);
$participant_result = mysqli_stmt_get_result($stmt);
$participant_data = mysqli_fetch_assoc($participant_result);
$meeting['participants_count'] = $participant_data['count'];

// Get shared files
$stmt = mysqli_prepare($conn, "SELECT sf.*, u.full_name as uploaded_by 
    FROM shared_files sf 
    JOIN users u ON sf.user_id = u.id 
    WHERE sf.meeting_id = ? 
    ORDER BY sf.created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $meeting['id']);
mysqli_stmt_execute($stmt);
$files_result = mysqli_stmt_get_result($stmt);
$files = [];
while ($file = mysqli_fetch_assoc($files_result)) {
    $files[] = $file;
}
$meeting['files'] = $files;

// Get recordings
$stmt = mysqli_prepare($conn, "SELECT r.*, u.full_name as recorded_by 
    FROM recordings r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.meeting_id = ? AND r.status = 'completed' 
    ORDER BY r.created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $meeting['id']);
mysqli_stmt_execute($stmt);
$recordings_result = mysqli_stmt_get_result($stmt);
$recordings = [];
while ($rec = mysqli_fetch_assoc($recordings_result)) {
    $recordings[] = $rec;
}
$meeting['recordings'] = $recordings;

// Get whiteboard session
$stmt = mysqli_prepare($conn, "SELECT * FROM whiteboard_sessions WHERE meeting_id = ?");
mysqli_stmt_bind_param($stmt, "i", $meeting['id']);
mysqli_stmt_execute($stmt);
$whiteboard_result = mysqli_stmt_get_result($stmt);
$meeting['whiteboard'] = mysqli_fetch_assoc($whiteboard_result);

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'meeting' => $meeting
]);
?>
