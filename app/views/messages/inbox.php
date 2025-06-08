<div class="container py-4">
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
                                               value="<?= htmlspecialchars($search_query) ?>">
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
                                                        From: <?= htmlspecialchars($message['first_name'] . ' ' . $message['last_name']) ?>
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
                                                        To: <?= htmlspecialchars($message['first_name'] . ' ' . $message['last_name']) ?>
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
                                                        <?= htmlspecialchars($conversation['first_name'] . ' ' . $conversation['last_name']) ?>
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

<!-- Compose Message Modal -->
<div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compose Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="composeForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="mb-3">
                        <label for="receiver_id" class="form-label">To (User ID) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="receiver_id" name="receiver_id" required>
                        <div class="form-text">Enter the User ID of the person you want to message</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
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

<script>
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
            bootstrap.Modal.getInstance(document.getElementById('composeModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Send failed: ' + error.message);
    });
});
</script>