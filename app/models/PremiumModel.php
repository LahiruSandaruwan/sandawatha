<?php

namespace App\models;

use Exception;
use PDOException;
use App\models\ContactRequestModel;


class PremiumModel extends BaseModel {
    protected $table = 'premium_memberships';
    
    protected function getAllowedColumns() {
        return [
            'user_id', 'plan_type', 'plan_name', 'price_lkr', 'duration_months',
            'features', 'start_date', 'end_date', 'status', 'payment_method',
            'transaction_id', 'created_at', 'updated_at'
        ];
    }
    
    public function createMembership($userId, $planType, $durationMonths = 1) {
        $plans = [
            'basic' => ['name' => 'Basic Plan', 'price' => 500],
            'premium' => ['name' => 'Premium Plan', 'price' => 1000],
            'platinum' => ['name' => 'Platinum Plan', 'price' => 1500]
        ];
        
        if (!isset($plans[$planType])) {
            throw new Exception('Invalid plan type');
        }
        
        $plan = $plans[$planType];
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$durationMonths} month"));
        
        // Deactivate existing memberships
        $this->deactivateUserMemberships($userId);
        
        return $this->create([
            'user_id' => $userId,
            'plan_type' => $planType,
            'plan_name' => $plan['name'],
            'price_lkr' => $plan['price'] * $durationMonths,
            'duration_months' => $durationMonths,
            'features' => json_encode(PREMIUM_FEATURES[$planType]),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'payment_method' => 'mock',
            'transaction_id' => 'MOCK_' . uniqid(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function getActiveMembership($userId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                AND status = 'active' 
                AND end_date >= CURDATE()
                ORDER BY created_at DESC 
                LIMIT 1";
        
        return $this->fetchOne($sql, [':user_id' => $userId]);
    }
    
    public function hasActivePremium($userId) {
        $membership = $this->getActiveMembership($userId);
        return !empty($membership);
    }
    
    public function getUserMemberships($userId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";
        
        return $this->fetchAll($sql, [':user_id' => $userId]);
    }
    
    public function deactivateUserMemberships($userId) {
        $sql = "UPDATE {$this->table} 
                SET status = 'cancelled' 
                WHERE user_id = :user_id AND status = 'active'";
        
        return $this->execute($sql, [':user_id' => $userId]);
    }
    
    public function extendMembership($userId, $additionalMonths) {
        $sql = "UPDATE {$this->table} 
                SET end_date = DATE_ADD(end_date, INTERVAL :months MONTH),
                    updated_at = NOW()
                WHERE user_id = :user_id 
                AND status = 'active' 
                AND end_date >= CURDATE()";
        
        return $this->execute($sql, [
            ':user_id' => $userId,
            ':months' => $additionalMonths
        ]);
    }
    
    public function checkExpiredMemberships() {
        $sql = "UPDATE {$this->table} 
                SET status = 'expired' 
                WHERE status = 'active' 
                AND end_date < CURDATE()";
        
        return $this->execute($sql);
    }
    
    public function getMembershipStats() {
        $sql = "SELECT 
                    plan_type,
                    COUNT(*) as total_subscriptions,
                    COUNT(CASE WHEN status = 'active' AND end_date >= CURDATE() THEN 1 END) as active_subscriptions,
                    SUM(price_lkr) as total_revenue,
                    AVG(price_lkr) as avg_revenue
                FROM {$this->table}
                GROUP BY plan_type
                ORDER BY total_revenue DESC";
        
        return $this->fetchAll($sql);
    }
    
    public function getRevenueByMonth($months = 12) {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as subscriptions,
                    SUM(price_lkr) as revenue
                FROM {$this->table}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC";
        
        return $this->fetchAll($sql, [':months' => $months]);
    }
    
    public function getTopPayingUsers($limit = 10) {
        $sql = "SELECT 
                    pm.user_id,
                    up.first_name,
                    up.last_name,
                    SUM(pm.price_lkr) as total_spent,
                    COUNT(*) as subscription_count
                FROM {$this->table} pm
                LEFT JOIN user_profiles up ON pm.user_id = up.user_id
                GROUP BY pm.user_id
                ORDER BY total_spent DESC
                LIMIT :limit";
        
        return $this->fetchAll($sql, [':limit' => $limit]);
    }
    
    public function getExpiringMemberships($days = 7) {
        $sql = "SELECT pm.*, up.first_name, up.last_name, u.email
                FROM {$this->table} pm
                LEFT JOIN user_profiles up ON pm.user_id = up.user_id
                LEFT JOIN users u ON pm.user_id = u.id
                WHERE pm.status = 'active' 
                AND pm.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                ORDER BY pm.end_date ASC";
        
        return $this->fetchAll($sql, [':days' => $days]);
    }
    
    public function getPremiumUserStats() {
        $sql = "SELECT 
                    COUNT(DISTINCT pm.user_id) as total_premium_users,
                    COUNT(CASE WHEN pm.status = 'active' AND pm.end_date >= CURDATE() THEN 1 END) as active_premium_users,
                    AVG(DATEDIFF(pm.end_date, pm.start_date)) as avg_subscription_days,
                    COUNT(CASE WHEN pm.plan_type = 'basic' AND pm.status = 'active' AND pm.end_date >= CURDATE() THEN 1 END) as basic_users,
                    COUNT(CASE WHEN pm.plan_type = 'premium' AND pm.status = 'active' AND pm.end_date >= CURDATE() THEN 1 END) as premium_users,
                    COUNT(CASE WHEN pm.plan_type = 'platinum' AND pm.status = 'active' AND pm.end_date >= CURDATE() THEN 1 END) as platinum_users
                FROM {$this->table} pm";
        
        return $this->fetchOne($sql);
    }
    
    public function getUserFeatures($userId) {
        $membership = $this->getActiveMembership($userId);
        
        if (!$membership) {
            return PREMIUM_FEATURES['basic'];
        }
        
        return json_decode($membership['features'], true) ?: PREMIUM_FEATURES['basic'];
    }
    
    public function canUseFeature($userId, $feature) {
        $features = $this->getUserFeatures($userId);
        return $features[$feature] ?? false;
    }
    
    public function getRemainingQuota($userId, $feature) {
        $features = $this->getUserFeatures($userId);
        $limit = $features[$feature] ?? 0;
        
        if ($limit === 'unlimited') {
            return 'unlimited';
        }
        
        // Get usage count for today based on feature
        switch ($feature) {
            case 'contact_requests_per_day':
                require_once 'ContactRequestModel.php';
                $model = new ContactRequestModel();
                $sql = "SELECT COUNT(*) as count FROM contact_requests 
                        WHERE sender_id = :user_id AND DATE(sent_at) = CURDATE()";
                break;
                
            case 'message_limit_per_day':
                require_once 'MessageModel.php';
                $model = new MessageModel();
                $sql = "SELECT COUNT(*) as count FROM messages 
                        WHERE sender_id = :user_id AND DATE(created_at) = CURDATE()";
                break;
                
            default:
                return $limit;
        }
        
        $used = $model->fetchOne($sql, [':user_id' => $userId])['count'] ?? 0;
        return max(0, $limit - $used);
    }
}
?>