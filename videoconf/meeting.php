<?php
/**
 * Video Conference SFU Solution - Meeting Room Page
 */

require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();
$meeting_id = sanitize($_GET['id'] ?? '');

if (empty($meeting_id)) {
    redirect('dashboard.php');
}

$conn = getDBConnection();

// Get meeting info
$stmt = mysqli_prepare($conn, "SELECT m.*, u.full_name as host_name FROM meetings m 
    JOIN users u ON m.host_id = u.id WHERE m.meeting_id = ?");
mysqli_stmt_bind_param($stmt, "s", $meeting_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$meeting = mysqli_fetch_assoc($result);

if (!$meeting) {
    redirect('dashboard.php?error=Meeting not found');
}

// Check if user has access
if ($meeting['host_id'] != $user['id'] && !$meeting['is_public']) {
    // Check if user is invited or participant
    $stmt = mysqli_prepare($conn, "SELECT id FROM participants WHERE meeting_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $meeting['id'], $user['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!mysqli_fetch_assoc($result)) {
        redirect('dashboard.php?error=Access denied');
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($meeting['title']); ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Recording Indicator -->
    <div id="recordingIndicator" class="recording-indicator d-none">
        <i class="bi bi-record-circle-fill"></i>
        <span>Recording in progress</span>
    </div>

    <!-- Main Container -->
    <div class="container-fluid h-100">
        <div class="row h-100 g-0">
            <!-- Video Area -->
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="h-100 d-flex flex-column">
                    <!-- Header -->
                    <div class="p-3 bg-dark text-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo htmlspecialchars($meeting['title']); ?></h5>
                            <small class="text-muted">Meeting ID: <?php echo htmlspecialchars($meeting['meeting_id']); ?></small>
                        </div>
                        <div>
                            <span class="badge bg-primary me-2">
                                <i class="bi bi-people-fill"></i> <span id="participantCount">0</span> Participants
                            </span>
                            <span class="badge bg-success" id="connectionStatus">
                                <i class="bi bi-wifi"></i> Connected
                            </span>
                        </div>
                    </div>

                    <!-- Video Grid -->
                    <div class="flex-grow-1 overflow-auto" style="background: #0f0f0f;">
                        <div class="video-grid h-100">
                            <!-- Local Video -->
                            <div class="video-tile">
                                <video id="localVideo" autoplay muted playsinline></video>
                                <div class="participant-name">
                                    <i class="bi bi-camera-video-fill"></i>
                                    <span><?php echo htmlspecialchars($user['full_name']); ?> (You)</span>
                                </div>
                                <div class="participant-controls">
                                    <button class="btn btn-sm btn-link text-white" title="Pin">
                                        <i class="bi bi-pin-angle"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Remote Videos will be added dynamically -->
                            <div class="video-tile d-none" id="remoteVideoTile">
                                <video id="remoteVideo" autoplay playsinline></video>
                                <div class="participant-name">
                                    <i class="bi bi-person-fill"></i>
                                    <span id="remoteParticipantName">Participant</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meeting Controls -->
                    <div class="p-3 bg-dark">
                        <div class="meeting-controls">
                            <button id="micBtn" class="control-btn active" data-active-icon="bi-mic-fill" data-inactive-icon="bi-mic-mute-fill" onclick="toggleMicrophone()" title="Toggle Microphone">
                                <i class="bi bi-mic-fill"></i>
                            </button>
                            <button id="cameraBtn" class="control-btn active" data-active-icon="bi-camera-video-fill" data-inactive-icon="bi-camera-video-off-fill" onclick="toggleCamera()" title="Toggle Camera">
                                <i class="bi bi-camera-video-fill"></i>
                            </button>
                            <button id="screenShareBtn" class="control-btn" onclick="shareScreen()" title="Share Screen">
                                <i class="bi bi-display"></i>
                            </button>
                            <button id="recordBtn" class="control-btn" onclick="startRecording()" title="Start Recording">
                                <i class="bi bi-record-circle"></i>
                            </button>
                            <button id="whiteboardBtn" class="control-btn" onclick="openWhiteboard()" title="Open Whiteboard">
                                <i class="bi bi-palette-fill"></i>
                            </button>
                            <button id="chatBtn" class="control-btn" onclick="toggleChatPanel()" title="Toggle Chat">
                                <i class="bi bi-chat-dots-fill"></i>
                            </button>
                            <button id="participantsBtn" class="control-btn" onclick="toggleParticipantsPanel()" title="Participants">
                                <i class="bi bi-people-fill"></i>
                            </button>
                            <button id="leaveBtn" class="control-btn danger" onclick="leaveMeeting()" title="Leave Meeting">
                                <i class="bi bi-telephone-x-fill"></i>
                            </button>
                            <?php if ($meeting['host_id'] == $user['id']): ?>
                            <button id="endMeetingBtn" class="control-btn danger" onclick="endMeeting()" title="End Meeting">
                                <i class="bi bi-stop-circle-fill"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Panel -->
            <div id="chatPanel" class="chat-panel d-none d-lg-flex">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-chat-dots-fill"></i> Group Chat</h6>
                    <button class="btn btn-sm btn-link" onclick="toggleChatPanel()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div id="chatMessages" class="chat-messages"></div>
                <div class="chat-input">
                    <div class="input-group">
                        <input type="file" id="fileUpload" class="d-none" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar">
                        <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('fileUpload').click()">
                            <i class="bi bi-paperclip"></i>
                        </button>
                        <input type="text" id="chatInput" class="form-control" placeholder="Type a message...">
                        <button class="btn btn-primary" type="button" onclick="sendChatMessage()">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Participants Panel -->
            <div id="participantsPanel" class="participant-list d-none">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-people-fill"></i> Participants</h6>
                    <button class="btn btn-sm btn-link" onclick="toggleParticipantsPanel()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div id="participantsList" class="overflow-auto">
                    <!-- Participants will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Screen Share Preview -->
    <div id="screenSharePreview" class="screen-share-preview d-none">
        <video id="screenShareVideo" autoplay muted></video>
        <div class="p-2 bg-dark text-white d-flex justify-content-between align-items-center">
            <small>Screen Sharing</small>
            <button class="btn btn-sm btn-danger" onclick="stopScreenShare()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- Whiteboard Modal -->
    <div class="modal fade" id="whiteboardModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-palette-fill"></i> Whiteboard</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="whiteboard-container h-100">
                        <div class="whiteboard-toolbar">
                            <button class="tool-btn active" data-tool="pen" title="Pen">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button class="tool-btn" data-tool="eraser" title="Eraser">
                                <i class="bi bi-eraser-fill"></i>
                            </button>
                            <button class="tool-btn" data-tool="line" title="Line">
                                <i class="bi bi-slash-lg"></i>
                            </button>
                            <button class="tool-btn" data-tool="rectangle" title="Rectangle">
                                <i class="bi bi-square"></i>
                            </button>
                            <button class="tool-btn" data-tool="circle" title="Circle">
                                <i class="bi bi-circle"></i>
                            </button>
                            <input type="color" class="color-picker ms-3" value="#000000" title="Color">
                            <input type="range" class="ms-3" min="1" max="20" value="3" title="Brush Size">
                            <button class="btn btn-sm btn-outline-danger ms-auto" onclick="clearWhiteboard()">
                                <i class="bi bi-trash-fill"></i> Clear
                            </button>
                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="saveWhiteboard()">
                                <i class="bi bi-save-fill"></i> Save
                            </button>
                        </div>
                        <canvas id="whiteboardCanvas" class="w-100" style="height: calc(100vh - 140px);"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <script>
        // Initialize meeting on page load
        document.addEventListener('DOMContentLoaded', function() {
            const meetingId = '<?php echo htmlspecialchars($meeting['meeting_id']); ?>';
            const userId = '<?php echo $user['id']; ?>';
            
            initMeeting(meetingId, userId);
            loadParticipants(meetingId);
            loadChatMessages(meetingId);
            
            // Update participant count periodically
            setInterval(() => {
                loadParticipants(meetingId);
            }, 10000);
        });

        // Load participants
        async function loadParticipants(meetingId) {
            try {
                const response = await fetch(`api/get_participants.php?meeting_id=${meetingId}`);
                const data = await response.json();
                
                if (data.success) {
                    updateParticipantList(data.participants);
                    document.getElementById('participantCount').textContent = data.count;
                }
            } catch (error) {
                console.error('Error loading participants:', error);
            }
        }

        // Update participant list UI
        function updateParticipantList(participants) {
            const container = document.getElementById('participantsList');
            if (!container) return;
            
            container.innerHTML = participants.map(p => `
                <div class="participant-item">
                    <div class="participant-avatar">${getInitials(p.display_name)}</div>
                    <div class="participant-info">
                        <div class="fw-bold">${escapeHtml(p.display_name)} ${p.is_host ? '<span class="badge bg-primary ms-1">Host</span>' : ''}</div>
                        <div class="participant-status">
                            ${!p.is_host ? `
                            <button class="btn btn-sm btn-link p-0" onclick="muteParticipant(${p.id})" title="Mute">
                                <i class="bi bi-mic-mute"></i>
                            </button>
                            <button class="btn btn-sm btn-link p-0" onclick="removeParticipant(${p.id})" title="Remove">
                                <i class="bi bi-person-x"></i>
                            </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Load chat messages
        async function loadChatMessages(meetingId) {
            try {
                const response = await fetch(`api/get_chat.php?meeting_id=${meetingId}`);
                const data = await response.json();
                
                if (data.success) {
                    const container = document.getElementById('chatMessages');
                    if (container) {
                        container.innerHTML = data.messages.map(msg => `
                            <div class="chat-message">
                                <div class="sender">${escapeHtml(msg.sender_name)}</div>
                                <div class="content">${escapeHtml(msg.message)}</div>
                                <div class="time">${formatTime(new Date(msg.created_at))}</div>
                            </div>
                        `).join('');
                        container.scrollTop = container.scrollHeight;
                    }
                }
            } catch (error) {
                console.error('Error loading chat:', error);
            }
        }

        // Leave meeting
        async function leaveMeeting() {
            if (confirm('Are you sure you want to leave this meeting?')) {
                try {
                    const formData = new FormData();
                    formData.append('meeting_id', '<?php echo htmlspecialchars($meeting['meeting_id']); ?>');
                    
                    const response = await fetch('api/leave_meeting.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    window.location.href = 'dashboard.php';
                } catch (error) {
                    console.error('Error leaving meeting:', error);
                    showToast('Failed to leave meeting', 'error');
                }
            }
        }

        // End meeting (host only)
        async function endMeeting() {
            if (confirm('Are you sure you want to end this meeting for everyone?')) {
                try {
                    const formData = new FormData();
                    formData.append('meeting_id', '<?php echo htmlspecialchars($meeting['meeting_id']); ?>');
                    
                    const response = await fetch('api/end_meeting.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    window.location.href = 'dashboard.php';
                } catch (error) {
                    console.error('Error ending meeting:', error);
                    showToast('Failed to end meeting', 'error');
                }
            }
        }

        // Helper functions
        function getInitials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatTime(date) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    </script>
</body>
</html>
