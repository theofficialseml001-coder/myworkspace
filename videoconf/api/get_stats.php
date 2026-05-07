<?php
/**
 * Video Conference SFU Solution - API: Get Dashboard Stats
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

$stats = [];

// Total meetings hosted
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM meetings WHERE host_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$stats['total_meetings'] = $data['count'];

// Active meetings
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM meetings 
    WHERE host_id = ? AND status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$stats['active_meetings'] = $data['count'];

// Upcoming meetings
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM meetings 
    WHERE host_id = ? AND scheduled_start > NOW() AND status = 'waiting'");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$stats['upcoming_meetings'] = $data['count'];

// Total recordings
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM recordings r 
    JOIN meetings m ON r.meeting_id = m.id 
    WHERE m.host_id = ? AND r.status = 'completed'");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$stats['total_recordings'] = $data['count'];

// Total storage used (in bytes)
$stmt = mysqli_prepare($conn, "SELECT SUM(file_size) as total FROM recordings r 
    JOIN meetings m ON r.meeting_id = m.id 
    WHERE m.host_id = ? AND r.status = 'completed'");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$stats['storage_used'] = $data['total'] ?? 0;

// Total participants across all meetings
$stmt = mysqli_prepare($conn, "SELECT COUNT(DISTINCT p.user_id) as count FROM participants p 
    JOIN meetings m ON p.meeting_id = m.id 
    WHERE m.host_id = ? AND p.user_id IS NOT NULL");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$stats['unique_participants'] = $data['count'];

// Meetings this month
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM meetings 
    WHERE host_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$stats['meetings_this_month'] = $data['count'];

// Recent meetings (last 5)
$stmt = mysqli_prepare($conn, "SELECT id, meeting_id, title, status, created_at 
    FROM meetings 
    WHERE host_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$recent_meetings = [];
while ($meeting = mysqli_fetch_assoc($result)) {
    $recent_meetings[] = $meeting;
}
$stats['recent_meetings'] = $recent_meetings;

// Get subscription info
$stmt = mysqli_prepare($conn, "SELECT sp.name as plan_name, sp.max_participants, sp.max_duration,
    sp.max_recordings, us.status as subscription_status, us.ends_at
    FROM user_subscriptions us
    JOIN subscription_plans sp ON us.plan_id = sp.id
    WHERE us.user_id = ? AND us.status = 'active'
    ORDER BY us.created_at DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$subscription = mysqli_fetch_assoc($result);
$stats['subscription'] = $subscription;

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'stats' => $stats
]);
?>
