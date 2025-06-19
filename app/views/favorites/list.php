<div class="container py-5">
    <div class="row">
        <!-- Main favorites list -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">My Favorites (<?= htmlspecialchars($favorite_count ?? 0) ?>)</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($favorites)): ?>
                        <div class="text-center py-5">
                            <img src="/assets/images/empty-favorites.svg" alt="No favorites" class="img-fluid mb-3" style="max-width: 200px;">
                            <h5>No Favorites Yet</h5>
                            <p class="text-muted">Start browsing profiles and add people you're interested in to your favorites!</p>
                            <a href="/browse" class="btn btn-primary">Browse Profiles</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($favorites as $profile): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="position-relative">
                                            <img src="<?= htmlspecialchars($profile['profile_photo'] ?? '/assets/images/default-avatar.svg') ?>" 
                                                 class="card-img-top" alt="Profile photo" 
                                                 style="height: 200px; object-fit: cover;">
                                            <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-favorite" 
                                                    data-profile-id="<?= htmlspecialchars($profile['id'] ?? '') ?>"
                                                    data-csrf="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                                <i class="fas fa-heart-broken"></i>
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title mb-1">
                                                <a href="/profile/<?= htmlspecialchars($profile['id'] ?? '') ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($profile['first_name'] ?? '') ?> 
                                                    <?= htmlspecialchars($profile['last_name'] ?? '') ?>
                                                </a>
                                                <?php if (isset($profile['id']) && !empty($mutual_favorites) && in_array($profile['id'], array_column($mutual_favorites, 'id'))): ?>
                                                    <span class="badge bg-success ms-2" title="Mutual favorite">
                                                        <i class="fas fa-heart"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </h5>
                                            <p class="text-muted small mb-2">
                                                <?= htmlspecialchars($profile['age'] ?? '') ?> years • 
                                                <?= htmlspecialchars($profile['city'] ?? '') ?>
                                            </p>
                                            <p class="card-text small mb-2">
                                                <?= htmlspecialchars(substr($profile['bio'] ?? '', 0, 100)) ?>...
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <a href="/profile/<?= htmlspecialchars($profile['id'] ?? '') ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    View Profile
                                                </a>
                                                <a href="/messages/view/<?= htmlspecialchars($profile['id'] ?? '') ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-comment-dots"></i> Message
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if (($favorite_count ?? 0) > 20): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php
                                    $total_pages = ceil(($favorite_count ?? 0) / 20);
                                    for ($i = 1; $i <= $total_pages; $i++):
                                    ?>
                                        <li class="page-item <?= ($current_page ?? 1) == $i ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Who favorited me -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Who Favorited Me</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($who_favorited_me)): ?>
                        <p class="text-muted text-center mb-0">No one has favorited you yet</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($who_favorited_me as $admirer): ?>
                                <a href="/profile/<?= htmlspecialchars($admirer['id'] ?? '') ?>" 
                                   class="list-group-item list-group-item-action d-flex align-items-center">
                                    <img src="<?= htmlspecialchars($admirer['profile_photo'] ?? '/assets/images/default-avatar.svg') ?>" 
                                         class="rounded-circle me-3" 
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($admirer['first_name'] ?? '') ?></h6>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($admirer['age'] ?? '') ?> years • 
                                            <?= htmlspecialchars($admirer['city'] ?? '') ?>
                                        </small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Favorite Stats</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="fas fa-heart text-danger"></i>
                            <strong>Total Favorites:</strong> <?= htmlspecialchars($stats['total_favorites'] ?? 0) ?>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-heart text-success"></i>
                            <strong>Mutual Favorites:</strong> <?= htmlspecialchars($stats['mutual_favorites'] ?? 0) ?>
                        </li>
                        <li>
                            <i class="fas fa-users text-info"></i>
                            <strong>Who Favorited Me:</strong> <?= htmlspecialchars($stats['favorited_by'] ?? 0) ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for handling favorite removal -->
<script>
document.querySelectorAll('.remove-favorite').forEach(button => {
    button.addEventListener('click', async (e) => {
        if (!confirm('Are you sure you want to remove this profile from your favorites?')) {
            return;
        }
        
        const profileId = e.target.closest('.remove-favorite').dataset.profileId;
        const csrfToken = e.target.closest('.remove-favorite').dataset.csrf;
        
        try {
            const response = await fetch('/favorites/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `profile_id=${profileId}&csrf_token=${csrfToken}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Remove the card from the UI
                e.target.closest('.col-md-6').remove();
                // Optionally reload the page to update counts
                location.reload();
            } else {
                alert(data.message || 'Failed to remove from favorites');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while removing from favorites');
        }
    });
});
</script> 