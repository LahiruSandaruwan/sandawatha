/**
 * Enhanced Chat Interface
 * Modern chat functionality with WebSocket, file sharing, and permission integration
 */

class EnhancedChat {
    constructor(options = {}) {
        this.options = {
            websocketUrl: options.websocketUrl || 'ws://localhost:8080',
            baseUrl: options.baseUrl || '',
            currentUserId: options.currentUserId,
            otherUserId: options.otherUserId,
            maxFileSize: options.maxFileSize || 10 * 1024 * 1024, // 10MB
            allowedFileTypes: options.allowedFileTypes || ['image/*', 'video/*', 'audio/*', '.pdf', '.doc', '.docx'],
            messageLimit: options.messageLimit || null,
            ...options
        };

        this.websocket = null;
        this.isConnected = false;
        this.messageQueue = [];
        this.typingTimer = null;
        this.isTyping = false;
        this.onlineUsers = new Map();
        this.permissions = {};
        this.currentUsage = {};

        this.init();
    }

    /**
     * Initialize the chat interface
     */
    init() {
        this.initializeElements();
        this.bindEvents();
        this.connectWebSocket();
        this.loadPermissions();
        this.autoResizeTextarea();
        this.initializeEmojiPicker();
    }

    /**
     * Initialize DOM elements
     */
    initializeElements() {
        this.elements = {
            container: document.querySelector('.enhanced-chat-container'),
            sidebar: document.querySelector('.chat-sidebar'),
            messagesContainer: document.querySelector('.chat-messages'),
            messageForm: document.querySelector('.message-form'),
            messageInput: document.querySelector('.message-textarea'),
            sendButton: document.querySelector('.send-button'),
            fileInput: document.querySelector('#file-input'),
            fileUploadArea: document.querySelector('.file-upload-area'),
            typingIndicator: document.querySelector('.typing-indicator'),
            onlineUsersContainer: document.querySelector('.online-users-list'),
            statusDropdown: document.querySelector('.status-dropdown'),
            videoCallBtn: document.querySelector('.video-call-btn'),
            audioCallBtn: document.querySelector('.audio-call-btn'),
            emojiBtn: document.querySelector('.emoji-btn'),
            attachBtn: document.querySelector('.attach-btn')
        };
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Message form submission
        if (this.elements.messageForm) {
            this.elements.messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.sendMessage();
            });
        }

        // Message input events
        if (this.elements.messageInput) {
            this.elements.messageInput.addEventListener('input', () => {
                this.handleTyping();
                this.autoResizeTextarea();
            });

            this.elements.messageInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            this.elements.messageInput.addEventListener('paste', (e) => {
                this.handlePaste(e);
            });
        }

        // File upload events
        if (this.elements.fileInput) {
            this.elements.fileInput.addEventListener('change', (e) => {
                this.handleFileSelect(e.target.files);
            });
        }

        // Drag and drop
        if (this.elements.messagesContainer) {
            this.elements.messagesContainer.addEventListener('dragover', (e) => {
                e.preventDefault();
                this.showFileUploadArea();
            });

            this.elements.messagesContainer.addEventListener('dragleave', (e) => {
                if (!e.relatedTarget || !this.elements.messagesContainer.contains(e.relatedTarget)) {
                    this.hideFileUploadArea();
                }
            });

            this.elements.messagesContainer.addEventListener('drop', (e) => {
                e.preventDefault();
                this.hideFileUploadArea();
                this.handleFileSelect(e.dataTransfer.files);
            });
        }

        // Action buttons
        if (this.elements.videoCallBtn) {
            this.elements.videoCallBtn.addEventListener('click', () => {
                this.initiateVideoCall();
            });
        }

        if (this.elements.audioCallBtn) {
            this.elements.audioCallBtn.addEventListener('click', () => {
                this.initiateAudioCall();
            });
        }

        if (this.elements.emojiBtn) {
            this.elements.emojiBtn.addEventListener('click', () => {
                this.toggleEmojiPicker();
            });
        }

        if (this.elements.attachBtn) {
            this.elements.attachBtn.addEventListener('click', () => {
                this.elements.fileInput?.click();
            });
        }

        // Status dropdown
        if (this.elements.statusDropdown) {
            this.elements.statusDropdown.addEventListener('click', (e) => {
                if (e.target.dataset.status) {
                    this.updateUserStatus(e.target.dataset.status);
                }
            });
        }

        // Sidebar toggle for mobile
        this.bindMobileEvents();
    }

    /**
     * Connect to WebSocket server
     */
    connectWebSocket() {
        try {
            this.websocket = new WebSocket(this.options.websocketUrl);

            this.websocket.onopen = () => {
                console.log('WebSocket connected');
                this.isConnected = true;
                this.onConnectionOpen();
            };

            this.websocket.onmessage = (event) => {
                this.handleWebSocketMessage(event);
            };

            this.websocket.onclose = () => {
                console.log('WebSocket disconnected');
                this.isConnected = false;
                this.onConnectionClose();
            };

            this.websocket.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.showNotification('Connection error. Please refresh the page.', 'error');
            };

        } catch (error) {
            console.error('Failed to connect to WebSocket:', error);
            this.showNotification('Failed to establish real-time connection', 'warning');
        }
    }

    /**
     * Handle WebSocket connection open
     */
    onConnectionOpen() {
        // Send user identification
        this.sendWebSocketMessage({
            type: 'user_connect',
            userId: this.options.currentUserId,
            chatWith: this.options.otherUserId
        });

        // Process queued messages
        this.processMessageQueue();
        
        this.showNotification('Connected to real-time chat', 'success');
    }

    /**
     * Handle WebSocket connection close
     */
    onConnectionClose() {
        // Attempt to reconnect after 3 seconds
        setTimeout(() => {
            if (!this.isConnected) {
                console.log('Attempting to reconnect...');
                this.connectWebSocket();
            }
        }, 3000);
    }

    /**
     * Handle incoming WebSocket messages
     */
    handleWebSocketMessage(event) {
        try {
            const message = JSON.parse(event.data);

            switch (message.type) {
                case 'new_message':
                    this.handleIncomingMessage(message.data);
                    break;
                case 'typing_start':
                    this.showTypingIndicator(message.data.userId);
                    break;
                case 'typing_stop':
                    this.hideTypingIndicator();
                    break;
                case 'user_online':
                    this.updateUserOnlineStatus(message.data.userId, true);
                    break;
                case 'user_offline':
                    this.updateUserOnlineStatus(message.data.userId, false);
                    break;
                case 'online_users':
                    this.updateOnlineUsersList(message.data.users);
                    break;
                case 'message_status':
                    this.updateMessageStatus(message.data.messageId, message.data.status);
                    break;
                case 'call_incoming':
                    this.handleIncomingCall(message.data);
                    break;
                case 'call_accepted':
                    this.handleCallAccepted(message.data);
                    break;
                case 'call_rejected':
                    this.handleCallRejected(message.data);
                    break;
                default:
                    console.log('Unknown message type:', message.type);
            }
        } catch (error) {
            console.error('Error parsing WebSocket message:', error);
        }
    }

    /**
     * Send WebSocket message
     */
    sendWebSocketMessage(message) {
        if (this.isConnected && this.websocket) {
            this.websocket.send(JSON.stringify(message));
        } else {
            // Queue message if not connected
            this.messageQueue.push(message);
        }
    }

    /**
     * Process queued messages
     */
    processMessageQueue() {
        while (this.messageQueue.length > 0) {
            const message = this.messageQueue.shift();
            this.sendWebSocketMessage(message);
        }
    }

    /**
     * Send a chat message
     */
    async sendMessage() {
        const content = this.elements.messageInput?.value.trim();
        if (!content) return;

        // Check message limit
        if (!await this.checkMessageLimit()) {
            return;
        }

        // Clear input
        this.elements.messageInput.value = '';
        this.autoResizeTextarea();

        // Disable send button temporarily
        if (this.elements.sendButton) {
            this.elements.sendButton.disabled = true;
        }

        try {
            // Send via API
            const response = await fetch(`${this.options.baseUrl}/chat/send-message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    receiver_id: this.options.otherUserId,
                    message: content,
                    type: 'text'
                })
            });

            const result = await response.json();

            if (result.success) {
                // Add message to UI
                this.addMessageToUI({
                    id: result.messageId,
                    content: content,
                    sender_id: this.options.currentUserId,
                    created_at: new Date().toISOString(),
                    type: 'text',
                    status: 'sent'
                });

                // Send via WebSocket for real-time delivery
                this.sendWebSocketMessage({
                    type: 'send_message',
                    data: {
                        messageId: result.messageId,
                        senderId: this.options.currentUserId,
                        receiverId: this.options.otherUserId,
                        content: content,
                        messageType: 'text'
                    }
                });

                // Update usage count
                this.updateUsageCount('daily_messages');

            } else {
                throw new Error(result.message || 'Failed to send message');
            }

        } catch (error) {
            console.error('Error sending message:', error);
            this.showNotification('Failed to send message. Please try again.', 'error');
            
            // Restore message content
            this.elements.messageInput.value = content;
        } finally {
            // Re-enable send button
            if (this.elements.sendButton) {
                this.elements.sendButton.disabled = false;
            }
        }
    }

    /**
     * Handle incoming message
     */
    handleIncomingMessage(messageData) {
        this.addMessageToUI({
            ...messageData,
            status: 'received'
        });

        // Mark as read if chat is visible
        if (document.visibilityState === 'visible') {
            this.markMessageAsRead(messageData.id);
        }

        // Show notification if page is not visible
        if (document.visibilityState === 'hidden') {
            this.showBrowserNotification(messageData);
        }

        // Play sound notification
        this.playNotificationSound();
    }

    /**
     * Add message to UI
     */
    addMessageToUI(messageData) {
        if (!this.elements.messagesContainer) return;

        const messageElement = this.createMessageElement(messageData);
        this.elements.messagesContainer.appendChild(messageElement);
        
        // Scroll to bottom
        this.scrollToBottom();

        // Remove empty state if present
        const emptyState = this.elements.messagesContainer.querySelector('.chat-empty-state');
        if (emptyState) {
            emptyState.remove();
        }
    }

    /**
     * Create message element
     */
    createMessageElement(messageData) {
        const isSent = messageData.sender_id == this.options.currentUserId;
        const messageTime = new Date(messageData.created_at);

        const wrapper = document.createElement('div');
        wrapper.className = `message-wrapper ${isSent ? 'sent' : 'received'}`;
        wrapper.dataset.messageId = messageData.id;

        const bubble = document.createElement('div');
        bubble.className = `message-bubble ${isSent ? 'sent' : 'received'}`;

        const content = document.createElement('div');
        content.className = 'message-content';
        
        if (messageData.type === 'text') {
            content.innerHTML = this.formatMessageContent(messageData.content);
        } else if (messageData.type === 'file') {
            content.appendChild(this.createFileMessageContent(messageData));
        }

        const timeContainer = document.createElement('div');
        timeContainer.className = 'message-time';
        
        const timeSpan = document.createElement('span');
        timeSpan.textContent = this.formatTime(messageTime);
        timeContainer.appendChild(timeSpan);

        if (isSent) {
            const statusContainer = document.createElement('div');
            statusContainer.className = 'message-status';
            statusContainer.innerHTML = this.getStatusIcon(messageData.status || 'sent');
            timeContainer.appendChild(statusContainer);
        }

        bubble.appendChild(content);
        bubble.appendChild(timeContainer);
        wrapper.appendChild(bubble);

        return wrapper;
    }

    /**
     * Format message content (add emoji support, links, etc.)
     */
    formatMessageContent(content) {
        // Convert URLs to links
        const urlRegex = /(https?:\/\/[^\s]+)/g;
        content = content.replace(urlRegex, '<a href="$1" target="_blank" rel="noopener">$1</a>');

        // Convert line breaks
        content = content.replace(/\n/g, '<br>');

        // Add emoji support (you can integrate with an emoji library here)
        content = this.convertEmojis(content);

        return content;
    }

    /**
     * Convert emoji codes to emojis
     */
    convertEmojis(text) {
        const emojiMap = {
            ':)': '😊',
            ':-)': '😊',
            ':(': '😢',
            ':-(': '😢',
            ':D': '😃',
            ':-D': '😃',
            ';)': '😉',
            ';-)': '😉',
            ':P': '😛',
            ':-P': '😛',
            ':o': '😮',
            ':-o': '😮',
            '<3': '❤️',
            '</3': '💔',
            ':thumbsup:': '👍',
            ':thumbsdown:': '👎',
            ':heart:': '❤️',
            ':fire:': '🔥',
            ':star:': '⭐',
            ':100:': '💯'
        };

        Object.keys(emojiMap).forEach(code => {
            const regex = new RegExp(this.escapeRegex(code), 'g');
            text = text.replace(regex, emojiMap[code]);
        });

        return text;
    }

    /**
     * Escape regex special characters
     */
    escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Handle typing indicator
     */
    handleTyping() {
        if (!this.isTyping) {
            this.isTyping = true;
            this.sendWebSocketMessage({
                type: 'typing_start',
                data: {
                    userId: this.options.currentUserId,
                    receiverId: this.options.otherUserId
                }
            });
        }

        // Clear existing timer
        if (this.typingTimer) {
            clearTimeout(this.typingTimer);
        }

        // Set new timer
        this.typingTimer = setTimeout(() => {
            this.isTyping = false;
            this.sendWebSocketMessage({
                type: 'typing_stop',
                data: {
                    userId: this.options.currentUserId,
                    receiverId: this.options.otherUserId
                }
            });
        }, 2000);
    }

    /**
     * Show typing indicator
     */
    showTypingIndicator(userId) {
        if (userId == this.options.otherUserId && this.elements.typingIndicator) {
            this.elements.typingIndicator.style.display = 'flex';
        }
    }

    /**
     * Hide typing indicator
     */
    hideTypingIndicator() {
        if (this.elements.typingIndicator) {
            this.elements.typingIndicator.style.display = 'none';
        }
    }

    /**
     * Auto-resize textarea
     */
    autoResizeTextarea() {
        if (!this.elements.messageInput) return;

        this.elements.messageInput.style.height = 'auto';
        const newHeight = Math.min(this.elements.messageInput.scrollHeight, 120);
        this.elements.messageInput.style.height = newHeight + 'px';
    }

    /**
     * Handle file selection
     */
    async handleFileSelect(files) {
        if (!files || files.length === 0) return;

        for (const file of files) {
            if (!this.validateFile(file)) continue;

            await this.uploadFile(file);
        }
    }

    /**
     * Validate file
     */
    validateFile(file) {
        // Check file size
        if (file.size > this.options.maxFileSize) {
            this.showNotification(`File "${file.name}" is too large. Maximum size is ${this.formatFileSize(this.options.maxFileSize)}.`, 'error');
            return false;
        }

        // Check file type
        const isAllowed = this.options.allowedFileTypes.some(type => {
            if (type.includes('/')) {
                return file.type.match(new RegExp(type.replace('*', '.*')));
            } else {
                return file.name.toLowerCase().endsWith(type.toLowerCase());
            }
        });

        if (!isAllowed) {
            this.showNotification(`File type "${file.type}" is not allowed.`, 'error');
            return false;
        }

        return true;
    }

    /**
     * Upload file
     */
    async uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('receiver_id', this.options.otherUserId);
        formData.append('type', 'file');

        try {
            // Show upload progress
            const progressElement = this.showUploadProgress(file.name);

            const response = await fetch(`${this.options.baseUrl}/chat/upload-file`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Add file message to UI
                this.addMessageToUI({
                    id: result.messageId,
                    content: result.fileUrl,
                    fileName: file.name,
                    fileSize: file.size,
                    fileType: file.type,
                    sender_id: this.options.currentUserId,
                    created_at: new Date().toISOString(),
                    type: 'file',
                    status: 'sent'
                });

                // Send via WebSocket
                this.sendWebSocketMessage({
                    type: 'send_message',
                    data: {
                        messageId: result.messageId,
                        senderId: this.options.currentUserId,
                        receiverId: this.options.otherUserId,
                        content: result.fileUrl,
                        fileName: file.name,
                        fileSize: file.size,
                        fileType: file.type,
                        messageType: 'file'
                    }
                });

            } else {
                throw new Error(result.message || 'Upload failed');
            }

        } catch (error) {
            console.error('File upload error:', error);
            this.showNotification(`Failed to upload "${file.name}"`, 'error');
        } finally {
            this.hideUploadProgress();
        }
    }

    /**
     * Check message limit
     */
    async checkMessageLimit() {
        if (!this.options.messageLimit) return true;

        try {
            const response = await fetch(`${this.options.baseUrl}/api/check-usage?feature=daily_messages`);
            const result = await response.json();

            if (result.limitReached) {
                this.showPremiumOverlay('daily_messages', result.currentUsage, result.limit);
                return false;
            }

            return true;
        } catch (error) {
            console.error('Error checking message limit:', error);
            return true; // Allow message if check fails
        }
    }

    /**
     * Show premium overlay
     */
    showPremiumOverlay(feature, current, limit) {
        const overlay = document.createElement('div');
        overlay.className = 'premium-overlay';
        overlay.innerHTML = `
            <div class="premium-overlay-content">
                <i class="bi bi-lock-fill"></i>
                <h4>Upgrade Required</h4>
                <p>You've reached your daily limit of ${limit} messages. Upgrade to Premium for unlimited messaging!</p>
                <a href="${this.options.baseUrl}/premium" class="upgrade-btn">Upgrade Now</a>
                <button class="btn btn-link mt-2" onclick="this.closest('.premium-overlay').remove()">Close</button>
            </div>
        `;

        this.elements.container?.appendChild(overlay);

        // Auto-remove after 10 seconds
        setTimeout(() => {
            overlay.remove();
        }, 10000);
    }

    /**
     * Load user permissions
     */
    async loadPermissions() {
        try {
            const response = await fetch(`${this.options.baseUrl}/api/user-permissions`);
            const result = await response.json();

            this.permissions = result.permissions || {};
            this.currentUsage = result.usage || {};

            // Update UI based on permissions
            this.updateUIBasedOnPermissions();

        } catch (error) {
            console.error('Error loading permissions:', error);
        }
    }

    /**
     * Update UI based on permissions
     */
    updateUIBasedOnPermissions() {
        // Disable video call if not permitted
        if (this.elements.videoCallBtn) {
            if (!this.permissions.video_calling) {
                this.elements.videoCallBtn.classList.add('disabled');
                this.elements.videoCallBtn.title = 'Video calling requires Premium subscription';
            }
        }

        // Disable audio call if not permitted
        if (this.elements.audioCallBtn) {
            if (!this.permissions.audio_calling) {
                this.elements.audioCallBtn.classList.add('disabled');
                this.elements.audioCallBtn.title = 'Audio calling requires Premium subscription';
            }
        }
    }

    /**
     * Initiate video call
     */
    async initiateVideoCall() {
        if (!this.permissions.video_calling) {
            this.showPremiumOverlay('video_calling');
            return;
        }

        try {
            // Use WebRTC calling system if available
            if (window.webrtcCalling) {
                await window.webrtcCalling.initiateCall('video');
            } else {
                // Fallback to API call
                const response = await fetch(`${this.options.baseUrl}/chat/initiate-call`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        receiver_id: this.options.otherUserId,
                        call_type: 'video'
                    })
                });

                const result = await response.json();
                if (result.success) {
                    this.showNotification('Video call initiated!', 'success');
                } else {
                    this.showNotification(result.message || 'Failed to initiate call', 'error');
                }
            }

        } catch (error) {
            console.error('Error initiating video call:', error);
            this.showNotification('Unable to initiate video call. Please try again.', 'error');
        }
    }

    /**
     * Initiate audio call
     */
    async initiateAudioCall() {
        if (!this.permissions.audio_calling) {
            this.showPremiumOverlay('audio_calling');
            return;
        }

        try {
            // Use WebRTC calling system if available
            if (window.webrtcCalling) {
                await window.webrtcCalling.initiateCall('audio');
            } else {
                // Fallback to API call
                const response = await fetch(`${this.options.baseUrl}/chat/initiate-call`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        receiver_id: this.options.otherUserId,
                        call_type: 'audio'
                    })
                });

                const result = await response.json();
                if (result.success) {
                    this.showNotification('Audio call initiated!', 'success');
                } else {
                    this.showNotification(result.message || 'Failed to initiate call', 'error');
                }
            }

        } catch (error) {
            console.error('Error initiating audio call:', error);
            this.showNotification('Unable to initiate audio call. Please try again.', 'error');
        }
    }

    /**
     * Show call interface (deprecated - now handled by WebRTC calling system)
     */
    showCallInterface(callType, direction) {
        console.log(`Call interface delegated to WebRTC system: ${callType} ${direction}`);
    }

    /**
     * Initialize emoji picker
     */
    initializeEmojiPicker() {
        // Basic emoji picker implementation
        // You can integrate with a library like emoji-picker-element
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    /**
     * Format time
     */
    formatTime(date) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    /**
     * Format file size
     */
    formatFileSize(bytes) {
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        if (bytes === 0) return '0 Bytes';
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
    }

    /**
     * Scroll to bottom
     */
    scrollToBottom() {
        if (this.elements.messagesContainer) {
            this.elements.messagesContainer.scrollTop = this.elements.messagesContainer.scrollHeight;
        }
    }

    /**
     * Mobile event bindings
     */
    bindMobileEvents() {
        // Sidebar toggle for mobile
        const toggleBtn = document.querySelector('.sidebar-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                this.elements.sidebar?.classList.toggle('show');
            });
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && 
                this.elements.sidebar?.classList.contains('show') &&
                !this.elements.sidebar.contains(e.target) &&
                !e.target.matches('.sidebar-toggle')) {
                this.elements.sidebar.classList.remove('show');
            }
        });
    }

    /**
     * Cleanup when leaving the page
     */
    destroy() {
        if (this.websocket) {
            this.websocket.close();
        }
        
        if (this.typingTimer) {
            clearTimeout(this.typingTimer);
        }
    }
}

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Get configuration from data attributes
    const currentUserId = document.body.dataset.currentUserId;
    const otherUserId = document.body.dataset.otherUserId;
    const baseUrl = document.body.dataset.baseUrl || '';

    if (currentUserId && otherUserId) {
        const chat = new EnhancedChat({
            currentUserId: currentUserId,
            otherUserId: otherUserId,
            baseUrl: baseUrl,
            websocketUrl: 'ws://localhost:8080'
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            chat.destroy();
        });

        // Make chat globally accessible
        window.enhancedChat = chat;
    }
}); 