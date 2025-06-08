<?php
require_once 'BaseController.php';
require_once SITE_ROOT . '/app/models/ProfileModel.php';
require_once SITE_ROOT . '/app/models/UserModel.php';

class ProfileController extends BaseController {
    private $profileModel;
    private $userModel;
    
    public function __construct() {
        $this->profileModel = new ProfileModel();
        $this->userModel = new UserModel();
    }
    
    public function browse() {
        $page = $_GET['page'] ?? 1;
        $currentUserId = $_SESSION['user_id'] ?? null;
        
        // Get filters from request
        $filters = [
            'gender' => $_GET['gender'] ?? '',
            'religion' => $_GET['religion'] ?? '',
            'district' => $_GET['district'] ?? '',
            'age_min' => $_GET['age_min'] ?? '',
            'age_max' => $_GET['age_max'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        // Remove empty filters
        $filters = array_filter($filters);
        
        $profiles = $this->profileModel->searchProfiles($filters, $currentUserId, $page, 12);
        $stats = $this->profileModel->getProfileStats();
        
        $data = [
            'title' => 'Browse Profiles - Sandawatha.lk',
            'profiles' => $profiles,
            'stats' => $stats,
            'filters' => $filters,
            'current_page' => $page,
            'scripts' => ['search']
        ];
        
        $this->layout('main', 'profiles/browse', $data);
    }
    
    public function viewProfile($userId) {
        try {
            $currentUserId = $_SESSION['user_id'] ?? null;
            $profile = $this->profileModel->findByUserId($userId);
            
            if (!$profile) {
                $this->redirectWithMessage('/', 'Profile not found or is not accessible.', 'error');
                return;
            }
            
            // Get user details
            require_once SITE_ROOT . '/app/models/UserModel.php';
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            
            if (!$user || $user['status'] !== 'active') {
                $this->redirectWithMessage('/', 'Profile not found or is not accessible.', 'error');
                return;
            }
            
            // Merge user details with profile
            $profile['email'] = $user['email'];
            $profile['phone'] = $user['phone'];
            $profile['user_status'] = $user['status'];
            $profile['member_since'] = $user['created_at'];
            
            // If viewer is the profile owner, return full profile
            if ($currentUserId && $currentUserId == $userId) {
                // Skip privacy checks for own profile
            } else {
                // Apply privacy settings
                $privacySettings = json_decode($profile['privacy_settings'], true) ?? [
                    'photo' => 'public',
                    'contact' => 'private',
                    'horoscope' => 'private',
                    'income' => 'private',
                    'bio' => 'public',
                    'education' => 'public',
                    'occupation' => 'public',
                    'goals' => 'private'
                ];

                // Fields that are always public
                $publicFields = [
                    'id', 'user_id', 'first_name', 'last_name', 'gender',
                    'religion', 'district', 'marital_status', 'height_cm',
                    'view_count', 'profile_completion', 'created_at', 'updated_at'
                ];

                // Fields that require contact acceptance
                $privateFields = [
                    'email', 'phone', 'caste', 'city', 'income_lkr',
                    'wants_migration', 'career_focused', 'wants_early_marriage',
                    'horoscope_file'
                ];

                // Get contact status
                $contactStatus = null;
                if ($currentUserId) {
                    require_once SITE_ROOT . '/app/models/ContactRequestModel.php';
                    $contactModel = new ContactRequestModel();
                    $contactRequest = $contactModel->getRequestBetweenUsers($currentUserId, $userId);
                    $contactStatus = $contactRequest['status'] ?? null;
                }

                // Process each field based on privacy settings
                foreach ($profile as $field => $value) {
                    // Skip public fields
                    if (in_array($field, $publicFields)) {
                        continue;
                    }

                    // Process private fields
                    if (in_array($field, $privateFields)) {
                        if ($contactStatus !== 'accepted') {
                            unset($profile[$field]);
                            continue;
                        }
                    }

                    // Process configurable fields
                    if (isset($privacySettings[$field])) {
                        if ($privacySettings[$field] === 'private' && $contactStatus !== 'accepted') {
                            unset($profile[$field]);
                        }
                    }
                }

                // Special handling for profile photo
                if ($privacySettings['photo'] === 'private' && $contactStatus !== 'accepted') {
                    $profile['profile_photo'] = null;
                }
            }
            
            // Increment view count if user is logged in and viewing someone else's profile
            if ($currentUserId && $currentUserId != $userId) {
                try {
                    $this->profileModel->incrementViewCount($userId, $currentUserId);
                } catch (Exception $e) {
                    error_log("Error incrementing view count: " . $e->getMessage());
                    // Continue execution even if view count fails
                }
            }
            
            // Get similar profiles
            try {
                $similarProfiles = $this->profileModel->getSimilarProfiles($userId, 6);
            } catch (Exception $e) {
                error_log("Error getting similar profiles: " . $e->getMessage());
                $similarProfiles = [];
            }
            
            // Calculate age
            $profile['age'] = $this->calculateAge($profile['date_of_birth']);
            
            // Check if profile is in favorites
            $isFavorite = false;
            if ($currentUserId) {
                try {
                    require_once SITE_ROOT . '/app/models/FavoriteModel.php';
                    $favoriteModel = new FavoriteModel();
                    $isFavorite = $favoriteModel->isFavorite($currentUserId, $userId);
                } catch (Exception $e) {
                    error_log("Error checking favorite status: " . $e->getMessage());
                }
            }
            
            $data = [
                'title' => $profile['first_name'] . ' ' . $profile['last_name'] . ' - Profile',
                'profile' => $profile,
                'similar_profiles' => $similarProfiles,
                'contact_status' => $contactStatus ?? null,
                'is_favorite' => $isFavorite,
                'csrf_token' => $this->generateCsrf(),
                'scripts' => ['profile-view']
            ];
            
            $this->layout('main', 'profiles/view', $data);
            
        } catch (Exception $e) {
            error_log("Error in viewProfile: " . $e->getMessage());
            $this->redirectWithMessage('/', 'An error occurred while loading the profile. Please try again later.', 'error');
        }
    }
    
    public function edit() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->redirect('/login');
        }
        
        $profile = $this->profileModel->findByUserId($currentUser['id']);
        
        $data = [
            'title' => 'Edit Profile - Sandawatha.lk',
            'profile' => $profile,
            'user' => $currentUser,
            'csrf_token' => $this->generateCsrf(),
            'districts' => $this->getDistricts(),
            'religions' => $this->getReligions(),
            'castes' => $this->getCastes(),
            'education_levels' => $this->getEducationLevels(),
            'scripts' => ['profile-edit']
        ];
        
        $this->layout('main', 'profiles/edit', $data);
    }
    
    public function update() {
        if (!$this->validateCsrf()) {
            $this->redirectWithMessage('/profile/edit', 'Invalid security token.', 'error');
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->redirect('/login');
        }
        
        $data = $this->sanitizeInput($_POST);
        
        // Handle privacy settings
        if (isset($data['privacy_settings']) && is_array($data['privacy_settings'])) {
            $privacySettings = [];
            $allowedValues = ['public', 'private'];
            $allowedFields = ['photo', 'contact', 'horoscope', 'income', 'bio', 'education', 'occupation', 'goals'];
            
            foreach ($allowedFields as $field) {
                $value = $data['privacy_settings'][$field] ?? 'private';
                $privacySettings[$field] = in_array($value, $allowedValues) ? $value : 'private';
            }
            
            $data['privacy_settings'] = json_encode($privacySettings);
        }
        
        // Decode HTML entities for education field
        if (isset($data['education'])) {
            $data['education'] = html_entity_decode($data['education'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'date_of_birth', 'gender', 'religion', 'district', 'height_cm', 'education'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->redirectWithMessage('/profile/edit', "Please fill in the {$field} field.", 'error');
            }
        }
        
        // Validate date of birth (must be at least 18 years old)
        $birthDate = new DateTime($data['date_of_birth']);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        
        if ($age < 18) {
            $this->redirectWithMessage('/profile/edit', 'You must be at least 18 years old.', 'error');
        }
        
        // Validate height
        if ($data['height_cm'] < 100 || $data['height_cm'] > 250) {
            $this->redirectWithMessage('/profile/edit', 'Please enter a valid height between 100-250 cm.', 'error');
        }
        
        // Validate income if provided
        if (!empty($data['income_lkr']) && $data['income_lkr'] < 0) {
            $this->redirectWithMessage('/profile/edit', 'Income cannot be negative.', 'error');
        }
        
        // Clean up boolean fields
        $data['wants_migration'] = isset($_POST['wants_migration']) ? 1 : 0;
        $data['career_focused'] = isset($_POST['career_focused']) ? 1 : 0;
        $data['wants_early_marriage'] = isset($_POST['wants_early_marriage']) ? 1 : 0;
        
        // Remove CSRF token from data
        unset($data['csrf_token']);
        
        try {
            $existingProfile = $this->profileModel->findByUserId($currentUser['id']);
            
            if ($existingProfile) {
                $success = $this->profileModel->updateProfile($currentUser['id'], $data);
            } else {
                $success = $this->profileModel->createProfile($currentUser['id'], $data);
            }
            
            if ($success) {
                // Update profile completion percentage
                $this->profileModel->updateProfileCompletion($currentUser['id']);
                
                // Update session with user's full name
                $_SESSION['user_name'] = trim($data['first_name'] . ' ' . $data['last_name']);
                
                $this->redirectWithMessage('/profile/edit', 'Profile updated successfully!', 'success');
            } else {
                $this->redirectWithMessage('/profile/edit', 'Failed to update profile.', 'error');
            }
        } catch (Exception $e) {
            $this->redirectWithMessage('/profile/edit', 'Error updating profile: ' . $e->getMessage(), 'error');
        }
    }
    
    public function uploadPhoto() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        try {
            $filename = $this->uploadFile(
                $_FILES['photo'],
                'profiles',
                ALLOWED_IMAGE_TYPES,
                MAX_PROFILE_PHOTO_SIZE
            );
            
            // Delete old photo if exists
            $profile = $this->profileModel->findByUserId($currentUser['id']);
            if ($profile && $profile['profile_photo']) {
                $this->deleteFile($profile['profile_photo']);
            }
            
            // Update profile with new photo
            $this->profileModel->updateProfile($currentUser['id'], ['profile_photo' => $filename]);
            
            $this->json([
                'success' => true,
                'message' => 'Photo uploaded successfully',
                'filename' => $filename,
                'url' => UPLOAD_URL . $filename
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function uploadHoroscope() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        try {
            $filename = $this->uploadFile(
                $_FILES['horoscope'],
                'horoscopes',
                ALLOWED_DOCUMENT_TYPES,
                MAX_DOCUMENT_SIZE
            );
            
            // Delete old horoscope if exists
            $profile = $this->profileModel->findByUserId($currentUser['id']);
            if ($profile && $profile['horoscope_file']) {
                $this->deleteFile($profile['horoscope_file']);
            }
            
            // Update profile with new horoscope
            $this->profileModel->updateProfile($currentUser['id'], ['horoscope_file' => $filename]);
            
            $this->json([
                'success' => true,
                'message' => 'Horoscope uploaded successfully',
                'filename' => $filename,
                'url' => UPLOAD_URL . $filename
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function uploadHealth() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        try {
            $filename = $this->uploadFile(
                $_FILES['health_report'],
                'health',
                ALLOWED_DOCUMENT_TYPES,
                MAX_DOCUMENT_SIZE
            );
            
            // Mock health risk analysis
            $healthRisks = $this->analyzeHealthReport($filename);
            
            // Delete old health report if exists
            $profile = $this->profileModel->findByUserId($currentUser['id']);
            if ($profile && $profile['health_report']) {
                $this->deleteFile($profile['health_report']);
            }
            
            // Update profile with new health report
            $this->profileModel->updateProfile($currentUser['id'], [
                'health_report' => $filename,
                'health_risks' => json_encode($healthRisks)
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Health report uploaded successfully',
                'filename' => $filename,
                'url' => UPLOAD_URL . $filename,
                'risks' => $healthRisks
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function search() {
        $filters = $this->sanitizeInput($_POST);
        $currentUserId = $_SESSION['user_id'] ?? null;
        
        $profiles = $this->profileModel->searchProfiles($filters, $currentUserId, 1, 20);
        
        $this->json([
            'success' => true,
            'profiles' => $profiles,
            'count' => count($profiles)
        ]);
    }
    
    private function calculateAge($birthDate) {
        $birth = new DateTime($birthDate);
        $today = new DateTime();
        return $today->diff($birth)->y;
    }
    
    private function analyzeHealthReport($filename) {
        // Mock health risk analysis
        // In a real application, this would integrate with medical APIs or AI services
        $risks = [];
        
        // Simulate random risk factors based on filename or content analysis
        $possibleRisks = [
            'Thalassemia carrier detected',
            'High cholesterol levels',
            'Diabetes risk factors present',
            'Vitamin D deficiency',
            'Iron deficiency anemia'
        ];
        
        // Randomly select 0-2 risk factors for demo
        $numRisks = rand(0, 2);
        $selectedRisks = array_rand(array_flip($possibleRisks), $numRisks);
        
        if ($numRisks === 1) {
            $risks[] = $selectedRisks;
        } elseif ($numRisks > 1) {
            $risks = $selectedRisks;
        }
        
        return $risks;
    }
    
    private function getDistricts() {
        return [
            'Colombo', 'Gampaha', 'Kalutara', 'Kandy', 'Matale', 'Nuwara Eliya',
            'Galle', 'Matara', 'Hambantota', 'Jaffna', 'Kilinochchi', 'Mannar',
            'Vavuniya', 'Mullaitivu', 'Batticaloa', 'Ampara', 'Trincomalee',
            'Kurunegala', 'Puttalam', 'Anuradhapura', 'Polonnaruwa', 'Badulla',
            'Moneragala', 'Ratnapura', 'Kegalle'
        ];
    }
    
    private function getReligions() {
        return [
            'Buddhist', 'Hindu', 'Muslim', 'Christian', 'Roman Catholic', 'Other'
        ];
    }
    
    private function getCastes() {
        return [
            'Govigama', 'Karava', 'Salagama', 'Durava', 'Wahumpura',
            'Bathgama', 'Deva', 'Rada', 'Other', 'Prefer not to say'
        ];
    }
    
    private function getEducationLevels() {
        return [
            'O/L', 'A/L', 'Diploma', 'Bachelor\'s Degree', 'Master\'s Degree',
            'PhD/Doctorate', 'Professional Qualification', 'Other'
        ];
    }
}
?>