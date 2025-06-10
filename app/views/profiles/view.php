<?php
// Profile Header
?>
<div class="container py-4">
    <div class="row">
        <!-- Profile Photo and Basic Info -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="position-relative">
                    <?php 
                    $canViewPhoto = true;
                    $privacySettings = json_decode($profile['privacy_settings'] ?? '{}', true);
                    $photoPrivacy = $privacySettings['photo'] ?? 'public';
                    
                    // Check photo privacy
                    if ($photoPrivacy === 'private' && (!isset($contact_status) || $contact_status !== 'accepted')) {
                        $canViewPhoto = false;
                    } elseif ($photoPrivacy === 'registered' && !isset($_SESSION['user_id'])) {
                        $canViewPhoto = false;
                    }
                    
                    if ($canViewPhoto && !empty($profile['profile_photo'])): ?>
                        <img src="<?= UPLOAD_URL . $profile['profile_photo'] ?>" 
                             class="card-img-top profile-image" 
                             alt="Profile Photo"
                             style="height: 300px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top profile-image d-flex align-items-center justify-content-center bg-light" 
                             style="height: 300px;">
                            <div class="text-center">
                                <i class="bi bi-person-circle text-muted" style="font-size: 6rem;"></i>
                                <?php if (!$canViewPhoto): ?>
                                    <p class="text-muted mt-2 mb-0">
                                        <i class="bi bi-lock"></i> 
                                        <?php if ($photoPrivacy === 'private'): ?>
                                            Photo visible after connecting
                                        <?php else: ?>
                                            Photo visible to registered users
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($profile['online_status']) && $profile['online_status'] === 'online'): ?>
                        <span class="badge bg-success position-absolute top-0 end-0 m-2">Online</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h4 class="card-title mb-1"><?= htmlspecialchars($profile['first_name'] ?? '') . ' ' . htmlspecialchars($profile['last_name'] ?? '') ?></h4>
                    <p class="text-muted mb-2">
                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($profile['district'] ?? '') ?>
                        <?php if (!empty($profile['city'])): ?>
                            , <?= htmlspecialchars($profile['city']) ?>
                        <?php endif; ?>
                    </p>
                    
                    <!-- Action Buttons -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_id'] !== $profile['user_id']): ?>
                        <div class="d-grid gap-2">
                                <?php if (isset($contact_status)): ?>
                            <?php if ($contact_status === 'accepted'): ?>
                                        <button class="btn btn-success" disabled>
                                            <i class="bi bi-check-circle"></i> Connected
                                        </button>
                            <?php elseif ($contact_status === 'pending'): ?>
                                <button class="btn btn-warning" disabled>
                                            <i class="bi bi-clock"></i> Request Pending
                                </button>
                            <?php else: ?>
                                        <button class="btn btn-primary" onclick="sendContactRequest(<?= $profile['user_id'] ?>)">
                                            <i class="bi bi-person-plus"></i> Send Contact Request
                                </button>
                                    <?php endif; ?>
                            <?php endif; ?>
                            
                            <button class="btn <?= $is_favorite ? 'btn-danger' : 'btn-outline-danger' ?> toggle-favorite" 
                                    data-user-id="<?= $profile['user_id'] ?>" 
                                    data-csrf="<?= $csrf_token ?>">
                                <i class="bi bi-heart<?= $is_favorite ? '-fill' : '' ?>"></i> 
                                <?= $is_favorite ? 'Remove from Favorites' : 'Add to Favorites' ?>
                            </button>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="d-grid gap-2">
                            <a href="<?= BASE_URL ?>/login" class="btn btn-primary">
                                <i class="bi bi-person"></i> Log in to Connect
                            </a>
                            <a href="<?= BASE_URL ?>/register" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus"></i> Register to View More
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Basic Information Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <?php if (isset($profile['age'])): ?>
                        <li class="mb-2">
                            <i class="bi bi-calendar me-2"></i> <?= $profile['age'] ?> years
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($profile['gender'])): ?>
                        <li class="mb-2">
                            <i class="bi bi-gender-ambiguous me-2"></i> <?= htmlspecialchars($profile['gender']) ?>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($profile['marital_status'])): ?>
                        <li class="mb-2">
                            <i class="bi bi-heart me-2"></i> <?= htmlspecialchars($profile['marital_status']) ?>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($profile['height_cm'])): ?>
                        <li class="mb-2">
                            <i class="bi bi-rulers me-2"></i> <?= htmlspecialchars($profile['height_cm']) ?> cm
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($profile['religion'])): ?>
                        <li class="mb-2">
                            <i class="bi bi-book me-2"></i> <?= htmlspecialchars($profile['religion']) ?>
                            <?php if (!empty($profile['caste'])): ?>
                                - <?= htmlspecialchars($profile['caste']) ?>
                            <?php endif; ?>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($profile['member_since'])): ?>
                        <li>
                            <i class="bi bi-person-badge me-2"></i> Member since <?= date('F Y', strtotime($profile['member_since'])) ?>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Profile Details -->
        <div class="col-lg-8">
            <!-- About Me -->
            <?php if (isset($profile['bio'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">About Me</h5>
                </div>
                <div class="card-body">
                        <?php if ($profile['bio']): ?>
                        <p class="card-text"><?= nl2br(htmlspecialchars($profile['bio'])) ?></p>
                    <?php else: ?>
                        <p class="text-muted">No bio available.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Education & Career -->
            <?php if (isset($profile['education']) || isset($profile['occupation']) || isset($profile['income_lkr'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Education & Career</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                            <?php if (isset($profile['education'])): ?>
                        <div class="col-md-6">
                            <h6>Education</h6>
                                    <p><?= $profile['education'] ? htmlspecialchars($profile['education']) : 'Not specified' ?></p>
                        </div>
                            <?php endif; ?>
                            <?php if (isset($profile['occupation']) || isset($profile['income_lkr'])): ?>
                        <div class="col-md-6">
                                    <?php if (isset($profile['occupation'])): ?>
                            <h6>Occupation</h6>
                                        <p><?= $profile['occupation'] ? htmlspecialchars($profile['occupation']) : 'Not specified' ?></p>
                                    <?php endif; ?>
                            
                            <?php if (isset($profile['income_lkr']) && $profile['income_lkr']): ?>
                                <h6>Monthly Income</h6>
                                <p>LKR <?= number_format($profile['income_lkr']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Goals & Preferences -->
            <?php if (isset($profile['goals']) || isset($profile['wants_migration']) || isset($profile['career_focused']) || isset($profile['wants_early_marriage'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Goals & Preferences</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($profile['goals']) && $profile['goals']): ?>
                        <h6>Life Goals</h6>
                        <p><?= nl2br(htmlspecialchars($profile['goals'])) ?></p>
                    <?php endif; ?>
                    
                    <div class="row mt-3">
                        <?php if (isset($profile['wants_migration'])): ?>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi <?= $profile['wants_migration'] ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?> me-2"></i>
                                <span>Interested in Migration</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($profile['career_focused'])): ?>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi <?= $profile['career_focused'] ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?> me-2"></i>
                                <span>Career Focused</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($profile['wants_early_marriage'])): ?>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi <?= $profile['wants_early_marriage'] ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?> me-2"></i>
                                <span>Prefers Early Marriage</span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Horoscope -->
            <?php if (isset($profile['horoscope_file']) && $profile['horoscope_file']): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Horoscope</h5>
                </div>
                <div class="card-body">
                    <a href="<?= UPLOAD_URL . $profile['horoscope_file'] ?>" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-file-earmark-text"></i> View Horoscope
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Similar Profiles -->
    <?php if (isset($_SESSION['user_id']) && !empty($similar_profiles)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <h4 class="mb-4">Similar Profiles</h4>
            <div class="row">
                <?php foreach ($similar_profiles as $similar): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <?php $similarPhotoUrl = $similar['profile_photo'] ? UPLOAD_URL . $similar['profile_photo'] : BASE_URL . '/assets/images/default-profile.jpg'; ?>
                            <img src="<?= $similarPhotoUrl ?>" class="card-img-top" alt="Profile Photo">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($similar['first_name']) ?></h5>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <?= $similar['age'] ?> years • <?= htmlspecialchars($similar['district']) ?>
                                    </small>
                                </p>
                                <a href="<?= BASE_URL ?>/profile/<?= $similar['user_id'] ?>" class="btn btn-primary btn-sm">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
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
<?php endif; ?>

<!-- JavaScript for handling contact requests and favorites -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Send Contact Request
    document.querySelectorAll('.send-contact-request').forEach(button => {
        button.addEventListener('click', async function() {
            const userId = this.dataset.userId;
            const csrfToken = this.dataset.csrf;
            
            try {
                const response = await fetch(`${BASE_URL}/contact-request/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}&csrf_token=${csrfToken}`
                });
                
                const result = await response.json();
                if (result.success) {
                    this.classList.replace('btn-primary', 'btn-warning');
                    this.disabled = true;
                    this.innerHTML = '<i class="bi bi-clock"></i> Contact Request Pending';
                    showToast('success', 'Contact request sent successfully');
                } else {
                    showToast('error', result.message || 'Failed to send contact request');
                }
            } catch (error) {
                showToast('error', 'An error occurred. Please try again.');
            }
        });
    });
    
    // Toggle Favorite
    document.querySelectorAll('.toggle-favorite').forEach(button => {
        button.addEventListener('click', async function() {
            const userId = this.dataset.userId;
            const csrfToken = this.dataset.csrf;
            const isFavorite = this.classList.contains('btn-danger');
            
            try {
                const response = await fetch(`${BASE_URL}/favorites/${isFavorite ? 'remove' : 'add'}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}&csrf_token=${csrfToken}`
                });
                
                const result = await response.json();
                if (result.success) {
                    if (isFavorite) {
                        this.classList.replace('btn-danger', 'btn-outline-danger');
                        this.querySelector('i').classList.replace('bi-heart-fill', 'bi-heart');
                        this.innerHTML = '<i class="bi bi-heart"></i> Add to Favorites';
                    } else {
                        this.classList.replace('btn-outline-danger', 'btn-danger');
                        this.querySelector('i').classList.replace('bi-heart', 'bi-heart-fill');
                        this.innerHTML = '<i class="bi bi-heart-fill"></i> Remove from Favorites';
                    }
                    showToast('success', result.message);
                } else {
                    showToast('error', result.message || 'Failed to update favorites');
                }
            } catch (error) {
                showToast('error', 'An error occurred. Please try again.');
            }
        });
    });
});
</script> 