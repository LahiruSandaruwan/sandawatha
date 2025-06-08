<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Browse Profiles</h1>
            <p class="text-muted">Find your perfect match from thousands of verified profiles</p>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="search-container mb-4">
        <form id="searchForm" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search by Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Enter name..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender">
                        <option value="">All</option>
                        <option value="male" <?= ($filters['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= ($filters['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="religion" class="form-label">Religion</label>
                    <select class="form-select" id="religion" name="religion">
                        <option value="">All Religions</option>
                        <option value="Buddhist" <?= ($filters['religion'] ?? '') === 'Buddhist' ? 'selected' : '' ?>>Buddhist</option>
                        <option value="Hindu" <?= ($filters['religion'] ?? '') === 'Hindu' ? 'selected' : '' ?>>Hindu</option>
                        <option value="Christian" <?= ($filters['religion'] ?? '') === 'Christian' ? 'selected' : '' ?>>Christian</option>
                        <option value="Muslim" <?= ($filters['religion'] ?? '') === 'Muslim' ? 'selected' : '' ?>>Muslim</option>
                        <option value="Other" <?= ($filters['religion'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="district" class="form-label">District</label>
                    <select class="form-select" id="district" name="district">
                        <option value="">All Districts</option>
                        <option value="Colombo" <?= ($filters['district'] ?? '') === 'Colombo' ? 'selected' : '' ?>>Colombo</option>
                        <option value="Gampaha" <?= ($filters['district'] ?? '') === 'Gampaha' ? 'selected' : '' ?>>Gampaha</option>
                        <option value="Kalutara" <?= ($filters['district'] ?? '') === 'Kalutara' ? 'selected' : '' ?>>Kalutara</option>
                        <option value="Kandy" <?= ($filters['district'] ?? '') === 'Kandy' ? 'selected' : '' ?>>Kandy</option>
                        <option value="Galle" <?= ($filters['district'] ?? '') === 'Galle' ? 'selected' : '' ?>>Galle</option>
                        <option value="Matara" <?= ($filters['district'] ?? '') === 'Matara' ? 'selected' : '' ?>>Matara</option>
                        <option value="Jaffna" <?= ($filters['district'] ?? '') === 'Jaffna' ? 'selected' : '' ?>>Jaffna</option>
                        <option value="Kurunegala" <?= ($filters['district'] ?? '') === 'Kurunegala' ? 'selected' : '' ?>>Kurunegala</option>
                        <option value="Anuradhapura" <?= ($filters['district'] ?? '') === 'Anuradhapura' ? 'selected' : '' ?>>Anuradhapura</option>
                        <option value="Badulla" <?= ($filters['district'] ?? '') === 'Badulla' ? 'selected' : '' ?>>Badulla</option>
                    </select>
                </div>
            </div>
            
            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <label for="age_min" class="form-label">Age Range</label>
                    <div class="row">
                        <div class="col-6">
                            <input type="number" class="form-control" id="age_min" name="age_min" 
                                   placeholder="Min" min="18" max="100" value="<?= htmlspecialchars($filters['age_min'] ?? '') ?>">
                        </div>
                        <div class="col-6">
                            <input type="number" class="form-control" id="age_max" name="age_max" 
                                   placeholder="Max" min="18" max="100" value="<?= htmlspecialchars($filters['age_max'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Goal-Based Filters</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="wants_migration" name="wants_migration" value="1">
                            <label class="form-check-label small" for="wants_migration">Migration</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="career_focused" name="career_focused" value="1">
                            <label class="form-check-label small" for="career_focused">Career</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="early_marriage" name="early_marriage" value="1">
                            <label class="form-check-label small" for="early_marriage">Early Marriage</label>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <a href="<?= BASE_URL ?>/browse" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="<?= BASE_URL ?>/ai-matches" class="btn btn-success">
                                <i class="bi bi-robot"></i> AI Matches
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="row mb-3">
        <div class="col-md-6">
            <p class="text-muted mb-0" id="resultsCount">
                <?= count($profiles) ?> profiles found
                <?php if (!empty($filters)): ?>
                    with your search criteria
                <?php endif; ?>
            </p>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group btn-group-sm">
                <input type="radio" class="btn-check" name="viewMode" id="gridView" checked>
                <label class="btn btn-outline-primary" for="gridView">
                    <i class="bi bi-grid"></i> Grid
                </label>
                
                <input type="radio" class="btn-check" name="viewMode" id="listView">
                <label class="btn btn-outline-primary" for="listView">
                    <i class="bi bi-list"></i> List
                </label>
            </div>
        </div>
    </div>

    <!-- Profiles Grid -->
    <div class="row g-4" id="searchResults">
        <?php if (empty($profiles)): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-search" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">No profiles found</h4>
                <p class="text-muted">Try adjusting your search criteria or browse all profiles</p>
                <a href="<?= BASE_URL ?>/browse" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise"></i> Browse All Profiles
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($profiles as $profile): ?>
                <div class="col-lg-4 col-md-6 profile-item">
                    <div class="card profile-card h-100">
                        <div class="position-relative">
                            <img src="<?= $profile['profile_photo'] ? UPLOAD_URL . $profile['profile_photo'] : BASE_URL . '/assets/images/default-profile.jpg' ?>" 
                                 class="card-img-top profile-image" alt="Profile" style="height: 250px; object-fit: cover;">
                            
                            <!-- Online Status -->
                            <?php if (isset($profile['is_online']) && $profile['is_online']): ?>
                                <div class="online-indicator"></div>
                            <?php endif; ?>
                            
                            <!-- Premium Badge -->
                            <?php if (isset($profile['is_premium']) && $profile['is_premium']): ?>
                                <span class="badge premium-badge position-absolute top-0 start-0 m-2">Premium</span>
                            <?php endif; ?>
                            
                            <!-- Compatibility Score -->
                            <?php if (isset($profile['compatibility_score']) && $profile['compatibility_score']): ?>
                                <div class="compatibility-score <?= $profile['compatibility_score'] >= 80 ? 'high' : ($profile['compatibility_score'] >= 60 ? 'medium' : 'low') ?> position-absolute top-0 end-0 m-2">
                                    <?= $profile['compatibility_score'] ?>%
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title mb-2">
                                <?= htmlspecialchars($profile['first_name']) ?>
                                <?php if ($profile['age']): ?>
                                    <small class="text-muted">, <?= $profile['age'] ?></small>
                                <?php endif; ?>
                            </h5>
                            
                            <div class="profile-details mb-3">
                                <p class="card-text mb-1">
                                    <i class="bi bi-geo-alt text-muted"></i>
                                    <?= htmlspecialchars($profile['district']) ?>
                                </p>
                                <p class="card-text mb-1">
                                    <i class="bi bi-mortarboard text-muted"></i>
                                    <?= htmlspecialchars($profile['education']) ?>
                                </p>
                                <p class="card-text mb-1">
                                    <i class="bi bi-heart text-muted"></i>
                                    <?= htmlspecialchars($profile['religion']) ?>
                                </p>
                                <?php if ($profile['height_cm']): ?>
                                    <p class="card-text mb-1">
                                        <i class="bi bi-arrows-vertical text-muted"></i>
                                        <?= $profile['height_cm'] ?> cm
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Bio Preview -->
                            <?php if ($profile['bio']): ?>
                                <p class="card-text small text-muted">
                                    <?= htmlspecialchars(substr($profile['bio'], 0, 100)) ?>
                                    <?= strlen($profile['bio']) > 100 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Goal Tags -->
                            <div class="goal-tags mb-3">
                                <?php if ($profile['wants_migration']): ?>
                                    <span class="badge bg-info">Migration</span>
                                <?php endif; ?>
                                <?php if ($profile['career_focused']): ?>
                                    <span class="badge bg-success">Career Focused</span>
                                <?php endif; ?>
                                <?php if ($profile['wants_early_marriage']): ?>
                                    <span class="badge bg-warning">Early Marriage</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100">
                                <a href="<?= BASE_URL ?>/profile/<?= $profile['user_id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye"></i> View Profile
                                </a>
                                
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button class="btn <?= isset($profile['is_favorite']) && $profile['is_favorite'] ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm" 
                                            onclick="toggleFavorite(<?= $profile['user_id'] ?>)" 
                                            data-profile-id="<?= $profile['user_id'] ?>">
                                        <i class="bi bi-heart<?= isset($profile['is_favorite']) && $profile['is_favorite'] ? '-fill' : '' ?>"></i>
                                    </button>
                                    
                                    <?php if (!isset($profile['contact_status'])): ?>
                                        <button class="btn btn-outline-primary btn-sm" onclick="sendContactRequest(<?= $profile['user_id'] ?>)">
                                            <i class="bi bi-envelope"></i>
                                        </button>
                                    <?php elseif ($profile['contact_status'] === 'pending'): ?>
                                        <button class="btn btn-warning btn-sm" disabled>
                                            <i class="bi bi-clock"></i> Pending
                                        </button>
                                    <?php elseif ($profile['contact_status'] === 'accepted'): ?>
                                        <button class="btn btn-success btn-sm" disabled>
                                            <i class="bi bi-check-circle"></i> Connected
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/login" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-heart"></i> Like
                                    </a>
                                    <a href="<?= BASE_URL ?>/login" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-envelope"></i> Contact
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (count($profiles) >= 12): ?>
    <nav aria-label="Profile pagination" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $current_page - 1 ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>
            <?php endif; ?>
            
            <?php for ($i = max(1, $current_page - 2); $i <= min($current_page + 2, ceil(count($profiles) / 12)); $i++): ?>
                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <li class="page-item">
                <a class="page-link" href="?page=<?= $current_page + 1 ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">
                    Next <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="text-center py-4" style="display: none;">
        <div class="loading-spinner mx-auto"></div>
        <p class="text-muted mt-2">Searching profiles...</p>
    </div>
</div>

<!-- Contact Request Modal -->
<div class="modal fade" id="contactRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Contact Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contactRequestForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="profile_id" id="contactProfileId">
                    
                    <div class="mb-3">
                        <label for="contactMessage" class="form-label">Message (Optional)</label>
                        <textarea class="form-control" id="contactMessage" name="message" rows="3" 
                                  placeholder="Introduce yourself and why you're interested..."></textarea>
                        <div class="form-text">A personalized message increases your chances of acceptance.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitContactRequest()">
                    <i class="bi bi-envelope"></i> Send Request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showLoadingSpinner();
    this.submit();
});

// View mode toggle
document.querySelectorAll('input[name="viewMode"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const container = document.getElementById('searchResults');
        if (this.id === 'listView') {
            container.classList.add('list-view');
        } else {
            container.classList.remove('list-view');
        }
    });
});

// Contact request functionality
function sendContactRequest(profileId) {
    <?php if (!isset($_SESSION['user_id'])): ?>
        window.location.href = '<?= BASE_URL ?>/login';
        return;
    <?php endif; ?>
    
    document.getElementById('contactProfileId').value = profileId;
    const modal = new bootstrap.Modal(document.getElementById('contactRequestModal'));
    modal.show();
}

function submitContactRequest() {
    const form = document.getElementById('contactRequestForm');
    const formData = new FormData(form);
    
    fetch('<?= BASE_URL ?>/contact-request/send', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('contactRequestModal')).hide();
            // Update button state
            const profileId = formData.get('profile_id');
            updateContactButton(profileId, 'sent');
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('Failed to send contact request. Please try again.', 'error');
    });
}

function updateContactButton(profileId, status) {
    const profileCard = document.querySelector(`[data-profile-id="${profileId}"]`).closest('.profile-card');
    const contactBtn = profileCard.querySelector('[onclick*="sendContactRequest"]');
    
    if (contactBtn && status === 'sent') {
        contactBtn.className = 'btn btn-warning btn-sm';
        contactBtn.innerHTML = '<i class="bi bi-clock"></i> Pending';
        contactBtn.onclick = null;
        contactBtn.disabled = true;
    }
}

// Auto-complete for search
let searchTimeout;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (this.value.length >= 2) {
            // Trigger search
            document.getElementById('searchForm').submit();
        }
    }, 500);
});
</script>