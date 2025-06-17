<div class="min-vh-100 d-flex align-items-center bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-heart-fill text-danger" style="font-size: 3rem;"></i>
                            <h3 class="mt-2 mb-1">Welcome Back</h3>
                            <p class="text-muted">Sign in to your Sandawatha.lk account</p>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>/login">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           placeholder="Enter your email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required 
                                           placeholder="Enter your password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="bi bi-eye" id="password-toggle"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">
                                            Remember me
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6 text-end">
                                    <a href="<?= BASE_URL ?>/forgot-password" class="text-decoration-none small">
                                        Forgot password?
                                    </a>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                                <i class="bi bi-box-arrow-in-right"></i> Sign In
                            </button>
                        </form>

                        <!-- Social Login Buttons -->
                        <?php 
                        try {
                            require_once SITE_ROOT . '/app/helpers/SocialAuth.php';
                            $availableProviders = SocialAuth::getAvailableProviders();
                        } catch (Exception $e) {
                            $availableProviders = []; // Fallback if there's an error
                        }
                        
                        // Show demo buttons even without configuration for testing
                        $showDemoButtons = true; // Set to false once OAuth is configured
                        
                        if (!empty($availableProviders) || $showDemoButtons): ?>
                        <div class="text-center mb-3">
                            <div class="d-flex align-items-center my-3">
                                <hr class="flex-grow-1">
                                <span class="px-3 text-muted small">OR</span>
                                <hr class="flex-grow-1">
                            </div>
                            
                            <div class="d-grid gap-2">
                                <?php if (in_array('google', $availableProviders) || $showDemoButtons): ?>
                                <a href="<?= BASE_URL ?>/auth/google" class="btn btn-outline-danger">
                                    <i class="bi bi-google"></i> Continue with Google
                                    <?php if ($showDemoButtons && !in_array('google', $availableProviders)): ?>
                                    <small class="d-block text-muted">(Demo - requires configuration)</small>
                                    <?php endif; ?>
                                </a>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($showDemoButtons && empty($availableProviders)): ?>
                            <div class="mt-2">
                                <small class="text-info">
                                    <i class="bi bi-info-circle"></i> 
                                    Social login buttons are visible for demo. Configure OAuth credentials to make them functional.
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="text-center">
                            <p class="text-muted mb-0">
                                Don't have an account? 
                                <a href="<?= BASE_URL ?>/register" class="text-decoration-none fw-semibold">
                                    Create one now
                                </a>
                            </p>
                        </div>

                        <!-- Demo Accounts Info -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <small class="text-muted">
                                <strong>Demo Accounts:</strong><br>
                                Admin: admin@sandawatha.lk / password123<br>
                                User: demo@sandawatha.lk / password123
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>