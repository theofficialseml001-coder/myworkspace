<?php
/**
 * Video Conference SFU Solution - API: Invite Participants
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
$emails = $_POST['emails'] ?? []; // Array of emails
$role = sanitize($_POST['role'] ?? 'participant');

if (empty($meeting_id) || empty($emails)) {
    echo json_encode(['success' => false, 'error' => 'Meeting ID and emails are required']);
    exit;
}

// Validate role
$allowed_roles = ['participant', 'co-host'];
if (!in_array($role, $allowed_roles)) {
    $role = 'participant';
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

// Only host can send invites
if ($meeting['host_id'] != $user['id']) {
    echo json_encode(['success' => false, 'error' => 'Only the host can send invitations']);
    exit;
}

$invited = [];
$failed = [];

foreach ($emails as $email) {
    $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        $failed[] = $email;
        continue;
    }
    
    // Check if user exists
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existing_user = mysqli_fetch_assoc($result);
    
    $user_id = $existing_user ? $existing_user['id'] : null;
    
    // Check if already invited
    $stmt = mysqli_prepare($conn, "SELECT id FROM invitations 
        WHERE meeting_id = ? AND email = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, "is", $meeting['id'], $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_fetch_assoc($result)) {
        continue; // Already invited
    }
    
    // Create invitation
    $token = generateToken();
    $stmt = mysqli_prepare($conn, "INSERT INTO invitations 
        (meeting_id, email, user_id, role, token, invited_by) 
        VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "siissi", $meeting['id'], $email, $user_id, $role, $token, $user['id']);
    
    if (mysqli_stmt_execute($stmt)) {
        $invited[] = $email;
        
        // TODO: Send email notification
        // sendInvitationEmail($email, $meeting_id, $token);
    } else {
        $failed[] = $email;
    }
}

// Log activity
if (!empty($invited)) {
    $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description, ip_address) 
        VALUES (?, 'send_invites', 'Sent ' . count($invited) . ' invitations for meeting: {$meeting_id}', ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_stmt_bind_param($stmt, "is", $user['id'], $ip);
    mysqli_stmt_execute($stmt);
}

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'invited' => $invited,
    'failed' => $failed,
    'message' => count($invited) . ' invitations sent successfully'
]);
?>
