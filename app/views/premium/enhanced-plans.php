<?php
require_once __DIR__ . '/../../helpers/PermissionMiddleware.php';

// Get current user's package info
$currentPackage = PermissionMiddleware::getUserPackageInfo();
$userPermissions = PermissionMiddleware::getUserPermissions();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Plans - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .pricing-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .pricing-card.current-plan {
            border-color: #28a745;
            background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
        }
        
        .pricing-card.popular {
            border-color: #007bff;
            position: relative;
        }
        
        .popular-badge {
            position: absolute;
            top: -1px;
            right: -1px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 8px 20px;
            font-size: 0.75rem;
            font-weight: bold;
            border-radius: 0 0 0 20px;
            box-shadow: 0 2px 10px rgba(0,123,255,0.3);
        }
        
        .price-display {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .feature-list {
            padding: 0;
            margin: 0;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .feature-item:last-child {
            border-bottom: none;
        }
        
        .feature-icon {
            width: 24px;
            margin-right: 12px;
        }
        
        .upgrade-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .upgrade-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102,126,234,0.3);
        }
        
        .current-plan-btn {
            background: #28a745;
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            font-weight: 600;
        }
        
        .features-comparison {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 20px;
            padding: 40px;
            margin: 60px 0;
        }
        
        .comparison-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .comparison-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .usage-stats {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .usage-bar {
            height: 10px;
            background: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .usage-progress {
            height: 100%;
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            transition: width 0.3s ease;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .testimonial-stars {
            color: #ffc107;
            font-size: 1.5rem;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    
    <div class="container-fluid py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 mb-4">Choose Your Perfect Plan</h1>
                    <p class="lead mb-0">Find your soulmate with the right subscription plan</p>
                    
                    <?php if ($currentPackage): ?>
                        <div class="alert alert-light mt-4" role="alert">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            You are currently on the <strong><?= htmlspecialchars($currentPackage['name']) ?></strong> plan
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($currentPackage): ?>
    <!-- Current Usage Stats -->
    <div class="container">
        <div class="usage-stats">
            <h3 class="mb-4"><i class="bi bi-graph-up text-primary me-2"></i>Your Usage This Month</h3>
            <div class="row">
                <div class="col-md-4">
                    <h6>Daily Messages</h6>
                    <?php 
                    $messageQuota = PermissionMiddleware::getRemainingQuota('daily_messages');
                    $messageLimit = $currentPackage['features']['daily_messages'] ?? 5;
                    $messageUsed = is_numeric($messageLimit) ? $messageLimit - $messageQuota : 0;
                    ?>
                    <div class="d-flex justify-content-between">
                        <span><?= $messageUsed ?> used</span>
                        <span><?= is_numeric($messageQuota) ? $messageQuota . ' remaining' : 'Unlimited' ?></span>
                    </div>
                    <div class="usage-bar">
                        <div class="usage-progress" style="width: <?= is_numeric($messageLimit) ? ($messageUsed / $messageLimit * 100) . '%' : '100%' ?>"></div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <h6>Profile Views</h6>
                    <?php 
                    $viewQuota = PermissionMiddleware::getRemainingQuota('profile_views');
                    $viewLimit = $currentPackage['features']['profile_views'] ?? 10;
                    $viewUsed = is_numeric($viewLimit) ? $viewLimit - $viewQuota : 0;
                    ?>
                    <div class="d-flex justify-content-between">
                        <span><?= $viewUsed ?> used</span>
                        <span><?= is_numeric($viewQuota) ? $viewQuota . ' remaining' : 'Unlimited' ?></span>
                    </div>
                    <div class="usage-bar">
                        <div class="usage-progress" style="width: <?= is_numeric($viewLimit) ? ($viewUsed / $viewLimit * 100) . '%' : '100%' ?>"></div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <h6>Contact Requests</h6>
                    <?php 
                    $contactQuota = PermissionMiddleware::getRemainingQuota('contact_requests');
                    $contactLimit = $currentPackage['features']['contact_requests'] ?? 3;
                    $contactUsed = is_numeric($contactLimit) ? $contactLimit - $contactQuota : 0;
                    ?>
                    <div class="d-flex justify-content-between">
                        <span><?= $contactUsed ?> used</span>
                        <span><?= is_numeric($contactQuota) ? $contactQuota . ' remaining' : 'Unlimited' ?></span>
                    </div>
                    <div class="usage-bar">
                        <div class="usage-progress" style="width: <?= is_numeric($contactLimit) ? ($contactUsed / $contactLimit * 100) . '%' : '100%' ?>"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Pricing Plans -->
    <div class="container py-5">
        <div class="row justify-content-center">
            
            <!-- Basic Plan -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card pricing-card h-100 <?= ($currentPackage && $currentPackage['slug'] === 'basic') ? 'current-plan' : '' ?>">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <span class="badge" style="background-color: #6c757d; color: white; padding: 8px 16px; border-radius: 20px;">FREE</span>
                        </div>
                        
                        <h3 class="card-title">Basic</h3>
                        <p class="text-muted">Perfect for getting started</p>
                        
                        <div class="price-display mb-4">
                            ₹0<span class="fs-6 text-muted">/month</span>
                        </div>
                        
                        <ul class="feature-list list-unstyled text-start">
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span>5 daily messages</span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span>10 profile views per day</span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span>3 contact requests per day</span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-x-circle text-muted feature-icon"></i>
                                <span>Video calling</span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-x-circle text-muted feature-icon"></i>
                                <span>Audio calling</span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-x-circle text-muted feature-icon"></i>
                                <span>Profile highlighting</span>
                            </li>
                        </ul>
                        
                        <div class="mt-4">
                            <?php if ($currentPackage && $currentPackage['slug'] === 'basic'): ?>
                                <button class="btn current-plan-btn w-100" disabled>
                                    <i class="bi bi-check-circle-fill me-2"></i>Current Plan
                                </button>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/premium/subscribe/basic" class="btn btn-outline-secondary w-100">
                                    Select Basic
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Premium Plan -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card pricing-card h-100 popular <?= ($currentPackage && $currentPackage['slug'] === 'premium') ? 'current-plan' : '' ?>">
                    <div class="popular-badge">MOST POPULAR</div>
                    
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <span class="badge" style="background-color: #007bff; color: white; padding: 8px 16px; border-radius: 20px;">POPULAR</span>
                        </div>
                        
                        <h3 class="card-title">Premium</h3>
                        <p class="text-muted">Most popular choice for serious dating</p>
                        
                        <div class="price-display mb-4">
                            ₹999<span class="fs-6 text-muted">/month</span>
                        </div>
                        
                        <ul class="feature-list list-unstyled text-start">
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span><strong>50 daily messages</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span><strong>100 profile views per day</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span><strong>15 contact requests per day</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span><strong>Video calling</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span><strong>Audio calling</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span><strong>Profile highlighting</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span>Advanced search filters</span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span>Ad-free experience</span>
                            </li>
                        </ul>
                        
                        <div class="mt-4">
                            <?php if ($currentPackage && $currentPackage['slug'] === 'premium'): ?>
                                <button class="btn current-plan-btn w-100" disabled>
                                    <i class="bi bi-check-circle-fill me-2"></i>Current Plan
                                </button>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/premium/subscribe/premium" class="btn upgrade-btn text-white w-100">
                                    <i class="bi bi-arrow-up-circle me-2"></i>Upgrade to Premium
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Platinum Plan -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card pricing-card h-100 <?= ($currentPackage && $currentPackage['slug'] === 'platinum') ? 'current-plan' : '' ?>">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <span class="badge" style="background-color: #ffd700; color: #333; padding: 8px 16px; border-radius: 20px;">PREMIUM</span>
                        </div>
                        
                        <h3 class="card-title">Platinum</h3>
                        <p class="text-muted">Ultimate dating experience</p>
                        
                        <div class="price-display mb-4">
                            ₹1999<span class="fs-6 text-muted">/month</span>
                        </div>
                        
                        <ul class="feature-list list-unstyled text-start">
                            <li class="feature-item">
                                <i class="bi bi-infinity text-primary feature-icon"></i>
                                <span><strong>Unlimited messages</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-infinity text-primary feature-icon"></i>
                                <span><strong>Unlimited profile views</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-infinity text-primary feature-icon"></i>
                                <span><strong>Unlimited contact requests</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span><strong>Video calling</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span><strong>Audio calling</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span><strong>Priority profile highlighting</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span><strong>Priority customer support</strong></span>
                            </li>
                            <li class="feature-item">
                                <i class="bi bi-check-circle-fill text-success feature-icon"></i>
                                <span>All Premium features</span>
                            </li>
                        </ul>
                        
                        <div class="mt-4">
                            <?php if ($currentPackage && $currentPackage['slug'] === 'platinum'): ?>
                                <button class="btn current-plan-btn w-100" disabled>
                                    <i class="bi bi-check-circle-fill me-2"></i>Current Plan
                                </button>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/premium/subscribe/platinum" class="btn upgrade-btn text-white w-100">
                                    <i class="bi bi-star-fill me-2"></i>Go Platinum
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Testimonials -->
    <div class="container py-5">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2>What Our Members Say</h2>
                <p class="text-muted">Real stories from real people</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <img src="<?= BASE_URL ?>/public/assets/images/testimonial1.jpg" alt="User" class="rounded-circle mb-3" width="80" height="80">
                    <div class="testimonial-stars">★★★★★</div>
                    <p>"Found my soulmate within 2 months of upgrading to Premium. The video calling feature really helped us connect!"</p>
                    <strong>Sarah & David</strong>
                    <small class="text-muted d-block">Premium Members</small>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="testimonial-card">
                    <img src="<?= BASE_URL ?>/public/assets/images/testimonial2.jpg" alt="User" class="rounded-circle mb-3" width="80" height="80">
                    <div class="testimonial-stars">★★★★★</div>
                    <p>"The advanced filters saved me so much time. I could find exactly what I was looking for. Worth every rupee!"</p>
                    <strong>Priya & Rajesh</strong>
                    <small class="text-muted d-block">Platinum Members</small>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="testimonial-card">
                    <img src="<?= BASE_URL ?>/public/assets/images/testimonial3.jpg" alt="User" class="rounded-circle mb-3" width="80" height="80">
                    <div class="testimonial-stars">★★★★★</div>
                    <p>"Started with Basic and upgraded after seeing the quality of matches. Best investment for my future!"</p>
                    <strong>Amit & Kavya</strong>
                    <small class="text-muted d-block">Premium Members</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- FAQ Section -->
    <div class="container py-5">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2>Frequently Asked Questions</h2>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Can I change my plan at any time?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! You can upgrade or downgrade your plan at any time. When you upgrade, you get immediate access to new features. When you downgrade, changes take effect at your next billing cycle.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept all major credit/debit cards, UPI, net banking, and digital wallets. All payments are processed securely through our trusted payment partners.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Is there a money-back guarantee?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We offer a 7-day money-back guarantee for all Premium and Platinum plans. If you're not satisfied within the first week, we'll provide a full refund.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 