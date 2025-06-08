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
        if (!$profile) {
            $this->redirectWithMessage('/profile/edit', 'Please complete your profile first.', 'info');
        }
        
        // Get profile views
        $profileViews = $this->profileModel->getProfileViews($currentUser['id'], 30);
        
        // Get contact requests
        $contactRequests = $this->contactModel->getReceivedRequests($currentUser['id'], 1, 5);
        
        // Get suggested matches
        $suggestedMatches = $this->profileModel->getSimilarProfiles($currentUser['id'], 4);
        
        // Get recent visitors
        $recentVisitors = $this->profileModel->getRecentVisitors($currentUser['id'], 5);
        
        // Get premium features
        $features = $this->premiumModel->getUserFeatures($currentUser['id']);
        
        $data = [
            'title' => 'Dashboard - Sandawatha.lk',
            'profile' => $profile,
            'profile_views' => $profileViews,
            'contact_requests' => $contactRequests,
            'suggested_matches' => $suggestedMatches,
            'recent_visitors' => $recentVisitors,
            'features' => $features,
            'csrf_token' => $this->generateCsrf(),
            'scripts' => ['dashboard']
        ];
        
        $this->layout('main', 'dashboard/index', $data);
    }
}
?>