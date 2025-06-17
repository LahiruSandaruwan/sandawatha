// Modern Chat Enhancements
class ModernChatEnhancements {
    constructor() {
        this.messageInput = document.getElementById('messageInput');
        this.sendButton = document.getElementById('sendButton');
        this.typingIndicator = document.getElementById('typing-indicator');
        this.chatMessages = document.querySelector('.chat-messages');
        this.quickReplies = document.getElementById('quick-replies');
        
        this.typingTimer = null;
        this.isTyping = false;
        
        this.init();
    }
    
    init() {
        this.setupAutoResize();
        this.setupQuickReplies();
        this.setupTypingIndicator();
        this.setupMessageAnimations();
        this.setupSendButtonAnimation();
        this.setupKeyboardShortcuts();
        this.setupEmojiButton();
    }
    
    // Auto-resize textarea
    setupAutoResize() {
        if (!this.messageInput) return;
        
        this.messageInput.addEventListener('input', () => {
            // Reset height to auto to get the correct scrollHeight
            this.messageInput.style.height = 'auto';
            
            // Calculate new height
            const scrollHeight = this.messageInput.scrollHeight;
            const maxHeight = 120; // Maximum height in pixels
            const minHeight = 50;  // Minimum height in pixels
            
            // Set the new height
            const newHeight = Math.min(Math.max(scrollHeight, minHeight), maxHeight);
            this.messageInput.style.height = newHeight + 'px';
            
            // Show/hide scrollbar if content exceeds max height
            if (scrollHeight > maxHeight) {
                this.messageInput.style.overflowY = 'auto';
            } else {
                this.messageInput.style.overflowY = 'hidden';
            }
        });
    }
    
    // Setup quick reply buttons
    setupQuickReplies() {
        const quickReplyButtons = document.querySelectorAll('.quick-reply-btn');
        
        quickReplyButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const replyText = button.textContent.trim();
                
                if (this.messageInput) {
                    this.messageInput.value = replyText;
                    this.messageInput.focus();
                    this.messageInput.dispatchEvent(new Event('input')); // Trigger auto-resize
                }
            });
        });
    }
    
    // Setup typing indicator
    setupTypingIndicator() {
        if (!this.messageInput) return;
        
        this.messageInput.addEventListener('input', () => {
            this.handleTyping();
        });
        
        this.messageInput.addEventListener('keydown', () => {
            this.handleTyping();
        });
    }
    
    handleTyping() {
        // Clear existing timer
        if (this.typingTimer) {
            clearTimeout(this.typingTimer);
        }
        
        // Show typing indicator if not already shown
        if (!this.isTyping && this.messageInput.value.trim().length > 0) {
            this.showTypingIndicator();
        }
        
        // Set timer to hide typing indicator
        this.typingTimer = setTimeout(() => {
            this.hideTypingIndicator();
        }, 2000); // Hide after 2 seconds of inactivity
    }
    
    showTypingIndicator() {
        if (this.typingIndicator) {
            this.typingIndicator.style.display = 'block';
            this.isTyping = true;
            
            // Send typing status via WebSocket if available
            if (window.chatManager && window.chatManager.ws) {
                window.chatManager.sendWebSocketMessage({
                    type: 'typing_start',
                    sender_id: document.body.dataset.currentUserId,
                    receiver_id: document.body.dataset.otherUserId
                });
            }
        }
    }
    
    hideTypingIndicator() {
        if (this.typingIndicator) {
            this.typingIndicator.style.display = 'none';
            this.isTyping = false;
            
            // Send stop typing status via WebSocket if available
            if (window.chatManager && window.chatManager.ws) {
                window.chatManager.sendWebSocketMessage({
                    type: 'typing_stop',
                    sender_id: document.body.dataset.currentUserId,
                    receiver_id: document.body.dataset.otherUserId
                });
            }
        }
    }
    
    // Setup message animations
    setupMessageAnimations() {
        // Observe new messages being added
        if (this.chatMessages) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach((node) => {
                            if (node.nodeType === 1 && node.classList.contains('message')) {
                                this.animateNewMessage(node);
                            }
                        });
                    }
                });
            });
            
            observer.observe(this.chatMessages, {
                childList: true,
                subtree: true
            });
        }
    }
    
    animateNewMessage(messageElement) {
        // Add entrance animation
        messageElement.style.opacity = '0';
        messageElement.style.transform = 'translateY(30px) scale(0.9)';
        
        // Trigger animation
        requestAnimationFrame(() => {
            messageElement.style.transition = 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            messageElement.style.opacity = '1';
            messageElement.style.transform = 'translateY(0) scale(1)';
        });
        
        // Scroll to bottom smoothly
        setTimeout(() => {
            this.scrollToBottom();
        }, 100);
    }
    
    // Setup send button animation
    setupSendButtonAnimation() {
        if (!this.sendButton || !this.messageInput) return;
        
        this.messageInput.addEventListener('input', () => {
            const hasContent = this.messageInput.value.trim().length > 0;
            
            if (hasContent) {
                this.sendButton.style.transform = 'scale(1.1)';
                this.sendButton.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            } else {
                this.sendButton.style.transform = 'scale(1)';
                this.sendButton.style.background = 'linear-gradient(135deg, #9ca3af 0%, #6b7280 100%)';
            }
        });
        
        // Success animation after sending
        this.sendButton.addEventListener('click', () => {
            this.animateSendSuccess();
        });
    }
    
    animateSendSuccess() {
        const originalIcon = this.sendButton.innerHTML;
        
        // Show loading animation
        this.sendButton.innerHTML = '<div class="loading-dots"><span></span><span></span><span></span></div>';
        this.sendButton.disabled = true;
        
        // Simulate sending delay (adjust based on actual send completion)
        setTimeout(() => {
            // Show success animation
            this.sendButton.innerHTML = '<i class="bi bi-check2"></i>';
            this.sendButton.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
            
            setTimeout(() => {
                // Reset to original state
                this.sendButton.innerHTML = originalIcon;
                this.sendButton.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                this.sendButton.disabled = false;
            }, 800);
        }, 1000);
    }
    
    // Setup keyboard shortcuts
    setupKeyboardShortcuts() {
        if (!this.messageInput) return;
        
        this.messageInput.addEventListener('keydown', (e) => {
            // Send message on Ctrl+Enter or Cmd+Enter
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                this.sendButton.click();
            }
            
            // Quick emoji shortcuts
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case '1':
                        e.preventDefault();
                        this.insertEmoji('😊');
                        break;
                    case '2':
                        e.preventDefault();
                        this.insertEmoji('❤️');
                        break;
                    case '3':
                        e.preventDefault();
                        this.insertEmoji('👍');
                        break;
                    case '4':
                        e.preventDefault();
                        this.insertEmoji('😂');
                        break;
                }
            }
        });
    }
    
    // Setup emoji button
    setupEmojiButton() {
        const emojiButton = document.querySelector('[title="Add Emoji"]');
        
        if (emojiButton) {
            emojiButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.showEmojiPicker();
            });
        }
    }
    
    showEmojiPicker() {
        // Simple emoji picker (can be enhanced with a full emoji library)
        const emojis = ['😊', '😂', '❤️', '👍', '👎', '🎉', '🔥', '💯', '🤔', '😍', '🥰', '😎', '🙌', '👏', '🚀'];
        
        // Remove existing picker
        const existingPicker = document.getElementById('emoji-picker');
        if (existingPicker) {
            existingPicker.remove();
            return;
        }
        
        // Create emoji picker
        const picker = document.createElement('div');
        picker.id = 'emoji-picker';
        picker.className = 'emoji-picker position-absolute bg-white rounded shadow-lg p-2';
        picker.style.cssText = `
            bottom: 60px;
            right: 80px;
            z-index: 1000;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.5rem;
            max-width: 200px;
            border: 1px solid rgba(102, 126, 234, 0.2);
        `;
        
        emojis.forEach(emoji => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-sm emoji-btn';
            button.textContent = emoji;
            button.style.cssText = `
                border: none;
                background: transparent;
                font-size: 1.2rem;
                padding: 0.5rem;
                border-radius: 0.5rem;
                cursor: pointer;
                transition: all 0.2s ease;
            `;
            
            button.addEventListener('mouseenter', () => {
                button.style.background = 'rgba(102, 126, 234, 0.1)';
                button.style.transform = 'scale(1.2)';
            });
            
            button.addEventListener('mouseleave', () => {
                button.style.background = 'transparent';
                button.style.transform = 'scale(1)';
            });
            
            button.addEventListener('click', () => {
                this.insertEmoji(emoji);
                picker.remove();
            });
            
            picker.appendChild(button);
        });
        
        // Position and add picker
        const cardFooter = document.querySelector('.card-footer');
        cardFooter.style.position = 'relative';
        cardFooter.appendChild(picker);
        
        // Remove picker when clicking outside
        setTimeout(() => {
            document.addEventListener('click', (e) => {
                if (!picker.contains(e.target) && !e.target.closest('[title="Add Emoji"]')) {
                    picker.remove();
                }
            }, { once: true });
        }, 100);
    }
    
    insertEmoji(emoji) {
        if (!this.messageInput) return;
        
        const start = this.messageInput.selectionStart;
        const end = this.messageInput.selectionEnd;
        const text = this.messageInput.value;
        
        this.messageInput.value = text.substring(0, start) + emoji + text.substring(end);
        this.messageInput.selectionStart = this.messageInput.selectionEnd = start + emoji.length;
        
        this.messageInput.focus();
        this.messageInput.dispatchEvent(new Event('input')); // Trigger auto-resize
    }
    
    // Smooth scroll to bottom
    scrollToBottom() {
        if (this.chatMessages) {
            this.chatMessages.scrollTo({
                top: this.chatMessages.scrollHeight,
                behavior: 'smooth'
            });
        }
    }
    
    // Add message with animation
    addMessage(messageData, isSent = false) {
        if (!this.chatMessages) return;
        
        const messageHTML = this.createMessageHTML(messageData, isSent);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = messageHTML;
        const messageElement = tempDiv.firstElementChild;
        
        this.chatMessages.appendChild(messageElement);
        this.animateNewMessage(messageElement);
        
        return messageElement;
    }
    
    createMessageHTML(messageData, isSent) {
        const messageTime = messageData.created_at ? 
            new Date(messageData.created_at).toLocaleString() : 'Just now';
        
        return `
            <div class="message mb-3 ${isSent ? 'sent' : 'received'}">
                <div class="d-flex ${isSent ? 'justify-content-end' : 'justify-content-start'}">
                    <div class="message-bubble p-3 rounded ${isSent ? 'bg-primary text-white' : 'bg-light'}" 
                         style="max-width: 70%;">
                        ${messageData.subject && messageData.subject !== 'Chat Message' ? 
                            `<div class="fw-bold mb-1">${this.escapeHtml(messageData.subject)}</div>` : ''}
                        <div>${this.escapeHtml(messageData.message).replace(/\n/g, '<br>')}</div>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <small class="${isSent ? 'text-white-50' : 'text-muted'}">
                                ${messageTime}
                            </small>
                            ${isSent ? '<div class="message-status sent"><i class="bi bi-check2-all"></i></div>' : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Handle incoming typing status
    handleTypingStatus(data) {
        if (data.type === 'typing_start' && data.sender_id != document.body.dataset.currentUserId) {
            this.showRemoteTypingIndicator();
        } else if (data.type === 'typing_stop') {
            this.hideRemoteTypingIndicator();
        }
    }
    
    showRemoteTypingIndicator() {
        // Create or show remote typing indicator
        let remoteTyping = document.getElementById('remote-typing-indicator');
        
        if (!remoteTyping) {
            remoteTyping = document.createElement('div');
            remoteTyping.id = 'remote-typing-indicator';
            remoteTyping.className = 'message mb-3 received';
            remoteTyping.innerHTML = `
                <div class="d-flex justify-content-start">
                    <div class="message-bubble p-3 rounded bg-light" style="max-width: 70%;">
                        <div class="loading-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <small class="text-muted d-block mt-1">Typing...</small>
                    </div>
                </div>
            `;
            this.chatMessages.appendChild(remoteTyping);
        } else {
            remoteTyping.style.display = 'block';
        }
        
        this.scrollToBottom();
    }
    
    hideRemoteTypingIndicator() {
        const remoteTyping = document.getElementById('remote-typing-indicator');
        if (remoteTyping) {
            remoteTyping.style.opacity = '0';
            remoteTyping.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                remoteTyping.remove();
            }, 300);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.modernChatEnhancements = new ModernChatEnhancements();
});

// CSS for loading dots animation
const style = document.createElement('style');
style.textContent = `
    .emoji-picker {
        animation: fadeInUp 0.3s ease;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .loading-dots span {
        background: currentColor;
        opacity: 0.6;
    }
`;
document.head.appendChild(style); 