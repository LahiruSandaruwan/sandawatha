<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="text-center mb-5">
                <i class="bi bi-chat-heart display-4 text-primary mb-3"></i>
                <h1 class="h2 fw-bold mb-3">We Value Your Feedback</h1>
                <p class="text-muted">Help us improve Sandawatha.lk by sharing your experience and suggestions</p>
            </div>

            <!-- Feedback Form -->
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <form method="POST" action="<?= BASE_URL ?>/feedback" id="feedbackForm">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        
                        <!-- Personal Information -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       placeholder="Your full name">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       placeholder="your@email.com">
                            </div>
                        </div>

                        <!-- Rating -->
                        <div class="mb-4">
                            <label class="form-label">Overall Rating *</label>
                            <div class="rating-container d-flex align-items-center gap-2">
                                <div class="star-rating" data-rating="0">
                                    <span class="star" data-value="1"><i class="bi bi-star"></i></span>
                                    <span class="star" data-value="2"><i class="bi bi-star"></i></span>
                                    <span class="star" data-value="3"><i class="bi bi-star"></i></span>
                                    <span class="star" data-value="4"><i class="bi bi-star"></i></span>
                                    <span class="star" data-value="5"><i class="bi bi-star"></i></span>
                                </div>
                                <span id="ratingText" class="text-muted">Click to rate</span>
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" required>
                        </div>

                        <!-- Subject -->
                        <div class="mb-4">
                            <label for="subject" class="form-label">Subject *</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Select a topic</option>
                                <option value="General Feedback">General Feedback</option>
                                <option value="Website Usability">Website Usability</option>
                                <option value="Profile Matching">Profile Matching</option>
                                <option value="Premium Features">Premium Features</option>
                                <option value="Customer Support">Customer Support</option>
                                <option value="Technical Issues">Technical Issues</option>
                                <option value="Feature Request">Feature Request</option>
                                <option value="Privacy & Security">Privacy & Security</option>
                                <option value="Success Story">Success Story</option>
                                <option value="Complaint">Complaint</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- Message -->
                        <div class="mb-4">
                            <label for="message" class="form-label">Your Feedback *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" required
                                      placeholder="Please share your detailed feedback, suggestions, or experience with us..."></textarea>
                            <div class="form-text">
                                <span id="charCount">0</span>/1000 characters
                            </div>
                        </div>

                        <!-- Public Feedback Option -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public">
                                <label class="form-check-label" for="is_public">
                                    Allow this feedback to be displayed publicly as a testimonial
                                    <small class="text-muted d-block">Your personal information will not be shared</small>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-send"></i> Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="row g-4 mt-5">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-chat-dots" style="font-size: 1.5rem;"></i>
                        </div>
                        <h5>Quick Response</h5>
                        <p class="text-muted small">We respond to all feedback within 24 hours</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-shield-check" style="font-size: 1.5rem;"></i>
                        </div>
                        <h5>Privacy Protected</h5>
                        <p class="text-muted small">Your personal information is kept confidential</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-arrow-repeat" style="font-size: 1.5rem;"></i>
                        </div>
                        <h5>Continuous Improvement</h5>
                        <p class="text-muted small">Your feedback helps us make the platform better</p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="text-center mt-5">
                <h5 class="mb-3">Other Ways to Reach Us</h5>
                <div class="row g-3 justify-content-center">
                    <div class="col-auto">
                        <a href="mailto:support@sandawatha.lk" class="btn btn-outline-primary">
                            <i class="bi bi-envelope"></i> support@sandawatha.lk
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="tel:+94112345678" class="btn btn-outline-success">
                            <i class="bi bi-phone"></i> +94 11 234 5678
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Star Rating System
    const starRating = document.querySelector('.star-rating');
    const stars = starRating.querySelectorAll('.star');
    const ratingText = document.getElementById('ratingText');
    const ratingValue = document.getElementById('ratingValue');
    
    const ratingTexts = {
        1: 'Poor - Needs significant improvement',
        2: 'Fair - Below expectations',
        3: 'Good - Meets expectations',
        4: 'Very Good - Exceeds expectations',
        5: 'Excellent - Outstanding experience'
    };
    
    stars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const value = this.dataset.value;
            highlightStars(value);
            ratingText.textContent = ratingTexts[value];
        });
        
        star.addEventListener('click', function() {
            const value = this.dataset.value;
            ratingValue.value = value;
            starRating.dataset.rating = value;
            ratingText.textContent = ratingTexts[value];
            ratingText.className = 'text-warning fw-semibold';
        });
    });
    
    starRating.addEventListener('mouseleave', function() {
        const currentRating = this.dataset.rating;
        highlightStars(currentRating);
        if (currentRating > 0) {
            ratingText.textContent = ratingTexts[currentRating];
            ratingText.className = 'text-warning fw-semibold';
        } else {
            ratingText.textContent = 'Click to rate';
            ratingText.className = 'text-muted';
        }
    });
    
    function highlightStars(rating) {
        stars.forEach((star, index) => {
            const starIcon = star.querySelector('i');
            if (index < rating) {
                starIcon.className = 'bi bi-star-fill text-warning';
            } else {
                starIcon.className = 'bi bi-star text-muted';
            }
        });
    }
    
    // Character counter
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    
    messageTextarea.addEventListener('input', function() {
        const count = this.value.length;
        charCount.textContent = count;
        
        if (count > 1000) {
            charCount.className = 'text-danger fw-bold';
            this.value = this.value.substring(0, 1000);
            charCount.textContent = '1000';
        } else if (count > 900) {
            charCount.className = 'text-warning';
        } else {
            charCount.className = '';
        }
    });
    
    // Form validation
    const form = document.getElementById('feedbackForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        const rating = ratingValue.value;
        
        if (!rating || rating < 1) {
            e.preventDefault();
            alert('Please select a rating before submitting.');
            return;
        }
        
        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
    });
    
    // Auto-fill email for logged-in users
    <?php if (isset($_SESSION['user_id'])): ?>
        document.getElementById('email').value = '<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>';
    <?php endif; ?>
});
</script>

<style>
.star-rating {
    font-size: 2rem;
}

.star {
    cursor: pointer;
    transition: all 0.2s ease;
    margin: 0 2px;
}

.star:hover {
    transform: scale(1.1);
}

.star i {
    transition: all 0.2s ease;
}

.rating-container {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

[data-bs-theme="dark"] .rating-container {
    background: #2d3748;
    border-color: #4a5568;
}

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

/* Animation for submission feedback */
.submit-success {
    animation: submitSuccess 0.6s ease;
}

@keyframes submitSuccess {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>