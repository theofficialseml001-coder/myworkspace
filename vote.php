<?php
include 'config.php';

// Ensure database is selected
if (!mysqli_select_db($conn, 'idea_validator')) {
    die(json_encode(array('success' => false, 'message' => 'Database not available')));
}

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method'));
    exit;
}

// Get idea ID
if (!isset($_POST['idea_id']) || empty($_POST['idea_id'])) {
    echo json_encode(array('success' => false, 'message' => 'Idea ID is required'));
    exit;
}

$idea_id = intval($_POST['idea_id']);
$voter_ip = $_SERVER['REMOTE_ADDR'];

// Check if idea exists
$check_sql = "SELECT id, votes FROM ideas WHERE id = $idea_id AND status = 'approved'";
$check_result = mysqli_query($conn, $check_sql);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    echo json_encode(array('success' => false, 'message' => 'Idea not found'));
    if ($check_result) mysqli_free_result($check_result);
    exit;
}

$idea = mysqli_fetch_assoc($check_result);
mysqli_free_result($check_result);

// Check if user already voted
$vote_check_sql = "SELECT id FROM votes WHERE idea_id = $idea_id AND voter_ip = '$voter_ip'";
$vote_check_result = mysqli_query($conn, $vote_check_sql);

if (mysqli_num_rows($vote_check_result) > 0) {
    echo json_encode(array('success' => false, 'message' => 'You have already voted for this idea'));
    mysqli_free_result($vote_check_result);
    exit;
}
mysqli_free_result($vote_check_result);

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Insert vote
    $insert_vote_sql = "INSERT INTO votes (idea_id, voter_ip) VALUES ($idea_id, '$voter_ip')";
    if (!mysqli_query($conn, $insert_vote_sql)) {
        throw new Exception("Failed to record vote");
    }

    // Update vote count
    $new_vote_count = $idea['votes'] + 1;
    $update_sql = "UPDATE ideas SET votes = $new_vote_count WHERE id = $idea_id";
    if (!mysqli_query($conn, $update_sql)) {
        throw new Exception("Failed to update vote count");
    }

    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode(array(
        'success' => true, 
        'message' => 'Vote recorded successfully',
        'new_vote_count' => $new_vote_count
    ));
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    echo json_encode(array('success' => false, 'message' => $e->getMessage()));
}
?>
