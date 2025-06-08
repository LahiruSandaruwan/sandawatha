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