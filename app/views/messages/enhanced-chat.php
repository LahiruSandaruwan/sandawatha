<?php
require_once __DIR__ . '/../../helpers/PermissionMiddleware.php';

// Get user permissions and package info
$userPackage = PermissionMiddleware::getUserPackageInfo();
$canVideoCall = PermissionMiddleware::hasFeature('video_calling');
$canAudioCall = PermissionMiddleware::hasFeature('audio_calling');
$messageLimit = PermissionMiddleware::getUserFeatureLimit($_SESSION['user_id'], 'daily_messages');
$remainingMessages = PermissionMiddleware::getRemainingQuota('daily_messages');

// Set default values for undefined variables
$other_user = $other_user ?? [];
$messages = $messages ?? [];
$current_user_id = $current_user_id ?? $_SESSION['user_id'] ?? 0;

// Get other user's ID for routing
$other_user_id = $other_user['user_id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?= htmlspecialchars($other_user['first_name'] ?? 'User') ?> - <?= SITE_NAME ?></title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Enhanced Chat Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/chat/enhanced-chat.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/chat/calling.css">
    
    <!-- Data attributes for JavaScript -->
    <script>
        document.body.dataset.currentUserId = '<?= $current_user_id ?>';
        document.body.dataset.otherUserId = '<?= $other_user['user_id'] ?? 0 ?>';
        document.body.dataset.baseUrl = '<?= BASE_URL ?>';
        document.body.dataset.firstName = '<?= htmlspecialchars($_SESSION['first_name'] ?? '') ?>';
        document.body.dataset.lastName = '<?= htmlspecialchars($_SESSION['last_name'] ?? '') ?>';
        document.body.dataset.profilePhoto = '<?= htmlspecialchars($_SESSION['profile_photo'] ?? '') ?>';
        document.body.dataset.canVideoCall = '<?= $canVideoCall ? 'true' : 'false' ?>';
        document.body.dataset.canAudioCall = '<?= $canAudioCall ? 'true' : 'false' ?>';
        document.body.dataset.messageLimit = '<?= $messageLimit ?>';
        document.body.dataset.remainingMessages = '<?= $remainingMessages ?>';
    </script>
</head>
<body>
    <!-- Main Chat Container -->
    <div class="enhanced-chat-container">
        <div class="container-fluid h-100 p-0">
            <div class="row h-100 g-0">
                
                <!-- Sidebar - Online Users -->
                <div class="col-lg-3 col-md-4 d-none d-md-block">
                    <div class="chat-sidebar">
                        <!-- User Status Header -->
                        <div class="user-status-header">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="position-relative me-3">
                                        <?php if (!empty($_SESSION['profile_photo'])): ?>
                                            <img src="<?= UPLOAD_URL . htmlspecialchars($_SESSION['profile_photo']) ?>" 
                                                 alt="Your Profile" class="user-avatar">
                                        <?php else: ?>
                                            <div class="user-avatar d-flex align-items-center justify-content-center" 
                                                 style="background: rgba(255,255,255,0.2);">
                                                <i class="bi bi-person text-white fs-4"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="online-indicator"></div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($_SESSION['first_name'] . ' ' . ($_SESSION['last_name'] ?? '')) ?></h6>
                                        <small class="text-white-50">Online</small>
                                    </div>
                                </div>
                                
                                <!-- Mobile Toggle -->
                                <button class="btn btn-link text-white d-md-none sidebar-toggle" type="button">
                                    <i class="bi bi-list fs-4"></i>
                                </button>
                            </div>
                            
                            <!-- Status Dropdown -->
                            <div class="status-dropdown dropdown">
                                <button class="btn dropdown-toggle w-100" type="button" 
                                        id="statusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="text-success">●</span> Online
                                </button>
                                <ul class="dropdown-menu w-100" aria-labelledby="statusDropdown">
                                    <li><a class="dropdown-item" href="#" data-status="online">
                                        <span class="text-success">●</span> Online
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" data-status="away">
                                        <span class="text-warning">●</span> Away
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" data-status="busy">
                                        <span class="text-danger">●</span> Busy
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" data-status="offline">
                                        <span class="text-muted">●</span> Offline
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Online Users List -->
                        <div class="online-users-list">
                            <h6 class="text-muted px-3 mb-3">
                                <i class="bi bi-people me-2"></i>
                                Online Users <span id="online-count" class="badge bg-success ms-2">0</span>
                            </h6>
                            
                            <div id="connected-users" class="connected-users-container">
                                <!-- Current chat user -->
                                <div class="online-user-item active" data-user-id="<?= $other_user['user_id'] ?>">
                                    <div class="position-relative me-3">
                                        <?php if (!empty($other_user['profile_photo'])): ?>
                                            <img src="<?= UPLOAD_URL . htmlspecialchars($other_user['profile_photo']) ?>" 
                                                 alt="Profile" class="user-avatar">
                                        <?php else: ?>
                                            <div class="user-avatar d-flex align-items-center justify-content-center" 
                                                 style="background: rgba(102, 126, 234, 0.2);">
                                                <i class="bi bi-person text-primary fs-5"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="online-indicator"></div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold"><?= htmlspecialchars($other_user['first_name'] . ' ' . ($other_user['last_name'] ?? '')) ?></div>
                                        <small class="text-muted"><?= $other_user['age'] ?? '' ?> years • <?= htmlspecialchars($other_user['district'] ?? '') ?></small>
                                    </div>
                                </div>
                                
                                <!-- No other users message -->
                                <div id="no-users-message" class="text-center py-4 text-muted">
                                    <i class="bi bi-people fs-3 opacity-50"></i>
                                    <p class="mb-0 mt-2">No other users online</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Back to Messages -->
                        <div class="p-3 mt-auto">
                            <a href="<?= BASE_URL ?>/messages" class="btn btn-outline-primary w-100">
                                <i class="bi bi-arrow-left me-2"></i>Back to Messages
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Main Chat Area -->
                <div class="col-lg-9 col-md-8 col-12">
                    <div class="chat-main">
                        
                        <!-- Chat Header -->
                        <div class="chat-header">
                            <div class="chat-user-info">
                                <!-- Mobile Sidebar Toggle -->
                                <button class="btn btn-link text-white d-md-none me-3 sidebar-toggle" type="button">
                                    <i class="bi bi-list fs-4"></i>
                                </button>
                                
                                <div class="position-relative me-3">
                                    <?php if (!empty($other_user['profile_photo'])): ?>
                                        <img src="<?= UPLOAD_URL . htmlspecialchars($other_user['profile_photo']) ?>" 
                                             alt="Profile" class="user-avatar">
                                    <?php else: ?>
                                        <div class="user-avatar d-flex align-items-center justify-content-center" 
                                             style="background: rgba(255,255,255,0.2);">
                                            <i class="bi bi-person text-white fs-4"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="online-indicator"></div>
                                </div>
                                
                                <div class="chat-user-details">
                                    <h5><?= htmlspecialchars($other_user['first_name'] . ' ' . ($other_user['last_name'] ?? '')) ?></h5>
                                    <div class="chat-user-status">
                                        <span><?= $other_user['age'] ?? '' ?> years old</span>
                                        <?php if (!empty($other_user['district'])): ?>
                                            <span>•</span>
                                            <span><?= htmlspecialchars($other_user['district']) ?></span>
                                        <?php endif; ?>
                                        
                                        <!-- Typing Indicator -->
                                        <div class="typing-indicator">
                                            <i class="bi bi-three-dots"></i>
                                            <span>Typing</span>
                                            <div class="typing-dots">
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Chat Actions -->
                            <div class="chat-actions">
                                <!-- Video Call -->
                                <button class="chat-action-btn video-call-btn <?= !$canVideoCall ? 'disabled' : '' ?>" 
                                        title="<?= $canVideoCall ? 'Start Video Call' : 'Video calling requires Premium subscription' ?>">
                                    <i class="bi bi-camera-video"></i>
                                </button>
                                
                                <!-- Audio Call -->
                                <button class="chat-action-btn audio-call-btn <?= !$canAudioCall ? 'disabled' : '' ?>" 
                                        title="<?= $canAudioCall ? 'Start Voice Call' : 'Voice calling requires Premium subscription' ?>">
                                    <i class="bi bi-telephone"></i>
                                </button>
                                
                                <!-- Profile Link -->
                                <a href="<?= BASE_URL ?>/profile/<?= $other_user_id ?>" 
                                   class="chat-action-btn" title="View Profile">
                                    <i class="bi bi-person"></i>
                                </a>
                                
                                <!-- More Options -->
                                <div class="dropdown">
                                    <button class="chat-action-btn" type="button" 
                                            data-bs-toggle="dropdown" aria-expanded="false" title="More Options">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="window.enhancedChat?.clearHistory()">
                                            <i class="bi bi-trash me-2"></i>Clear Chat History
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="window.enhancedChat?.blockUser()">
                                            <i class="bi bi-slash-circle me-2"></i>Block User
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="window.enhancedChat?.reportUser()">
                                            <i class="bi bi-flag me-2"></i>Report User
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Usage Indicator for Limited Users -->
                        <?php if ($messageLimit !== 'unlimited' && is_numeric($messageLimit)): ?>
                        <div class="usage-indicator border-bottom border-light">
                            <div class="px-4 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-chat-dots me-1"></i>
                                        Daily Messages: <?= $remainingMessages ?> / <?= $messageLimit ?> remaining
                                    </small>
                                    <?php if ($remainingMessages < 5): ?>
                                        <a href="<?= BASE_URL ?>/premium" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-arrow-up-circle me-1"></i>Upgrade
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: <?= ($remainingMessages / $messageLimit) * 100 ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Messages Container -->
                        <div class="chat-messages" id="messagesContainer">
                            <?php if (empty($messages)): ?>
                                <div class="chat-empty-state">
                                    <i class="bi bi-chat-heart"></i>
                                    <h5>Start a conversation</h5>
                                    <p>Send a message to start chatting with <?= htmlspecialchars($other_user['first_name'] ?? 'this user') ?></p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                    <div class="message-wrapper <?= $message['sender_id'] == $current_user_id ? 'sent' : 'received' ?>" 
                                         data-message-id="<?= $message['id'] ?>">
                                        <div class="message-bubble <?= $message['sender_id'] == $current_user_id ? 'sent' : 'received' ?>">
                                            <div class="message-content">
                                                <?= nl2br(htmlspecialchars($message['message'])) ?>
                                            </div>
                                            <div class="message-time">
                                                <span><?= date('M j, g:i A', strtotime($message['created_at'])) ?></span>
                                                <?php if ($message['sender_id'] == $current_user_id): ?>
                                                    <div class="message-status">
                                                        <i class="bi bi-check status-sent" title="Sent"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- File Upload Area -->
                        <div class="file-upload-area" id="fileUploadArea">
                            <i class="bi bi-cloud-upload fs-1 text-primary mb-3"></i>
                            <h5>Drop files here to share</h5>
                            <p class="text-muted">Support for images, documents, and media files up to 10MB</p>
                        </div>
                        
                        <!-- Message Input -->
                        <div class="chat-input">
                            <form class="message-form" id="messageForm">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                <input type="hidden" name="receiver_id" value="<?= $other_user['user_id'] ?>">
                                
                                <div class="message-input-group">
                                    <textarea class="message-textarea" id="messageInput" name="message" 
                                             placeholder="Type your message..." 
                                             rows="1" required 
                                             <?= ($remainingMessages <= 0 && $messageLimit !== 'unlimited') ? 'disabled' : '' ?>></textarea>
                                    
                                    <div class="input-actions">
                                        <button type="button" class="input-action-btn emoji-btn" title="Add Emoji">
                                            <i class="bi bi-emoji-smile"></i>
                                        </button>
                                        <button type="button" class="input-action-btn attach-btn" title="Attach File">
                                            <i class="bi bi-paperclip"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <button type="submit" class="send-button" 
                                        <?= ($remainingMessages <= 0 && $messageLimit !== 'unlimited') ? 'disabled' : '' ?>>
                                    <i class="bi bi-send-fill"></i>
                                </button>
                            </form>
                            
                            <!-- Hidden file input -->
                            <input type="file" id="file-input" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx" style="display: none;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/chat/enhanced-chat.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/chat/webrtc-calling.js"></script>
    
    <!-- Call Interface Modal (for future implementation) -->
    <div class="modal fade" id="callModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Video Call</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="call-interface">
                        <div class="video-container">
                            <video id="localVideo" autoplay muted style="width: 100%; max-height: 300px;"></video>
                            <video id="remoteVideo" autoplay style="position: absolute; top: 20px; right: 20px; width: 150px; height: 100px; border-radius: 10px;"></video>
                        </div>
                        <div class="call-controls mt-4">
                            <button class="btn btn-danger btn-lg rounded-circle me-3" onclick="window.enhancedChat?.endCall()">
                                <i class="bi bi-telephone-x"></i>
                            </button>
                            <button class="btn btn-secondary btn-lg rounded-circle me-3" onclick="window.enhancedChat?.toggleMute()">
                                <i class="bi bi-mic-mute"></i>
                            </button>
                            <button class="btn btn-secondary btn-lg rounded-circle" onclick="window.enhancedChat?.toggleVideo()">
                                <i class="bi bi-camera-video-off"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Additional responsive styles */
        @media (max-width: 768px) {
            .chat-sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                width: 80%;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s ease;
            }
            
            .chat-sidebar.show {
                left: 0;
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                display: none;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
    
    <script>
        // Enhanced mobile sidebar handling
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtns = document.querySelectorAll('.sidebar-toggle');
            const sidebar = document.querySelector('.chat-sidebar');
            
            if (toggleBtns.length && sidebar) {
                // Create overlay for mobile
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                document.body.appendChild(overlay);
                
                toggleBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        sidebar.classList.toggle('show');
                        overlay.classList.toggle('show');
                    });
                });
                
                // Close sidebar when clicking overlay
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html> 