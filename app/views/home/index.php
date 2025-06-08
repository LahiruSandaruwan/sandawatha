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
                    <div class="feature-icon bg-info text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-camera-video" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Video Introductions</h5>
                    <p class="text-muted">Share video introductions and voice messages to make a great first impression and connect better.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card text-center p-4 h-100">
                    <div class="feature-icon bg-danger text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-heart-pulse" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Health Screening</h5>
                    <p class="text-muted">Optional health report uploads with basic risk analysis for transparent health information sharing.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card text-center p-4 h-100">
                    <div class="feature-icon bg-purple text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-chat-dots" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Secure Messaging</h5>
                    <p class="text-muted">Private and secure messaging system with read receipts and message management features.</p>
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
                        <img src="<?= $profile['profile_photo'] ? UPLOAD_URL . $profile['profile_photo'] : BASE_URL . '/assets/images/default-profile.jpg' ?>" 
                                 class="card-img-top profile-image" alt="Profile">
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
                            <a href="<?= BASE_URL ?>/profile/<?= $profile['id'] ?>" class="btn btn-primary w-100">
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

<!-- How It Works Section -->
<section class="how-it-works-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">How It Works</h2>
            <p class="text-muted">Simple steps to find your life partner</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="step-number bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fw-bold">1</span>
                </div>
                <h5 class="fw-bold mb-2">Register Free</h5>
                <p class="text-muted">Create your account with email and phone verification</p>
            </div>
            
            <div class="col-md-3 text-center">
                <div class="step-number bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fw-bold">2</span>
                </div>
                <h5 class="fw-bold mb-2">Complete Profile</h5>
                <p class="text-muted">Add photos, videos, horoscope, and personal details</p>
            </div>
            
            <div class="col-md-3 text-center">
                <div class="step-number bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fw-bold">3</span>
                </div>
                <h5 class="fw-bold mb-2">Find Matches</h5>
                <p class="text-muted">Browse profiles, use AI matching, and send contact requests</p>
            </div>
            
            <div class="col-md-3 text-center">
                <div class="step-number bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fw-bold">4</span>
                </div>
                <h5 class="fw-bold mb-2">Start Chatting</h5>
                <p class="text-muted">Connect, chat, and plan your future together</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">Success Stories</h2>
            <p class="text-muted">Real couples who found love through Sandawatha.lk</p>
        </div>
        
        <div class="row g-4">
            <!-- First Success Story -->
            <div class="col-md-4">
                <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm h-100">
                    <div class="couple-icon-large mb-4 mx-auto text-center">
                        <img src="<?= BASE_URL ?>/assets/images/c1.png"
                             alt="Priya & Kasun"
                             class="img-fluid couple-img"
                             onerror="this.onerror=null;this.src='https://via.placeholder.com/160?text=Priya+%26+Kasun';">
                    </div>
                    <div class="text-warning mb-3">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="text-muted mb-3">"We met through Sandawatha and instantly connected. The platform made it easy to find someone who shared our values and culture."</p>
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-1">Priya & Kasun</h6>
                            <small class="text-muted">Married 2 years</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Success Story -->
            <div class="col-md-4">
                <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm h-100">
                    <div class="couple-icon-large mb-4 mx-auto text-center">
                        <img src="<?= BASE_URL ?>/assets/images/c2.png"
                             alt="Nuwan & Dilini"
                             class="img-fluid couple-img"
                             onerror="this.onerror=null;this.src='https://via.placeholder.com/160?text=Nuwan+%26+Dilini';">
                    </div>
                    <div class="text-warning mb-3">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="text-muted mb-3">"The matching system on Sandawatha helped us find each other. We're grateful for this platform bringing us together."</p>
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-1">Nuwan & Dilini</h6>
                            <small class="text-muted">Married 1 year</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Third Success Story -->
            <div class="col-md-4">
                <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm h-100">
                    <div class="couple-icon-large mb-4 mx-auto text-center">
                        <img src="<?= BASE_URL ?>/assets/images/c3.png"
                             alt="Chamara & Sachini"
                             class="img-fluid couple-img"
                             onerror="this.onerror=null;this.src='https://via.placeholder.com/160?text=Chamara+%26+Sachini';">
                    </div>
                    <div class="text-warning mb-3">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="text-muted mb-3">"What started as a simple message on Sandawatha turned into a beautiful journey of love and understanding."</p>
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-1">Chamara & Sachini</h6>
                            <small class="text-muted">Married 6 months</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section bg-primary text-white py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Ready to Find Your Perfect Match?</h2>
        <p class="lead mb-4">Join thousands of verified profiles and start your journey to finding true love today.</p>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>/register" class="btn btn-warning btn-lg me-3">
                <i class="bi bi-person-plus"></i> Join Free Now
            </a>
            <a href="<?= BASE_URL ?>/browse" class="btn btn-outline-light btn-lg">
                <i class="bi bi-search"></i> Browse Profiles
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/browse" class="btn btn-warning btn-lg me-3">
                <i class="bi bi-search"></i> Find Your Match
            </a>
            <a href="<?= BASE_URL ?>/premium" class="btn btn-outline-light btn-lg">
                <i class="bi bi-star"></i> Upgrade to Premium
            </a>
        <?php endif; ?>
    </div>
</section>