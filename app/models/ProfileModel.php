<?php

namespace App\models;

use PDO;
use Exception;

class ProfileModel extends BaseModel {
    protected $table = 'user_profiles';
    
    protected function getAllowedColumns() {
        return [
            'id', 'user_id', 'first_name', 'last_name', 'date_of_birth', 'gender',
            'religion', 'caste', 'district', 'city', 'marital_status', 'height_cm',
            'education', 'occupation', 'income_lkr', 'bio', 'goals',
            'wants_migration', 'career_focused', 'wants_early_marriage',
            'profile_photo', 'horoscope_file',
            'view_count', 'profile_completion', 'privacy_settings',
            'created_at', 'updated_at'
        ];
    }
    
    public function createProfile($userId, $data) {
        try {
            $this->db->beginTransaction();
            
            $profileData = array_merge($data, [
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Create the profile
            $success = $this->create($profileData);
            
            if ($success) {
                // Calculate and update profile completion
                $this->updateProfileCompletion($userId);
                $this->db->commit();
                return true;
            }
            
            $this->db->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in createProfile: " . $e->getMessage());
            return false;
        }
    }
    
    public function findByUserId($userId) {
        try {
            $sql = "SELECT p.*, u.email, u.phone, u.status as user_status
                    FROM users u
                    LEFT JOIN {$this->table} p ON p.user_id = u.id
                    WHERE u.id = :user_id";
            
            $profile = $this->fetchOne($sql, [':user_id' => $userId]);
            
            // If no profile exists, return default values
            if (!$profile || !isset($profile['first_name'])) {
                return [
                    'user_id' => $userId,
                    'first_name' => 'User',
                    'last_name' => $userId,
                    'email' => $profile['email'] ?? '',
                    'phone' => $profile['phone'] ?? '',
                    'user_status' => $profile['user_status'] ?? 'active'
                ];
            }
            
            return $profile;
        } catch (Exception $e) {
            error_log("Error in findByUserId: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateProfile($userId, $data) {
        $sql = "UPDATE {$this->table} 
                SET " . implode(', ', array_map(fn($key) => "{$key} = :{$key}", array_keys($data))) . ",
                    updated_at = NOW()
                WHERE user_id = :user_id";
        
        $params = array_merge($data, [':user_id' => $userId]);
        return $this->execute($sql, $params);
    }
    
    public function getProfileWithUser($userId, $viewerId = null) {
        try {
            // Single optimized query to get all necessary data
            $sql = "SELECT 
                    up.*,
                    u.email,
                    u.status as user_status,
                    u.role,
                    u.created_at as member_since,
                    TIMESTAMPDIFF(YEAR, up.date_of_birth, CURDATE()) as age,
                    CASE WHEN f.id IS NOT NULL THEN 1 ELSE 0 END as is_favorite,
                    cr.status as contact_status,
                    cr.id as has_contact
                FROM user_profiles up
                JOIN users u ON up.user_id = u.id
                LEFT JOIN favorites f ON f.favorite_user_id = up.user_id AND f.user_id = :viewer_id1
                LEFT JOIN contact_requests cr ON 
                    ((cr.sender_id = :viewer_id2 AND cr.receiver_id = up.user_id) OR 
                     (cr.sender_id = up.user_id AND cr.receiver_id = :viewer_id3))
                    AND cr.status = 'accepted'
                WHERE up.user_id = :user_id AND u.status = 'active'";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':viewer_id1', $viewerId, PDO::PARAM_INT);
            $stmt->bindValue(':viewer_id2', $viewerId, PDO::PARAM_INT);
            $stmt->bindValue(':viewer_id3', $viewerId, PDO::PARAM_INT);
            $stmt->execute();
            
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$profile) {
                return null;
            }

            // Decode privacy settings
            $privacySettings = json_decode($profile['privacy_settings'] ?? '{}', true) ?? [
                'default' => 'registered',
                'photo' => 'registered',
                'contact' => 'private',
                'horoscope' => 'private',
                'income' => 'private',
                'bio' => 'registered',
                'education' => 'registered',
                'occupation' => 'registered',
                'goals' => 'registered'
            ];

            // Basic public information always visible
            $publicProfile = [
                'user_id' => $profile['user_id'],
                'first_name' => $profile['first_name'],
                'last_name' => $profile['last_name'],
                'age' => $profile['age'],
                'district' => $profile['district'],
                'religion' => $profile['religion'],
                'education' => $this->checkPrivacy($profile['education'], $privacySettings['education'], $viewerId, $profile['has_contact']),
                'profile_photo' => $this->checkPrivacy($profile['profile_photo'], $privacySettings['photo'], $viewerId, $profile['has_contact'])
            ];

            // If it's the profile owner or an admin
            if ($viewerId && ($viewerId === (int)$userId || $profile['role'] === 'admin')) {
                return array_merge($profile, [
                    'is_favorite' => $profile['is_favorite'],
                    'contact_status' => $profile['contact_status']
                ]);
            }

            // If not logged in, return only public profile
            if (!$viewerId) {
                return $publicProfile;
            }

            // For logged-in users, check privacy settings for each field
            $fullProfile = array_merge($publicProfile, [
                'gender' => $profile['gender'],
                'city' => $profile['city'],
                'caste' => $profile['caste'],
                'marital_status' => $profile['marital_status'],
                'height_cm' => $profile['height_cm'],
                'occupation' => $this->checkPrivacy($profile['occupation'], $privacySettings['occupation'], $viewerId, $profile['has_contact']),
                'bio' => $this->checkPrivacy($profile['bio'], $privacySettings['bio'], $viewerId, $profile['has_contact']),
                'goals' => $this->checkPrivacy($profile['goals'], $privacySettings['goals'], $viewerId, $profile['has_contact']),
                'income_lkr' => $this->checkPrivacy($profile['income_lkr'], $privacySettings['income'], $viewerId, $profile['has_contact']),
                'horoscope_file' => $this->checkPrivacy($profile['horoscope_file'], $privacySettings['horoscope'], $viewerId, $profile['has_contact']),
                'is_favorite' => $profile['is_favorite'],
                'contact_status' => $profile['contact_status']
            ]);

            // Remove null values from the array
            return array_filter($fullProfile, function($value) {
                return $value !== null;
            });

        } catch (Exception $e) {
            error_log("Error in getProfileWithUser: " . $e->getMessage());
            return null;
        }
    }

    private function checkPrivacy($value, $privacySetting, $viewerId, $hasContact) {
        if (!$value) {
            return null;
        }

        switch ($privacySetting) {
            case 'public':
                return $value;
            case 'registered':
                return $viewerId ? $value : null;
            case 'private':
                return $hasContact ? $value : null;
            default:
                return $viewerId ? $value : null;
        }
    }

    private function isAdmin($userId) {
        $sql = "SELECT role FROM users WHERE id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user && $user['role'] === 'admin';
    }
    
    public function searchProfiles($filters = [], $currentUserId = null, $page = 1, $limit = 20) {
        $sql = "SELECT p.*, u.status as user_status,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age,
                       CASE WHEN f.id IS NOT NULL THEN 1 ELSE 0 END as is_favorite,
                       CASE WHEN cr.id IS NOT NULL THEN cr.status ELSE NULL END as contact_status,
                       CASE WHEN pm.id IS NOT NULL THEN 1 ELSE 0 END as is_premium,
                       CASE 
                           WHEN JSON_EXTRACT(p.privacy_settings, '$.photo') = 'private' AND (cr.status IS NULL OR cr.status != 'accepted') THEN NULL
                           WHEN JSON_EXTRACT(p.privacy_settings, '$.photo') = 'registered' AND :current_user_id IS NULL THEN NULL
                           ELSE p.profile_photo
                       END as profile_photo
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN favorites f ON f.favorite_user_id = p.user_id AND f.user_id = :current_user_id1
                LEFT JOIN contact_requests cr ON (
                    (cr.sender_id = :current_user_id2 AND cr.receiver_id = p.user_id) OR 
                    (cr.sender_id = p.user_id AND cr.receiver_id = :current_user_id3)
                )
                LEFT JOIN premium_memberships pm ON pm.user_id = p.user_id AND pm.status = 'active' AND pm.end_date >= CURDATE()
                WHERE u.status = 'active' AND p.user_id != :current_user_id4";
        
        $params = [
            ':current_user_id' => $currentUserId,
            ':current_user_id1' => $currentUserId,
            ':current_user_id2' => $currentUserId,
            ':current_user_id3' => $currentUserId,
            ':current_user_id4' => $currentUserId
        ];
        
        // Apply filters
        if (!empty($filters['gender'])) {
            $sql .= " AND p.gender = :gender";
            $params[':gender'] = $filters['gender'];
        }
        
        if (!empty($filters['religion'])) {
            $sql .= " AND p.religion = :religion";
            $params[':religion'] = $filters['religion'];
        }
        
        if (!empty($filters['caste'])) {
            $sql .= " AND p.caste = :caste";
            $params[':caste'] = $filters['caste'];
        }
        
        if (!empty($filters['district'])) {
            $sql .= " AND p.district = :district";
            $params[':district'] = $filters['district'];
        }
        
        if (!empty($filters['marital_status'])) {
            $sql .= " AND p.marital_status = :marital_status";
            $params[':marital_status'] = $filters['marital_status'];
        }
        
        if (!empty($filters['age_min'])) {
            $maxDate = date('Y-m-d', strtotime("-{$filters['age_min']} years"));
            $sql .= " AND p.date_of_birth <= :max_birth_date";
            $params[':max_birth_date'] = $maxDate;
        }
        
        if (!empty($filters['age_max'])) {
            $minDate = date('Y-m-d', strtotime("-{$filters['age_max']} years"));
            $sql .= " AND p.date_of_birth >= :min_birth_date";
            $params[':min_birth_date'] = $minDate;
        }
        
        if (!empty($filters['height_min'])) {
            $sql .= " AND p.height_cm >= :height_min";
            $params[':height_min'] = $filters['height_min'];
        }
        
        if (!empty($filters['height_max'])) {
            $sql .= " AND p.height_cm <= :height_max";
            $params[':height_max'] = $filters['height_max'];
        }
        
        if (!empty($filters['income_min'])) {
            $sql .= " AND p.income_lkr >= :income_min";
            $params[':income_min'] = $filters['income_min'];
        }
        
        if (!empty($filters['income_max'])) {
            $sql .= " AND p.income_lkr <= :income_max";
            $params[':income_max'] = $filters['income_max'];
        }
        
        if (!empty($filters['education'])) {
            $sql .= " AND p.education LIKE :education";
            $params[':education'] = "%{$filters['education']}%";
        }
        
        if (!empty($filters['wants_migration'])) {
            $sql .= " AND p.wants_migration = :wants_migration";
            $params[':wants_migration'] = $filters['wants_migration'];
        }
        
        if (!empty($filters['career_focused'])) {
            $sql .= " AND p.career_focused = :career_focused";
            $params[':career_focused'] = $filters['career_focused'];
        }
        
        if (!empty($filters['wants_early_marriage'])) {
            $sql .= " AND p.wants_early_marriage = :wants_early_marriage";
            $params[':wants_early_marriage'] = $filters['wants_early_marriage'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (p.first_name LIKE :search OR p.last_name LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        // Order by premium status, then by creation date
        $sql .= " ORDER BY is_premium DESC, p.created_at DESC";
        
        // Add pagination
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        return $this->fetchAll($sql, $params);
    }
    
    public function getProfileStats() {
        $sql = "SELECT 
                    COUNT(*) as total_profiles,
                    SUM(CASE WHEN p.gender = 'male' THEN 1 ELSE 0 END) as male_profiles,
                    SUM(CASE WHEN p.gender = 'female' THEN 1 ELSE 0 END) as female_profiles,
                    AVG(TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE())) as avg_age,
                    COUNT(CASE WHEN p.profile_photo IS NOT NULL THEN 1 END) as with_photos,
                    COUNT(CASE WHEN p.video_intro IS NOT NULL THEN 1 END) as with_videos
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE u.status = 'active'";
        
        return $this->fetchOne($sql);
    }
    
    public function getRecentProfiles($limit = 6, $currentUserId = null) {
        $sql = "SELECT p.*, u.status as user_status,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age,
                       CASE 
                           WHEN JSON_EXTRACT(p.privacy_settings, '$.photo') = 'private' THEN NULL
                           WHEN JSON_EXTRACT(p.privacy_settings, '$.photo') = 'registered' AND :current_user_id IS NULL THEN NULL
                           ELSE p.profile_photo
                       END as profile_photo
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE u.status = 'active'
                " . ($currentUserId ? "AND p.user_id != :exclude_user_id" : "") . "
                ORDER BY p.created_at DESC
                LIMIT :limit";
        
        $params = [':limit' => $limit];
        if ($currentUserId) {
            $params[':current_user_id'] = $currentUserId;
            $params[':exclude_user_id'] = $currentUserId;
        } else {
            $params[':current_user_id'] = null;
        }
        
        return $this->fetchAll($sql, $params);
    }
    
    public function incrementViewCount($userId, $viewerId) {
        // Get the profile ID from the user ID
        $profile = $this->findByUserId($userId);
        if (!$profile) return false;
        
        // First, check if this viewer has already viewed this profile today
        $sql = "SELECT id FROM profile_views 
                WHERE viewer_id = :viewer_id AND viewed_profile_id = :profile_id AND view_date = CURDATE()";
        
        $existing = $this->fetchOne($sql, [
            ':viewer_id' => $viewerId,
            ':profile_id' => $profile['id']
        ]);
        
        if ($existing) {
            // Update existing view count
            $sql = "UPDATE profile_views 
                    SET view_count = view_count + 1, updated_at = NOW()
                    WHERE id = :id";
            $this->execute($sql, [':id' => $existing['id']]);
        } else {
            // Insert new view record
            $sql = "INSERT INTO profile_views (viewer_id, viewed_profile_id, view_date, view_count)
                    VALUES (:viewer_id, :profile_id, CURDATE(), 1)";
            $this->execute($sql, [
                ':viewer_id' => $viewerId,
                ':profile_id' => $profile['id']
            ]);
        }
        
        // Update total view count in profile
        $sql = "UPDATE {$this->table} 
                SET view_count = (
                    SELECT SUM(view_count) 
                    FROM profile_views 
                    WHERE viewed_profile_id = :profile_id
                )
                WHERE id = :profile_id";
        
        return $this->execute($sql, [':profile_id' => $profile['id']]);
    }
    
    public function getProfileViews($userId, $days = 30) {
        $profile = $this->findByUserId($userId);
        if (!$profile) return [];

        $sql = "SELECT DATE(pv.view_date) as date, SUM(pv.view_count) as views,
                       COUNT(DISTINCT pv.viewer_id) as unique_viewers
                FROM profile_views pv
                WHERE pv.viewed_profile_id = :profile_id 
                AND pv.view_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY DATE(pv.view_date)
                ORDER BY date DESC";

        return $this->fetchAll($sql, [
            ':profile_id' => $profile['id'],
            ':days' => $days
        ]);
    }

    
    public function updateProfileCompletion($userId) {
        $profile = $this->findByUserId($userId);
        if (!$profile) return false;
        
        $requiredFields = [
            'first_name', 'last_name', 'date_of_birth', 'gender', 'religion',
            'district', 'height_cm', 'education', 'bio'
        ];
        
        $optionalFields = [
            'caste', 'income_lkr', 'profile_photo', 'horoscope_file',
            'goals'
        ];
        
        $completedRequired = 0;
        $completedOptional = 0;
        
        foreach ($requiredFields as $field) {
            if (!empty($profile[$field])) {
                $completedRequired++;
            }
        }
        
        foreach ($optionalFields as $field) {
            if (!empty($profile[$field])) {
                $completedOptional++;
            }
        }
        
        $requiredPercentage = ($completedRequired / count($requiredFields)) * 70; // 70% weight
        $optionalPercentage = ($completedOptional / count($optionalFields)) * 30; // 30% weight
        
        $totalCompletion = round($requiredPercentage + $optionalPercentage);
        
        return $this->updateProfile($userId, ['profile_completion' => $totalCompletion]);
    }
    
    public function getSimilarProfiles($profileId, $limit = 6) {
        $profile = $this->find($profileId);
        if (!$profile) return [];
        
        $sql = "SELECT p.*, u.status as user_status, u.id as user_id,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age,
                       (
                           CASE WHEN p.religion = :religion THEN 2 ELSE 0 END +
                           CASE WHEN p.district = :district THEN 1 ELSE 0 END +
                           CASE WHEN p.education = :education THEN 1 ELSE 0 END +
                           CASE WHEN ABS(TIMESTAMPDIFF(YEAR, p.date_of_birth, :birth_date)) <= 3 THEN 1 ELSE 0 END
                       ) as similarity_score,
                       CASE WHEN cr.id IS NOT NULL THEN cr.status ELSE NULL END as contact_status,
                       CASE 
                           WHEN JSON_EXTRACT(p.privacy_settings, '$.photo') = 'private' AND (cr.status IS NULL OR cr.status != 'accepted') THEN NULL
                           WHEN JSON_EXTRACT(p.privacy_settings, '$.photo') = 'registered' AND :current_user_id IS NULL THEN NULL
                           ELSE p.profile_photo
                       END as profile_photo
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN contact_requests cr ON (
                    (cr.sender_id = :current_user_id2 AND cr.receiver_id = p.user_id) OR 
                    (cr.sender_id = p.user_id AND cr.receiver_id = :current_user_id3)
                )
                WHERE u.status = 'active' 
                AND p.id != :profile_id 
                AND p.user_id != :user_id
                AND p.gender != :gender
                HAVING similarity_score > 0
                ORDER BY similarity_score DESC, p.created_at DESC
                LIMIT :limit";
        
        return $this->fetchAll($sql, [
            ':religion' => $profile['religion'],
            ':district' => $profile['district'],
            ':education' => $profile['education'],
            ':birth_date' => $profile['date_of_birth'],
            ':profile_id' => $profileId,
            ':user_id' => $profile['user_id'],
            ':gender' => $profile['gender'],
            ':current_user_id' => $profile['user_id'],
            ':current_user_id2' => $profile['user_id'],
            ':current_user_id3' => $profile['user_id'],
            ':limit' => $limit
        ]);
    }

    /**
     * Get recent visitors of a profile.
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentVisitors($userId, $limit = 5) {
        $profile = $this->findByUserId($userId);
        if (!$profile) {
            return [];
        }

        $sql = "SELECT pv.viewer_id AS user_id,
                       up.first_name, up.last_name, up.profile_photo, up.district,
                       TIMESTAMPDIFF(YEAR, up.date_of_birth, CURDATE()) AS age,
                       MAX(pv.view_date) AS last_visit_date
                FROM profile_views pv
                LEFT JOIN user_profiles up ON pv.viewer_id = up.user_id
                LEFT JOIN users u ON pv.viewer_id = u.id
                WHERE pv.viewed_profile_id = :profile_id AND u.status = 'active'
                GROUP BY pv.viewer_id
                ORDER BY last_visit_date DESC
                LIMIT :limit";

        return $this->fetchAll($sql, [
            ':profile_id' => $profile['id'],
            ':limit'      => $limit
        ]);
    }
    
    public function getDistrictStats() {
        $sql = "SELECT district, COUNT(*) as count
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE u.status = 'active'
                GROUP BY district
                ORDER BY count DESC";
        
        return $this->fetchAll($sql);
    }
    
    public function getReligionStats() {
        $sql = "SELECT religion, COUNT(*) as count
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE u.status = 'active'
                GROUP BY religion
                ORDER BY count DESC";
        
        return $this->fetchAll($sql);
    }
    
    /**
     * Create profile from social login data
     */
    public function createFromSocialData($userId, $socialData) {
        $profileData = [
            'user_id' => $userId,
            'first_name' => $socialData['first_name'] ?? '',
            'last_name' => $socialData['last_name'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Download and save profile picture if provided
        if (!empty($socialData['profile_picture_url'])) {
            $this->downloadSocialProfilePicture($userId, $socialData['profile_picture_url']);
        }
        
        return $this->create($profileData);
    }
    
    /**
     * Update profile picture from URL
     */
    public function updateProfilePicture($userId, $pictureUrl) {
        return $this->downloadSocialProfilePicture($userId, $pictureUrl);
    }
    
    /**
     * Download and save social media profile picture
     */
    private function downloadSocialProfilePicture($userId, $pictureUrl) {
        try {
            // Create uploads directory if it doesn't exist
            $uploadDir = SITE_ROOT . '/public/uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $filename = $userId . '_' . time() . '.png';
            $filepath = $uploadDir . $filename;
            
            // Download the image
            $imageData = file_get_contents($pictureUrl);
            if ($imageData !== false) {
                file_put_contents($filepath, $imageData);
                
                // Update profile with new picture
                return $this->updateProfile($userId, [
                    'profile_photo' => $filename
                ]);
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error downloading social profile picture: " . $e->getMessage());
            return false;
        }
    }
}
?>