<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Sandawatha.lk</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="min-vh-100 d-flex align-items-center bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 text-center">
                    <div class="error-content">
                        <!-- Error Illustration -->
                        <div class="error-illustration mb-4">
                            <i class="bi bi-shield-lock display-1 text-danger"></i>
                        </div>
                        
                        <!-- Error Message -->
                        <h1 class="display-4 fw-bold text-primary mb-3">403</h1>
                        <h2 class="h4 mb-3">Access Denied</h2>
                        <p class="text-muted mb-4">
                            Sorry, you don't have permission to access this page.
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                Please log in to continue.
                            <?php endif; ?>
                        </p>
                        
                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-lg me-3">
                                    <i class="bi bi-box-arrow-in-right"></i> Log In
                                </a>
                                <a href="<?= BASE_URL ?>/register" class="btn btn-outline-primary btn-lg">
                                    <i class="bi bi-person-plus"></i> Register
                                </a>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/" class="btn btn-primary btn-lg me-3">
                                    <i class="bi bi-house"></i> Go Home
                                </a>
                                <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-primary btn-lg">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Contact Support -->
                        <div class="support-section mt-5 p-4 bg-white rounded">
                            <h6 class="text-muted mb-2">Need help?</h6>
                            <p class="small text-muted mb-3">If you believe this is a mistake, please contact our support team.</p>
                            <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-envelope"></i> Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate the lock icon
            const lockIcon = document.querySelector('.bi-shield-lock');
            if (lockIcon) {
                setInterval(() => {
                    lockIcon.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        lockIcon.style.transform = 'scale(1)';
                    }, 200);
                }, 3000);
            }
        });
    </script>
    
    <style>
        .error-illustration {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .error-content {
            animation: fadeInUp 0.8s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>
</html> 