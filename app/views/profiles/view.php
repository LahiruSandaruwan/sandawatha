<?php
// Profile Header
?>
<div class="container py-4">
    <div class="row">
        <!-- Profile Photo and Basic Info -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="position-relative">
                    <img src="<?= $profile['profile_photo'] ? UPLOAD_URL . $profile['profile_photo'] : BASE_URL . '/assets/images/default-profile.jpg' ?>" 
                         class="card-img-top profile-image" alt="Profile Photo">
                    <?php if (isset($profile['online_status']) && $profile['online_status'] === 'online'): ?>
                        <span class="badge bg-success position-absolute top-0 end-0 m-2">Online</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h4 class="card-title mb-1"><?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?></h4>
                    <p class="text-muted mb-2">
                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($profile['district']) ?>
                        <?php if (isset($profile['city'])): ?>
                            , <?= htmlspecialchars($profile['city']) ?>
                        <?php endif; ?>
                    </p>
                    
                    <!-- Action Buttons -->
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== $profile['user_id']): ?>
                        <div class="d-grid gap-2">
                            <?php if ($contact_status === 'accepted'): ?>
                                <a href="<?= BASE_URL ?>/messages/<?= $profile['user_id'] ?>" class="btn btn-success">
                                    <i class="bi bi-chat"></i> Send Message
                                </a>
                            <?php elseif ($contact_status === 'pending'): ?>
                                <button class="btn btn-warning" disabled>
                                    <i class="bi bi-clock"></i> Contact Request Pending
                                </button>
                            <?php else: ?>
                                <button class="btn btn-primary" onclick="sendContactRequest(<?= $profile['user_id'] ?>)">
                                    <i class="bi bi-envelope"></i> Send Contact Request
                                </button>
                            <?php endif; ?>
                            
                            <button class="btn <?= $is_favorite ? 'btn-danger' : 'btn-outline-danger' ?>" 
                                    onclick="toggleFavorite(<?= $profile['user_id'] ?>)">
                                <i class="bi bi-heart<?= $is_favorite ? '-fill' : '' ?>"></i> 
                                <?= $is_favorite ? 'Remove from Favorites' : 'Add to Favorites' ?>
                            </button>
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
                        <li class="mb-2">
                            <i class="bi bi-calendar me-2"></i> <?= $profile['age'] ?> years
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-gender-ambiguous me-2"></i> <?= htmlspecialchars($profile['gender']) ?>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-heart me-2"></i> <?= htmlspecialchars($profile['marital_status']) ?>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-rulers me-2"></i> <?= htmlspecialchars($profile['height_cm']) ?> cm
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-book me-2"></i> <?= htmlspecialchars($profile['religion']) ?>
                            <?php if (isset($profile['caste'])): ?>
                                - <?= htmlspecialchars($profile['caste']) ?>
                            <?php endif; ?>
                        </li>
                        <li>
                            <i class="bi bi-person-badge me-2"></i> Member since <?= date('F Y', strtotime($profile['member_since'])) ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Profile Details -->
        <div class="col-lg-8">
            <!-- About Me -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">About Me</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($profile['bio'])): ?>
                        <p class="card-text"><?= nl2br(htmlspecialchars($profile['bio'])) ?></p>
                    <?php else: ?>
                        <p class="text-muted">No bio available.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Education & Career -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Education & Career</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Education</h6>
                            <p><?= htmlspecialchars($profile['education']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Occupation</h6>
                            <p><?= isset($profile['occupation']) ? htmlspecialchars($profile['occupation']) : 'Not specified' ?></p>
                            
                            <?php if (isset($profile['income_lkr'])): ?>
                                <h6>Monthly Income</h6>
                                <p>LKR <?= number_format($profile['income_lkr']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Goals & Preferences -->
            <?php if (isset($profile['goals']) || isset($profile['wants_migration']) || isset($profile['career_focused']) || isset($profile['wants_early_marriage'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Goals & Preferences</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($profile['goals'])): ?>
                        <h6>Life Goals</h6>
                        <p><?= nl2br(htmlspecialchars($profile['goals'])) ?></p>
                    <?php endif; ?>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi <?= $profile['wants_migration'] ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?> me-2"></i>
                                <span>Interested in Migration</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi <?= $profile['career_focused'] ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?> me-2"></i>
                                <span>Career Focused</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi <?= $profile['wants_early_marriage'] ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?> me-2"></i>
                                <span>Prefers Early Marriage</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Horoscope -->
            <?php if (isset($profile['horoscope_file'])): ?>
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
    <?php if (!empty($similar_profiles)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <h4 class="mb-4">Similar Profiles</h4>
            <div class="row">
                <?php foreach ($similar_profiles as $similar): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <img src="<?= $similar['profile_photo'] ? UPLOAD_URL . $similar['profile_photo'] : BASE_URL . '/assets/images/default-profile.jpg' ?>" 
                                 class="card-img-top" alt="Profile Photo">
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