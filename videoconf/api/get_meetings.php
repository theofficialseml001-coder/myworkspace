<?php
/**
 * Video Conference SFU Solution - API: Get User Meetings
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$conn = getDBConnection();

// Get parameters
$type = sanitize($_GET['type'] ?? 'all'); // all, hosted, joined, upcoming, past
$limit = (int)($_GET['limit'] ?? 20);
$offset = (int)($_GET['offset'] ?? 0);
$status = sanitize($_GET['status'] ?? ''); // active, waiting, ended

// Build query based on type
switch ($type) {
    case 'hosted':
        $stmt = mysqli_prepare($conn, "SELECT m.*, 
            (SELECT COUNT(*) FROM participants WHERE meeting_id = m.id AND status = 'active') as participant_count,
            (SELECT COUNT(*) FROM recordings WHERE meeting_id = m.id AND status = 'completed') as recording_count
            FROM meetings m 
            WHERE m.host_id = ? 
            ORDER BY m.created_at DESC 
            LIMIT ? OFFSET ?");
        mysqli_stmt_bind_param($stmt, "iii", $user['id'], $limit, $offset);
        break;
        
    case 'joined':
        $stmt = mysqli_prepare($conn, "SELECT m.*, 
            (SELECT COUNT(*) FROM participants WHERE meeting_id = m.id AND status = 'active') as participant_count
            FROM meetings m 
            JOIN participants p ON m.id = p.meeting_id 
            WHERE p.user_id = ? AND p.role != 'host'
            ORDER BY m.created_at DESC 
            LIMIT ? OFFSET ?");
        mysqli_stmt_bind_param($stmt, "iii", $user['id'], $limit, $offset);
        break;
        
    case 'upcoming':
        $stmt = mysqli_prepare($conn, "SELECT m.*, 
            (SELECT COUNT(*) FROM participants WHERE meeting_id = m.id AND status = 'active') as participant_count
            FROM meetings m 
            WHERE m.host_id = ? AND m.scheduled_start > NOW() AND m.status = 'waiting'
            ORDER BY m.scheduled_start ASC 
            LIMIT ? OFFSET ?");
        mysqli_stmt_bind_param($stmt, "iii", $user['id'], $limit, $offset);
        break;
        
    case 'past':
        $stmt = mysqli_prepare($conn, "SELECT m.*, 
            (SELECT COUNT(*) FROM participants WHERE meeting_id = m.id) as total_participants,
            (SELECT COUNT(*) FROM recordings WHERE meeting_id = m.id) as recording_count
            FROM meetings m 
            WHERE m.host_id = ? AND (m.scheduled_start < NOW() OR m.status = 'ended')
            ORDER BY m.created_at DESC 
            LIMIT ? OFFSET ?");
        mysqli_stmt_bind_param($stmt, "iii", $user['id'], $limit, $offset);
        break;
        
    default:
        $stmt = mysqli_prepare($conn, "SELECT m.*, 
            (SELECT COUNT(*) FROM participants WHERE meeting_id = m.id AND status = 'active') as participant_count,
            (SELECT COUNT(*) FROM recordings WHERE meeting_id = m.id AND status = 'completed') as recording_count
            FROM meetings m 
            WHERE m.host_id = ?
            ORDER BY m.created_at DESC 
            LIMIT ? OFFSET ?");
        mysqli_stmt_bind_param($stmt, "iii", $user['id'], $limit, $offset);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$meetings = [];
while ($meeting = mysqli_fetch_assoc($result)) {
    $meetings[] = [
        'id' => $meeting['id'],
        'meeting_id' => $meeting['meeting_id'],
        'title' => $meeting['title'],
        'description' => $meeting['description'],
        'type' => $meeting['meeting_type'],
        'status' => $meeting['status'],
        'scheduled_start' => $meeting['scheduled_start'],
        'duration' => $meeting['duration'],
        'is_public' => $meeting['is_public'],
        'participant_count' => $meeting['participant_count'] ?? 0,
        'recording_count' => $meeting['recording_count'] ?? 0,
        'created_at' => $meeting['created_at'],
        'join_url' => APP_URL . '/join.php?meeting=' . $meeting['meeting_id']
    ];
}

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'meetings' => $meetings,
    'count' => count($meetings)
]);
?>
