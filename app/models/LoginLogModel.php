<?php
require_once 'BaseModel.php';

class LoginLogModel extends BaseModel {
    protected $table = 'login_logs';
    
    public function logLogin($userId, $ipAddress, $userAgent, $browser = null, $device = null) {
        return $this->create([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'browser' => $browser,
            'device' => $device,
            'login_time' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function logLogout($logId) {
        $loginTime = $this->find($logId)['login_time'];
        $sessionDuration = time() - strtotime($loginTime);
        
        return $this->update($logId, [
            'logout_time' => date('Y-m-d H:i:s'),
            'session_duration' => $sessionDuration
        ]);
    }
    
    public function getUserLoginHistory($userId, $limit = 50) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY login_time DESC 
                LIMIT :limit";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':limit' => $limit
        ]);
    }
    
    public function getRecentLogins($hours = 24, $limit = 100) {
        $sql = "SELECT ll.*, u.email, up.first_name, up.last_name
                FROM {$this->table} ll
                LEFT JOIN users u ON ll.user_id = u.id
                LEFT JOIN user_profiles up ON ll.user_id = up.user_id
                WHERE ll.login_time >= DATE_SUB(NOW(), INTERVAL :hours HOUR)
                ORDER BY ll.login_time DESC
                LIMIT :limit";
        
        return $this->fetchAll($sql, [
            ':hours' => $hours,
            ':limit' => $limit
        ]);
    }
    
    public function getLoginStats($days = 30) {
        $sql = "SELECT 
                    DATE(login_time) as date,
                    COUNT(*) as login_count,
                    COUNT(DISTINCT user_id) as unique_users
                FROM {$this->table}
                WHERE login_time >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(login_time)
                ORDER BY date DESC";
        
        return $this->fetchAll($sql, [':days' => $days]);
    }
    
    public function getDeviceStats() {
        $sql = "SELECT 
                    device,
                    COUNT(*) as count,
                    COUNT(DISTINCT user_id) as unique_users
                FROM {$this->table}
                WHERE device IS NOT NULL
                GROUP BY device
                ORDER BY count DESC";
        
        return $this->fetchAll($sql);
    }
    
    public function getBrowserStats() {
        $sql = "SELECT 
                    browser,
                    COUNT(*) as count,
                    COUNT(DISTINCT user_id) as unique_users
                FROM {$this->table}
                WHERE browser IS NOT NULL
                GROUP BY browser
                ORDER BY count DESC";
        
        return $this->fetchAll($sql);
    }
    
    public function getSuspiciousLogins($threshold = 5) {
        // Users with more than threshold logins from different IPs in last 24 hours
        $sql = "SELECT 
                    user_id,
                    COUNT(DISTINCT ip_address) as unique_ips,
                    COUNT(*) as login_count,
                    u.email,
                    up.first_name,
                    up.last_name
                FROM {$this->table} ll
                LEFT JOIN users u ON ll.user_id = u.id
                LEFT JOIN user_profiles up ON ll.user_id = up.user_id
                WHERE ll.login_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY user_id
                HAVING unique_ips >= :threshold
                ORDER BY unique_ips DESC";
        
        return $this->fetchAll($sql, [':threshold' => $threshold]);
    }
    
    public function getActiveUsers($hours = 24) {
        $sql = "SELECT COUNT(DISTINCT user_id) as count
                FROM {$this->table}
                WHERE login_time >= DATE_SUB(NOW(), INTERVAL :hours HOUR)";
        
        $result = $this->fetchOne($sql, [':hours' => $hours]);
        return $result['count'];
    }
    
    protected function getAllowedColumns() {
        return [
            'user_id',
            'ip_address',
            'user_agent',
            'browser',
            'device',
            'login_time',
            'logout_time',
            'session_duration'
        ];
    }
}
?>