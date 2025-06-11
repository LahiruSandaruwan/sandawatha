<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark' : 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sandawatha.lk - Find Your Perfect Match' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Common CSS -->
    <link href="<?= BASE_URL ?>/assets/css/common/common.css" rel="stylesheet">
    
    <!-- Component CSS -->
    <?php if (isset($component_css)): ?>
        <?php foreach ($component_css as $css): ?>
            <link href="<?= BASE_URL ?>/assets/css/<?= $css ?>.css" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Meta tags -->
    <meta name="description" content="<?= $description ?? 'Find your perfect life partner in Sri Lanka. Join thousands of verified profiles on Sandawatha.lk - the most trusted matrimonial platform.' ?>">
    <meta name="keywords" content="matrimony, marriage, Sri Lanka, Buddhist, Hindu, Christian, Muslim, profiles">
    <meta name="author" content="Sandawatha.lk">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
    
    <!-- Favicon -->
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= $title ?? 'Sandawatha.lk - Find Your Perfect Match' ?>">
    <meta property="og:description" content="<?= $description ?? 'Find your perfect life partner in Sri Lanka' ?>">
    <meta property="og:image" content="<?= BASE_URL ?>/assets/images/og-image.jpg">
    <meta property="og:url" content="<?= BASE_URL ?>">
</head>
<body>
    <!-- Cookie Consent Banner -->
    <div id="cookieConsent" class="cookie-consent" style="display: none;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-0">
                        <i class="bi bi-cookie"></i>
                        We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.
                        <a href="<?= BASE_URL ?>/privacy-policy" class="text-decoration-none">Learn more</a>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-sm btn-outline-light me-2" id="declineCookiesBtn">Decline</button>
                    <button class="btn btn-sm btn-light" id="acceptCookiesBtn">Accept</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/">
                <i class="bi bi-heart-fill text-danger"></i>
                Sandawatha.lk
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/browse">Browse Profiles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/contact">Contact</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <!-- Dark Mode Toggle -->
                    <li class="nav-item">
                        <button class="btn btn-link nav-link" onclick="toggleDarkMode()" title="Toggle Dark Mode">
                            <i class="bi bi-moon-fill" id="darkModeIcon"></i>
                        </button>
                    </li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Logged in user menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                                <?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin"><i class="bi bi-speedometer2"></i> Admin Dashboard</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile/edit"><i class="bi bi-person-gear"></i> Edit Profile</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/messages"><i class="bi bi-envelope"></i> Messages</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/favorites"><i class="bi bi-heart"></i> Favorites</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/contact-requests"><i class="bi bi-person-plus"></i> Contact Requests</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/premium"><i class="bi bi-star"></i> Premium</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Guest menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light btn-sm ms-2" href="<?= BASE_URL ?>/register">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] === 'error' ? 'danger' : $_SESSION['flash_type'] ?> alert-dismissible fade show m-0" role="alert">
            <div class="container">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <!-- Main Content -->
    <main>
        <?php 
        $viewFile = SITE_ROOT . '/app/views/' . $content_view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo '<div class="container py-4"><div class="alert alert-danger">View file not found: ' . htmlspecialchars($content_view) . '</div></div>';
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="mb-3 text-light">
                        <i class="bi bi-heart-fill text-danger"></i>
                        Sandawatha.lk
                    </h5>
                    <p class="text-light opacity-75">Find your perfect life partner in Sri Lanka. Join thousands of verified profiles on the most trusted matrimonial platform.</p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <h6 class="mb-3 text-light">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>/" class="text-light text-decoration-none opacity-75">Home</a></li>
                        <li><a href="<?= BASE_URL ?>/browse" class="text-light text-decoration-none opacity-75">Browse</a></li>
                        <li><a href="<?= BASE_URL ?>/about" class="text-light text-decoration-none opacity-75">About</a></li>
                        <li><a href="<?= BASE_URL ?>/contact" class="text-light text-decoration-none opacity-75">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-md-2">
                    <h6 class="mb-3 text-light">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>/feedback" class="text-light text-decoration-none opacity-75">Feedback</a></li>
                        <li><a href="<?= BASE_URL ?>/privacy-policy" class="text-light text-decoration-none opacity-75">Privacy Policy</a></li>
                        <li><a href="<?= BASE_URL ?>/terms-conditions" class="text-light text-decoration-none opacity-75">Terms</a></li>
                        <li><a href="#" class="text-light text-decoration-none opacity-75">Help</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4">
                    <h6 class="mb-3 text-light">Newsletter</h6>
                    <p class="text-light opacity-75 mb-3">Stay updated with new features and matches.</p>
                    <form class="newsletter-form" onsubmit="subscribeNewsletter(event)">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your email" required>
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <hr class="my-4 opacity-25">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-light opacity-75 mb-0">&copy; <?= date('Y') ?> Sandawatha.lk. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="text-light opacity-75 mb-0">Made with <i class="bi bi-heart-fill text-danger"></i> in Sri Lanka</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Common JS -->
    <script src="<?= BASE_URL ?>/assets/js/common/app.js" defer></script>
    
    <!-- Component JS -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= BASE_URL ?>/assets/js/<?= $script ?>.js" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Contact Request Scripts -->
    <script src="<?= BASE_URL ?>/assets/js/contact-requests.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check for cookie consent
            if (!localStorage.getItem('cookieConsent')) {
                document.getElementById('cookieConsent').style.display = 'block';
            }
            
            // Initialize cookie consent buttons
            const acceptBtn = document.getElementById('acceptCookiesBtn');
            const declineBtn = document.getElementById('declineCookiesBtn');
            
            if (acceptBtn) {
                acceptBtn.onclick = function() {
                    localStorage.setItem('cookieConsent', 'accepted');
                    document.getElementById('cookieConsent').style.display = 'none';
                };
            }
            
            if (declineBtn) {
                declineBtn.onclick = function() {
                    localStorage.setItem('cookieConsent', 'declined');
                    document.getElementById('cookieConsent').style.display = 'none';
                };
            }
            
            // Update dark mode icon if function exists
            if (typeof updateDarkModeIcon === 'function') {
                updateDarkModeIcon();
            }
        });
    </script>
</body>
</html>