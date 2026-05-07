<?php
/**
 * Video Conference SFU Solution - API: Update Whiteboard
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
$action = sanitize($_POST['action'] ?? '');
$data = $_POST['data'] ?? '';

if (empty($meeting_id) || empty($action)) {
    echo json_encode(['success' => false, 'error' => 'Meeting ID and action are required']);
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

switch ($action) {
    case 'save':
        // Save whiteboard data
        $stmt = mysqli_prepare($conn, "UPDATE whiteboard_sessions SET board_data = ?, updated_at = NOW() 
            WHERE meeting_id = ?");
        mysqli_stmt_bind_param($stmt, "si", $data, $meeting['id']);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Whiteboard saved successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save whiteboard']);
        }
        break;
        
    case 'clear':
        // Clear whiteboard
        $stmt = mysqli_prepare($conn, "UPDATE whiteboard_sessions SET board_data = '{}' WHERE meeting_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $meeting['id']);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Whiteboard cleared']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to clear whiteboard']);
        }
        break;
        
    case 'get':
        // Get whiteboard data
        $stmt = mysqli_prepare($conn, "SELECT board_data FROM whiteboard_sessions WHERE meeting_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $meeting['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $whiteboard = mysqli_fetch_assoc($result);
        
        echo json_encode([
            'success' => true,
            'data' => $whiteboard ? $whiteboard['board_data'] : '{}'
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

mysqli_close($conn);
?>
