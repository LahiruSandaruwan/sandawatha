<?php $this->startSection('content'); ?>
<div class="container py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-banner bg-gradient-primary text-white p-4 rounded-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">
                            Welcome back, <?= htmlspecialchars($profile['first_name'] ?? 'User') ?>! 
                            <?php if ($premium_membership): ?>
                                <span class="badge premium-badge ms-2"><?= ucfirst($premium_membership['plan_type']) ?></span>
                            <?php endif; ?>
                        </h2>
                        <p class="mb-0">Your profile is <?= $profile_completion ?>% complete. Find your perfect match today!</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="profile-completion">
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: <?= $profile_completion ?>%"></div>
                            </div>
                            <small>Profile Completion: <?= $profile_completion ?>%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card bg-primary text-white">
                <div class="d-flex align-items-center">
                    <i class="bi bi-envelope-heart display-6 me-3"></i>
                    <div>
                        <h3 class="mb-0"><?= $contact_stats['pending_received'] ?? 0 ?></h3>
                        <small>New Requests</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="stat-card bg-success text-white">
                <div class="d-flex align-items-center">
                    <i class="bi bi-heart-fill display-6 me-3"></i>
                    <div>
                        <h3 class="mb-0"><?= $favorite_stats['favorited_by'] ?? 0 ?></h3>
                        <small>Favorited You</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="stat-card bg-info text-white">
                <div class="d-flex align-items-center">
                    <i class="bi bi-eye-fill display-6 me-3"></i>
                    <div>
                        <h3 class="mb-0">
                            <?php
                            if (!empty($profile_views)) {
                                $totalViews = array_sum(array_column($profile_views, 'views'));
                                echo $totalViews;
                            } else {
                                echo $profile['view_count'] ?? 0;
                            }
                            ?>
                        </h3>
                        <small>Profile Views</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="stat-card bg-warning text-white">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill display-6 me-3"></i>
                    <div>
                        <h3 class="mb-0"><?= $contact_stats['accepted'] ?? 0 ?></h3>
                        <small>Connections</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Profile Completion Card -->
            <?php if ($profile_completion < 100): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Complete Your Profile</h5>
                </div>
                <div class="card-body">
                    <p>Complete your profile to get better matches and increase your visibility.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Profile Photo
                                    <?php if ($profile && $profile['profile_photo']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><i class="bi bi-x"></i></span>
                                    <?php endif; ?>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Horoscope
                                    <?php if ($profile && $profile['horoscope_file']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><i class="bi bi-x"></i></span>
                                    <?php endif; ?>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Income Details
                                    <?php if ($profile && $profile['income_lkr'] > 0): ?>
                                        <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><i class="bi bi-x"></i></span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Goals & Preferences
                                    <?php if ($profile && $profile['goals']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><i class="bi bi-x"></i></span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/profile/edit" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Complete Profile
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Contact Requests -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-envelope-heart"></i> Recent Contact Requests</h5>
                    <a href="<?= BASE_URL ?>/contact-requests" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_requests)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-envelope display-4 text-muted"></i>
                            <p class="text-muted mt-2">No contact requests yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_requests as $request): ?>
                            <div class="d-flex align-items-center p-3 border-bottom" data-request-id="<?= $request['id'] ?>">
                                <img src="<?= $request['profile_photo'] ?? BASE_URL . '/assets/images/default-avatar.svg' ?>" 
                                     class="rounded-circle me-3" width="50" height="50" alt="Profile Photo"
                                     onerror="this.src='<?= BASE_URL ?>/assets/images/default-avatar.svg'">
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
                                        <button class="btn btn-sm btn-success" onclick="respondToRequest(<?= $request['id'] ?>, 'accepted')">
                                            <i class="bi bi-check"></i> Accept
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="respondToRequest(<?= $request['id'] ?>, 'rejected')">
                                            <i class="bi bi-x"></i> Reject
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Suggested Matches -->
            <?php if (!empty($suggested_matches)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Suggested Matches</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <?php foreach ($suggested_matches as $match): ?>
                            <div class="col-md-6">
                                <div class="match-card">
                                    <?php if (isset($match['compatibility_score'])): ?>
                                    <div class="match-compatibility">
                                        <i class="bi bi-heart-fill text-danger"></i> <?= round($match['compatibility_score']) ?>% Match
                                                        </div>
                                                    <?php endif; ?>
                                    
                                    <div class="match-badges">
                                        <?php if ($match['is_premium'] ?? false): ?>
                                        <span class="match-badge">
                                            <i class="bi bi-star-fill text-warning"></i> Premium
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($match['is_verified'] ?? false): ?>
                                        <span class="match-badge">
                                            <i class="bi bi-patch-check-fill text-primary"></i> Verified
                                        </span>
                                        <?php endif; ?>
                                    </div>

                                    <img src="<?= $match['profile_photo'] ?? BASE_URL . '/assets/images/default-avatar.svg' ?>" 
                                         class="match-photo" alt="Profile Photo"
                                         onerror="this.src='<?= BASE_URL ?>/assets/images/default-avatar.svg'">
                                    
                                    <div class="match-info">
                                        <div>
                                            <h6><?= htmlspecialchars($match['first_name'] . ' ' . $match['last_name']) ?></h6>
                                            <p class="text-muted mb-2">
                                                <?= $match['age'] ?> years • <?= htmlspecialchars($match['district']) ?>
                                                <?php if (!empty($match['occupation'])): ?>
                                                <br><?= htmlspecialchars($match['occupation']) ?>
                                                <?php endif; ?>
                                            </p>
                                            <?php if (!empty($match['interests'])): ?>
                                            <div class="d-flex flex-wrap gap-1 mb-3">
                                                <?php foreach (array_slice(explode(',', $match['interests']), 0, 3) as $interest): ?>
                                                <span class="badge bg-light text-dark"><?= htmlspecialchars(trim($interest)) ?></span>
                                                <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="match-actions">
                                            <a href="<?= BASE_URL ?>/profile/<?= $match['id'] ?>" class="btn btn-primary">
                                                <i class="bi bi-eye"></i> View Profile
                                            </a>
                                            <button class="btn btn-outline-danger btn-favorite" data-profile-id="<?= $match['id'] ?>">
                                                <i class="bi bi-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Recent Visitors -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-eye"></i> Recent Visitors</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_visitors)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-eye display-4 text-muted"></i>
                            <p class="text-muted mt-2">No recent visitors</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_visitors as $visitor): ?>
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?= $visitor['profile_photo'] ?? BASE_URL . '/assets/images/default-avatar.svg' ?>" 
                                     class="rounded-circle me-3" width="40" height="40" alt="Profile Photo"
                                     onerror="this.src='<?= BASE_URL ?>/assets/images/default-avatar.svg'">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($visitor['first_name'] . ' ' . $visitor['last_name']) ?></h6>
                                    <small class="text-muted">Visited <?= isset($visitor['visited_at']) ? timeAgo($visitor['visited_at']) : 'recently' ?></small>
                                </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Premium Features -->
            <?php if (!$premium_membership): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-star"></i> Upgrade to Premium</h5>
                </div>
                <div class="card-body">
                    <p>Get access to premium features and increase your chances of finding the perfect match!</p>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle text-success"></i> Unlimited contact requests</li>
                        <li><i class="bi bi-check-circle text-success"></i> See who viewed your profile</li>
                        <li><i class="bi bi-check-circle text-success"></i> Advanced search filters</li>
                        <li><i class="bi bi-check-circle text-success"></i> Priority customer support</li>
                    </ul>
                    <a href="<?= BASE_URL ?>/premium" class="btn btn-primary">
                        <i class="bi bi-star"></i> Upgrade Now
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->startSection('scripts'); ?>
<script>
function respondToRequest(requestId, status) {
        if (!confirm('Are you sure you want to ' + status + ' this request?')) {
            return;
        }
    
        fetch('/contact-requests/respond', {
        method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                request_id: requestId,
                status: status
            })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
                // Update the UI
                const requestElement = document.querySelector(`[data-request-id="${requestId}"]`);
                if (requestElement) {
                    if (status === 'rejected') {
                        requestElement.remove();
                    } else {
                        const statusBadge = requestElement.querySelector('.status-badge');
                        if (statusBadge) {
                            statusBadge.className = `badge bg-${status === 'accepted' ? 'success' : 'danger'} status-badge`;
                            statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        }
                        const actionButtons = requestElement.querySelector('.btn-group-vertical');
                        if (actionButtons) {
                            actionButtons.remove();
                        }
                    }
                }
                
                // Show success message
                alert(data.message);
        } else {
                alert(data.message || 'An error occurred. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
            alert('An error occurred. Please try again.');
    });
}

    // Handle favorite buttons
    document.querySelectorAll('.btn-favorite').forEach(button => {
        button.addEventListener('click', function() {
            const profileId = this.dataset.profileId;
            const isFavorited = this.classList.contains('active');
            
            fetch('/favorites/' + (isFavorited ? 'remove' : 'add'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ profile_id: profileId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.toggle('active');
                    this.querySelector('i').classList.toggle('bi-heart');
                    this.querySelector('i').classList.toggle('bi-heart-fill');
                    
                    // Update favorite count in stats if it exists
                    const favoritedByCount = document.querySelector('.favorited-by-count');
                    if (favoritedByCount) {
                        let count = parseInt(favoritedByCount.textContent);
                        favoritedByCount.textContent = isFavorited ? count - 1 : count + 1;
                    }
                } else {
                    alert(data.message || 'An error occurred. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    });
</script>
<?php $this->endSection(); ?>