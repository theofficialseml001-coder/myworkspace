<?php
/**
 * Video Conference SFU Solution - API: Send Chat Message
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
$message = trim($_POST['message'] ?? '');
$recipient_id = (int)($_POST['recipient_id'] ?? 0); // 0 for public message

if (empty($meeting_id) || empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Meeting ID and message are required']);
    exit;
}

// Validate message length
if (strlen($message) > 5000) {
    echo json_encode(['success' => false, 'error' => 'Message too long (max 5000 characters)']);
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

// Determine message type
$message_type = ($recipient_id > 0) ? 'private' : 'public';

// Save chat message to database
$stmt = mysqli_prepare($conn, "INSERT INTO chat_messages 
    (meeting_id, sender_id, recipient_id, message, message_type) 
    VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iiiss", $meeting['id'], $user['id'], $recipient_id, $message, $message_type);

if (mysqli_stmt_execute($stmt)) {
    $message_id = mysqli_insert_id($conn);
    
    // Log activity (only for public messages)
    if ($message_type === 'public') {
        $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description, ip_address) 
            VALUES (?, 'send_chat', 'Sent chat message in meeting: {$meeting_id}', ?)");
        $ip = $_SERVER['REMOTE_ADDR'];
        mysqli_stmt_bind_param($stmt, "is", $user['id'], $ip);
        mysqli_stmt_execute($stmt);
    }
    
    echo json_encode([
        'success' => true,
        'message' => [
            'id' => $message_id,
            'sender_id' => $user['id'],
            'sender_name' => $user['full_name'],
            'message' => sanitize($message),
            'message_type' => $message_type,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to send message']);
}

mysqli_close($conn);
?>
