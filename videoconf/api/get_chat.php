<?php
/**
 * Video Conference SFU Solution - API: Get Chat Messages
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

// Get chat messages (public and private to/from current user)
$stmt = mysqli_prepare($conn, "SELECT cm.*, u.full_name as sender_name, u.avatar as sender_avatar 
    FROM chat_messages cm 
    JOIN users u ON cm.sender_id = u.id 
    WHERE cm.meeting_id = ? AND (cm.message_type = 'public' OR cm.recipient_id = ? OR cm.sender_id = ?) 
    ORDER BY cm.created_at DESC 
    LIMIT ? OFFSET ?");
mysqli_stmt_bind_param($stmt, "iiiii", $meeting['id'], $user['id'], $user['id'], $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];
while ($msg = mysqli_fetch_assoc($result)) {
    $messages[] = $msg;
}

// Reverse array to show oldest first
$messages = array_reverse($messages);

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'messages' => $messages,
    'has_more' => count($messages) == $limit
]);
?>
