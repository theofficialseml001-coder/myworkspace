/**
 * Video Conference SFU Solution - Main JavaScript
 * WebRTC Integration for Video Conferencing
 */

// Global variables
let localStream = null;
let remoteStreams = {};
let peerConnections = {};
let dataChannels = {};
let currentMeeting = null;
let isRecording = false;
let mediaRecorder = null;
let recordedChunks = [];

// Configuration for WebRTC
const rtcConfig = {
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' }
    ]
};

// Initialize meeting
async function initMeeting(meetingId, userId) {
    try {
        currentMeeting = { id: meetingId, userId: userId };
        
        // Get user media (camera and microphone)
        await getLocalStream();
        
        // Create peer connection
        await createPeerConnection();
        
        // Setup WebSocket for signaling
        setupWebSocket();
        
        console.log('Meeting initialized successfully');
    } catch (error) {
        console.error('Error initializing meeting:', error);
        showToast('Failed to initialize meeting', 'error');
    }
}

// Get local stream (camera and microphone)
async function getLocalStream() {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 1920 },
                height: { ideal: 1080 },
                frameRate: { ideal: 30 }
            },
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true
            }
        });
        
        // Display local video
        const localVideo = document.getElementById('localVideo');
        if (localVideo) {
            localVideo.srcObject = localStream;
        }
        
        return localStream;
    } catch (error) {
        console.error('Error getting local stream:', error);
        showToast('Could not access camera/microphone', 'error');
        throw error;
    }
}

// Create RTCPeerConnection
function createPeerConnection() {
    const peerConnection = new RTCPeerConnection(rtcConfig);
    
    // Add local tracks to peer connection
    if (localStream) {
        localStream.getTracks().forEach(track => {
            peerConnection.addTrack(track, localStream);
        });
    }
    
    // Handle ICE candidates
    peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
            sendSignalingMessage({
                type: 'ice-candidate',
                candidate: event.candidate
            });
        }
    };
    
    // Handle incoming tracks
    peerConnection.ontrack = (event) => {
        const remoteVideo = document.getElementById('remoteVideo');
        if (remoteVideo && event.streams[0]) {
            remoteVideo.srcObject = event.streams[0];
        }
    };
    
    // Handle connection state changes
    peerConnection.onconnectionstatechange = () => {
        console.log('Connection state:', peerConnection.connectionState);
    };
    
    return peerConnection;
}

// Setup WebSocket for signaling
function setupWebSocket() {
    const wsUrl = `ws://${window.location.host}/ws/meeting/${currentMeeting.id}`;
    const ws = new WebSocket(wsUrl);
    
    ws.onopen = () => {
        console.log('WebSocket connected');
        showToast('Connected to meeting', 'success');
    };
    
    ws.onmessage = async (event) => {
        const message = JSON.parse(event.data);
        handleSignalingMessage(message);
    };
    
    ws.onclose = () => {
        console.log('WebSocket disconnected');
    };
    
    ws.onerror = (error) => {
        console.error('WebSocket error:', error);
    };
    
    return ws;
}

// Handle incoming signaling messages
async function handleSignalingMessage(message) {
    switch (message.type) {
        case 'offer':
            await handleOffer(message.offer);
            break;
        case 'answer':
            await handleAnswer(message.answer);
            break;
        case 'ice-candidate':
            await handleIceCandidate(message.candidate);
            break;
        case 'participant-joined':
            handleParticipantJoined(message.participant);
            break;
        case 'participant-left':
            handleParticipantLeft(message.participantId);
            break;
    }
}

// Handle incoming offer
async function handleOffer(offer) {
    const peerConnection = createPeerConnection();
    await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
    const answer = await peerConnection.createAnswer();
    await peerConnection.setLocalDescription(answer);
    
    sendSignalingMessage({
        type: 'answer',
        answer: answer
    });
}

// Handle incoming answer
async function handleAnswer(answer) {
    const peerConnection = Object.values(peerConnections)[0];
    if (peerConnection) {
        await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
    }
}

// Handle ICE candidate
async function handleIceCandidate(candidate) {
    const peerConnection = Object.values(peerConnections)[0];
    if (peerConnection && candidate) {
        await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
    }
}

// Handle participant joined
function handleParticipantJoined(participant) {
    console.log('Participant joined:', participant);
    showToast(`${participant.name} joined the meeting`, 'info');
    
    // Create offer for new participant
    createAndSendOffer();
}

// Handle participant left
function handleParticipantLeft(participantId) {
    console.log('Participant left:', participantId);
    showToast('A participant left the meeting', 'warning');
    
    // Close peer connection
    if (peerConnections[participantId]) {
        peerConnections[participantId].close();
        delete peerConnections[participantId];
    }
}

// Send signaling message
function sendSignalingMessage(message) {
    if (window.ws) {
        window.ws.send(JSON.stringify(message));
    }
}

// Create and send offer
async function createAndSendOffer() {
    const peerConnection = createPeerConnection();
    const offer = await peerConnection.createOffer();
    await peerConnection.setLocalDescription(offer);
    
    sendSignalingMessage({
        type: 'offer',
        offer: offer
    });
}

// Toggle microphone
function toggleMicrophone() {
    if (localStream) {
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack) {
            audioTrack.enabled = !audioTrack.enabled;
            updateControlButton('micBtn', audioTrack.enabled);
        }
    }
}

// Toggle camera
function toggleCamera() {
    if (localStream) {
        const videoTrack = localStream.getVideoTracks()[0];
        if (videoTrack) {
            videoTrack.enabled = !videoTrack.enabled;
            updateControlButton('cameraBtn', videoTrack.enabled);
        }
    }
}

// Update control button appearance
function updateControlButton(buttonId, isActive) {
    const button = document.getElementById(buttonId);
    if (button) {
        if (isActive) {
            button.classList.remove('danger');
            button.querySelector('i').className = button.dataset.activeIcon;
        } else {
            button.classList.add('danger');
            button.querySelector('i').className = button.dataset.inactiveIcon;
        }
    }
}

// Share screen
async function shareScreen() {
    try {
        const screenStream = await navigator.mediaDevices.getDisplayMedia({
            video: {
                width: { ideal: 1920 },
                height: { ideal: 1080 },
                frameRate: { ideal: 30 }
            },
            audio: true
        });
        
        // Replace video track in peer connection
        const sender = Object.values(peerConnections)[0]
            ?.getSenders()
            ?.find(s => s.track?.kind === 'video');
        
        if (sender) {
            sender.replaceTrack(screenStream.getVideoTracks()[0]);
        }
        
        // Display screen share preview
        const screenVideo = document.getElementById('screenShareVideo');
        if (screenVideo) {
            screenVideo.srcObject = screenStream;
            document.getElementById('screenSharePreview').classList.remove('d-none');
        }
        
        // Handle screen share stop
        screenStream.getVideoTracks()[0].onended = () => {
            stopScreenShare();
        };
        
        showToast('Screen sharing started', 'success');
    } catch (error) {
        console.error('Error sharing screen:', error);
        showToast('Failed to share screen', 'error');
    }
}

// Stop screen share
async function stopScreenShare() {
    try {
        // Get back camera stream
        const cameraStream = await navigator.mediaDevices.getUserMedia({
            video: true
        });
        
        const sender = Object.values(peerConnections)[0]
            ?.getSenders()
            ?.find(s => s.track?.kind === 'video');
        
        if (sender) {
            sender.replaceTrack(cameraStream.getVideoTracks()[0]);
        }
        
        document.getElementById('screenSharePreview')?.classList.add('d-none');
        showToast('Screen sharing stopped', 'info');
    } catch (error) {
        console.error('Error stopping screen share:', error);
    }
}

// Start recording
function startRecording() {
    if (!localStream) return;
    
    try {
        const options = { mimeType: 'video/webm;codecs=vp9,opus' };
        mediaRecorder = new MediaRecorder(localStream, options);
        
        mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                recordedChunks.push(event.data);
            }
        };
        
        mediaRecorder.onstop = saveRecording;
        
        mediaRecorder.start(1000);
        isRecording = true;
        
        // Show recording indicator
        document.getElementById('recordingIndicator')?.classList.remove('d-none');
        
        showToast('Recording started', 'warning');
    } catch (error) {
        console.error('Error starting recording:', error);
        showToast('Failed to start recording', 'error');
    }
}

// Stop recording
function stopRecording() {
    if (mediaRecorder && isRecording) {
        mediaRecorder.stop();
        isRecording = false;
        recordedChunks = [];
        
        // Hide recording indicator
        document.getElementById('recordingIndicator')?.classList.add('d-none');
        
        showToast('Recording stopped', 'info');
    }
}

// Save recording
function saveRecording() {
    if (recordedChunks.length === 0) return;
    
    const blob = new Blob(recordedChunks, { type: 'video/webm' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `meeting-recording-${Date.now()}.webm`;
    a.click();
    
    // Upload to server
    uploadRecording(blob);
}

// Upload recording to server
async function uploadRecording(blob) {
    const formData = new FormData();
    formData.append('recording', blob);
    formData.append('meeting_id', currentMeeting?.id);
    
    try {
        const response = await fetch('api/upload_recording.php', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            showToast('Recording saved successfully', 'success');
        }
    } catch (error) {
        console.error('Error uploading recording:', error);
    }
}

// Send chat message
function sendChatMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (!message || !currentMeeting) return;
    
    // Send via WebSocket
    sendSignalingMessage({
        type: 'chat-message',
        message: message,
        sender: currentMeeting.userId
    });
    
    // Display locally
    displayChatMessage(currentMeeting.userId, message, new Date());
    
    input.value = '';
}

// Display chat message
function displayChatMessage(senderId, content, timestamp) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message';
    messageDiv.innerHTML = `
        <div class="sender">${senderId}</div>
        <div class="content">${escapeHtml(content)}</div>
        <div class="time">${formatTime(timestamp)}</div>
    `;
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Open whiteboard
function openWhiteboard() {
    const whiteboardModal = new bootstrap.Modal(document.getElementById('whiteboardModal'));
    whiteboardModal.show();
    
    // Initialize whiteboard canvas
    initWhiteboard();
}

// Initialize whiteboard
function initWhiteboard() {
    const canvas = document.getElementById('whiteboardCanvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;
    
    canvas.addEventListener('mousedown', (e) => {
        isDrawing = true;
        [lastX, lastY] = [e.offsetX, e.offsetY];
    });
    
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', () => isDrawing = false);
    canvas.addEventListener('mouseout', () => isDrawing = false);
    
    function draw(e) {
        if (!isDrawing) return;
        
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
        
        [lastX, lastY] = [e.offsetX, e.offsetY];
        
        // Broadcast drawing data
        broadcastWhiteboardData({
            type: 'draw',
            from: { x: lastX, y: lastY },
            to: { x: e.offsetX, y: e.offsetY }
        });
    }
}

// Broadcast whiteboard data
function broadcastWhiteboardData(data) {
    sendSignalingMessage({
        type: 'whiteboard-data',
        data: data
    });
}

// Upload file
function uploadFile(file) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('meeting_id', currentMeeting?.id);
    
    fetch('api/upload_file.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('File uploaded successfully', 'success');
            displayFileInChat(data.file);
        } else {
            showToast('File upload failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error uploading file:', error);
        showToast('File upload failed', 'error');
    });
}

// Display file in chat
function displayFileInChat(file) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message';
    messageDiv.innerHTML = `
        <div class="file-item">
            <div class="file-icon ${getFileIconClass(file.type)}">
                <i class="bi bi-file-earmark"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold">${file.name}</div>
                <small class="text-muted">${formatFileSize(file.size)}</small>
            </div>
            <a href="${file.url}" class="btn btn-sm btn-primary" download>
                <i class="bi bi-download"></i>
            </a>
        </div>
    `;
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Get file icon class based on type
function getFileIconClass(fileType) {
    if (fileType.includes('pdf')) return 'pdf';
    if (fileType.includes('word') || fileType.includes('document')) return 'doc';
    if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'xls';
    if (fileType.includes('powerpoint') || fileType.includes('presentation')) return 'ppt';
    if (fileType.includes('image')) return 'img';
    if (fileType.includes('zip') || fileType.includes('compressed')) return 'zip';
    return '';
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Leave meeting
function leaveMeeting() {
    // Close all peer connections
    Object.values(peerConnections).forEach(pc => pc.close());
    peerConnections = {};
    
    // Stop local stream
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }
    
    // Close WebSocket
    if (window.ws) {
        window.ws.close();
    }
    
    // Redirect to dashboard
    window.location.href = 'dashboard.php';
}

// End meeting (host only)
function endMeeting() {
    if (confirm('Are you sure you want to end this meeting for all participants?')) {
        sendSignalingMessage({ type: 'meeting-ended' });
        leaveMeeting();
    }
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(date) {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `
        <i class="bi bi-${getToastIcon(type)}"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

function getToastIcon(type) {
    const icons = {
        success: 'check-circle-fill',
        error: 'exclamation-triangle-fill',
        warning: 'exclamation-circle-fill',
        info: 'info-circle-fill'
    };
    return icons[type] || 'info-circle-fill';
}

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Control buttons
    document.getElementById('micBtn')?.addEventListener('click', toggleMicrophone);
    document.getElementById('cameraBtn')?.addEventListener('click', toggleCamera);
    document.getElementById('screenShareBtn')?.addEventListener('click', shareScreen);
    document.getElementById('recordBtn')?.addEventListener('click', () => {
        if (isRecording) {
            stopRecording();
        } else {
            startRecording();
        }
    });
    document.getElementById('chatBtn')?.addEventListener('click', toggleChatPanel);
    document.getElementById('participantsBtn')?.addEventListener('click', toggleParticipantsPanel);
    document.getElementById('whiteboardBtn')?.addEventListener('click', openWhiteboard);
    document.getElementById('leaveBtn')?.addEventListener('click', leaveMeeting);
    document.getElementById('endMeetingBtn')?.addEventListener('click', endMeeting);
    
    // Chat input
    document.getElementById('chatInput')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendChatMessage();
        }
    });
    
    // File upload
    document.getElementById('fileUpload')?.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            uploadFile(e.target.files[0]);
        }
    });
});

// Panel toggles
function toggleChatPanel() {
    const panel = document.getElementById('chatPanel');
    panel?.classList.toggle('show');
}

function toggleParticipantsPanel() {
    const panel = document.getElementById('participantsPanel');
    panel?.classList.toggle('show');
}

// Export functions for global access
window.toggleMicrophone = toggleMicrophone;
window.toggleCamera = toggleCamera;
window.shareScreen = shareScreen;
window.startRecording = startRecording;
window.stopRecording = stopRecording;
window.sendChatMessage = sendChatMessage;
window.openWhiteboard = openWhiteboard;
window.leaveMeeting = leaveMeeting;
window.endMeeting = endMeeting;
