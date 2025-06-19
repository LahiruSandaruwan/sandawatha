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
    <?= \App\helpers\AssetHelper::css('common/common.css') ?>
    
    <!-- Component CSS -->
    <?php if (isset($component_css)): ?>
        <?php foreach ($component_css as $css): ?>
            <?= \App\helpers\AssetHelper::css($css) ?>
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
    
    <!-- Custom CSS -->
    <?= \App\helpers\AssetHelper::css('style.css') ?>
    <?php echo $additionalCss ?? ''; ?>
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

    <!-- Include Header -->
    <?php include SITE_ROOT . '/app/views/partials/header.php'; ?>

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
        <?= $this->getSection('content') ?>
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
            
            <hr class="border-light opacity-25 my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-light opacity-75 mb-md-0">
                        © <?= date('Y') ?> Sandawatha.lk. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-light opacity-75 mb-0">
                        Made with <i class="bi bi-heart-fill text-danger"></i> in Sri Lanka
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery (before Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Common Scripts -->
    <script src="<?= BASE_URL ?>/assets/js/common/app.js"></script>
    
    <!-- Component Scripts -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= BASE_URL ?>/assets/js/<?= $script ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Page-specific Scripts -->
    <?= $this->getSection('scripts', '') ?>
</body>
</html>