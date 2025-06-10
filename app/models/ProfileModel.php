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
                    cr.status as contact_status
                FROM user_profiles up
                JOIN users u ON up.user_id = u.id
                LEFT JOIN favorites f ON f.favorite_user_id = up.user_id AND f.user_id = :viewer_id1
                LEFT JOIN contact_requests cr ON 
                    ((cr.sender_id = :viewer_id2 AND cr.receiver_id = up.user_id) OR 
                     (cr.sender_id = up.user_id AND cr.receiver_id = :viewer_id3))
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

            // Basic public information
            $publicProfile = [
                'user_id' => $profile['user_id'],
                'first_name' => $profile['first_name'],
                'age' => $profile['age'],
                'district' => $profile['district'],
                'religion' => $profile['religion'],
                'education' => $profile['education'],
                'profile_photo' => $profile['profile_photo']
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

            // For logged-in users, add more information
            return array_merge($publicProfile, [
                'gender' => $profile['gender'],
                'city' => $profile['city'],
                'caste' => $profile['caste'],
                'marital_status' => $profile['marital_status'],
                'height_cm' => $profile['height_cm'],
                'occupation' => $profile['occupation'],
                'bio' => $profile['bio'],
                'is_favorite' => $profile['is_favorite'],
                'contact_status' => $profile['contact_status']
            ]);

        } catch (Exception $e) {
            error_log("Error in getProfileWithUser: " . $e->getMessage());
            return null;
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
}
?>