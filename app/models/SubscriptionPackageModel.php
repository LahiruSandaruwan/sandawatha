<?php
require_once 'BaseModel.php';
require_once __DIR__ . '/../helpers/UuidTrait.php';

class SubscriptionPackageModel extends BaseModel {
    use UuidTrait;
    
    protected $table = 'subscription_packages';
    
    protected function getAllowedColumns() {
        return ['uuid', 'name', 'slug', 'description', 'price_monthly', 'price_yearly', 
                'features', 'badge_color', 'badge_text', 'is_popular', 'is_active', 
                'sort_order', 'created_at', 'updated_at'];
    }
    
    /**
     * Get package by slug
     */
    public function findBySlug($slug) {
        return $this->findBy('slug', $slug);
    }
    
    /**
     * Get all active packages ordered by sort_order
     */
    public function getActivePackages() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                ORDER BY sort_order ASC, price_monthly ASC";
        
        return $this->fetchAll($sql);
    }
    
    /**
     * Get user's active subscription package
     */
    public function getUserActivePackage($userId) {
        $sql = "SELECT sp.* FROM {$this->table} sp
                INNER JOIN premium_memberships pm ON sp.slug = pm.plan_type
                WHERE pm.user_id = :user_id 
                AND pm.status = 'active' 
                AND pm.end_date >= CURDATE()
                ORDER BY pm.created_at DESC
                LIMIT 1";
        
        return $this->fetchOne($sql, [':user_id' => $userId]);
    }
    
    /**
     * Check if user has specific feature
     */
    public function userHasFeature($userId, $featureSlug) {
        $package = $this->getUserActivePackage($userId);
        
        if (!$package) {
            // Get basic package as default
            $package = $this->findBySlug('basic');
        }
        
        if (!$package) {
            return false;
        }
        
        $features = json_decode($package['features'], true);
        return isset($features[$featureSlug]) ? $features[$featureSlug] : false;
    }
    
    /**
     * Get user's feature limit
     */
    public function getUserFeatureLimit($userId, $featureSlug) {
        $package = $this->getUserActivePackage($userId);
        
        if (!$package) {
            $package = $this->findBySlug('basic');
        }
        
        if (!$package) {
            return 0;
        }
        
        $features = json_decode($package['features'], true);
        $limit = $features[$featureSlug] ?? 0;
        
        // Handle unlimited features
        if ($limit === 'unlimited') {
            return PHP_INT_MAX;
        }
        
        return is_numeric($limit) ? (int)$limit : 0;
    }
    
    /**
     * Check if user has reached feature limit
     */
    public function hasReachedLimit($userId, $featureSlug, $currentUsage = null) {
        $limit = $this->getUserFeatureLimit($userId, $featureSlug);
        
        if ($limit === PHP_INT_MAX) {
            return false; // Unlimited
        }
        
        if ($currentUsage === null) {
            $currentUsage = $this->getCurrentUsage($userId, $featureSlug);
        }
        
        return $currentUsage >= $limit;
    }
    
    /**
     * Get current usage for a feature
     */
    private function getCurrentUsage($userId, $featureSlug) {
        $today = date('Y-m-d');
        
        switch ($featureSlug) {
            case 'daily_messages':
                $sql = "SELECT COUNT(*) as count FROM messages 
                        WHERE sender_id = :user_id AND DATE(created_at) = :today";
                break;
                
            case 'profile_views':
                $sql = "SELECT COALESCE(SUM(view_count), 0) as count FROM profile_views 
                        WHERE viewer_id = :user_id AND view_date = :today";
                break;
                
            case 'contact_requests':
                $sql = "SELECT COUNT(*) as count FROM contact_requests 
                        WHERE sender_id = :user_id AND DATE(sent_at) = :today";
                break;
                
            default:
                return 0;
        }
        
        $result = $this->fetchOne($sql, [':user_id' => $userId, ':today' => $today]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get package comparison data
     */
    public function getPackageComparison() {
        $packages = $this->getActivePackages();
        $features = $this->getAllFeatures();
        
        $comparison = [];
        foreach ($packages as $package) {
            $packageFeatures = json_decode($package['features'], true);
            $package['feature_details'] = [];
            
            foreach ($features as $feature) {
                $value = $packageFeatures[$feature['slug']] ?? false;
                $package['feature_details'][$feature['slug']] = [
                    'name' => $feature['name'],
                    'value' => $value,
                    'formatted' => $this->formatFeatureValue($value, $feature['feature_type'])
                ];
            }
            
            $comparison[] = $package;
        }
        
        return $comparison;
    }
    
    /**
     * Get all available features
     */
    private function getAllFeatures() {
        $sql = "SELECT * FROM package_features WHERE is_active = 1 ORDER BY name";
        return $this->fetchAll($sql);
    }
    
    /**
     * Format feature value for display
     */
    private function formatFeatureValue($value, $type) {
        if ($value === true || $value === 'true') {
            return '<i class="bi bi-check-circle-fill text-success"></i>';
        }
        
        if ($value === false || $value === 'false') {
            return '<i class="bi bi-x-circle text-muted"></i>';
        }
        
        if ($value === 'unlimited') {
            return '<span class="text-primary fw-bold">Unlimited</span>';
        }
        
        if (is_numeric($value)) {
            return '<span class="fw-bold">' . number_format($value) . '</span>';
        }
        
        return htmlspecialchars($value);
    }
    
    /**
     * Create or update package
     */
    public function createPackage($data) {
        // Ensure features is JSON encoded
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update package features
     */
    public function updatePackageFeatures($packageId, $features) {
        return $this->update($packageId, [
            'features' => json_encode($features),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get package statistics
     */
    public function getPackageStats() {
        $sql = "SELECT 
                    sp.name,
                    sp.slug,
                    COUNT(pm.id) as total_subscriptions,
                    COUNT(CASE WHEN pm.status = 'active' AND pm.end_date >= CURDATE() THEN 1 END) as active_subscriptions,
                    SUM(CASE WHEN pm.status = 'active' AND pm.end_date >= CURDATE() THEN pm.price_lkr ELSE 0 END) as active_revenue
                FROM {$this->table} sp
                LEFT JOIN premium_memberships pm ON sp.slug = pm.plan_type
                WHERE sp.is_active = 1
                GROUP BY sp.id
                ORDER BY sp.sort_order";
        
        return $this->fetchAll($sql);
    }
} 