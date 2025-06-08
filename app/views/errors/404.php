<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Sandawatha.lk</title>
    
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
                            <i class="bi bi-heart-break display-1 text-danger"></i>
                        </div>
                        
                        <!-- Error Message -->
                        <h1 class="display-4 fw-bold text-primary mb-3">404</h1>
                        <h2 class="h4 mb-3">Oops! Page Not Found</h2>
                        <p class="text-muted mb-4">
                            The page you're looking for seems to have gone on a honeymoon! 
                            Let's get you back to finding your perfect match.
                        </p>
                        
                        <!-- Search Box -->
                        <div class="search-section mb-4">
                            <h5 class="mb-3">What were you looking for?</h5>
                            <form action="<?= BASE_URL ?>/browse" method="GET" class="d-flex gap-2 justify-content-center">
                                <div class="input-group" style="max-width: 400px;">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Search profiles..." aria-label="Search profiles">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <a href="<?= BASE_URL ?>/" class="btn btn-primary btn-lg me-3">
                                <i class="bi bi-house"></i> Go Home
                            </a>
                            <a href="<?= BASE_URL ?>/browse" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-search"></i> Browse Profiles
                            </a>
                        </div>
                        
                        <!-- Quick Links -->
                        <div class="quick-links mt-5">
                            <h6 class="text-muted mb-3">Or try these popular pages:</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="<?= BASE_URL ?>/browse" class="text-decoration-none">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-body text-center p-3">
                                                <i class="bi bi-people display-6 text-primary mb-2"></i>
                                                <h6 class="card-title">Browse Profiles</h6>
                                                <p class="card-text small text-muted">Find your perfect match</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="<?= BASE_URL ?>/register" class="text-decoration-none">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-body text-center p-3">
                                                <i class="bi bi-person-plus display-6 text-success mb-2"></i>
                                                <h6 class="card-title">Join Free</h6>
                                                <p class="card-text small text-muted">Create your profile</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Support -->
                        <div class="support-section mt-5 p-4 bg-white rounded">
                            <h6 class="text-muted mb-2">Still can't find what you're looking for?</h6>
                            <p class="small text-muted mb-3">Our support team is here to help you navigate to the right place.</p>
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
            // Animate the heart icon
            const heartIcon = document.querySelector('.bi-heart-break');
            if (heartIcon) {
                setInterval(() => {
                    heartIcon.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        heartIcon.style.transform = 'scale(1)';
                    }, 200);
                }, 3000);
            }
            
            // Focus on search input
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.focus();
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
        
        .quick-links .card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
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