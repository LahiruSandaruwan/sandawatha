<?php
require_once 'BaseController.php';
require_once SITE_ROOT . '/app/models/ProfileModel.php';
require_once SITE_ROOT . '/app/models/ContactRequestModel.php';
require_once SITE_ROOT . '/app/models/FavoriteModel.php';
require_once SITE_ROOT . '/app/models/PremiumModel.php';

class DashboardController extends BaseController {
    private $profileModel;
    private $contactModel;
    private $favoriteModel;
    private $premiumModel;
    
    public function __construct() {
        $this->profileModel = new ProfileModel();
        $this->contactModel = new ContactRequestModel();
        $this->favoriteModel = new FavoriteModel();
        $this->premiumModel = new PremiumModel();
    }
    
    public function index() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->redirect('/login');
        }
        
        $profile = $this->profileModel->findByUserId($currentUser['id']);
        
        // Get dashboard statistics with error handling
        try {
            $contactStats = $this->contactModel->getRequestStats($currentUser['id']);
        } catch (Exception $e) {
            error_log("Error getting contact stats: " . $e->getMessage());
            $contactStats = ['pending_received' => 0, 'pending_sent' => 0, 'accepted' => 0, 'rejected' => 0];
        }
        
        try {
            $favoriteStats = $this->favoriteModel->getFavoriteStats($currentUser['id']);
        } catch (Exception $e) {
            error_log("Error getting favorite stats: " . $e->getMessage());
            $favoriteStats = ['my_favorites' => 0, 'favorited_by' => 0];
        }
        
        // Get recent activities
        $recentContactRequests = $this->contactModel->getReceivedRequests($currentUser['id'], 1, 5);
        $recentFavorites = $this->favoriteModel->getRecentFavorites($currentUser['id'], 7);
        
        // Get profile views if profile exists
        $profileViews = [];
        if ($profile) {
            $profileViews = $this->profileModel->getProfileViews($profile['id'], 30);
        }
        
        // Check premium status
        $premiumMembership = $this->premiumModel->getActiveMembership($currentUser['id']);
        $premiumFeatures = $this->premiumModel->getUserFeatures($currentUser['id']);
        
        // Get suggested matches
        $suggestedMatches = [];
        if ($profile) {
            $suggestedMatches = $this->profileModel->getSimilarProfiles($profile['id'], 4);
        }
        
        // Calculate profile completion
        $profileCompletion = $profile['profile_completion'] ?? 0;
        
        $data = [
            'title' => 'Dashboard - Sandawatha.lk',
            'user' => $currentUser,
            'profile' => $profile,
            'contact_stats' => $contactStats,
            'favorite_stats' => $favoriteStats,
            'recent_requests' => $recentContactRequests,
            'recent_favorites' => $recentFavorites,
            'profile_views' => $profileViews,
            'premium_membership' => $premiumMembership,
            'premium_features' => $premiumFeatures,
            'suggested_matches' => $suggestedMatches,
            'profile_completion' => $profileCompletion
        ];
        
        $this->layout('main', 'dashboard/index', $data);
    }
}
?>