<div class="min-vh-100 d-flex align-items-center bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-heart-fill text-danger" style="font-size: 3rem;"></i>
                            <h3 class="mt-2 mb-1">Join Sandawatha.lk</h3>
                            <p class="text-muted">Create your account and find your perfect match</p>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>/register" id="registerForm">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" required 
                                               placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                    </div>
                                    <div class="form-text">We'll send verification link to this email</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                        <input type="tel" class="form-control" id="phone" name="phone" required 
                                               placeholder="+94771234567" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                                    </div>
                                    <div class="form-text">Format: +94XXXXXXXXX or 0XXXXXXXXX</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required 
                                               placeholder="Minimum 8 characters">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                            <i class="bi bi-eye" id="password-toggle"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength mt-1">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" id="passwordStrength" style="width: 0%"></div>
                                        </div>
                                        <small class="text-muted" id="passwordStrengthText">Password strength</small>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required 
                                               placeholder="Repeat your password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                            <i class="bi bi-eye" id="confirm_password-toggle"></i>
                                        </button>
                                    </div>
                                    <div class="password-match mt-1">
                                        <small class="text-muted" id="passwordMatchText"></small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the 
                                        <a href="<?= BASE_URL ?>/terms-conditions" target="_blank" class="text-decoration-none">Terms & Conditions</a>
                                        and 
                                        <a href="<?= BASE_URL ?>/privacy-policy" target="_blank" class="text-decoration-none">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                    <label class="form-check-label" for="newsletter">
                                        Subscribe to our newsletter for updates and tips
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mb-3" id="submitBtn">
                                <i class="bi bi-person-plus"></i> Create Account
                            </button>
                        </form>

                        <div class="text-center">
                            <p class="text-muted mb-0">
                                Already have an account? 
                                <a href="<?= BASE_URL ?>/login" class="text-decoration-none fw-semibold">
                                    Sign in here
                                </a>
                            </p>
                        </div>

                        <!-- Registration Benefits -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="mb-2"><i class="bi bi-check-circle text-success"></i> Registration Benefits:</h6>
                            <ul class="list-unstyled small text-muted mb-0">
                                <li><i class="bi bi-check text-success"></i> Browse thousands of verified profiles</li>
                                <li><i class="bi bi-check text-success"></i> AI-powered compatibility matching</li>
                                <li><i class="bi bi-check text-success"></i> Secure messaging system</li>
                                <li><i class="bi bi-check text-success"></i> Horoscope compatibility analysis</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    
    let strength = 0;
    let text = 'Weak';
    let color = 'bg-danger';
    
    if (password.length >= 8) strength += 25;
    if (/[a-z]/.test(password)) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 25;
    
    if (strength >= 75) {
        text = 'Strong';
        color = 'bg-success';
    } else if (strength >= 50) {
        text = 'Medium';
        color = 'bg-warning';
    }
    
    strengthBar.style.width = strength + '%';
    strengthBar.className = 'progress-bar ' + color;
    strengthText.textContent = text + ' password';
});

// Password match checker
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const matchText = document.getElementById('passwordMatchText');
    
    if (confirmPassword) {
        if (password === confirmPassword) {
            matchText.textContent = '✓ Passwords match';
            matchText.className = 'text-success';
        } else {
            matchText.textContent = '✗ Passwords do not match';
            matchText.className = 'text-danger';
        }
    } else {
        matchText.textContent = '';
    }
});

// Form validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long!');
        return false;
    }
    
    // Disable submit button to prevent double submission
    document.getElementById('submitBtn').disabled = true;
});
</script>