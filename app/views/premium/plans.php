<div class="container py-4">
    <!-- Page Header -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold mb-3">
            <i class="bi bi-star-fill text-warning"></i>
            Premium Membership Plans
        </h1>
        <p class="lead text-muted">Upgrade your experience and find your perfect match faster</p>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i>
                Premium membership activated successfully! Welcome to premium features.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Current Membership Status -->
    <?php if ($current_membership): ?>
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title mb-2">
                                <i class="bi bi-star-fill"></i>
                                Current: <?= ucfirst($current_membership['plan_type']) ?> Member
                            </h5>
                            <p class="card-text mb-0">
                                Valid until <?= date('M d, Y', strtotime($current_membership['end_date'])) ?>
                                <br>
                                <small>Enjoying all premium benefits</small>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="premium-badge fs-6 bg-white text-dark">
                                <?= ucfirst($current_membership['plan_type']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pricing Plans -->
    <div class="row g-4 mb-5">
        <?php foreach ($plans as $planKey => $plan): ?>
            <div class="col-lg-4">
                <div class="premium-plan card h-100 <?= isset($plan['popular']) ? 'featured' : '' ?>">
                    <?php if (isset($plan['popular'])): ?>
                        <div class="ribbon">
                            <span>Most Popular</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-header bg-<?= $plan['color'] ?> text-white text-center">
                        <h4 class="card-title mb-0"><?= $plan['name'] ?></h4>
                        <p class="mb-0"><?= $plan['duration'] ?></p>
                    </div>
                    
                    <div class="card-body text-center">
                        <div class="price-display mb-4">
                            <span class="currency">LKR</span>
                            <span class="amount"><?= number_format($plan['price']) ?></span>
                            <span class="period">/month</span>
                        </div>
                        
                        <ul class="list-unstyled features-list">
                            <?php foreach ($plan['features'] as $feature): ?>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <?= htmlspecialchars($feature) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="card-footer bg-transparent text-center">
                        <?php if ($current_membership && $current_membership['plan_type'] === $planKey): ?>
                            <button class="btn btn-outline-<?= $plan['color'] ?> w-100" disabled>
                                <i class="bi bi-check-circle"></i> Current Plan
                            </button>
                        <?php else: ?>
                            <button class="btn btn-<?= $plan['color'] ?> w-100" onclick="selectPlan('<?= $planKey ?>')">
                                <i class="bi bi-star"></i> 
                                <?= $current_membership ? 'Upgrade Now' : 'Choose Plan' ?>
                            </button>
                            
                            <!-- Multi-month options -->
                            <div class="mt-3">
                                <div class="btn-group w-100" role="group">
                                    <button class="btn btn-outline-<?= $plan['color'] ?> btn-sm" onclick="selectPlan('<?= $planKey ?>', 3)">
                                        3 Months<br>
                                        <small>Save 10%</small>
                                    </button>
                                    <button class="btn btn-outline-<?= $plan['color'] ?> btn-sm" onclick="selectPlan('<?= $planKey ?>', 6)">
                                        6 Months<br>
                                        <small>Save 15%</small>
                                    </button>
                                    <button class="btn btn-outline-<?= $plan['color'] ?> btn-sm" onclick="selectPlan('<?= $planKey ?>', 12)">
                                        12 Months<br>
                                        <small>Save 25%</small>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Features Comparison -->
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="text-center mb-4">Feature Comparison</h3>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Features</th>
                            <th class="text-center">Free</th>
                            <th class="text-center">Basic</th>
                            <th class="text-center">Premium</th>
                            <th class="text-center">Platinum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Profile Creation</td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                        </tr>
                        <tr>
                            <td>Browse Profiles</td>
                            <td class="text-center">Limited</td>
                            <td class="text-center">20/day</td>
                            <td class="text-center">100/day</td>
                            <td class="text-center">Unlimited</td>
                        </tr>
                        <tr>
                            <td>Contact Requests</td>
                            <td class="text-center">1/day</td>
                            <td class="text-center">3/day</td>
                            <td class="text-center">10/day</td>
                            <td class="text-center">Unlimited</td>
                        </tr>
                        <tr>
                            <td>Messages</td>
                            <td class="text-center">1/day</td>
                            <td class="text-center">5/day</td>
                            <td class="text-center">25/day</td>
                            <td class="text-center">Unlimited</td>
                        </tr>
                        <tr>
                            <td>See Who Viewed Profile</td>
                            <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                        </tr>
                        <tr>
                            <td>AI-Powered Matching</td>
                            <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                        </tr>
                        <tr>
                            <td>Priority Listing</td>
                            <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                        </tr>
                        <tr>
                            <td>Video Call Feature</td>
                            <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i></td>
                        </tr>
                        <tr>
                            <td>Dedicated Support</td>
                            <td class="text-center"><i class="bi bi-x-circle text-danger"></i></td>
                            <td class="text-center">Email</td>
                            <td class="text-center">Priority</td>
                            <td class="text-center">Manager</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Membership History -->
    <?php if (!empty($membership_history)): ?>
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="mb-4">Membership History</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Duration</th>
                            <th>Amount</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($membership_history as $membership): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary"><?= ucfirst($membership['plan_type']) ?></span>
                                </td>
                                <td><?= $membership['duration_months'] ?> month(s)</td>
                                <td>LKR <?= number_format($membership['price_lkr']) ?></td>
                                <td><?= date('M d, Y', strtotime($membership['start_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($membership['end_date'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $membership['status'] === 'active' ? 'success' : ($membership['status'] === 'expired' ? 'secondary' : 'warning') ?>">
                                        <?= ucfirst($membership['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FAQ Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <h3 class="text-center mb-4">Frequently Asked Questions</h3>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            How does the premium membership work?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Premium membership gives you enhanced features like unlimited messaging, priority listing in search results, and access to our AI-powered matching system. Your membership is active immediately after payment.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Can I cancel my membership?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, you can cancel your membership at any time. Contact our support team and we'll process your request. Your premium features will remain active until the end of your current billing period.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            What payment methods do you accept?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Currently, this is a demo version with mock payment processing. In the live version, we accept all major credit cards, bank transfers, and popular Sri Lankan payment methods like eZ Cash and mCash.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complete Your Subscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bi bi-star-fill text-warning" style="font-size: 3rem;"></i>
                    <h4 id="selectedPlanName">Premium Plan</h4>
                    <p class="text-muted" id="selectedPlanDetails">1 Month - LKR 1,000</p>
                </div>
                
                <form id="paymentForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="plan_type" id="selectedPlanType">
                    <input type="hidden" name="duration" id="selectedDuration" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="card payment-method">
                                    <div class="card-body text-center">
                                        <i class="bi bi-credit-card display-6 text-primary"></i>
                                        <h6 class="mt-2">Credit Card</h6>
                                        <small class="text-muted">Visa, MasterCard</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card payment-method">
                                    <div class="card-body text-center">
                                        <i class="bi bi-phone display-6 text-success"></i>
                                        <h6 class="mt-2">Mobile Payment</h6>
                                        <small class="text-muted">eZ Cash, mCash</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Demo Mode:</strong> This is a demonstration. No actual payment will be processed.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="processPayment()">
                    <i class="bi bi-credit-card"></i> Process Payment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedPlan = '';
let selectedDuration = 1;

const planPrices = {
    'basic': 500,
    'premium': 1000,
    'platinum': 1500
};

const planNames = {
    'basic': 'Basic Plan',
    'premium': 'Premium Plan',
    'platinum': 'Platinum Plan'
};

function selectPlan(planType, duration = 1) {
    selectedPlan = planType;
    selectedDuration = duration;
    
    const price = planPrices[planType] * duration;
    const discount = duration > 1 ? (duration >= 12 ? 25 : (duration >= 6 ? 15 : 10)) : 0;
    const discountedPrice = price * (100 - discount) / 100;
    
    document.getElementById('selectedPlanName').textContent = planNames[planType];
    document.getElementById('selectedPlanDetails').textContent = 
        `${duration} Month${duration > 1 ? 's' : ''} - LKR ${discountedPrice.toLocaleString()}${discount > 0 ? ` (${discount}% off)` : ''}`;
    document.getElementById('selectedPlanType').value = planType;
    document.getElementById('selectedDuration').value = duration;
    
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}

function processPayment() {
    const formData = new FormData(document.getElementById('paymentForm'));
    
    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    btn.disabled = true;
    
    fetch('<?= BASE_URL ?>/premium/subscribe', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect || '<?= BASE_URL ?>/premium';
            }, 2000);
        } else {
            showAlert(data.message, 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        showAlert('Payment processing failed. Please try again.', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Payment method selection
document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
        this.classList.add('selected');
    });
});
</script>

<style>
.premium-plan {
    position: relative;
    transition: all 0.3s ease;
}

.premium-plan:hover {
    transform: translateY(-10px);
}

.premium-plan.featured {
    border: 2px solid #ffd700;
    transform: scale(1.05);
}

.ribbon {
    position: absolute;
    top: -5px;
    right: -5px;
    background: linear-gradient(45deg, #ff6b6b, #ee5a24);
    color: white;
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: bold;
    border-radius: 0 0 0 1rem;
    z-index: 1;
}

.price-display {
    position: relative;
}

.currency {
    font-size: 1.2rem;
    font-weight: 500;
    vertical-align: top;
}

.amount {
    font-size: 3rem;
    font-weight: 700;
    color: #2d3748;
}

.period {
    font-size: 1rem;
    color: #718096;
}

.features-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.features-list li:last-child {
    border-bottom: none;
}

.payment-method {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.payment-method:hover {
    border-color: #0d6efd;
    transform: translateY(-2px);
}

.payment-method.selected {
    border-color: #0d6efd;
    background: #f8f9ff;
}

[data-bs-theme="dark"] .amount {
    color: #f7fafc;
}

[data-bs-theme="dark"] .features-list li {
    border-bottom-color: #4a5568;
}
</style>