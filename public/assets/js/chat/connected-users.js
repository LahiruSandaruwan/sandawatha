class ConnectedUsers {
    constructor() {
        this.users = new Map();
        this.socket = null;
        this.template = document.getElementById('connected-user-template');
        this.container = document.getElementById('connected-users');
        this.noUsersMessage = document.getElementById('no-users-message');
        this.onlineCountBadge = document.getElementById('online-count');
        this.onlineStatusToggle = document.getElementById('online-status');
        
        // Check if all required elements exist
        if (!this.template) console.error('connected-user-template not found');
        if (!this.container) console.error('connected-users container not found');
        if (!this.noUsersMessage) console.error('no-users-message not found');
        if (!this.onlineCountBadge) console.error('online-count badge not found');
        if (!this.onlineStatusToggle) console.error('online-status toggle not found');
        
        // Get current user info from the page
        this.currentUser = {
            id: parseInt(document.body.dataset.userId || document.body.getAttribute('data-user-id')),
            firstName: document.body.dataset.userFirstName || document.body.getAttribute('data-user-first-name'),
            lastName: document.body.dataset.userLastName || document.body.getAttribute('data-user-last-name')
        };
        
        console.log('Current user data:', this.currentUser);
        
        // Get base URL from meta tag
        const baseUrlMeta = document.querySelector('meta[name="base-url"]');
        if (!baseUrlMeta) {
            console.error('base-url meta tag not found');
            this.baseUrl = '/sandawatha';
        } else {
            this.baseUrl = baseUrlMeta.content;
        }
        this.uploadUrl = this.baseUrl + '/uploads/';
        
        console.log('Base URL:', this.baseUrl);
        
        this.init();
    }

    init() {
        this.initializeWebSocket();
        this.bindEvents();
    }

    initializeWebSocket() {
        // Connect to the WebSocket server
        this.socket = new WebSocket(`ws://${window.location.hostname}:8080`);
        
        this.socket.onopen = () => {
            console.log('Connected to chat server');
            // Send user information when connecting
            this.socket.send(JSON.stringify({
                type: 'user_connect',
                user_id: this.currentUser.id,
                first_name: this.currentUser.firstName,
                last_name: this.currentUser.lastName
            }));
            
            // Automatically set status to online after connecting
            setTimeout(() => {
                this.changeUserStatus('online');
            }, 500);
        };
        
        this.socket.onmessage = (event) => {
            const data = JSON.parse(event.data);
            console.log('WebSocket message received:', data);
            this.handleWebSocketMessage(data);
        };
        
        this.socket.onclose = () => {
            console.log('Disconnected from chat server');
            // Try to reconnect after 5 seconds
            setTimeout(() => this.initializeWebSocket(), 5000);
        };

        this.socket.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
    }

    bindEvents() {
        // Handle status dropdown
        const statusDropdownItems = document.querySelectorAll('[data-status]');
        statusDropdownItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const newStatus = e.target.getAttribute('data-status');
                this.changeUserStatus(newStatus);
            });
        });

        // Handle online status toggle (legacy support)
        if (this.onlineStatusToggle) {
            this.onlineStatusToggle.addEventListener('change', (e) => {
                this.setOnlineStatus(e.target.checked);
            });
        }

        // Handle clicking on a user
        this.container.addEventListener('click', (e) => {
            console.log('Click event triggered on:', e.target);
            const userItem = e.target.closest('.list-group-item');
            console.log('Found user item:', userItem);
            if (userItem) {
                const userId = userItem.dataset.userId;
                console.log('User ID from dataset:', userId);
                this.startConversation(userId);
            }
        });
    }

    handleWebSocketMessage(data) {
        switch (data.type) {
            case 'users_list':
                this.updateUsersList(data.users);
                break;
            case 'user_connected':
                this.addUser(data.user);
                break;
            case 'user_disconnected':
                this.removeUser(data.userId);
                break;
            case 'status_changed':
                this.updateUserStatus(data.userId, data.status);
                break;
        }
    }

    updateUsersList(users) {
        console.log('Updating users list:', users);
        console.log('Current user ID:', this.currentUser.id);
        this.users.clear();
        this.container.innerHTML = '';
        
        // Filter out current user from the list
        const otherUsers = users.filter(user => parseInt(user.id) !== this.currentUser.id);
        console.log('Other users after filtering:', otherUsers);
        otherUsers.forEach(user => this.addUser(user));
        this.updateUsersCount();
        this.toggleNoUsersMessage();
    }

    addUser(user) {
        console.log('Adding user:', user);
        // Don't add current user to the list
        if (parseInt(user.id) === this.currentUser.id) {
            console.log('Skipping current user');
            return;
        }

        if (this.users.has(String(user.id))) {
            console.log('User already exists, updating status');
            this.updateUserStatus(String(user.id), user.status);
            return;
        }

        const userElement = this.createUserElement(user);
        console.log('Created user element:', userElement);
        // Store user with string ID to match dataset
        this.users.set(String(user.id), {
            data: user,
            element: userElement
        });

        this.container.appendChild(userElement);
        this.updateUsersCount();
        this.toggleNoUsersMessage();
    }

    removeUser(userId) {
        const user = this.users.get(userId);
        if (user) {
            user.element.remove();
            this.users.delete(userId);
            this.updateUsersCount();
            this.toggleNoUsersMessage();
        }
    }

    createUserElement(user) {
        const clone = this.template.content.cloneNode(true);
        const element = clone.querySelector('.list-group-item');
        
        element.dataset.userId = String(user.id);
        
        // Handle profile photo with proper fallback
        const avatarImg = element.querySelector('.user-avatar');
        if (user.profile_photo) {
            avatarImg.src = this.uploadUrl + user.profile_photo;
        } else {
            avatarImg.src = this.baseUrl + '/assets/images/default-avatar.svg';
        }
        avatarImg.onerror = () => {
            avatarImg.src = this.baseUrl + '/assets/images/default-avatar.svg';
        };
        
        element.querySelector('.user-name').textContent = `${user.firstName || user.first_name || ''} ${user.lastName || user.last_name || ''}`;
        
        // Show status-based text instead of lastActive for WebSocket users
        const lastActiveElement = element.querySelector('.last-active');
        if (user.status === 'online') {
            lastActiveElement.textContent = 'Online';
        } else if (user.status === 'away') {
            lastActiveElement.textContent = 'Away';
        } else if (user.status === 'busy') {
            lastActiveElement.textContent = 'Busy';
        } else {
            lastActiveElement.textContent = this.getLastActiveText(user.lastActive);
        }
        
        const indicator = element.querySelector('.online-indicator');
        if (user.status === 'online') {
            indicator.style.backgroundColor = '#28a745'; // Green
        } else if (user.status === 'away') {
            indicator.style.backgroundColor = '#ffc107'; // Yellow
        } else if (user.status === 'busy') {
            indicator.style.backgroundColor = '#dc3545'; // Red
        } else {
            indicator.style.backgroundColor = '#6c757d'; // Gray
        }
        
        // Make it clear that the element is clickable
        element.style.cursor = 'pointer';
        element.title = `Click to chat with ${user.firstName || user.first_name || ''} ${user.lastName || user.last_name || ''}`;
        
        return element;
    }

    updateUserStatus(userId, status) {
        const user = this.users.get(userId);
        if (user) {
            const indicator = user.element.querySelector('.online-indicator');
            const lastActiveElement = user.element.querySelector('.last-active');
            
            // Update indicator color and text based on status
            if (status === 'online') {
                indicator.style.backgroundColor = '#28a745'; // Green
                lastActiveElement.textContent = 'Online';
            } else if (status === 'away') {
                indicator.style.backgroundColor = '#ffc107'; // Yellow
                lastActiveElement.textContent = 'Away';
            } else if (status === 'busy') {
                indicator.style.backgroundColor = '#dc3545'; // Red
                lastActiveElement.textContent = 'Busy';
            } else {
                indicator.style.backgroundColor = '#6c757d'; // Gray
                lastActiveElement.textContent = 'Offline';
            }
            
            user.data.status = status;
        }
    }

    changeUserStatus(newStatus) {
        // Send status change to WebSocket server
        if (this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify({
                type: 'status_change',
                status: newStatus
            }));
        }
        
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

    setOnlineStatus(isOnline) {
        if (this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify({
                type: 'status_change',
                status: isOnline ? 'online' : 'offline'
            }));
        }
    }

    updateUsersCount() {
        const onlineCount = Array.from(this.users.values())
            .filter(user => user.data.status === 'online').length;
        this.onlineCountBadge.textContent = onlineCount;
    }

    toggleNoUsersMessage() {
        this.noUsersMessage.style.display = this.users.size === 0 ? 'block' : 'none';
    }

    startConversation(userId) {
        console.log('Starting conversation with user ID:', userId);
        const user = this.users.get(userId);
        console.log('Found user data:', user);
        
        // Temporary alert for testing
        alert(`Attempting to start conversation with user ID: ${userId}`);
        
        if (user) {
            const chatUrl = `${this.baseUrl}/messages/chat/${userId}`;
            console.log('Navigating to:', chatUrl);
            // Navigate to the chat view with the selected user
            window.location.href = chatUrl;
        } else {
            console.error('User not found in users map for ID:', userId);
            console.log('Available users in map:', Array.from(this.users.keys()));
        }
    }

    getLastActiveText(timestamp) {
        if (!timestamp) return 'Never active';
        
        const date = new Date(timestamp);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000); // diff in seconds

        if (diff < 60) return 'Just now';
        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        return date.toLocaleDateString();
    }
}

// Remove automatic initialization - will be initialized from inbox.php
