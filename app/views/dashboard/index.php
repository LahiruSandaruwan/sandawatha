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
                        <h3 class="mb-0"><?= $profile['view_count'] ?? 0 ?></h3>
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
                                        <span class="badge bg-danger"><i class="bi bi-x"></i></span>
                                    <?php endif; ?>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Bio Description
                                    <?php if ($profile && $profile['bio']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x"></i></span>
                                    <?php endif; ?>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Video Introduction
                                    <?php if ($profile && $profile['video_intro']): ?>
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
                            <div class="d-flex align-items-center p-3 border-bottom">
                                <img src="<?= $request['profile_photo'] ? UPLOAD_URL . $request['profile_photo'] : BASE_URL . '/assets/images/default-profile.jpg' ?>" 
                                     alt="Profile" class="rounded-circle me-3" width="50" height="50">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?></h6>
                                    <small class="text-muted"><?= $request['age'] ?> years • <?= htmlspecialchars($request['district']) ?></small>
                                    <?php if ($request['message']): ?>
                                        <p class="small text-muted mb-0"><?= htmlspecialchars(substr($request['message'], 0, 100)) ?>...</p>
                                    <?php endif; ?>
                                </div>
                                <div class="btn-group-vertical">
                                    <button class="btn btn-sm btn-success" onclick="respondToRequest(<?= $request['id'] ?>, 'accepted')">
                                        <i class="bi bi-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="respondToRequest(<?= $request['id'] ?>, 'rejected')">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Suggested Matches -->
            <?php if (!empty($suggested_matches)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-heart-arrow"></i> Suggested Matches</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($suggested_matches as $match): ?>
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <img src="<?= $match['profile_photo'] ? UPLOAD_URL . $match['profile_photo'] : BASE_URL . '/assets/images/default-profile.jpg' ?>" 
                                                 alt="Profile" class="rounded-circle me-3" width="60" height="60">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= htmlspecialchars($match['first_name']) ?></h6>
                                                <small class="text-muted"><?= $match['age'] ?> years • <?= htmlspecialchars($match['district']) ?></small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <a href="<?= BASE_URL ?>/profile/<?= $match['id'] ?>" class="btn btn-sm btn-primary me-2">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" onclick="toggleFavorite(<?= $match['user_id'] ?>)">
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
            <!-- Premium Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-star"></i> Membership Status</h5>
                </div>
                <div class="card-body">
                    <?php if ($premium_membership): ?>
                        <div class="text-center">
                            <span class="badge premium-badge fs-6 mb-2"><?= ucfirst($premium_membership['plan_type']) ?> Member</span>
                            <p class="text-muted">Valid until <?= date('M d, Y', strtotime($premium_membership['end_date'])) ?></p>
                            
                            <div class="premium-features">
                                <h6>Your Benefits:</h6>
                                <ul class="list-unstyled small">
                                    <li><i class="bi bi-check-circle text-success"></i> 
                                        <?= $premium_features['contact_requests_per_day'] === 'unlimited' ? 'Unlimited' : $premium_features['contact_requests_per_day'] ?> Contact Requests/day
                                    </li>
                                    <li><i class="bi bi-check-circle text-success"></i> 
                                        <?= $premium_features['message_limit_per_day'] === 'unlimited' ? 'Unlimited' : $premium_features['message_limit_per_day'] ?> Messages/day
                                    </li>
                                    <?php if ($premium_features['can_see_who_viewed']): ?>
                                        <li><i class="bi bi-check-circle text-success"></i> See who viewed your profile</li>
                                    <?php endif; ?>
                                    <?php if ($premium_features['priority_listing']): ?>
                                        <li><i class="bi bi-check-circle text-success"></i> Priority listing in search</li>
                                    <?php endif; ?>
                                    <?php if ($premium_features['ai_matching']): ?>
                                        <li><i class="bi bi-check-circle text-success"></i> AI-powered matching</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <i class="bi bi-star display-4 text-muted mb-3"></i>
                            <h6>Upgrade to Premium</h6>
                            <p class="text-muted small">Get more matches, unlimited messages, and priority support.</p>
                            <a href="<?= BASE_URL ?>/premium" class="btn btn-warning">
                                <i class="bi bi-star"></i> Upgrade Now
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/browse" class="btn btn-primary">
                            <i class="bi bi-search"></i> Browse Profiles
                        </a>
                        <a href="<?= BASE_URL ?>/favorites" class="btn btn-outline-primary">
                            <i class="bi bi-heart"></i> My Favorites (<?= $favorite_stats['my_favorites'] ?? 0 ?>)
                        </a>
                        <a href="<?= BASE_URL ?>/messages" class="btn btn-outline-primary">
                            <i class="bi bi-envelope"></i> Messages
                        </a>
                        <a href="<?= BASE_URL ?>/profile/edit" class="btn btn-outline-secondary">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile Views Chart -->
            <?php if (!empty($profile_views)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Profile Views (30 days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="viewsChart" height="200"></canvas>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function respondToRequest(requestId, status) {
    if (!confirm(`Are you sure you want to ${status} this request?`)) return;
    
    $.post('<?= BASE_URL ?>/contact-request/respond', {
        request_id: requestId,
        status: status,
        csrf_token: '<?= $csrf_token ?? '' ?>'
    })
    .done(function(response) {
        location.reload();
    })
    .fail(function() {
        alert('Failed to respond to request. Please try again.');
    });
}

// Profile Views Chart
<?php if (!empty($profile_views)): ?>
const viewsData = <?= json_encode(array_reverse($profile_views)) ?>;
const ctx = document.getElementById('viewsChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: viewsData.map(item => item.date),
        datasets: [{
            label: 'Views',
            data: viewsData.map(item => item.views),
            borderColor: 'rgb(13, 110, 253)',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
<?php endif; ?>
</script>