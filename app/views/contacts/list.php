<?php $this->startSection('content'); ?>
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link <?= $current_tab === 'received' ? 'active' : '' ?>" href="?tab=received">
                                <i class="bi bi-inbox"></i> Received
                                <?php if ($stats['pending_received'] > 0): ?>
                                    <span class="badge bg-primary"><?= $stats['pending_received'] ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $current_tab === 'sent' ? 'active' : '' ?>" href="?tab=sent">
                                <i class="bi bi-send"></i> Sent
                                <?php if ($stats['pending_sent'] > 0): ?>
                                    <span class="badge bg-warning"><?= $stats['pending_sent'] ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $current_tab === 'contacts' ? 'active' : '' ?>" href="?tab=contacts">
                                <i class="bi bi-people"></i> Contacts
                                <?php if ($stats['accepted'] > 0): ?>
                                    <span class="badge bg-success"><?= $stats['accepted'] ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <?php if ($current_tab === 'received'): ?>
                        <?php if (empty($received_requests)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="text-muted mt-2">No contact requests received</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($received_requests as $request): ?>
                                <div class="d-flex align-items-center p-3 border-bottom" data-request-id="<?= $request['id'] ?>">
                                    <img src="<?= $request['profile_photo'] ?? BASE_URL . '/assets/images/default-avatar.png' ?>" 
                                         class="rounded-circle me-3" width="50" height="50" alt="Profile Photo">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?>
                                            <span class="badge bg-<?= $request['status'] === 'pending' ? 'warning' : ($request['status'] === 'accepted' ? 'success' : 'danger') ?> status-badge">
                                                <?= ucfirst($request['status']) ?>
                                            </span>
                                        </h6>
                                        <small class="text-muted"><?= $request['age'] ?> years • <?= htmlspecialchars($request['district']) ?></small>
                                        <?php if ($request['message']): ?>
                                            <p class="small text-muted mb-0"><?= htmlspecialchars($request['message']) ?></p>
                                        <?php endif; ?>
                                        <small class="text-muted d-block">Sent <?= timeAgo($request['sent_at']) ?></small>
                                    </div>
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <div class="btn-group-vertical">
                                            <button class="btn btn-sm btn-success" onclick="respondToRequest('<?= $request['id'] ?>', 'accepted')">
                                                <i class="bi bi-check"></i> Accept
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="respondToRequest('<?= $request['id'] ?>', 'rejected')">
                                                <i class="bi bi-x"></i> Reject
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php elseif ($current_tab === 'sent'): ?>
                        <?php if (empty($sent_requests)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-send display-4 text-muted"></i>
                                <p class="text-muted mt-2">No contact requests sent</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($sent_requests as $request): ?>
                                <div class="d-flex align-items-center p-3 border-bottom">
                                    <img src="<?= $request['profile_photo'] ?? BASE_URL . '/assets/images/default-avatar.png' ?>" 
                                         class="rounded-circle me-3" width="50" height="50" alt="Profile Photo">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?>
                                            <span class="badge bg-<?= $request['status'] === 'pending' ? 'warning' : ($request['status'] === 'accepted' ? 'success' : 'danger') ?>">
                                                <?= ucfirst($request['status']) ?>
                                            </span>
                                        </h6>
                                        <small class="text-muted"><?= $request['age'] ?> years • <?= htmlspecialchars($request['district']) ?></small>
                                        <?php if ($request['message']): ?>
                                            <p class="small text-muted mb-0"><?= htmlspecialchars($request['message']) ?></p>
                                        <?php endif; ?>
                                        <small class="text-muted d-block">Sent <?= timeAgo($request['sent_at']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if (empty($accepted_contacts)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-people display-4 text-muted"></i>
                                <p class="text-muted mt-2">No contacts yet</p>
                            </div>
                        <?php else: ?>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                                <?php foreach ($accepted_contacts as $contact): ?>
                                    <div class="col">
                                        <div class="card h-100">
                                            <img src="<?= $contact['profile_photo'] ?? BASE_URL . '/assets/images/default-avatar.png' ?>" 
                                                 class="card-img-top" alt="Profile Photo" style="height: 200px; object-fit: cover;">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?></h5>
                                                <p class="card-text">
                                                    <i class="bi bi-envelope"></i> <?= htmlspecialchars($contact['email']) ?><br>
                                                    <i class="bi bi-telephone"></i> <?= htmlspecialchars($contact['phone']) ?>
                                                </p>
                                                <a href="<?= BASE_URL ?>/profile/<?= $contact['contact_user_id'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-eye"></i> View Profile
                                                </a>
                                            </div>
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
<?php $this->endSection(); ?> 