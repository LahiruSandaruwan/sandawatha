<?php
require_once 'BaseController.php';
require_once SITE_ROOT . '/app/models/PremiumModel.php';

class PremiumController extends BaseController {
    private $premiumModel;
    
    public function __construct() {
        $this->premiumModel = new PremiumModel();
    }
    
    public function plans() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->redirect('/login');
        }
        
        $currentMembership = $this->premiumModel->getActiveMembership($currentUser['id']);
        $membershipHistory = $this->premiumModel->getUserMemberships($currentUser['id']);
        
        $plans = [
            'basic' => [
                'name' => 'Basic Plan',
                'price' => 500,
                'duration' => '1 Month',
                'features' => [
                    '3 contact requests per day',
                    '20 profile views per day',
                    '5 messages per day',
                    'Basic search filters',
                    'Email support'
                ],
                'color' => 'primary'
            ],
            'premium' => [
                'name' => 'Premium Plan',
                'price' => 1000,
                'duration' => '1 Month',
                'popular' => true,
                'features' => [
                    '10 contact requests per day',
                    '100 profile views per day',
                    '25 messages per day',
                    'See who viewed your profile',
                    'Priority listing in search',
                    'AI-powered matching',
                    'Advanced search filters',
                    'Priority support'
                ],
                'color' => 'success'
            ],
            'platinum' => [
                'name' => 'Platinum Plan',
                'price' => 1500,
                'duration' => '1 Month',
                'features' => [
                    'Unlimited contact requests',
                    'Unlimited profile views',
                    'Unlimited messages',
                    'See who viewed your profile',
                    'Priority listing in search',
                    'AI-powered matching',
                    'Advanced search filters',
                    'Horoscope compatibility',
                    'Video call feature',
                    'Dedicated support manager'
                ],
                'color' => 'warning'
            ]
        ];
        
        $data = [
            'title' => 'Premium Plans - Sandawatha.lk',
            'plans' => $plans,
            'current_membership' => $currentMembership,
            'membership_history' => $membershipHistory,
            'csrf_token' => $this->generateCsrf(),
            'scripts' => ['premium']
        ];
        
        $this->layout('main', 'premium/plans', $data);
    }
    
    public function subscribe() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $planType = $_POST['plan_type'] ?? '';
        $duration = (int)($_POST['duration'] ?? 1);
        
        if (!in_array($planType, ['basic', 'premium', 'platinum'])) {
            $this->json(['success' => false, 'message' => 'Invalid plan type'], 400);
        }
        
        if ($duration < 1 || $duration > 12) {
            $this->json(['success' => false, 'message' => 'Duration must be between 1-12 months'], 400);
        }
        
        try {
            // Check if user already has active membership
            $existingMembership = $this->premiumModel->getActiveMembership($currentUser['id']);
            if ($existingMembership) {
                // Extend existing membership instead of creating new one
                $this->premiumModel->extendMembership($currentUser['id'], $duration);
                $message = 'Membership extended successfully!';
            } else {
                $membershipId = $this->premiumModel->createMembership($currentUser['id'], $planType, $duration);
                $message = 'Premium membership activated successfully!';
            }
            
            // Log activity
            $this->logUserActivity($currentUser['id'], 'premium_subscription', "Subscribed to {$planType} plan for {$duration} month(s)");
            
            // Send confirmation email (mock)
            $this->sendPremiumConfirmationEmail($currentUser, $planType, $duration);
            
            $this->json([
                'success' => true,
                'message' => $message,
                'redirect' => BASE_URL . '/premium?success=1'
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    private function sendPremiumConfirmationEmail($user, $planType, $duration) {
        $planNames = [
            'basic' => 'Basic Plan',
            'premium' => 'Premium Plan', 
            'platinum' => 'Platinum Plan'
        ];
        
        $planPrices = [
            'basic' => 500,
            'premium' => 1000,
            'platinum' => 1500
        ];
        
        $subject = 'Premium Membership Confirmation - Sandawatha.lk';
        $body = "
            <h2>Premium Membership Activated!</h2>
            <p>Dear {$user['email']},</p>
            <p>Thank you for upgrading to our premium membership. Your subscription details:</p>
            <ul>
                <li><strong>Plan:</strong> {$planNames[$planType]}</li>
                <li><strong>Duration:</strong> {$duration} month(s)</li>
                <li><strong>Total Amount:</strong> LKR " . number_format($planPrices[$planType] * $duration) . "</li>
                <li><strong>Activated:</strong> " . date('M d, Y') . "</li>
            </ul>
            <p>You can now enjoy all premium features including:</p>
            <ul>
                <li>Enhanced profile visibility</li>
                <li>Advanced matching algorithms</li>
                <li>Priority customer support</li>
                <li>And much more!</li>
            </ul>
            <p>Thank you for choosing Sandawatha.lk!</p>
            <p>Best regards,<br>Sandawatha.lk Team</p>
        ";
        
        return $this->sendEmail($user['email'], $subject, $body);
    }
}
?>