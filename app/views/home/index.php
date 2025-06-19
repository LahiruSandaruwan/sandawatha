<?php $this->startSection('content'); ?>
<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4 fade-in">
                        Find Your Perfect <span class="text-warning">Life Partner</span>
                    </h1>
                    <p class="lead mb-4 fade-in">
                        Join thousands of verified profiles on Sri Lanka's most trusted matrimonial platform. 
                        Experience AI-powered matching, horoscope compatibility, and secure communication.
                    </p>
                    
                    <div class="hero-stats row text-center mb-4 fade-in">
                        <div class="col-4">
                            <h3 class="fw-bold text-warning"><?= number_format($stats['total_profiles']) ?>+</h3>
                            <small>Verified Profiles</small>
                        </div>
                        <div class="col-4">
                            <h3 class="fw-bold text-warning"><?= $stats['success_stories'] ?>+</h3>
                            <small>Success Stories</small>
                        </div>
                        <div class="col-4">
                            <h3 class="fw-bold text-warning"><?= $stats['marriages_facilitated'] ?>+</h3>
                            <small>Marriages</small>
                        </div>
                    </div>
                    
                    <div class="hero-buttons fade-in">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="<?= BASE_URL ?>/register" class="btn btn-warning btn-lg me-3">
                                <i class="bi bi-person-plus"></i> Join Free
                            </a>
                            <a href="<?= BASE_URL ?>/browse" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-search"></i> Browse Profiles
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/browse" class="btn btn-warning btn-lg me-3">
                                <i class="bi bi-search"></i> Find Matches
                            </a>
                            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="hero-image text-center slide-in-left">
                    <div class="position-relative">
                        <img src="<?= BASE_URL ?>/assets/images/happy-couple.png"
                             alt="Happy Couple" class="img-fluid rounded-3 shadow-lg"
                             style="max-height: 500px; object-fit: cover;"
                             onerror="this.onerror=null;this.src='https://via.placeholder.com/500x400?text=Happy+Couple';">
                        
                        <!-- Floating testimonial -->
                        <div class="testimonial-float position-absolute bottom-0 start-0 bg-white text-dark p-3 rounded-3 shadow-lg m-3" style="max-width: 250px;">
                            <div class="d-flex align-items-center mb-2">
                                <img src="<?= BASE_URL ?>/assets/images/c1.png"
                                     alt="User" class="rounded-circle me-2" width="40" height="40"
                                     onerror="this.onerror=null;this.src='https://via.placeholder.com/40?text=User';">
                                <div>
                                    <h6 class="mb-0">Priya & Kasun</h6>
                                    <small class="text-muted">Married 2023</small>
                                </div>
                            </div>
                            <p class="small mb-0">"We found each other through Sandawatha.lk. The AI matching was amazing!"</p>
                            <div class="text-warning">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">Why Choose Sandawatha.lk?</h2>
            <p class="text-muted">Advanced features designed to help you find your perfect match</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card text-center p-4 h-100">
                    <div class="feature-icon bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-shield-check" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Verified Profiles</h5>
                    <p class="text-muted">All profiles are manually verified for authenticity. Connect with genuine people looking for serious relationships.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card text-center p-4 h-100">
                    <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-robot" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-3">AI-Powered Matching</h5>
                    <p class="text-muted">Our advanced AI analyzes compatibility based on personality, values, and preferences to suggest the best matches.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card text-center p-4 h-100">
                    <div class="feature-icon bg-warning text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-moon-stars" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Horoscope Matching</h5>
                    <p class="text-muted">Traditional horoscope compatibility analysis combined with modern matchmaking for the perfect balance.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Profiles Section -->
<?php if (!empty($recent_profiles)): ?>
<section class="recent-profiles-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">New Members</h2>
            <p class="text-muted">Recently joined verified profiles looking for their perfect match</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($recent_profiles as $profile): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card profile-card h-100">
                        <div class="position-relative">
                            <?php if ($profile['profile_photo']): ?>
                                <img src="<?= UPLOAD_URL . $profile['profile_photo'] ?>" 
                                     class="card-img-top profile-image" alt="Profile">
                            <?php else: ?>
                                <div class="card-img-top profile-image d-flex align-items-center justify-content-center bg-light" style="height: 250px;">
                                    <i class="bi bi-person-circle text-muted" style="font-size: 5rem;"></i>
                                </div>
                            <?php endif; ?>
                            <span class="badge bg-success position-absolute top-0 end-0 m-2">New</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($profile['first_name']) ?></h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    <?= $profile['age'] ?> years • <?= htmlspecialchars($profile['district']) ?><br>
                                    <?= htmlspecialchars($profile['religion']) ?> • <?= htmlspecialchars($profile['education']) ?>
                                </small>
                            </p>
                            <p class="card-text"><?= htmlspecialchars(substr($profile['bio'] ?? 'No bio available.', 0, 100)) ?>...</p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="<?= BASE_URL ?>/profile/<?= $profile['user_id'] ?>" class="btn btn-primary w-100">
                                <i class="bi bi-eye"></i> View Profile
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/browse" class="btn btn-outline-primary btn-lg">
                <i class="bi bi-grid"></i> View All Profiles
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Statistics Section -->
<section class="stats-section py-5">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <span class="stat-number"><?= number_format($stats['total_profiles']) ?></span>
                    <span class="stat-label">Total Profiles</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <span class="stat-number"><?= number_format($stats['active_users']) ?></span>
                    <span class="stat-label">Active Users</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <span class="stat-number"><?= $stats['success_stories'] ?></span>
                    <span class="stat-label">Success Stories</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <span class="stat-number"><?= $stats['marriages_facilitated'] ?></span>
                    <span class="stat-label">Marriages</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">Success Stories</h2>
            <p class="text-muted">Hear from couples who found their perfect match on Sandawatha.lk</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card text-center p-4">
                    <img src="<?= BASE_URL ?>/assets/images/c1.png" alt="Couple" class="rounded-circle mb-3" width="100" height="100">
                    <h5>Priya & Kasun</h5>
                    <p class="text-muted mb-3">Married 2023</p>
                    <p class="mb-3">"We found each other through Sandawatha.lk. The AI matching was amazing and helped us find our perfect match!"</p>
                    <div class="text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="testimonial-card text-center p-4">
                    <img src="<?= BASE_URL ?>/assets/images/c2.png" alt="Couple" class="rounded-circle mb-3" width="100" height="100">
                    <h5>Samantha & Nuwan</h5>
                    <p class="text-muted mb-3">Married 2022</p>
                    <p class="mb-3">"The horoscope matching feature was very helpful. We're so happy we found each other here!"</p>
                    <div class="text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="testimonial-card text-center p-4">
                    <img src="<?= BASE_URL ?>/assets/images/c3.png" alt="Couple" class="rounded-circle mb-3" width="100" height="100">
                    <h5>Dilini & Chamara</h5>
                    <p class="text-muted mb-3">Married 2023</p>
                    <p class="mb-3">"The verification process made us feel safe. We found our perfect match and couldn't be happier!"</p>
                    <div class="text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 text-center text-lg-start">
        <h2 class="fw-bold mb-3">Ready to Find Your Perfect Match?</h2>
                <p class="lead mb-lg-0">Join thousands of happy couples who found their life partner on Sandawatha.lk</p>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
        <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>/register" class="btn btn-warning btn-lg">
                        <i class="bi bi-person-plus"></i> Join Free Today
            </a>
        <?php else: ?>
                    <a href="<?= BASE_URL ?>/browse" class="btn btn-warning btn-lg">
                        <i class="bi bi-search"></i> Find Matches
            </a>
        <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>

<?php $this->startSection('scripts'); ?>
<script>
    // Fade in animations
    document.addEventListener('DOMContentLoaded', function() {
        const fadeElements = document.querySelectorAll('.fade-in');
        fadeElements.forEach((element, index) => {
            setTimeout(() => {
                element.style.opacity = '1';
            }, index * 200);
        });
    });
</script>
<?php $this->endSection(); ?>