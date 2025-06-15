<?php
// Set default values for undefined variables
$other_user = $other_user ?? [];
$messages = $messages ?? [];
$current_user_id = $current_user_id ?? 0;
?>

<!-- Set data attributes for JavaScript -->
<script>
document.body.dataset.currentUserId = '<?= $current_user_id ?>';
document.body.dataset.otherUserId = '<?= $other_user['user_id'] ?>';
document.body.dataset.firstName = '<?= htmlspecialchars($_SESSION['first_name'] ?? '') ?>';
document.body.dataset.lastName = '<?= htmlspecialchars($_SESSION['last_name'] ?? '') ?>';
document.body.dataset.profilePhoto = '<?= htmlspecialchars($_SESSION['profile_photo'] ?? '') ?>';
</script>

<!-- Connected User Template -->
<template id="connected-user-template">
    <div class="list-group-item list-group-item-action d-flex align-items-center">
        <div class="online-indicator me-2" style="width: 8px; height: 8px; border-radius: 50%;"></div>
        <img class="user-avatar rounded-circle me-2" width="32" height="32" alt="User Avatar">
        <div class="flex-grow-1">
            <div class="user-name fw-bold"></div>
            <small class="last-active text-muted"></small>
        </div>
                    </div>
</template>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Online Users Sidebar -->
        <div class="col-lg-3 col-md-4">
            <div class="online-users-sidebar">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Online Users <span id="online-count" class="badge bg-success">0</span></h5>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-success dropdown-toggle w-100" type="button" 
                                id="status-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span style="color: #28a745;">●</span> Online
                        </button>
                        <ul class="dropdown-menu w-100" aria-labelledby="status-dropdown">
                            <li><a class="dropdown-item" href="#" data-status="online">
                                <span style="color: #28a745;">●</span> Online
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-status="away">
                                <span style="color: #ffc107;">●</span> Away
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-status="busy">
                                <span style="color: #dc3545;">●</span> Busy
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" data-status="offline">
                                <span style="color: #6c757d;">●</span> Offline
                            </a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="no-users-message" class="text-center py-4 text-muted">
                        <i class="bi bi-people"></i>
                        <p class="mb-0">No users online</p>
        </div>
                    <div id="connected-users" class="connected-users-list list-group list-group-flush">
                        <!-- Connected users will be inserted here -->
                    </div>
                </div>
            </div>

            <!-- Back to Messages -->
            <div class="mt-3">
                <a href="<?= BASE_URL ?>/messages" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-left"></i> Back to Messages
                                    </a>
                                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="col-lg-9 col-md-8">
            <div class="card h-100 chat-area">
                <!-- Chat Header -->
                <div class="card-header chat-header d-flex align-items-center">
                    <div class="d-flex align-items-center">
                        <?php if (!empty($other_user['profile_photo'])): ?>
                            <img src="<?= UPLOAD_URL . htmlspecialchars($other_user['profile_photo']) ?>" 
                                 alt="Profile" class="rounded-circle me-3" width="40" height="40">
                                        <?php else: ?>
                            <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                <i class="bi bi-person text-white"></i>
                            </div>
                                        <?php endif; ?>
                        <div>
                            <h5 class="mb-0"><?= htmlspecialchars($other_user['first_name'] . ' ' . ($other_user['last_name'] ?? '')) ?></h5>
                            <small class="text-muted">
                                <?= $other_user['age'] ?? '' ?> years old
                                <?php if (!empty($other_user['district'])): ?>
                                    • <?= htmlspecialchars($other_user['district']) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                    <div class="ms-auto">
                        <a href="<?= BASE_URL ?>/profile/<?= $other_user['user_id'] ?>" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-person"></i> View Profile
                        </a>
                    </div>
                </div>

                <!-- Chat Messages -->
                <div class="card-body chat-messages" style="height: 400px; overflow-y: auto;">
                    <?php if (empty($messages)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-chat-dots display-1"></i>
                            <h5 class="mt-3">Start a conversation</h5>
                            <p>Send a message to start chatting with <?= htmlspecialchars($other_user['first_name']) ?></p>
            </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="message mb-3 <?= $message['sender_id'] == $current_user_id ? 'sent' : 'received' ?>">
                                <div class="d-flex <?= $message['sender_id'] == $current_user_id ? 'justify-content-end' : 'justify-content-start' ?>">
                                    <div class="message-bubble p-3 rounded <?= $message['sender_id'] == $current_user_id ? 'bg-primary text-white' : 'bg-light' ?>" 
                                         style="max-width: 70%;">
                                        <?php if (!empty($message['subject']) && $message['subject'] !== 'Chat Message'): ?>
                                            <div class="fw-bold mb-1"><?= htmlspecialchars($message['subject']) ?></div>
        <?php endif; ?>
                                        <div><?= nl2br(htmlspecialchars($message['message'])) ?></div>
                                        <small class="d-block mt-2 <?= $message['sender_id'] == $current_user_id ? 'text-white-50' : 'text-muted' ?>">
                                            <?= date('M j, Y g:i A', strtotime($message['created_at'])) ?>
                                        </small>
    </div>
</div>
                    </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Message Input -->
                <div class="card-footer">
                    <form id="messageForm" class="d-flex">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="receiver_id" value="<?= $other_user['user_id'] ?>">
                        <input type="hidden" name="subject" value="Chat Message">
                        <div class="flex-grow-1 me-2">
                            <textarea id="messageInput" name="message" class="form-control" rows="2" 
                                      placeholder="Type your message..." required></textarea>
                        </div>
                        <div class="align-self-end">
                            <button id="sendButton" type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Send
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inline ChatManager Class -->
<script>
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
                
                // Automatically set status to online after connecting
                setTimeout(() => {
                    this.changeUserStatus('online');
                }, 500);
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
                this.handleUsersList(data);
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
    
    handleUsersList(data) {
        console.log('Users list update:', data);
        console.log('Users array:', data.users);
        if (data.users && data.users.length > 0) {
            console.log('First user data:', data.users[0]);
        }
        
        const connectedUsersContainer = document.getElementById('connected-users');
        const noUsersMessage = document.getElementById('no-users-message');
        const onlineCount = document.getElementById('online-count');
        const userTemplate = document.getElementById('connected-user-template');
        
        if (!connectedUsersContainer || !userTemplate) return;
        
        // Clear existing users
        connectedUsersContainer.innerHTML = '';
        
        const users = data.users || [];
        // Filter users: exclude current user AND only show users with 'online' status
        const onlineUsers = users.filter(user => 
            user.id != this.currentUserId && user.status === 'online'
        );
        
        // Update online count
        if (onlineCount) {
            onlineCount.textContent = onlineUsers.length;
        }
        
        if (onlineUsers.length === 0) {
            // Show no users message
            if (noUsersMessage) {
                noUsersMessage.style.display = 'block';
            }
        } else {
            // Hide no users message
            if (noUsersMessage) {
                noUsersMessage.style.display = 'none';
            }
            
            // Add each online user
            onlineUsers.forEach(user => {
                const userElement = userTemplate.content.cloneNode(true);
                
                // Set user avatar
                const avatarContainer = userElement.querySelector('.user-avatar').parentNode;
                const oldAvatar = userElement.querySelector('.user-avatar');
                
                if (user.profile_photo && user.profile_photo.trim() !== '') {
                    // User has a profile photo - use img element
                    if (oldAvatar.tagName !== 'IMG') {
                        // Replace div with img
                        const newImg = document.createElement('img');
                        newImg.className = 'user-avatar rounded-circle me-2';
                        newImg.width = 32;
                        newImg.height = 32;
                        avatarContainer.replaceChild(newImg, oldAvatar);
                    }
                    const avatar = avatarContainer.querySelector('.user-avatar');
                    avatar.src = `<?= UPLOAD_URL ?>${user.profile_photo}`;
                    avatar.alt = `${user.firstName} ${user.lastName || ''}`;
                } else {
                    // User doesn't have a profile photo - use div element
                    if (oldAvatar.tagName !== 'DIV') {
                        // Replace img with div
                        const newDiv = document.createElement('div');
                        newDiv.className = 'user-avatar default-avatar rounded-circle me-2';
                        avatarContainer.replaceChild(newDiv, oldAvatar);
                    }
                    const avatar = avatarContainer.querySelector('.user-avatar');
                    avatar.className = 'user-avatar default-avatar rounded-circle me-2';
                    avatar.innerHTML = '<i class="bi bi-person"></i>';
                    avatar.title = `${user.firstName} ${user.lastName || ''}`;
                }
                
                // Set user name
                const userName = userElement.querySelector('.user-name');
                userName.textContent = `${user.firstName} ${user.lastName || ''}`.trim();
                
                // Set online indicator based on actual status
                const onlineIndicator = userElement.querySelector('.online-indicator');
                const lastActive = userElement.querySelector('.last-active');
                
                if (user.status === 'online') {
                    onlineIndicator.style.backgroundColor = '#28a745'; // Green for online
                    lastActive.textContent = 'Online';
                } else if (user.status === 'away') {
                    onlineIndicator.style.backgroundColor = '#ffc107'; // Yellow for away
                    lastActive.textContent = 'Away';
                } else if (user.status === 'busy') {
                    onlineIndicator.style.backgroundColor = '#dc3545'; // Red for busy
                    lastActive.textContent = 'Busy';
                } else {
                    onlineIndicator.style.backgroundColor = '#6c757d'; // Gray for offline
                    lastActive.textContent = 'Offline';
                }
                
                // Make user clickable to start chat
                const userItem = userElement.querySelector('.list-group-item');
                userItem.style.cursor = 'pointer';
                userItem.addEventListener('click', () => {
                    // Navigate to chat with this user
                    window.location.href = `<?= BASE_URL ?>/messages/chat/${user.id}`;
                });
                
                connectedUsersContainer.appendChild(userElement);
            });
        }
        
        // Also update the chat header status if we're chatting with someone
        this.updateChatHeaderStatus(users);
    }
    
    updateChatHeaderStatus(users) {
        // Find the user we're currently chatting with
        const currentChatUser = users.find(user => user.id == this.otherUserId);
        
        if (currentChatUser) {
            // Update the chat header to show the user's current status
            const chatHeader = document.querySelector('.chat-header');
            if (chatHeader) {
                let statusText = '';
                let statusColor = '';
                
                switch (currentChatUser.status) {
                    case 'online':
                        statusText = 'Online';
                        statusColor = '#28a745';
                        break;
                    case 'away':
                        statusText = 'Away';
                        statusColor = '#ffc107';
                        break;
                    case 'busy':
                        statusText = 'Busy';
                        statusColor = '#dc3545';
                        break;
                    default:
                        statusText = 'Offline';
                        statusColor = '#6c757d';
                }
                
                // Update or create status indicator in chat header
                let statusIndicator = chatHeader.querySelector('.chat-user-status');
                if (!statusIndicator) {
                    statusIndicator = document.createElement('small');
                    statusIndicator.className = 'chat-user-status text-muted d-block';
                    const userInfo = chatHeader.querySelector('h5');
                    if (userInfo) {
                        userInfo.parentNode.insertBefore(statusIndicator, userInfo.nextSibling);
                    }
                }
                
                statusIndicator.innerHTML = `<span style="color: ${statusColor};">●</span> ${statusText}`;
            }
        }
    }
    
    changeUserStatus(newStatus) {
        // Send status change to WebSocket server
        this.sendWebSocketMessage({
            type: 'status_change',
            status: newStatus
        });
        
        // Update the status dropdown button text
        const statusButton = document.getElementById('status-dropdown');
        if (statusButton) {
            let statusText = '';
            let statusColor = '';
            
            switch (newStatus) {
                case 'online':
                    statusText = 'Online';
                    statusColor = '#28a745';
                    break;
                case 'away':
                    statusText = 'Away';
                    statusColor = '#ffc107';
                    break;
                case 'busy':
                    statusText = 'Busy';
                    statusColor = '#dc3545';
                    break;
                default:
                    statusText = 'Offline';
                    statusColor = '#6c757d';
            }
            
            statusButton.innerHTML = `<span style="color: ${statusColor};">●</span> ${statusText}`;
        }
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
        const messageForm = document.getElementById('messageForm');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        
        if (messageForm) {
            messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const messageText = messageInput.value.trim();
                if (!messageText) return;
                
                // Disable form while sending
                sendButton.disabled = true;
                sendButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';
                
                this.sendMessage(messageText)
                    .then(data => {
                        if (data.success) {
                            messageInput.value = '';
                        } else {
                            alert('Error sending message: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error sending message:', error);
                        alert('Error sending message. Please try again.');
                    })
                    .finally(() => {
                        sendButton.disabled = false;
                        sendButton.innerHTML = '<i class="bi bi-send"></i> Send';
                    });
            });
        }
        
        // Auto-resize textarea
        if (messageInput) {
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
            
            // Send on Ctrl+Enter
            messageInput.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    messageForm.dispatchEvent(new Event('submit'));
                }
            });
        }
        
        // Status dropdown event listeners
        const statusDropdownItems = document.querySelectorAll('[data-status]');
        statusDropdownItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const newStatus = e.target.getAttribute('data-status');
                this.changeUserStatus(newStatus);
            });
        });
        
        // Online status toggle (legacy support)
        const onlineStatusToggle = document.getElementById('online-status');
        if (onlineStatusToggle) {
            onlineStatusToggle.addEventListener('change', (e) => {
                const isOnline = e.target.checked;
                this.changeUserStatus(isOnline ? 'online' : 'offline');
                
                // Update UI feedback
                const label = document.querySelector('label[for="online-status"]');
                if (label) {
                    label.textContent = isOnline ? 'Show as Online' : 'Show as Offline';
                }
            });
        }
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

// Initialize chat functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing chat...');
    console.log('jQuery available:', typeof $ !== 'undefined');
    
    // Auto-scroll to bottom of existing messages
    const chatMessages = document.querySelector('.chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Initialize ChatManager for real-time messaging
    window.chatManager = new ChatManager();
    console.log('ChatManager initialized');
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (window.chatManager) {
        window.chatManager.disconnect();
    }
});
</script>
