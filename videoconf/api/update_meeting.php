<?php
/**
 * Video Conference SFU Solution - API: Update Meeting Settings
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
$stmt = mysqli_prepare($conn, "SELECT id, host_id FROM meetings WHERE meeting_id = ?");
mysqli_stmt_bind_param($stmt, "s", $meeting_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$meeting = mysqli_fetch_assoc($result);

if (!$meeting) {
    echo json_encode(['success' => false, 'error' => 'Meeting not found']);
    exit;
}

// Only host can update settings
if ($meeting['host_id'] != $user['id']) {
    echo json_encode(['success' => false, 'error' => 'Only the host can update meeting settings']);
    exit;
}

// Build update fields
$update_fields = [];
$types = '';
$values = [];

if (isset($_POST['title'])) {
    $update_fields[] = 'title = ?';
    $types .= 's';
    $values[] = sanitize($_POST['title']);
}

if (isset($_POST['description'])) {
    $update_fields[] = 'description = ?';
    $types .= 's';
    $values[] = sanitize($_POST['description']);
}

if (isset($_POST['password'])) {
    $password = $_POST['password'];
    if (!empty($password)) {
        $update_fields[] = 'password = ?';
        $types .= 's';
        $values[] = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $update_fields[] = 'password = NULL';
    }
}

if (isset($_POST['is_public'])) {
    $update_fields[] = 'is_public = ?';
    $types .= 'i';
    $values[] = (int)$_POST['is_public'];
}

if (isset($_POST['allow_recording'])) {
    $update_fields[] = 'allow_recording = ?';
    $types .= 'i';
    $values[] = (int)$_POST['allow_recording'];
}

if (isset($_POST['allow_screen_share'])) {
    $update_fields[] = 'allow_screen_share = ?';
    $types .= 'i';
    $values[] = (int)$_POST['allow_screen_share'];
}

if (isset($_POST['allow_chat'])) {
    $update_fields[] = 'allow_chat = ?';
    $types .= 'i';
    $values[] = (int)$_POST['allow_chat'];
}

if (isset($_POST['allow_whiteboard'])) {
    $update_fields[] = 'allow_whiteboard = ?';
    $types .= 'i';
    $values[] = (int)$_POST['allow_whiteboard'];
}

if (isset($_POST['max_participants'])) {
    $update_fields[] = 'max_participants = ?';
    $types .= 'i';
    $values[] = (int)$_POST['max_participants'];
}

if (empty($update_fields)) {
    echo json_encode(['success' => false, 'error' => 'No fields to update']);
    exit;
}

// Build and execute update query
$sql = "UPDATE meetings SET " . implode(', ', $update_fields) . " WHERE id = ?";
$types .= 'i';
$values[] = $meeting['id'];

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$values);

if (mysqli_stmt_execute($stmt)) {
    // Log activity
    $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description, ip_address) 
        VALUES (?, 'update_meeting', 'Updated meeting settings for: {$meeting_id}', ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_stmt_bind_param($stmt, "is", $user['id'], $ip);
    mysqli_stmt_execute($stmt);
    
    echo json_encode([
        'success' => true,
        'message' => 'Meeting settings updated successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update meeting settings']);
}

mysqli_close($conn);
?>
