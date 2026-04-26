<?php
/**
 * Messages - Online Class Portal
 * Send and receive messages between users
 */

require_once 'portal_config.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$conn = getDBConnection();

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    verifyCSRFToken($_POST['csrf_token']);
    
    $receiver_id = (int)$_POST['receiver_id'];
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    if ($receiver_id > 0 && !empty($subject) && !empty($message)) {
        $subject = mysqli_real_escape_string($conn, $subject);
        $message = mysqli_real_escape_string($conn, $message);
        
        $sql = "INSERT INTO messages (sender_id, receiver_id, subject, message, sent_at) 
                VALUES ('$user_id', '$receiver_id', '$subject', '$message', NOW())";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success_message'] = 'Message sent successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to send message.';
        }
        header('Location: messages.php');
        exit;
    }
}

// Handle marking as read
if (isset($_GET['mark_read']) && $_GET['mark_read'] > 0) {
    $msg_id = (int)$_GET['mark_read'];
    $sql = "UPDATE messages SET is_read = 1 WHERE id = '$msg_id' AND receiver_id = '$user_id'";
    mysqli_query($conn, $sql);
    header('Location: messages.php');
    exit;
}

// Handle delete
if (isset($_GET['delete']) && $_GET['delete'] > 0) {
    $msg_id = (int)$_GET['delete'];
    $sql = "DELETE FROM messages WHERE id = '$msg_id' AND (sender_id = '$user_id' OR receiver_id = '$user_id')";
    mysqli_query($conn, $sql);
    header('Location: messages.php');
    exit;
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'inbox';

// Get messages
$messages = [];
if ($filter === 'inbox') {
    $sql = "SELECT m.*, u.first_name, u.last_name 
            FROM messages m
            INNER JOIN users u ON m.sender_id = u.id
            WHERE m.receiver_id = '$user_id' AND m.deleted_by_receiver = 0
            ORDER BY m.sent_at DESC";
} elseif ($filter === 'sent') {
    $sql = "SELECT m.*, u.first_name, u.last_name 
            FROM messages m
            INNER JOIN users u ON m.receiver_id = u.id
            WHERE m.sender_id = '$user_id' AND m.deleted_by_sender = 0
            ORDER BY m.sent_at DESC";
} else {
    $sql = "SELECT m.*, 
            sender.first_name as sender_first, sender.last_name as sender_last,
            receiver.first_name as receiver_first, receiver.last_name as receiver_last
            FROM messages m
            INNER JOIN users sender ON m.sender_id = sender.id
            INNER JOIN users receiver ON m.receiver_id = receiver.id
            WHERE m.sender_id = '$user_id' OR m.receiver_id = '$user_id'
            ORDER BY m.sent_at DESC";
}

$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

// Get users for dropdown (for messaging)
$users = [];
$sql = "SELECT id, first_name, last_name, user_type FROM users WHERE id != '$user_id' ORDER BY first_name, last_name";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

$page_title = 'Messages';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-chat-dots me-2"></i>Messages</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeModal">
                    <i class="bi bi-pencil-square me-1"></i>Compose
                </button>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="content-card">
        <!-- Filter Tabs -->
        <ul class="nav nav-tabs border-bottom-0 mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="?filter=inbox" class="nav-link <?php echo $filter === 'inbox' ? 'active' : ''; ?>">
                    <i class="bi bi-inbox me-1"></i>Inbox
                    <?php
                    $unread = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM messages WHERE receiver_id = '$user_id' AND is_read = 0"))['count'];
                    if ($unread > 0): ?>
                        <span class="badge bg-danger"><?php echo $unread; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="?filter=sent" class="nav-link <?php echo $filter === 'sent' ? 'active' : ''; ?>">
                    <i class="bi bi-send me-1"></i>Sent
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="?filter=all" class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    <i class="bi bi-folder me-1"></i>All
                </a>
            </li>
        </ul>

        <!-- Messages List -->
        <?php if (empty($messages)): ?>
            <p class="text-muted text-center py-5">No messages found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th><?php echo $filter === 'sent' ? 'To' : 'From'; ?></th>
                            <th>Subject</th>
                            <th>Preview</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                            <tr class="<?php echo !$msg['is_read'] && $filter === 'inbox' ? 'table-primary' : ''; ?>">
                                <td>
                                    <?php if ($filter === 'inbox'): ?>
                                        <?php if (!$msg['is_read']): ?>
                                            <span class="badge bg-primary">New</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Read</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($filter === 'sent'): ?>
                                        <?php echo htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($msg['subject']); ?></strong></td>
                                <td class="text-muted small"><?php echo htmlspecialchars(substr($msg['message'], 0, 60)); ?>...</td>
                                <td><?php echo formatDate($msg['sent_at'], 'M d, h:i A'); ?></td>
                                <td>
                                    <?php if ($filter === 'inbox' && !$msg['is_read']): ?>
                                        <a href="?mark_read=<?php echo $msg['id']; ?>" class="btn btn-sm btn-outline-success" title="Mark as read">
                                            <i class="bi bi-check-lg"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="#" class="btn btn-sm btn-outline-info" title="View" 
                                       data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $msg['id']; ?>">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="?delete=<?php echo $msg['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Delete this message?')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- View Modal -->
                            <div class="modal fade" id="viewModal<?php echo $msg['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?php echo htmlspecialchars($msg['subject']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>From:</strong> <?php 
                                                if ($filter === 'sent') {
                                                    echo htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']);
                                                } else {
                                                    echo htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']);
                                                }
                                            ?></p>
                                            <p><strong>To:</strong> <?php 
                                                if ($filter === 'sent' && isset($msg['receiver_first'])) {
                                                    echo htmlspecialchars($msg['receiver_first'] . ' ' . $msg['receiver_last']);
                                                } elseif (isset($msg['sender_first'])) {
                                                    echo htmlspecialchars($msg['sender_first'] . ' ' . $msg['sender_last']);
                                                }
                                            ?></p>
                                            <p><strong>Date:</strong> <?php echo formatDate($msg['sent_at'], 'F d, Y h:i A'); ?></p>
                                            <hr>
                                            <p class="mt-3"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                        </div>
                                        <div class="modal-footer">
                                            <?php if ($filter === 'inbox' && !$msg['is_read']): ?>
                                                <a href="?mark_read=<?php echo $msg['id']; ?>" class="btn btn-success">
                                                    Mark as Read
                                                </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Compose Modal -->
<div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Compose Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <?php generateCSRFToken(); ?>
                    <div class="mb-3">
                        <label class="form-label">To:</label>
                        <select name="receiver_id" class="form-select" required>
                            <option value="">Select recipient...</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo $u['id']; ?>">
                                    <?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?>
                                    (<?php echo ucfirst($u['user_type']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject:</label>
                        <input type="text" name="subject" class="form-control" required maxlength="200">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message:</label>
                        <textarea name="message" class="form-control" rows="8" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="send_message" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
