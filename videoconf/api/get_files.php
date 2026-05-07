<?php
/**
 * Video Conference SFU Solution - API: Get Shared Files
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$meeting_id = sanitize($_GET['meeting_id'] ?? '');
$limit = (int)($_GET['limit'] ?? 50);
$offset = (int)($_GET['offset'] ?? 0);

if (empty($meeting_id)) {
    echo json_encode(['success' => false, 'error' => 'Meeting ID is required']);
    exit;
}

$user = getCurrentUser();
$conn = getDBConnection();

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

// Get shared files
$stmt = mysqli_prepare($conn, "SELECT sf.*, u.full_name as uploaded_by, u.avatar 
    FROM shared_files sf 
    JOIN users u ON sf.user_id = u.id 
    WHERE sf.meeting_id = ? 
    ORDER BY sf.created_at DESC 
    LIMIT ? OFFSET ?");
mysqli_stmt_bind_param($stmt, "iii", $meeting['id'], $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$files = [];
while ($file = mysqli_fetch_assoc($result)) {
    $files[] = [
        'id' => $file['id'],
        'original_name' => $file['original_name'],
        'file_path' => $file['file_path'],
        'file_size' => $file['file_size'],
        'file_type' => $file['file_type'],
        'uploaded_by' => $file['uploaded_by'],
        'created_at' => $file['created_at'],
        'download_url' => APP_URL . '/' . $file['file_path'],
        'icon_class' => getFileIconClass($file['file_type'])
    ];
}

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'files' => $files,
    'count' => count($files)
]);

// Helper function for file icon class
function getFileIconClass($fileType) {
    if (strpos($fileType, 'pdf') !== false) return 'pdf';
    if (strpos($fileType, 'word') !== false || strpos($fileType, 'document') !== false) return 'doc';
    if (strpos($fileType, 'excel') !== false || strpos($fileType, 'spreadsheet') !== false) return 'xls';
    if (strpos($fileType, 'powerpoint') !== false || strpos($fileType, 'presentation') !== false) return 'ppt';
    if (strpos($fileType, 'image') !== false) return 'img';
    if (strpos($fileType, 'zip') !== false || strpos($fileType, 'rar') !== false || strpos($fileType, 'archive') !== false) return 'zip';
    return 'file';
}
?>
