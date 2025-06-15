<?php
// Chat scripts partial
?>
<script src="https://unpkg.com/emoji-mart/dist/browser.js"></script>
<script src="https://webrtc.github.io/adapter/adapter-latest.js"></script>

<script>
let ws;
let pc; // RTCPeerConnection
let localStream;
let remoteStream;
let currentCall = null;

// WebSocket Connection
function connectWebSocket() {
    ws = new WebSocket('wss://' + window.location.hostname + ':8080');
    
    ws.onopen = () => {
        console.log('Connected to chat server');
        // Send authentication
        ws.send(JSON.stringify({
            type: 'auth',
            user_id: <?= $_SESSION['user_id'] ?>
        }));
    };
    
    ws.onmessage = handleWebSocketMessage;
    
    ws.onclose = () => {
        console.log('Disconnected from chat server');
        // Try to reconnect after 5 seconds
        setTimeout(connectWebSocket, 5000);
    };
}

// Handle incoming WebSocket messages
function handleWebSocketMessage(event) {
    const data = JSON.parse(event.data);
    
    switch(data.type) {
        case 'message':
            appendMessage(data.message);
            break;
        case 'typing':
            updateTypingStatus(data.user_id, data.conversation_id);
            break;
        case 'call_request':
            handleIncomingCall(data);
            break;
        case 'call_answer':
            handleCallAnswer(data);
            break;
        case 'call_ice_candidate':
            handleNewICECandidate(data);
            break;
        case 'call_end':
            handleCallEnd(data);
            break;
    }
}

// Send chat message
$('#chat-form').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const content = form.find('[name="content"]').val().trim();
    const conversationId = form.find('[name="conversation_id"]').val();
    
    if (!content) return;
    
    const formData = new FormData();
    formData.append('conversation_id', conversationId);
    formData.append('content', content);
    formData.append('type', 'text');
    
    // Handle file upload
    const fileInput = $('#file-upload')[0];
    if (fileInput.files.length > 0) {
        formData.append('file', fileInput.files[0]);
        formData.append('type', fileInput.files[0].type.startsWith('image/') ? 'image' : 'file');
    }
    
    $.ajax({
        url: '/chat/sendMessage',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            const data = JSON.parse(response);
            if (data.success) {
                form.find('[name="content"]').val('');
                $('#file-upload').val('');
                ws.send(JSON.stringify({
                    type: 'message',
                    conversation_id: conversationId,
                    message_id: data.message_id
                }));
            }
        }
    });
});

// Initialize emoji picker
window.picker = new EmojiMart.Picker({
    onEmojiSelect: (emoji) => {
        const textarea = $('#chat-form [name="content"]');
        textarea.val(textarea.val() + emoji.native);
    }
});

// Handle file upload preview
$('#file-upload').on('change', function() {
    const file = this.files[0];
    if (!file) return;
    
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Show image preview
            $('.chat-input-preview').html(`
                <div class="position-relative">
                    <img src="${e.target.result}" class="img-thumbnail" style="height: 100px;">
                    <button type="button" class="btn-close position-absolute top-0 end-0"
                            onclick="removeFilePreview()"></button>
                </div>
            `).show();
        };
        reader.readAsDataURL(file);
    } else {
        // Show file name preview
        $('.chat-input-preview').html(`
            <div class="d-flex align-items-center">
                <i class="bi bi-file-earmark me-2"></i>
                <span>${file.name}</span>
                <button type="button" class="btn-close ms-2"
                        onclick="removeFilePreview()"></button>
            </div>
        `).show();
    }
});

function removeFilePreview() {
    $('.chat-input-preview').empty().hide();
    $('#file-upload').val('');
}

// Audio/Video Call Functions
async function initiateCall(type) {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({
            audio: true,
            video: type === 'video'
        });
        
        document.getElementById('local-video').srcObject = localStream;
        
        const conversationId = $('#chat-form [name="conversation_id"]').val();
        const receiverId = $('.chat-header').data('user-id');
        
        $.post('/chat/initiateCall', {
            conversation_id: conversationId,
            receiver_id: receiverId,
            call_type: type
        }, function(response) {
            const data = JSON.parse(response);
            if (data.success) {
                currentCall = {
                    id: data.call_data.call_id,
                    type: type
                };
                
                setupPeerConnection(data.call_data.ice_servers);
                
                $('#callModal').modal('show');
                updateCallUI('calling');
            }
        });
    } catch (err) {
        console.error('Error accessing media devices:', err);
        alert('Could not access camera/microphone');
    }
}

function setupPeerConnection(iceServers) {
    pc = new RTCPeerConnection({ iceServers });
    
    // Add local stream
    localStream.getTracks().forEach(track => {
        pc.addTrack(track, localStream);
    });
    
    // Handle incoming stream
    pc.ontrack = event => {
        remoteStream = event.streams[0];
        document.getElementById('remote-video').srcObject = remoteStream;
    };
    
    // Handle ICE candidates
    pc.onicecandidate = event => {
        if (event.candidate) {
            ws.send(JSON.stringify({
                type: 'call_ice_candidate',
                candidate: event.candidate,
                call_id: currentCall.id
            }));
        }
    };
}

async function handleIncomingCall(data) {
    const accept = confirm(`Incoming ${data.call_type} call from ${data.caller_name}`);
    
    if (accept) {
        try {
            localStream = await navigator.mediaDevices.getUserMedia({
                audio: true,
                video: data.call_type === 'video'
            });
            
            document.getElementById('local-video').srcObject = localStream;
            
            setupPeerConnection(data.ice_servers);
            
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            
            ws.send(JSON.stringify({
                type: 'call_answer',
                call_id: data.call_id,
                answer: answer
            }));
            
            currentCall = {
                id: data.call_id,
                type: data.call_type
            };
            
            $('#callModal').modal('show');
            updateCallUI('connected');
            
        } catch (err) {
            console.error('Error accepting call:', err);
            alert('Could not access camera/microphone');
        }
    } else {
        ws.send(JSON.stringify({
            type: 'call_rejected',
            call_id: data.call_id
        }));
    }
}

function endCall() {
    if (currentCall) {
        ws.send(JSON.stringify({
            type: 'call_end',
            call_id: currentCall.id
        }));
        
        cleanupCall();
    }
}

function cleanupCall() {
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }
    if (remoteStream) {
        remoteStream.getTracks().forEach(track => track.stop());
    }
    if (pc) {
        pc.close();
    }
    
    currentCall = null;
    $('#callModal').modal('hide');
}

function updateCallUI(state) {
    const controls = $('#call-controls');
    const status = $('#call-status');
    
    switch(state) {
        case 'calling':
            controls.addClass('d-none');
            status.removeClass('d-none');
            break;
        case 'connected':
            status.addClass('d-none');
            controls.removeClass('d-none');
            break;
    }
}

// Connect to WebSocket server when page loads
$(document).ready(function() {
    connectWebSocket();
});
</script>
