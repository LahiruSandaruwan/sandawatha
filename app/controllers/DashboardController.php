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
        $recent_requests = $this->contactModel->getReceivedRequests($currentUser['id'], 1, 5);
        $contact_stats   = $this->contactModel->getRequestStats($currentUser['id']);
        
        // Get suggested matches
        $suggestedMatches = $this->profileModel->getSimilarProfiles($currentUser['id'], 4);
        
        // Get recent visitors
        $recentVisitors = $this->profileModel->getRecentVisitors($currentUser['id'], 5);
        
        // Get premium features
        $premium_membership = $this->premiumModel->getActiveMembership($currentUser['id']);
        $premium_features   = $this->premiumModel->getUserFeatures($currentUser['id']);
        
        $profile_completion = isset($profile['profile_completion'])
            ? (int)$profile['profile_completion']
            : 0;

        $data = [
            'title'              => 'Dashboard - Sandawatha.lk',
            'profile'            => $profile,
            'profile_views'      => $profileViews,
            'recent_requests'    => $recent_requests,
            'contact_stats'      => $contact_stats,
            'favorite_stats'     => $this->favoriteModel->getFavoriteStats($currentUser['id']),
            'suggested_matches'  => $suggestedMatches,
            'recent_visitors'    => $recentVisitors,
            'profile_completion' => $profile_completion,
            'premium_membership' => $premium_membership,
            'premium_features'   => $premium_features,
            'csrf_token'         => $this->generateCsrf(),
            'component_css'      => ['dashboard/dashboard'],
            'scripts'           => ['dashboard/dashboard']
        ];
        
        $this->layout('main', 'dashboard/index', $data);
    }
}
?>