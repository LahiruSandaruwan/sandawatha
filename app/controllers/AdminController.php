<?php
require_once 'BaseController.php';
require_once SITE_ROOT . '/app/models/UserModel.php';
require_once SITE_ROOT . '/app/models/ProfileModel.php';
require_once SITE_ROOT . '/app/models/PremiumModel.php';
require_once SITE_ROOT . '/app/models/MessageModel.php';
require_once SITE_ROOT . '/app/models/FeedbackModel.php';

class AdminController extends BaseController {
    private $userModel;
    private $profileModel;
    private $premiumModel;
    private $messageModel;
    private $feedbackModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
        $this->profileModel = new ProfileModel();
        $this->premiumModel = new PremiumModel();
        $this->messageModel = new MessageModel();
        $this->feedbackModel = new FeedbackModel();
    }
    
    public function dashboard() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['role'] !== 'admin') {
            $this->redirect('/dashboard');
        }
        
        // Get dashboard statistics
        $userStats = $this->userModel->getUserStats();
        $profileStats = $this->profileModel->getProfileStats();
        $premiumStats = $this->premiumModel->getPremiumUserStats();
        $revenueData = $this->premiumModel->getRevenueByMonth(12);
        
        // Recent activities
        $recentUsers = $this->userModel->getRecentUsers(10);
        $recentProfiles = $this->profileModel->getRecentProfiles(10);
        $expiringMemberships = $this->premiumModel->getExpiringMemberships(7);
        $recentFeedback = $this->feedbackModel->getRecentFeedback(5);
        
        // System health checks
        $systemHealth = [
            'database_status' => 'OK',
            'file_permissions' => is_writable(UPLOAD_PATH) ? 'OK' : 'ERROR',
            'disk_usage' => $this->getDiskUsage(),
            'pending_verifications' => $userStats['pending_users']
        ];
        
        $data = [
            'title' => 'Admin Dashboard - Sandawatha.lk',
            'user_stats' => $userStats,
            'profile_stats' => $profileStats,
            'premium_stats' => $premiumStats,
            'revenue_data' => $revenueData,
            'recent_users' => $recentUsers,
            'recent_profiles' => $recentProfiles,
            'expiring_memberships' => $expiringMemberships,
            'recent_feedback' => $recentFeedback,
            'system_health' => $systemHealth,
            'scripts' => ['admin-dashboard']
        ];
        
        $this->layout('admin', 'admin/dashboard', $data);
    }
    
    public function users() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['role'] !== 'admin') {
            $this->redirect('/dashboard');
        }
        
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $users = $this->userModel->getUsersForAdmin($page, $search, $status);
        
        $data = [
            'title' => 'User Management - Admin',
            'users' => $users,
            'current_page' => $page,
            'search_query' => $search,
            'status_filter' => $status,
            'csrf_token' => $this->generateCsrf(),
            'scripts' => ['admin-users']
        ];
        
        $this->layout('admin', 'admin/users', $data);
    }
    
    public function approveUser($userId) {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['role'] !== 'admin') {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        try {
            $success = $this->userModel->updateStatus($userId, 'active');
            
            if ($success) {
                // Send welcome message
                $this->messageModel->sendMessage(
                    $currentUser['id'],
                    $userId,
                    'Welcome to Sandawatha.lk!',
                    'Your account has been approved. You can now start browsing profiles and connecting with potential matches. Complete your profile to get better visibility.',
                    true
                );
                
                $this->logUserActivity($currentUser['id'], 'user_approved', "Approved user ID: {$userId}");
                
                $this->json([
                    'success' => true,
                    'message' => 'User approved successfully!'
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to approve user'], 400);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function blockUser($userId) {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['role'] !== 'admin') {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        try {
            $success = $this->userModel->updateStatus($userId, 'blocked');
            
            if ($success) {
                $this->logUserActivity($currentUser['id'], 'user_blocked', "Blocked user ID: {$userId}");
                
                $this->json([
                    'success' => true,
                    'message' => 'User blocked successfully!'
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to block user'], 400);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function unblockUser($userId) {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['role'] !== 'admin') {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        try {
            $success = $this->userModel->updateStatus($userId, 'active');
            
            if ($success) {
                $this->logUserActivity($currentUser['id'], 'user_unblocked', "Unblocked user ID: {$userId}");
                
                $this->json([
                    'success' => true,
                    'message' => 'User unblocked successfully!'
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to unblock user'], 400);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function feedback() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['role'] !== 'admin') {
            $this->redirect('/dashboard');
        }
        
        $page = $_GET['page'] ?? 1;
        $rating = $_GET['rating'] ?? '';
        
        $feedback = $this->feedbackModel->getAllFeedback($page, $rating);
        $stats = $this->feedbackModel->getFeedbackStats();
        
        $data = [
            'title' => 'Feedback Management - Admin',
            'feedback' => $feedback,
            'stats' => $stats,
            'current_page' => $page,
            'rating_filter' => $rating,
            'csrf_token' => $this->generateCsrf(),
            'scripts' => ['admin-feedback']
        ];
        
        $this->layout('admin', 'admin/feedback', $data);
    }
    
    public function messages() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['role'] !== 'admin') {
            $this->redirect('/dashboard');
        }
        
        $data = [
            'title' => 'Message Center - Admin',
            'csrf_token' => $this->generateCsrf(),
            'scripts' => ['admin-messages']
        ];
        
        $this->layout('admin', 'admin/messages', $data);
    }
    
    public function sendMessage() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['role'] !== 'admin') {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        $type = $_POST['type'] ?? '';
        $subject = $this->sanitizeInput($_POST['subject'] ?? '');
        $message = $this->sanitizeInput($_POST['message'] ?? '');
        $userId = $_POST['user_id'] ?? '';
        
        if (empty($subject) || empty($message)) {
            $this->json(['success' => false, 'message' => 'Subject and message are required'], 400);
        }
        
        try {
            if ($type === 'broadcast') {
                $sentCount = $this->messageModel->broadcastAdminMessage($subject, $message, $currentUser['id']);
                $this->json([
                    'success' => true,
                    'message' => "Broadcast message sent to {$sentCount} users!"
                ]);
            } elseif ($type === 'individual' && $userId) {
                $messageId = $this->messageModel->sendMessage($currentUser['id'], $userId, $subject, $message, true);
                $this->json([
                    'success' => true,
                    'message' => 'Message sent successfully!',
                    'message_id' => $messageId
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Invalid message type or missing user ID'], 400);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function settings() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['role'] !== 'admin') {
            $this->redirect('/dashboard');
        }
        
        require_once SITE_ROOT . '/app/models/SiteSettingsModel.php';
        $settingsModel = new SiteSettingsModel();
        $settings = $settingsModel->getAllSettings();
        
        $data = [
            'title' => 'Site Settings - Admin',
            'settings' => $settings,
            'csrf_token' => $this->generateCsrf(),
            'scripts' => ['admin-settings']
        ];
        
        $this->layout('admin', 'admin/settings', $data);
    }
    
    public function updateSettings() {
        if (!$this->validateCsrf()) {
            $this->redirectWithMessage('/admin/settings', 'Invalid security token.', 'error');
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['role'] !== 'admin') {
            $this->redirect('/dashboard');
        }
        
        require_once SITE_ROOT . '/app/models/SiteSettingsModel.php';
        $settingsModel = new SiteSettingsModel();
        
        $settings = $_POST;
        unset($settings['csrf_token']);
        
        try {
            foreach ($settings as $key => $value) {
                $settingsModel->updateSetting($key, $this->sanitizeInput($value));
            }
            
            $this->logUserActivity($currentUser['id'], 'settings_updated', 'Updated site settings');
            $this->redirectWithMessage('/admin/settings', 'Settings updated successfully!', 'success');
        } catch (Exception $e) {
            $this->redirectWithMessage('/admin/settings', 'Failed to update settings: ' . $e->getMessage(), 'error');
        }
    }
    
    private function getDiskUsage() {
        $uploadPath = UPLOAD_PATH;
        if (!is_dir($uploadPath)) {
            return 'Unknown';
        }
        
        $totalSize = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadPath));
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $totalSize += $file->getSize();
            }
        }
        
        return $this->formatBytes($totalSize);
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
?>