<?php
require_once 'BaseModel.php';

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
        $profileData = array_merge($data, [
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->create($profileData);
    }
    
    public function findByUserId($userId) {
        $sql = "SELECT p.*, u.email, u.phone, u.status as user_status
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.user_id = :user_id";
        
        return $this->fetchOne($sql, [':user_id' => $userId]);
    }
    
    public function updateProfile($userId, $data) {
        $sql = "UPDATE {$this->table} 
                SET " . implode(', ', array_map(fn($key) => "{$key} = :{$key}", array_keys($data))) . ",
                    updated_at = NOW()
                WHERE user_id = :user_id";
        
        $params = array_merge($data, [':user_id' => $userId]);
        return $this->execute($sql, $params);
    }
    
    public function getProfileWithUser($profileId, $viewerId = null) {
        try {
            $sql = "SELECT p.*, u.email, u.phone, u.status as user_status, u.created_at as member_since
                    FROM {$this->table} p
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE p.id = :profile_id";
            
            $profile = $this->fetchOne($sql, [':profile_id' => $profileId]);
            
            if (!$profile) {
                error_log("Profile not found: {$profileId}");
                return null;
            }
            
            if ($profile['user_status'] !== 'active') {
                error_log("Profile {$profileId} found but user is not active. Status: {$profile['user_status']}");
                return null;
            }

            // If viewer is the profile owner, return full profile
            if ($viewerId && $viewerId == $profile['user_id']) {
                return $profile;
            }

            // Check contact status if viewer is provided
            $contactStatus = null;
            if ($viewerId) {
                require_once SITE_ROOT . '/app/models/ContactRequestModel.php';
                $contactModel = new ContactRequestModel();
                $contactRequest = $contactModel->getRequestBetweenUsers($viewerId, $profile['user_id']);
                $contactStatus = $contactRequest['status'] ?? null;
            }

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
            
            return $profile;
        } catch (Exception $e) {
            error_log("Error fetching profile {$profileId}: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function searchProfiles($filters = [], $currentUserId = null, $page = 1, $limit = 20) {
        $sql = "SELECT p.*, u.status as user_status,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age,
                       CASE WHEN f.id IS NOT NULL THEN 1 ELSE 0 END as is_favorite,
                       CASE WHEN cr.id IS NOT NULL THEN cr.status ELSE NULL END as contact_status,
                       CASE WHEN pm.id IS NOT NULL THEN 1 ELSE 0 END as is_premium
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN favorites f ON f.favorite_user_id = p.user_id AND f.user_id = :current_user_id1
                LEFT JOIN contact_requests cr ON (cr.sender_id = :current_user_id2 AND cr.receiver_id = p.user_id)
                LEFT JOIN premium_memberships pm ON pm.user_id = p.user_id AND pm.status = 'active' AND pm.end_date >= CURDATE()
                WHERE u.status = 'active' AND p.user_id != :current_user_id3";
        
        $params = [
            ':current_user_id1' => $currentUserId,
            ':current_user_id2' => $currentUserId,
            ':current_user_id3' => $currentUserId
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
    
    public function getRecentProfiles($limit = 6) {
        $sql = "SELECT p.*, u.status as user_status,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE u.status = 'active'
                ORDER BY p.created_at DESC
                LIMIT :limit";
        
        return $this->fetchAll($sql, [':limit' => $limit]);
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
                       ) as similarity_score
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE u.status = 'active' 
                AND p.id != :profile_id 
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
            ':gender' => $profile['gender'],
            ':limit' => $limit
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
}
?>