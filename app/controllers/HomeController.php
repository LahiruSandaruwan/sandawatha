<?php
require_once 'BaseController.php';
require_once SITE_ROOT . '/app/models/ProfileModel.php';
require_once SITE_ROOT . '/app/models/UserModel.php';

class HomeController extends BaseController {
    private $profileModel;
    private $userModel;
    
    public function __construct() {
        $this->profileModel = new ProfileModel();
        $this->userModel = new UserModel();
    }
    
    public function index() {
        // Get current user ID if logged in
        $currentUserId = $_SESSION['user_id'] ?? null;
        
        // Get recent profiles for homepage showcase
        $recentProfiles = $this->profileModel->getRecentProfiles(6, $currentUserId);
        
        // Get site statistics
        $stats = $this->profileModel->getProfileStats();
        $userStats = $this->userModel->getUserStats();
        
        // Combine stats
        $siteStats = [
            'total_profiles' => $stats['total_profiles'] ?? 0,
            'male_profiles' => $stats['male_profiles'] ?? 0,
            'female_profiles' => $stats['female_profiles'] ?? 0,
            'active_users' => $userStats['active_users'] ?? 0,
            'success_stories' => 127, // Mock data
            'marriages_facilitated' => 89 // Mock data
        ];
        
        // Get district and religion statistics for charts
        $districtStats = $this->profileModel->getDistrictStats();
        $religionStats = $this->profileModel->getReligionStats();
        
        $data = [
            'title' => 'Sandawatha.lk - Find Your Perfect Match in Sri Lanka',
            'description' => 'Join thousands of verified profiles on Sri Lanka\'s most trusted matrimonial platform. Find your life partner with advanced matching, AI compatibility, and secure messaging.',
            'recent_profiles' => $recentProfiles,
            'stats' => $siteStats,
            'district_stats' => $districtStats,
            'religion_stats' => $religionStats,
            'csrf_token' => $this->generateCsrf(),
            'component_css' => ['home/home'],
            'scripts' => ['home/homepage']
        ];
        
        $this->layout('main', 'home/index', $data);
    }
}
?>