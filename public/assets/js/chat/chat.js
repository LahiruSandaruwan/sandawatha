class ChatManager {
    constructor() {
        this.ws = null;
        this.currentUserId = null;
        this.otherUserId = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
        
        this.initializeChat();
    }
    
    initializeChat() {
        // Get user IDs from the page
        this.currentUserId = document.body.dataset.currentUserId;
        this.otherUserId = document.body.dataset.otherUserId;
        
        if (!this.currentUserId || !this.otherUserId) {
            console.error('User IDs not found');
            return;
        }
        
        this.connectWebSocket();
        this.setupEventListeners();
    }
    
    connectWebSocket() {
        try {
            this.ws = new WebSocket('ws://localhost:8080');
            
            this.ws.onopen = () => {
                console.log('WebSocket connected for chat');
                this.reconnectAttempts = 0;
                
                // Send connection message with user info
                this.sendWebSocketMessage({
                    type: 'connect',
                    user_id: this.currentUserId,
                    first_name: document.body.dataset.firstName || 'User',
                    last_name: document.body.dataset.lastName || '',
                    profile_photo: document.body.dataset.profilePhoto || ''
                });
            };
            
            this.ws.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleWebSocketMessage(data);
                } catch (error) {
                    console.error('Error parsing WebSocket message:', error);
                }
            };
            
            this.ws.onclose = () => {
                console.log('WebSocket disconnected');
                this.attemptReconnect();
            };
            
            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
            };
            
        } catch (error) {
            console.error('Error connecting to WebSocket:', error);
            this.attemptReconnect();
        }
    }
    
    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Attempting to reconnect... (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
            
            setTimeout(() => {
                this.connectWebSocket();
            }, this.reconnectDelay * this.reconnectAttempts);
        } else {
            console.error('Max reconnection attempts reached');
        }
    }
    
    sendWebSocketMessage(message) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(message));
        } else {
            console.error('WebSocket not connected');
        }
    }
    
    handleWebSocketMessage(data) {
        switch (data.type) {
            case 'new_message':
                this.handleNewMessage(data);
                break;
            case 'user_status':
                this.handleUserStatus(data);
                break;
            case 'users_list':
                // Handle users list update if needed
                break;
            default:
                console.log('Unknown message type:', data.type);
        }
    }
    
    handleNewMessage(data) {
        // Only show message if it's from the user we're chatting with
        if (data.sender_id == this.otherUserId) {
            this.addMessageToChat(data, false);
            
            // Mark message as read
            this.markMessageAsRead(data.message_id);
        }
    }
    
    handleUserStatus(data) {
        // Update user status in the chat header if needed
        console.log('User status update:', data);
    }
    
    addMessageToChat(messageData, isSent = false) {
        const chatMessages = document.querySelector('.chat-messages');
        const messageTime = messageData.created_at ? 
            new Date(messageData.created_at).toLocaleString() : 'Just now';
        
        const messageHtml = `
            <div class="message mb-3 ${isSent ? 'sent' : 'received'}">
                <div class="d-flex ${isSent ? 'justify-content-end' : 'justify-content-start'}">
                    <div class="message-bubble p-3 rounded ${isSent ? 'bg-primary text-white' : 'bg-light'}" 
                         style="max-width: 70%;">
                        ${messageData.subject && messageData.subject !== 'Chat Message' ? 
                            `<div class="fw-bold mb-1">${this.escapeHtml(messageData.subject)}</div>` : ''}
                        <div>${this.escapeHtml(messageData.message).replace(/\n/g, '<br>')}</div>
                        <small class="d-block mt-2 ${isSent ? 'text-white-50' : 'text-muted'}">
                            ${messageTime}
                        </small>
                    </div>
                </div>
            </div>
        `;
        
        chatMessages.insertAdjacentHTML('beforeend', messageHtml);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Remove "no messages" placeholder if it exists
        const noMessagesDiv = chatMessages.querySelector('.text-center.py-5');
        if (noMessagesDiv) {
            noMessagesDiv.remove();
        }
    }
    
    markMessageAsRead(messageId) {
        if (!messageId) return;
        
        const formData = new FormData();
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
        formData.append('message_id', messageId);
        
        fetch(window.location.origin + '/messages/mark-as-read', {
            method: 'POST',
            body: formData
        }).catch(error => {
            console.error('Error marking message as read:', error);
        });
    }
    
    sendMessage(messageText, subject = 'Chat Message') {
        const formData = new FormData();
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
        formData.append('receiver_id', this.otherUserId);
        formData.append('subject', subject);
        formData.append('message', messageText);
        
        return fetch(window.location.origin + '/messages/send', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add message to local chat
                this.addMessageToChat({
                    message: messageText,
                    subject: subject,
                    created_at: new Date().toISOString()
                }, true);
                
                // Broadcast message via WebSocket
                this.sendWebSocketMessage({
                    type: 'chat_message',
                    receiver_id: this.otherUserId,
                    sender_id: this.currentUserId,
                    message: messageText,
                    subject: subject,
                    message_id: data.message_id,
                    created_at: new Date().toISOString()
                });
            }
            return data;
        });
    }
    
    setupEventListeners() {
        const chatForm = document.getElementById('chatForm');
        if (!chatForm) return;
        
        const messageInput = chatForm.querySelector('textarea[name="message"]');
        const submitBtn = chatForm.querySelector('button[type="submit"]');
        
        // Handle form submission
        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const messageText = messageInput.value.trim();
            if (!messageText) return;
            
            // Disable form while sending
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';
            
            this.sendMessage(messageText)
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        messageInput.style.height = 'auto';
                    } else {
                        alert('Error sending message: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error sending message. Please try again.');
                })
                .finally(() => {
                    // Re-enable form
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-send"></i> Send';
                    messageInput.focus();
                });
        });
        
        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        
        // Send message on Ctrl+Enter
        messageInput.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    disconnect() {
        if (this.ws) {
            this.ws.close();
        }
    }
}

// Auto-scroll to bottom of existing messages when page loads
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.querySelector('.chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (window.chatManager) {
        window.chatManager.disconnect();
    }
}); 