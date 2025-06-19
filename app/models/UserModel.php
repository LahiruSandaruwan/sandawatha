<?php

namespace App\models;

use App\helpers\UuidTrait;

class UserModel extends BaseModel {
    use UuidTrait;
    protected $table = 'users';
    
    protected function getAllowedColumns() {
        return ['id', 'email', 'phone', 'status', 'created_at', 'updated_at'];
    }
    
    public function createUser($email, $phone, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $emailToken = bin2hex(random_bytes(32));
        $phoneCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        return $this->create([
            'email' => $email,
            'phone' => $phone,
            'password' => $hashedPassword,
            'email_verification_token' => $emailToken,
            'phone_verification_code' => $phoneCode,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function findByEmail($email) {
        return $this->findBy('email', $email);
    }
    
    public function findByPhone($phone) {
        return $this->findBy('phone', $phone);
    }
    
    public function verifyEmail($token) {
        $sql = "UPDATE {$this->table} 
                SET email_verified = 1, email_verification_token = NULL 
                WHERE email_verification_token = :token";
        
        return $this->execute($sql, [':token' => $token]);
    }
    
    public function verifyPhone($userId, $code) {
        $sql = "UPDATE {$this->table} 
                SET phone_verified = 1, phone_verification_code = NULL 
                WHERE id = :user_id AND phone_verification_code = :code";
        
        return $this->execute($sql, [
            ':user_id' => $userId,
            ':code' => $code
        ]);
    }
    
    public function setResetToken($email, $token, $expires) {
        $sql = "UPDATE {$this->table} 
                SET reset_token = :token, reset_expires = :expires 
                WHERE email = :email";
        
        return $this->execute($sql, [
            ':token' => $token,
            ':expires' => $expires,
            ':email' => $email
        ]);
    }
    
    public function resetPassword($token, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE {$this->table} 
                SET password = :password, reset_token = NULL, reset_expires = NULL 
                WHERE reset_token = :token AND reset_expires > NOW()";
        
        return $this->execute($sql, [
            ':password' => $hashedPassword,
            ':token' => $token
        ]);
    }
    
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    public function updateStatus($userId, $status) {
        return $this->update($userId, ['status' => $status]);
    }
    
    public function updateDarkMode($userId, $darkMode) {
        return $this->update($userId, ['dark_mode' => $darkMode]);
    }
    
    public function getUsersForAdmin($page = 1, $search = '', $status = '') {
        $where = "role = 'user'";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (email LIKE :search OR phone LIKE :search)";
            $params[':search'] = "%{$search}%";
        }
        
        if (!empty($status)) {
            $where .= " AND status = :status";
            $params[':status'] = $status;
        }
        
        return $this->paginate($page, 20, $where, $params);
    }
    
    public function getUserStats() {
        $sql = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_users,
                    SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked_users,
                    SUM(CASE WHEN email_verified = 1 THEN 1 ELSE 0 END) as email_verified,
                    SUM(CASE WHEN phone_verified = 1 THEN 1 ELSE 0 END) as phone_verified
                FROM {$this->table} 
                WHERE role = 'user'";
        
        return $this->fetchOne($sql);
    }
    
    public function getRecentUsers($limit = 5) {
        $sql = "SELECT id, email, created_at, status 
                FROM {$this->table} 
                WHERE role = 'user' 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        return $this->fetchAll($sql, [':limit' => $limit]);
    }
    
    public function getActiveUsers($days = 30) {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} u
                INNER JOIN login_logs ll ON u.id = ll.user_id
                WHERE u.role = 'user' 
                AND ll.login_time >= DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $result = $this->fetchOne($sql, [':days' => $days]);
        return $result['count'];
    }
    
    public function searchUsers($query, $filters = []) {
        $sql = "SELECT u.*, up.first_name, up.last_name, up.gender, up.date_of_birth, 
                       up.religion, up.district, up.profile_photo
                FROM {$this->table} u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE u.status = 'active' AND u.role = 'user'";
        
        $params = [];
        
        if (!empty($query)) {
            $sql .= " AND (up.first_name LIKE :query OR up.last_name LIKE :query)";
            $params[':query'] = "%{$query}%";
        }
        
        if (!empty($filters['gender'])) {
            $sql .= " AND up.gender = :gender";
            $params[':gender'] = $filters['gender'];
        }
        
        if (!empty($filters['religion'])) {
            $sql .= " AND up.religion = :religion";
            $params[':religion'] = $filters['religion'];
        }
        
        if (!empty($filters['district'])) {
            $sql .= " AND up.district = :district";
            $params[':district'] = $filters['district'];
        }
        
        if (!empty($filters['age_min']) || !empty($filters['age_max'])) {
            if (!empty($filters['age_min'])) {
                $maxDate = date('Y-m-d', strtotime("-{$filters['age_min']} years"));
                $sql .= " AND up.date_of_birth <= :max_date";
                $params[':max_date'] = $maxDate;
            }
            
            if (!empty($filters['age_max'])) {
                $minDate = date('Y-m-d', strtotime("-{$filters['age_max']} years"));
                $sql .= " AND up.date_of_birth >= :min_date";
                $params[':min_date'] = $minDate;
            }
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        return $this->fetchAll($sql, $params);
    }
    
    public function logLogin($userId, $ipAddress, $userAgent) {
        require_once 'LoginLogModel.php';
        $loginModel = new LoginLogModel();
        
        // Parse user agent for browser and device info
        $browser = $this->parseBrowser($userAgent);
        $device = $this->parseDevice($userAgent);
        
        return $loginModel->logLogin(
            $userId,
            $ipAddress,
            $userAgent,
            $browser,
            $device
        );
    }
    
    private function parseBrowser($userAgent) {
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';
        return 'Unknown';
    }
    
    private function parseDevice($userAgent) {
        if (strpos($userAgent, 'Mobile') !== false) return 'Mobile';
        if (strpos($userAgent, 'Tablet') !== false) return 'Tablet';
        return 'Desktop';
    }
    
    /**
     * Create user from social login
     */
    public function createSocialUser($email, $password, $provider, $providerId = null) {
        $data = [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'email_verified' => 1, // Social logins are pre-verified
            'phone_verified' => 0,
            'status' => 'active',
            'social_provider' => $provider,
            'social_provider_id' => $providerId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($data);
    }
    
    /**
     * Update social provider information
     */
    public function updateSocialProvider($userId, $provider, $providerId = null) {
        $data = [
            'social_provider' => $provider,
            'social_provider_id' => $providerId,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($userId, $data);
    }
    
    /**
     * Find user by social provider ID
     */
    public function findBySocialProvider($provider, $providerId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE social_provider = :provider 
                AND social_provider_id = :provider_id";
        
        return $this->fetchOne($sql, [
            ':provider' => $provider,
            ':provider_id' => $providerId
        ]);
    }
}
?>