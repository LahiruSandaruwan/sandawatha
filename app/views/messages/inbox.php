<?php
// Set default values for undefined variables
$current_tab = $current_tab ?? 'inbox';
$messages = $messages ?? [];
$sentMessages = $sentMessages ?? [];
$adminMessages = $adminMessages ?? [];
$conversations = $conversations ?? [];
$stats = $stats ?? [
    'received_count' => 0,
    'sent_count' => 0,
    'unread_count' => 0,
    'admin_count' => 0
];
$search = $search ?? '';
?>

<!-- Add required meta tags for WebSocket functionality -->
<script>
// Set user data for WebSocket functionality
document.body.setAttribute('data-user-id', '<?= htmlspecialchars($_SESSION['user_id'] ?? '') ?>');
document.body.setAttribute('data-user-first-name', '<?= htmlspecialchars($_SESSION['first_name'] ?? '') ?>');
document.body.setAttribute('data-user-last-name', '<?= htmlspecialchars($_SESSION['last_name'] ?? '') ?>');
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
                    <div id="connected-users" class="list-group list-group-flush">
                        <!-- Connected users will be inserted here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-9 col-md-8">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="h3 mb-2">Messages</h1>
                    <p class="text-muted">Manage your conversations and stay connected</p>
                </div>
            </div>

            <!-- Message Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><?= $stats['received_count'] ?? 0 ?></h5>
                            <p class="card-text text-muted">Received</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-success"><?= $stats['sent_count'] ?? 0 ?></h5>
                            <p class="card-text text-muted">Sent</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-warning"><?= $stats['unread_count'] ?? 0 ?></h5>
                            <p class="card-text text-muted">Unread</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-info"><?= $stats['admin_messages'] ?? 0 ?></h5>
                            <p class="card-text text-muted">Admin Messages</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Tabs and Search -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <!-- Message Tabs -->
                                    <ul class="nav nav-pills" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link <?= $current_tab === 'inbox' ? 'active' : '' ?>" 
                                               href="/sandawatha/messages?tab=inbox">
                                                Inbox
                                                <?php if ($unread_count > 0): ?>
                                                    <span class="badge bg-danger ms-1"><?= $unread_count ?></span>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?= $current_tab === 'sent' ? 'active' : '' ?>" 
                                               href="/sandawatha/messages?tab=sent">Sent</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?= $current_tab === 'conversations' ? 'active' : '' ?>" 
                                               href="/sandawatha/messages?tab=conversations">Conversations</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?= $current_tab === 'admin' ? 'active' : '' ?>" 
                                               href="/sandawatha/messages?tab=admin">Admin</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <!-- Search -->
                                    <div class="d-flex">
                                        <form class="flex-grow-1 me-2" method="GET">
                                            <input type="hidden" name="tab" value="<?= htmlspecialchars($current_tab) ?>">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                                <input type="text" class="form-control" name="search" 
                                                       placeholder="Search messages..." 
                                                       value="<?= htmlspecialchars($search ?? '') ?>">
                                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                                            </div>
                                        </form>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeModal">
                                            <i class="bi bi-plus"></i> Compose
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php if ($current_tab === 'inbox'): ?>
                                <!-- Inbox Messages -->
                                <?php if (empty($messages)): ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-inbox display-1 text-muted"></i>
                                        <h5 class="mt-3 text-muted">No messages in inbox</h5>
                                        <p class="text-muted">You haven't received any messages yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($messages as $message): ?>
                                            <div class="list-group-item list-group-item-action <?= $message['is_read'] ? '' : 'list-group-item-warning' ?>">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($message['profile_photo'])): ?>
                                                            <img src="<?= UPLOAD_URL . htmlspecialchars($message['profile_photo']) ?>" 
                                                                 alt="Profile" class="rounded-circle me-3" width="40" height="40">
                                                        <?php else: ?>
                                                            <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="bi bi-person text-white"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <a href="/sandawatha/messages/<?= $message['id'] ?>" class="text-decoration-none">
                                                                    <?= htmlspecialchars($message['subject']) ?>
                                                                </a>
                                                                <?php if (!$message['is_read']): ?>
                                                                    <span class="badge bg-primary ms-2">New</span>
                                                                <?php endif; ?>
                                                            </h6>
                                                                                                        <p class="mb-1 text-muted">
                                                From: <?= htmlspecialchars(($message['first_name'] ?? '') . ' ' . ($message['last_name'] ?? '')) ?>
                                            </p>
                                                            <small class="text-muted">
                                                                <?= substr(htmlspecialchars($message['message']), 0, 100) ?>...
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted"><?= date('M j, Y g:i A', strtotime($message['created_at'])) ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($current_tab === 'sent'): ?>
                                <!-- Sent Messages -->
                                <?php if (empty($sent_messages)): ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-send display-1 text-muted"></i>
                                        <h5 class="mt-3 text-muted">No sent messages</h5>
                                        <p class="text-muted">You haven't sent any messages yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($sent_messages as $message): ?>
                                            <div class="list-group-item list-group-item-action">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($message['profile_photo'])): ?>
                                                            <img src="<?= UPLOAD_URL . htmlspecialchars($message['profile_photo']) ?>" 
                                                                 alt="Profile" class="rounded-circle me-3" width="40" height="40">
                                                        <?php else: ?>
                                                            <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="bi bi-person text-white"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <a href="/sandawatha/messages/<?= $message['id'] ?>" class="text-decoration-none">
                                                                    <?= htmlspecialchars($message['subject']) ?>
                                                                </a>
                                                            </h6>
                                                                                                        <p class="mb-1 text-muted">
                                                To: <?= htmlspecialchars(($message['first_name'] ?? '') . ' ' . ($message['last_name'] ?? '')) ?>
                                            </p>
                                                            <small class="text-muted">
                                                                <?= substr(htmlspecialchars($message['message']), 0, 100) ?>...
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted"><?= date('M j, Y g:i A', strtotime($message['created_at'])) ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($current_tab === 'conversations'): ?>
                                <!-- Conversations -->
                                <?php if (empty($conversations)): ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-chat-dots display-1 text-muted"></i>
                                        <h5 class="mt-3 text-muted">No conversations</h5>
                                        <p class="text-muted">Start a conversation by sending a message.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($conversations as $conversation): ?>
                                            <div class="list-group-item list-group-item-action">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($conversation['profile_photo'])): ?>
                                                            <img src="<?= UPLOAD_URL . htmlspecialchars($conversation['profile_photo']) ?>" 
                                                                 alt="Profile" class="rounded-circle me-3" width="40" height="40">
                                                        <?php else: ?>
                                                            <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="bi bi-person text-white"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                                                                        <h6 class="mb-1">
                                                <?= htmlspecialchars(($conversation['first_name'] ?? '') . ' ' . ($conversation['last_name'] ?? '')) ?>
                                                                <?php if ($conversation['unread_count'] > 0): ?>
                                                                    <span class="badge bg-danger ms-2"><?= $conversation['unread_count'] ?></span>
                                                                <?php endif; ?>
                                                            </h6>
                                                            <p class="mb-1 text-muted">
                                                                <?= htmlspecialchars($conversation['last_subject']) ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted"><?= date('M j, Y g:i A', strtotime($conversation['last_message_time'])) ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($current_tab === 'admin'): ?>
                                <!-- Admin Messages -->
                                <?php if (empty($admin_messages)): ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-shield-check display-1 text-muted"></i>
                                        <h5 class="mt-3 text-muted">No admin messages</h5>
                                        <p class="text-muted">You have no messages from administrators.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($admin_messages as $message): ?>
                                            <div class="list-group-item list-group-item-action <?= $message['is_read'] ? '' : 'list-group-item-info' ?>">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
                                                            <i class="bi bi-shield-check text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <a href="/sandawatha/messages/<?= $message['id'] ?>" class="text-decoration-none">
                                                                    <?= htmlspecialchars($message['subject']) ?>
                                                                </a>
                                                                <?php if (!$message['is_read']): ?>
                                                                    <span class="badge bg-primary ms-2">New</span>
                                                                <?php endif; ?>
                                                            </h6>
                                                            <p class="mb-1 text-muted">From: Administrator</p>
                                                            <small class="text-muted">
                                                                <?= substr(htmlspecialchars($message['message']), 0, 100) ?>...
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted"><?= date('M j, Y g:i A', strtotime($message['created_at'])) ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Compose Message Modal -->
<div class="modal fade" id="composeModal" tabindex="-1" aria-labelledby="composeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="composeModalLabel">Compose Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="composeForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="mb-3">
                        <label for="receiverSelect" class="form-label">To:</label>
                        <select class="form-select" id="receiverSelect" name="receiver_id" required>
                            <option value="">Select recipient...</option>
                            <!-- Recipients will be loaded dynamically -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="messageSubject" class="form-label">Subject:</label>
                        <input type="text" class="form-control" id="messageSubject" name="subject" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="messageContent" class="form-label">Message:</label>
                        <textarea class="form-control" id="messageContent" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include the WebSocket client script -->
<script src="/sandawatha/public/assets/js/chat/connected-users.js"></script>
<script>
    // Initialize the connected users when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        new ConnectedUsers();
        
        // Handle compose form submission
        document.getElementById('composeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/sandawatha/messages/send', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal and show success message
                    const modal = bootstrap.Modal.getInstance(document.getElementById('composeModal'));
                    modal.hide();
                    
                    // Show success alert
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
                    alertDiv.style.cssText = 'top: 80px; right: 20px; z-index: 1060; min-width: 300px;';
                    alertDiv.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(alertDiv);
                    
                    // Auto dismiss after 5 seconds
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 5000);
                    
                    // Reset form
                    this.reset();
                    
                    // Reload page to show updated messages
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
                    alertDiv.style.cssText = 'top: 80px; right: 20px; z-index: 1060; min-width: 300px;';
                    alertDiv.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(alertDiv);
                    
                    // Auto dismiss after 5 seconds
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending the message.');
            });
        });
    });
</script>