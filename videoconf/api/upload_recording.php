<?php
/**
 * Video Conference SFU Solution - API: Upload Recording
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

// Check if file was uploaded
if (!isset($_FILES['recording']) || $_FILES['recording']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No recording file uploaded']);
    exit;
}

$meeting_id = sanitize($_POST['meeting_id'] ?? '');
$file = $_FILES['recording'];
$file_size = $file['size'];
$file_type = $file['type'];
$file_tmp = $file['tmp_name'];

// Validate file type
$allowed_types = ['video/webm', 'video/mp4', 'video/x-matroska'];
if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only video files are allowed.']);
    exit;
}

// Validate file size (max 2GB for recordings)
$max_size = 2147483648; // 2GB
if ($file_size > $max_size) {
    echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 2GB.']);
    exit;
}

// Create upload directory if it doesn't exist
$upload_dir = RECORDING_DIR;
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'recording_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
$filepath = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file_tmp, $filepath)) {
    // Get meeting info
    $stmt = mysqli_prepare($conn, "SELECT id FROM meetings WHERE meeting_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $meeting_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $meeting = mysqli_fetch_assoc($result);
    
    if (!$meeting) {
        unlink($filepath);
        echo json_encode(['success' => false, 'error' => 'Meeting not found']);
        exit;
    }
    
    // Save recording to database
    $stmt = mysqli_prepare($conn, "INSERT INTO recordings 
        (meeting_id, user_id, file_path, file_size, file_type, duration, status) 
        VALUES (?, ?, ?, ?, ?, 0, 'processing')");
    
    mysqli_stmt_bind_param($stmt, "iissi", $meeting['id'], $user['id'], $filepath, $file_size, $file_type);
    
    if (mysqli_stmt_execute($stmt)) {
        $recording_id = mysqli_insert_id($conn);
        
        // Log activity
        $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description, ip_address) 
            VALUES (?, 'upload_recording', 'Uploaded recording for meeting: {$meeting_id}', ?)");
        $ip = $_SERVER['REMOTE_ADDR'];
        mysqli_stmt_bind_param($stmt, "is", $user['id'], $ip);
        mysqli_stmt_execute($stmt);
        
        echo json_encode([
            'success' => true,
            'recording_id' => $recording_id,
            'url' => APP_URL . '/assets/uploads/recordings/' . $filename
        ]);
    } else {
        unlink($filepath);
        echo json_encode(['success' => false, 'error' => 'Failed to save recording to database']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save recording file']);
}

mysqli_close($conn);
?>
