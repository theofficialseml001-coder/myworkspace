<?php
/**
 * Video Conference SFU Solution - API: Get Participants
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
$stmt = mysqli_prepare($conn, "SELECT id FROM meetings WHERE meeting_id = ?");
mysqli_stmt_bind_param($stmt, "s", $meeting_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$meeting = mysqli_fetch_assoc($result);

if (!$meeting) {
    echo json_encode(['success' => false, 'error' => 'Meeting not found']);
    exit;
}

// Get all active participants
$stmt = mysqli_prepare($conn, "SELECT p.*, u.email, u.avatar 
    FROM participants p 
    LEFT JOIN users u ON p.user_id = u.id 
    WHERE p.meeting_id = ? AND p.status IN ('active', 'joined') 
    ORDER BY p.joined_at DESC");
mysqli_stmt_bind_param($stmt, "i", $meeting['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$participants = [];
while ($participant = mysqli_fetch_assoc($result)) {
    $participants[] = [
        'id' => $participant['id'],
        'user_id' => $participant['user_id'],
        'display_name' => $participant['display_name'],
        'role' => $participant['role'],
        'status' => $participant['status'],
        'email' => $participant['email'],
        'avatar' => $participant['avatar'],
        'joined_at' => $participant['joined_at'],
        'is_host' => $participant['role'] === 'host',
        'is_co_host' => $participant['role'] === 'co-host'
    ];
}

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'participants' => $participants,
    'count' => count($participants)
]);
?>
