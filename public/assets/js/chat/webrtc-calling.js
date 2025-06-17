/**
 * WebRTC Calling System
 * Handles audio and video calls with modern features
 */

class WebRTCCalling {
    constructor(options = {}) {
        this.options = {
            websocketUrl: options.websocketUrl || 'ws://localhost:8080',
            baseUrl: options.baseUrl || '',
            iceServers: options.iceServers || [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun2.l.google.com:19302' }
            ],
            ...options
        };

        this.localStream = null;
        this.remoteStream = null;
        this.peerConnection = null;
        this.websocket = null;
        this.currentCall = null;
        this.isCallInitiator = false;
        this.callModal = null;
        this.callTimer = null;
        this.callStartTime = null;

        this.init();
    }

    /**
     * Initialize WebRTC calling system
     */
    init() {
        this.initializeElements();
        this.bindEvents();
        this.connectWebSocket();
    }

    /**
     * Initialize DOM elements
     */
    initializeElements() {
        this.elements = {
            videoCallBtn: document.querySelector('.video-call-btn'),
            audioCallBtn: document.querySelector('.audio-call-btn'),
            callModal: document.querySelector('#callModal'),
            incomingCallModal: null, // Will be created dynamically
            localVideo: document.querySelector('#localVideo'),
            remoteVideo: document.querySelector('#remoteVideo'),
            callControls: document.querySelector('.call-controls'),
            muteBtn: document.querySelector('.mute-btn'),
            videoToggleBtn: document.querySelector('.video-toggle-btn'),
            endCallBtn: document.querySelector('.end-call-btn'),
            callStatus: document.querySelector('.call-status'),
            callTimer: document.querySelector('.call-timer')
        };
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Call initiation buttons
        if (this.elements.videoCallBtn) {
            this.elements.videoCallBtn.addEventListener('click', () => {
                this.initiateCall('video');
            });
        }

        if (this.elements.audioCallBtn) {
            this.elements.audioCallBtn.addEventListener('click', () => {
                this.initiateCall('audio');
            });
        }

        // Call control buttons
        if (this.elements.muteBtn) {
            this.elements.muteBtn.addEventListener('click', () => {
                this.toggleMute();
            });
        }

        if (this.elements.videoToggleBtn) {
            this.elements.videoToggleBtn.addEventListener('click', () => {
                this.toggleVideo();
            });
        }

        if (this.elements.endCallBtn) {
            this.elements.endCallBtn.addEventListener('click', () => {
                this.endCall();
            });
        }
    }

    /**
     * Connect to WebSocket for signaling
     */
    connectWebSocket() {
        if (this.websocket && this.websocket.readyState === WebSocket.OPEN) {
            return;
        }

        try {
            this.websocket = new WebSocket(this.options.websocketUrl);

            this.websocket.onopen = () => {
                console.log('WebRTC: WebSocket connected');
            };

            this.websocket.onmessage = (event) => {
                this.handleSignalingMessage(event);
            };

            this.websocket.onclose = () => {
                console.log('WebRTC: WebSocket disconnected');
                // Attempt to reconnect after 3 seconds
                setTimeout(() => this.connectWebSocket(), 3000);
            };

            this.websocket.onerror = (error) => {
                console.error('WebRTC: WebSocket error:', error);
            };

        } catch (error) {
            console.error('WebRTC: Failed to connect to WebSocket:', error);
        }
    }

    /**
     * Initiate a call
     */
    async initiateCall(callType = 'audio') {
        try {
            // Check permissions first
            if (!await this.checkCallPermissions(callType)) {
                return;
            }

            // Get user media
            this.localStream = await this.getUserMedia(callType);
            
            // Create call session
            this.currentCall = {
                id: this.generateCallId(),
                type: callType,
                initiator: true,
                receiverId: window.enhancedChat?.options?.otherUserId,
                status: 'initiating'
            };

            this.isCallInitiator = true;

            // Show call interface
            this.showCallInterface('outgoing');

            // Send call invitation
            this.sendSignalingMessage({
                type: 'call_invitation',
                callId: this.currentCall.id,
                callType: callType,
                fromUserId: window.enhancedChat?.options?.currentUserId,
                toUserId: this.currentCall.receiverId
            });

            // Set timeout for call answer (30 seconds)
            setTimeout(() => {
                if (this.currentCall && this.currentCall.status === 'initiating') {
                    this.endCall('no_answer');
                }
            }, 30000);

        } catch (error) {
            console.error('Error initiating call:', error);
            this.showCallError('Failed to initiate call. Please check your camera/microphone permissions.');
        }
    }

    /**
     * Handle incoming call
     */
    async handleIncomingCall(callData) {
        // Check if we're already in a call
        if (this.currentCall) {
            this.sendSignalingMessage({
                type: 'call_rejected',
                callId: callData.callId,
                reason: 'busy'
            });
            return;
        }

        this.currentCall = {
            id: callData.callId,
            type: callData.callType,
            initiator: false,
            callerId: callData.fromUserId,
            status: 'incoming'
        };

        // Show incoming call interface
        this.showIncomingCallInterface(callData);
    }

    /**
     * Accept incoming call
     */
    async acceptCall() {
        try {
            if (!this.currentCall) return;

            // Get user media
            this.localStream = await this.getUserMedia(this.currentCall.type);

            // Update call status
            this.currentCall.status = 'accepted';

            // Send acceptance
            this.sendSignalingMessage({
                type: 'call_accepted',
                callId: this.currentCall.id
            });

            // Hide incoming call interface and show call interface
            this.hideIncomingCallInterface();
            this.showCallInterface('incoming');

            // Create peer connection
            await this.createPeerConnection();

        } catch (error) {
            console.error('Error accepting call:', error);
            this.rejectCall('error');
        }
    }

    /**
     * Reject incoming call
     */
    rejectCall(reason = 'declined') {
        if (!this.currentCall) return;

        this.sendSignalingMessage({
            type: 'call_rejected',
            callId: this.currentCall.id,
            reason: reason
        });

        this.hideIncomingCallInterface();
        this.currentCall = null;
    }

    /**
     * Create peer connection
     */
    async createPeerConnection() {
        try {
            this.peerConnection = new RTCPeerConnection({
                iceServers: this.options.iceServers
            });

            // Add local stream
            if (this.localStream) {
                this.localStream.getTracks().forEach(track => {
                    this.peerConnection.addTrack(track, this.localStream);
                });
            }

            // Handle remote stream
            this.peerConnection.ontrack = (event) => {
                this.remoteStream = event.streams[0];
                if (this.elements.remoteVideo) {
                    this.elements.remoteVideo.srcObject = this.remoteStream;
                }
            };

            // Handle ICE candidates
            this.peerConnection.onicecandidate = (event) => {
                if (event.candidate) {
                    this.sendSignalingMessage({
                        type: 'ice_candidate',
                        callId: this.currentCall.id,
                        candidate: event.candidate
                    });
                }
            };

            // Handle connection state changes
            this.peerConnection.onconnectionstatechange = () => {
                const state = this.peerConnection.connectionState;
                console.log('WebRTC: Connection state:', state);

                if (state === 'connected') {
                    this.onCallConnected();
                } else if (state === 'disconnected' || state === 'failed') {
                    this.endCall('connection_failed');
                }
            };

            // Create offer if we're the initiator
            if (this.isCallInitiator) {
                const offer = await this.peerConnection.createOffer();
                await this.peerConnection.setLocalDescription(offer);

                this.sendSignalingMessage({
                    type: 'offer',
                    callId: this.currentCall.id,
                    sdp: offer
                });
            }

        } catch (error) {
            console.error('Error creating peer connection:', error);
            this.endCall('connection_error');
        }
    }

    /**
     * Handle signaling messages
     */
    async handleSignalingMessage(event) {
        try {
            const message = JSON.parse(event.data);

            switch (message.type) {
                case 'call_invitation':
                    await this.handleIncomingCall(message);
                    break;

                case 'call_accepted':
                    await this.handleCallAccepted(message);
                    break;

                case 'call_rejected':
                    this.handleCallRejected(message);
                    break;

                case 'offer':
                    await this.handleOffer(message);
                    break;

                case 'answer':
                    await this.handleAnswer(message);
                    break;

                case 'ice_candidate':
                    await this.handleIceCandidate(message);
                    break;

                case 'call_ended':
                    this.handleCallEnded(message);
                    break;

                default:
                    console.log('WebRTC: Unknown signaling message:', message.type);
            }
        } catch (error) {
            console.error('Error handling signaling message:', error);
        }
    }

    /**
     * Handle call accepted
     */
    async handleCallAccepted(message) {
        if (!this.currentCall || this.currentCall.id !== message.callId) return;

        this.currentCall.status = 'accepted';
        await this.createPeerConnection();
    }

    /**
     * Handle call rejected
     */
    handleCallRejected(message) {
        if (!this.currentCall || this.currentCall.id !== message.callId) return;

        let reason = 'Call declined';
        switch (message.reason) {
            case 'busy':
                reason = 'User is busy';
                break;
            case 'no_answer':
                reason = 'No answer';
                break;
            case 'error':
                reason = 'Call failed';
                break;
        }

        this.showCallError(reason);
        this.endCall();
    }

    /**
     * Handle WebRTC offer
     */
    async handleOffer(message) {
        if (!this.currentCall || this.currentCall.id !== message.callId) return;

        if (!this.peerConnection) {
            await this.createPeerConnection();
        }

        await this.peerConnection.setRemoteDescription(message.sdp);

        const answer = await this.peerConnection.createAnswer();
        await this.peerConnection.setLocalDescription(answer);

        this.sendSignalingMessage({
            type: 'answer',
            callId: this.currentCall.id,
            sdp: answer
        });
    }

    /**
     * Handle WebRTC answer
     */
    async handleAnswer(message) {
        if (!this.currentCall || this.currentCall.id !== message.callId) return;

        await this.peerConnection.setRemoteDescription(message.sdp);
    }

    /**
     * Handle ICE candidate
     */
    async handleIceCandidate(message) {
        if (!this.currentCall || this.currentCall.id !== message.callId) return;

        if (this.peerConnection) {
            await this.peerConnection.addIceCandidate(message.candidate);
        }
    }

    /**
     * Handle call ended
     */
    handleCallEnded(message) {
        this.endCall();
    }

    /**
     * Get user media
     */
    async getUserMedia(callType = 'audio') {
        const constraints = {
            audio: true,
            video: callType === 'video'
        };

        try {
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            
            // Show local video
            if (this.elements.localVideo && callType === 'video') {
                this.elements.localVideo.srcObject = stream;
            }

            return stream;
        } catch (error) {
            console.error('Error accessing media devices:', error);
            throw new Error('Unable to access camera/microphone. Please check permissions.');
        }
    }

    /**
     * Show call interface
     */
    showCallInterface(direction = 'outgoing') {
        // Create call modal if it doesn't exist
        if (!this.elements.callModal) {
            this.createCallModal();
        }

        const modal = new bootstrap.Modal(this.elements.callModal);
        modal.show();

        // Update call status
        this.updateCallStatus(direction === 'outgoing' ? 'Calling...' : 'Connected');

        // Start call timer if connected
        if (direction === 'incoming') {
            this.startCallTimer();
        }
    }

    /**
     * Show incoming call interface
     */
    showIncomingCallInterface(callData) {
        // Create incoming call modal
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'incomingCallModal';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center p-4">
                        <div class="incoming-call-avatar mb-3">
                            <i class="bi bi-person-circle fs-1 text-primary"></i>
                        </div>
                        <h5 class="mb-2">Incoming ${callData.callType} call</h5>
                        <p class="text-muted mb-4">From: User ${callData.fromUserId}</p>
                        
                        <div class="call-actions d-flex justify-content-center gap-3">
                            <button class="btn btn-success btn-lg rounded-circle accept-call-btn" style="width: 60px; height: 60px;">
                                <i class="bi bi-telephone-fill"></i>
                            </button>
                            <button class="btn btn-danger btn-lg rounded-circle reject-call-btn" style="width: 60px; height: 60px;">
                                <i class="bi bi-telephone-x-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        this.elements.incomingCallModal = modal;

        // Bind events
        modal.querySelector('.accept-call-btn').addEventListener('click', () => {
            this.acceptCall();
        });

        modal.querySelector('.reject-call-btn').addEventListener('click', () => {
            this.rejectCall();
        });

        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        // Play ringtone
        this.playRingtone();
    }

    /**
     * Hide incoming call interface
     */
    hideIncomingCallInterface() {
        if (this.elements.incomingCallModal) {
            const bsModal = bootstrap.Modal.getInstance(this.elements.incomingCallModal);
            if (bsModal) {
                bsModal.hide();
            }
            this.elements.incomingCallModal.remove();
            this.elements.incomingCallModal = null;
        }

        this.stopRingtone();
    }

    /**
     * Create call modal
     */
    createCallModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'callModal';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-camera-video me-2"></i>
                            ${this.currentCall?.type === 'video' ? 'Video' : 'Audio'} Call
                        </h5>
                        <div class="call-timer text-muted">00:00</div>
                    </div>
                    <div class="modal-body text-center p-0">
                        <div class="call-interface position-relative">
                            <video id="remoteVideo" autoplay style="width: 100%; height: 400px; background: #000;"></video>
                            <video id="localVideo" autoplay muted style="position: absolute; top: 20px; right: 20px; width: 150px; height: 100px; border-radius: 10px; border: 2px solid white;"></video>
                            <div class="call-status position-absolute top-50 start-50 translate-middle text-white">
                                <h4>Connecting...</h4>
                            </div>
                        </div>
                        <div class="call-controls p-4">
                            <button class="btn btn-secondary btn-lg rounded-circle me-3 mute-btn" title="Mute">
                                <i class="bi bi-mic-fill"></i>
                            </button>
                            <button class="btn btn-secondary btn-lg rounded-circle me-3 video-toggle-btn" title="Toggle Video">
                                <i class="bi bi-camera-video-fill"></i>
                            </button>
                            <button class="btn btn-danger btn-lg rounded-circle end-call-btn" title="End Call">
                                <i class="bi bi-telephone-x-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        this.elements.callModal = modal;

        // Re-initialize elements
        this.initializeElements();
        this.bindEvents();
    }

    /**
     * Toggle mute
     */
    toggleMute() {
        if (!this.localStream) return;

        const audioTrack = this.localStream.getAudioTracks()[0];
        if (audioTrack) {
            audioTrack.enabled = !audioTrack.enabled;
            
            const muteBtn = this.elements.muteBtn;
            const icon = muteBtn.querySelector('i');
            
            if (audioTrack.enabled) {
                icon.className = 'bi bi-mic-fill';
                muteBtn.classList.remove('btn-warning');
                muteBtn.classList.add('btn-secondary');
            } else {
                icon.className = 'bi bi-mic-mute-fill';
                muteBtn.classList.remove('btn-secondary');
                muteBtn.classList.add('btn-warning');
            }
        }
    }

    /**
     * Toggle video
     */
    toggleVideo() {
        if (!this.localStream || this.currentCall?.type !== 'video') return;

        const videoTrack = this.localStream.getVideoTracks()[0];
        if (videoTrack) {
            videoTrack.enabled = !videoTrack.enabled;
            
            const videoBtn = this.elements.videoToggleBtn;
            const icon = videoBtn.querySelector('i');
            
            if (videoTrack.enabled) {
                icon.className = 'bi bi-camera-video-fill';
                videoBtn.classList.remove('btn-warning');
                videoBtn.classList.add('btn-secondary');
            } else {
                icon.className = 'bi bi-camera-video-off-fill';
                videoBtn.classList.remove('btn-secondary');
                videoBtn.classList.add('btn-warning');
            }
        }
    }

    /**
     * End call
     */
    endCall(reason = 'user_ended') {
        // Send end call signal
        if (this.currentCall) {
            this.sendSignalingMessage({
                type: 'call_ended',
                callId: this.currentCall.id,
                reason: reason
            });
        }

        // Clean up
        this.cleanup();

        // Hide modals
        this.hideCallInterface();
        this.hideIncomingCallInterface();

        // Stop ringtone
        this.stopRingtone();

        // Reset state
        this.currentCall = null;
        this.isCallInitiator = false;
    }

    /**
     * Clean up resources
     */
    cleanup() {
        // Stop local stream
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => track.stop());
            this.localStream = null;
        }

        // Close peer connection
        if (this.peerConnection) {
            this.peerConnection.close();
            this.peerConnection = null;
        }

        // Stop call timer
        this.stopCallTimer();
    }

    /**
     * Hide call interface
     */
    hideCallInterface() {
        if (this.elements.callModal) {
            const bsModal = bootstrap.Modal.getInstance(this.elements.callModal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    }

    /**
     * Call connected
     */
    onCallConnected() {
        this.updateCallStatus('Connected');
        this.startCallTimer();
    }

    /**
     * Update call status
     */
    updateCallStatus(status) {
        const statusElement = this.elements.callModal?.querySelector('.call-status h4');
        if (statusElement) {
            statusElement.textContent = status;
        }
    }

    /**
     * Start call timer
     */
    startCallTimer() {
        this.callStartTime = new Date();
        this.callTimer = setInterval(() => {
            const elapsed = new Date() - this.callStartTime;
            const minutes = Math.floor(elapsed / 60000);
            const seconds = Math.floor((elapsed % 60000) / 1000);
            
            const timerElement = this.elements.callModal?.querySelector('.call-timer');
            if (timerElement) {
                timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }
        }, 1000);
    }

    /**
     * Stop call timer
     */
    stopCallTimer() {
        if (this.callTimer) {
            clearInterval(this.callTimer);
            this.callTimer = null;
        }
        this.callStartTime = null;
    }

    /**
     * Check call permissions
     */
    async checkCallPermissions(callType) {
        try {
            // Check feature permissions via API
            const response = await fetch(`${this.options.baseUrl}/api/user-permissions`);
            const result = await response.json();

            const featureName = callType === 'video' ? 'video_calling' : 'audio_calling';
            
            if (!result.permissions[featureName]) {
                this.showUpgradeModal(callType);
                return false;
            }

            return true;
        } catch (error) {
            console.error('Error checking permissions:', error);
            return true; // Allow call if check fails
        }
    }

    /**
     * Show upgrade modal
     */
    showUpgradeModal(callType) {
        // Use existing upgrade modal or create one
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-lock-fill fs-1 text-warning mb-3"></i>
                        <h5>Premium Feature Required</h5>
                        <p>${callType === 'video' ? 'Video' : 'Audio'} calling requires a premium subscription.</p>
                        <div class="mt-4">
                            <a href="${this.options.baseUrl}/premium" class="btn btn-primary">Upgrade Now</a>
                            <button class="btn btn-secondary ms-2" data-bs-dismiss="modal">Later</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    /**
     * Show call error
     */
    showCallError(message) {
        // Simple notification for now
        const notification = document.createElement('div');
        notification.className = 'alert alert-danger position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    /**
     * Send signaling message
     */
    sendSignalingMessage(message) {
        if (this.websocket && this.websocket.readyState === WebSocket.OPEN) {
            this.websocket.send(JSON.stringify(message));
        }
    }

    /**
     * Generate call ID
     */
    generateCallId() {
        return 'call_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
    }

    /**
     * Play ringtone
     */
    playRingtone() {
        // Simple audio notification
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+PzwXkpBSuBzvLZiTcIGWm98OScTgwQUarm7b1oHAg4k9n9zoUxBh6x6/TlmzcKFGS+ 9OGKQwwOUqzg9rhpHQU3k9j+zLUtBiVgfLjumaFREAsOpKHrL2pjEASKmgMAnpM/CA6HjIKFcE9gdJy0qV2EY9db/kF1JQUpd8fU6+2EORI9p+Xq5OGDOBoKdLPo4pJAB30=');
            audio.loop = true;
            audio.play();
            this.ringtoneAudio = audio;
        } catch (error) {
            console.log('Could not play ringtone:', error);
        }
    }

    /**
     * Stop ringtone
     */
    stopRingtone() {
        if (this.ringtoneAudio) {
            this.ringtoneAudio.pause();
            this.ringtoneAudio = null;
        }
    }

    /**
     * Destroy calling system
     */
    destroy() {
        this.endCall();
        
        if (this.websocket) {
            this.websocket.close();
        }
    }
}

// Initialize WebRTC calling when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (window.enhancedChat) {
        window.webrtcCalling = new WebRTCCalling({
            baseUrl: document.body.dataset.baseUrl || '',
            websocketUrl: 'ws://localhost:8080'
        });
    }
}); 