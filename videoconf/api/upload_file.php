<?php
/**
 * Video Conference SFU Solution - API: Upload File
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
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$meeting_id = sanitize($_POST['meeting_id'] ?? '');
$file = $_FILES['file'];
$file_size = $file['size'];
$file_type = $file['type'];
$file_name = sanitize($file['name']);
$file_tmp = $file['tmp_name'];

// Validate file size
if ($file_size > MAX_FILE_SIZE) {
    echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 100MB.']);
    exit;
}

// Validate file type (block executable files)
$blocked_extensions = ['exe', 'bat', 'sh', 'cmd', 'msi', 'dll', 'scr', 'pif', 'vbs', 'js'];
$extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
if (in_array($extension, $blocked_extensions)) {
    echo json_encode(['success' => false, 'error' => 'This file type is not allowed for security reasons.']);
    exit;
}

// Create upload directory if it doesn't exist
$upload_dir = FILE_SHARE_DIR;
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$filename = 'file_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
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
    
    // Save file to database
    $stmt = mysqli_prepare($conn, "INSERT INTO shared_files 
        (meeting_id, user_id, file_path, original_name, file_size, file_type) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    mysqli_stmt_bind_param($stmt, "iissis", $meeting['id'], $user['id'], $filepath, $file_name, $file_size, $file_type);
    
    if (mysqli_stmt_execute($stmt)) {
        $file_db_id = mysqli_insert_id($conn);
        
        // Log activity
        $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, action, description, ip_address) 
            VALUES (?, 'share_file', 'Shared file: {$file_name}', ?)");
        $ip = $_SERVER['REMOTE_ADDR'];
        mysqli_stmt_bind_param($stmt, "is", $user['id'], $ip);
        mysqli_stmt_execute($stmt);
        
        echo json_encode([
            'success' => true,
            'file' => [
                'id' => $file_db_id,
                'name' => $file_name,
                'size' => $file_size,
                'type' => $file_type,
                'url' => APP_URL . '/assets/uploads/files/' . $filename,
                'uploaded_by' => $user['full_name']
            ]
        ]);
    } else {
        unlink($filepath);
        echo json_encode(['success' => false, 'error' => 'Failed to save file to database']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
}

mysqli_close($conn);
?>
