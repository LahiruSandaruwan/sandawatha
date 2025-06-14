<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Edit Profile</h1>
            <p class="text-muted">Keep your profile updated to get better matches</p>
        </div>
    </div>

    <!-- Profile Edit Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/profile/update" method="POST" id="profileForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
                        <!-- Basic Information -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= htmlspecialchars($profile['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= htmlspecialchars($profile['last_name'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                       value="<?= htmlspecialchars($profile['date_of_birth'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" <?= ($profile['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= ($profile['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="height_cm" class="form-label">Height (cm) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="height_cm" name="height_cm" 
                                       value="<?= htmlspecialchars($profile['height_cm'] ?? '') ?>" min="100" max="250" required>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="religion" class="form-label">Religion <span class="text-danger">*</span></label>
                                <select class="form-select" id="religion" name="religion" required>
                                    <option value="">Select Religion</option>
                                    <?php foreach ($religions as $religion): ?>
                                        <option value="<?= htmlspecialchars($religion) ?>" 
                                                <?= ($profile['religion'] ?? '') === $religion ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($religion) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="caste" class="form-label">Caste</label>
                                <select class="form-select" id="caste" name="caste">
                                    <option value="">Select Caste</option>
                                    <?php foreach ($castes as $caste): ?>
                                        <option value="<?= htmlspecialchars($caste) ?>" 
                                                <?= ($profile['caste'] ?? '') === $caste ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($caste) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="district" class="form-label">District <span class="text-danger">*</span></label>
                                <select class="form-select" id="district" name="district" required>
                                    <option value="">Select District</option>
                                    <?php foreach ($districts as $district): ?>
                                        <option value="<?= htmlspecialchars($district) ?>" 
                                                <?= ($profile['district'] ?? '') === $district ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($district) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?= htmlspecialchars($profile['city'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Education & Career -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="education" class="form-label">Education Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="education" name="education" required>
                                    <option value="">Select Education</option>
                                    <?php foreach ($education_levels as $level): ?>
                                        <option value="<?= htmlspecialchars($level) ?>" 
                                                <?= ($profile['education'] ?? '') === $level ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($level) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="occupation" class="form-label">Occupation</label>
                                <input type="text" class="form-control" id="occupation" name="occupation" 
                                       value="<?= htmlspecialchars($profile['occupation'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="income_lkr" class="form-label">Monthly Income (LKR)</label>
                                <input type="number" class="form-control" id="income_lkr" name="income_lkr" 
                                       value="<?= htmlspecialchars($profile['income_lkr'] ?? '') ?>" min="0">
                            </div>
                            <div class="col-md-6">
                                <label for="marital_status" class="form-label">Marital Status</label>
                                <select class="form-select" id="marital_status" name="marital_status">
                                    <option value="">Select Status</option>
                                    <option value="single" <?= ($profile['marital_status'] ?? '') === 'single' ? 'selected' : '' ?>>Single</option>
                                    <option value="never_married" <?= ($profile['marital_status'] ?? '') === 'never_married' ? 'selected' : '' ?>>Never Married</option>
                                    <option value="divorced" <?= ($profile['marital_status'] ?? '') === 'divorced' ? 'selected' : '' ?>>Divorced</option>
                                    <option value="separated" <?= ($profile['marital_status'] ?? '') === 'separated' ? 'selected' : '' ?>>Separated</option>
                                    <option value="widowed" <?= ($profile['marital_status'] ?? '') === 'widowed' ? 'selected' : '' ?>>Widowed</option>
                                    <option value="annulled" <?= ($profile['marital_status'] ?? '') === 'annulled' ? 'selected' : '' ?>>Annulled</option>
                                </select>
                            </div>
                        </div>

                        <!-- About Me -->
                        <div class="mb-4">
                            <label for="bio" class="form-label">About Me</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" 
                                      placeholder="Tell us about yourself..."><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                        </div>

                        <!-- Goals and Preferences -->
                        <div class="mb-4">
                            <label for="goals" class="form-label">Goals & Preferences</label>
                            <textarea class="form-control" id="goals" name="goals" rows="3" 
                                      placeholder="What are your goals and what are you looking for in a partner?"><?= htmlspecialchars($profile['goals'] ?? '') ?></textarea>
                        </div>

                        <!-- Preference Checkboxes -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="wants_migration" name="wants_migration" value="1" 
                                           <?= (!empty($profile['wants_migration'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="wants_migration">
                                        Open to Migration
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="career_focused" name="career_focused" value="1" 
                                           <?= (!empty($profile['career_focused'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="career_focused">
                                        Career Focused
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="wants_early_marriage" name="wants_early_marriage" value="1" 
                                           <?= (!empty($profile['wants_early_marriage'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="wants_early_marriage">
                                        Wants Early Marriage
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Privacy Settings</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#privacyHelpModal">
                        <i class="bi bi-question-circle"></i> Help
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Control who can see your profile information</p>
                    
                    <?php
                    $privacySettings = json_decode($profile['privacy_settings'] ?? '{}', true) ?? [
                        'default' => 'registered',
                        'photo' => 'registered',
                        'contact' => 'private',
                        'horoscope' => 'private',
                        'income' => 'private',
                        'bio' => 'registered',
                        'education' => 'registered',
                        'occupation' => 'registered',
                        'goals' => 'registered'
                    ];
                    ?>

                    <form id="privacySettingsForm">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Default Profile Visibility</label>
                            <select name="default_privacy" class="form-select">
                                <option value="public" <?= ($privacySettings['default'] ?? 'registered') === 'public' ? 'selected' : '' ?>>Public - Anyone can view</option>
                                <option value="registered" <?= ($privacySettings['default'] ?? 'registered') === 'registered' ? 'selected' : '' ?>>Registered Users Only</option>
                                <option value="private" <?= ($privacySettings['default'] ?? 'registered') === 'private' ? 'selected' : '' ?>>Private - Only Connected Users</option>
                            </select>
                            <div class="form-text">This is your profile's default visibility setting.</div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%">Information</th>
                                        <th>Visibility Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="fw-medium">Profile Photo</div>
                                            <small class="text-muted">Your main profile picture</small>
                                        </td>
                                        <td>
                                            <select name="photo_privacy" class="form-select form-select-sm">
                                                <option value="public" <?= ($privacySettings['photo'] ?? 'registered') === 'public' ? 'selected' : '' ?>>Public</option>
                                                <option value="registered" <?= ($privacySettings['photo'] ?? 'registered') === 'registered' ? 'selected' : '' ?>>Registered Users</option>
                                                <option value="private" <?= ($privacySettings['photo'] ?? 'registered') === 'private' ? 'selected' : '' ?>>Connected Only</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-medium">Contact Information</div>
                                            <small class="text-muted">Email and phone number</small>
                                        </td>
                                        <td>
                                            <select name="contact_privacy" class="form-select form-select-sm">
                                                <option value="registered" <?= ($privacySettings['contact'] ?? 'private') === 'registered' ? 'selected' : '' ?>>Registered Users</option>
                                                <option value="private" <?= ($privacySettings['contact'] ?? 'private') === 'private' ? 'selected' : '' ?>>Connected Only</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-medium">Horoscope</div>
                                            <small class="text-muted">Your uploaded horoscope details</small>
                                        </td>
                                        <td>
                                            <select name="horoscope_privacy" class="form-select form-select-sm">
                                                <option value="registered" <?= ($privacySettings['horoscope'] ?? 'private') === 'registered' ? 'selected' : '' ?>>Registered Users</option>
                                                <option value="private" <?= ($privacySettings['horoscope'] ?? 'private') === 'private' ? 'selected' : '' ?>>Connected Only</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-medium">Income Details</div>
                                            <small class="text-muted">Your monthly income information</small>
                                        </td>
                                        <td>
                                            <select name="income_privacy" class="form-select form-select-sm">
                                                <option value="registered" <?= ($privacySettings['income'] ?? 'private') === 'registered' ? 'selected' : '' ?>>Registered Users</option>
                                                <option value="private" <?= ($privacySettings['income'] ?? 'private') === 'private' ? 'selected' : '' ?>>Connected Only</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-medium">Bio</div>
                                            <small class="text-muted">Your personal description</small>
                                        </td>
                                        <td>
                                            <select name="bio_privacy" class="form-select form-select-sm">
                                                <option value="public" <?= ($privacySettings['bio'] ?? 'registered') === 'public' ? 'selected' : '' ?>>Public</option>
                                                <option value="registered" <?= ($privacySettings['bio'] ?? 'registered') === 'registered' ? 'selected' : '' ?>>Registered Users</option>
                                                <option value="private" <?= ($privacySettings['bio'] ?? 'registered') === 'private' ? 'selected' : '' ?>>Connected Only</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-medium">Education</div>
                                            <small class="text-muted">Your educational background</small>
                                        </td>
                                        <td>
                                            <select name="education_privacy" class="form-select form-select-sm">
                                                <option value="public" <?= ($privacySettings['education'] ?? 'registered') === 'public' ? 'selected' : '' ?>>Public</option>
                                                <option value="registered" <?= ($privacySettings['education'] ?? 'registered') === 'registered' ? 'selected' : '' ?>>Registered Users</option>
                                                <option value="private" <?= ($privacySettings['education'] ?? 'registered') === 'private' ? 'selected' : '' ?>>Connected Only</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-medium">Occupation</div>
                                            <small class="text-muted">Your job and career details</small>
                                        </td>
                                        <td>
                                            <select name="occupation_privacy" class="form-select form-select-sm">
                                                <option value="public" <?= ($privacySettings['occupation'] ?? 'registered') === 'public' ? 'selected' : '' ?>>Public</option>
                                                <option value="registered" <?= ($privacySettings['occupation'] ?? 'registered') === 'registered' ? 'selected' : '' ?>>Registered Users</option>
                                                <option value="private" <?= ($privacySettings['occupation'] ?? 'registered') === 'private' ? 'selected' : '' ?>>Connected Only</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-medium">Goals & Preferences</div>
                                            <small class="text-muted">Your life goals and preferences</small>
                                        </td>
                                        <td>
                                            <select name="goals_privacy" class="form-select form-select-sm">
                                                <option value="public" <?= ($privacySettings['goals'] ?? 'registered') === 'public' ? 'selected' : '' ?>>Public</option>
                                                <option value="registered" <?= ($privacySettings['goals'] ?? 'registered') === 'registered' ? 'selected' : '' ?>>Registered Users</option>
                                                <option value="private" <?= ($privacySettings['goals'] ?? 'registered') === 'private' ? 'selected' : '' ?>>Connected Only</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-shield-check"></i> Save Privacy Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Privacy Help Modal -->
            <div class="modal fade" id="privacyHelpModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Understanding Privacy Settings</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <h6 class="fw-bold">Visibility Levels:</h6>
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="bi bi-globe text-primary"></i>
                                    <strong>Public</strong>
                                    <p class="text-muted small mb-0">Anyone visiting Sandawatha.lk can see this information</p>
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-person-badge text-success"></i>
                                    <strong>Registered Users</strong>
                                    <p class="text-muted small mb-0">Only users who have created an account can see this information</p>
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-people text-warning"></i>
                                    <strong>Connected Only</strong>
                                    <p class="text-muted small mb-0">Only users who you've accepted contact requests from can see this information</p>
                                </li>
                            </ul>
                            <hr>
                            <p class="small text-muted mb-0">
                                <i class="bi bi-info-circle"></i> Your basic information like name and religion will always be visible to help others find you.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo Upload Section -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Photo</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($profile['profile_photo'])): ?>
                        <img src="<?= UPLOAD_URL . htmlspecialchars($profile['profile_photo']) ?>" 
                             alt="Profile Photo" class="img-fluid rounded mb-3" style="max-height: 200px;">
                    <?php else: ?>
                        <div class="bg-light rounded p-4 mb-3">
                            <i class="bi bi-person-circle display-1 text-muted"></i>
                            <p class="text-muted mb-0">No photo uploaded</p>
                        </div>
                    <?php endif; ?>
                    
                    <form id="photoUploadForm" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <div class="mb-3">
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-outline-primary">Upload Photo</button>
                    </form>
                </div>
            </div>

            <!-- Additional Uploads -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Additional Files</h5>
                </div>
                <div class="card-body">
                    <!-- Horoscope Upload -->
                    <div class="mb-3">
                        <label class="form-label">Horoscope</label>
                        <?php if (!empty($profile['horoscope_file'])): ?>
                            <p class="text-success small">
                                <i class="bi bi-check-circle"></i> Horoscope uploaded
                            </p>
                        <?php endif; ?>
                        <form id="horoscopeUploadForm" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="file" class="form-control mb-2" name="horoscope" accept=".pdf,.jpg,.jpeg,.png">
                            <button type="submit" class="btn btn-sm btn-outline-primary">Upload Horoscope</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Define base URL for API endpoints
const BASE_URL = '<?= BASE_URL ?>';

// Handle photo upload
document.getElementById('photoUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch(BASE_URL + '/profile/upload-photo', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Upload failed: ' + error.message);
    });
});

// Handle horoscope upload
document.getElementById('horoscopeUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch(BASE_URL + '/profile/upload-horoscope', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Upload failed: ' + error.message);
    });
});

document.getElementById('privacySettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= BASE_URL ?>/profile/privacy-settings', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('Failed to update privacy settings. Please try again.', 'error');
    });
});
</script>