<?php

namespace App\helpers;

use App\models\RoleModel;
use App\models\SubscriptionPackageModel;

class PermissionMiddleware {
    
    private static $roleModel;
    private static $packageModel;
    
    private static function getRoleModel() {
        if (!self::$roleModel) {
            self::$roleModel = new RoleModel();
        }
        return self::$roleModel;
    }
    
    private static function getPackageModel() {
        if (!self::$packageModel) {
            self::$packageModel = new SubscriptionPackageModel();
        }
        return self::$packageModel;
    }
    
    /**
     * Check if user has required permission
     */
    public static function hasPermission($permission, $userId = null) {
        if (!$userId) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return false;
        }
        
        $roleModel = self::getRoleModel();
        return $roleModel->userHasPermission($userId, $permission);
    }
    
    /**
     * Check if user has required role
     */
    public static function hasRole($role, $userId = null) {
        if (!$userId) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return false;
        }
        
        $roleModel = self::getRoleModel();
        return $roleModel->userHasRole($userId, $role);
    }
    
    /**
     * Check if user has access to feature
     */
    public static function hasFeature($feature, $userId = null) {
        if (!$userId) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return false;
        }
        
        $packageModel = self::getPackageModel();
        return $packageModel->userHasFeature($userId, $feature);
    }
    
    /**
     * Check if user has reached feature limit
     */
    public static function hasReachedLimit($feature, $userId = null) {
        if (!$userId) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return true;
        }
        
        $packageModel = self::getPackageModel();
        return $packageModel->hasReachedLimit($userId, $feature);
    }
    
    /**
     * Require permission or redirect
     */
    public static function requirePermission($permission, $redirectUrl = '/login') {
        if (!self::hasPermission($permission)) {
            self::handleUnauthorized($redirectUrl);
        }
    }
    
    /**
     * Require role or redirect
     */
    public static function requireRole($role, $redirectUrl = '/login') {
        if (!self::hasRole($role)) {
            self::handleUnauthorized($redirectUrl);
        }
    }
    
    /**
     * Require feature or redirect
     */
    public static function requireFeature($feature, $redirectUrl = '/premium') {
        if (!self::hasFeature($feature)) {
            self::handleFeatureRequired($feature, $redirectUrl);
        }
    }
    
    /**
     * Check feature limit or redirect
     */
    public static function checkFeatureLimit($feature, $redirectUrl = '/premium') {
        if (self::hasReachedLimit($feature)) {
            self::handleLimitReached($feature, $redirectUrl);
        }
    }
    
    /**
     * Handle unauthorized access
     */
    private static function handleUnauthorized($redirectUrl) {
        if (isset($_SESSION['user_id'])) {
            // User is logged in but doesn't have permission
            $_SESSION['error'] = 'You do not have permission to access this page.';
            header('Location: ' . BASE_URL . '/dashboard');
        } else {
            // User is not logged in
            $_SESSION['error'] = 'Please log in to access this page.';
            header('Location: ' . BASE_URL . $redirectUrl);
        }
        exit;
    }
    
    /**
     * Handle feature requirement
     */
    private static function handleFeatureRequired($feature, $redirectUrl) {
        $_SESSION['info'] = "This feature requires a premium subscription. Upgrade your plan to access {$feature}.";
        header('Location: ' . BASE_URL . $redirectUrl);
        exit;
    }
    
    /**
     * Handle limit reached
     */
    private static function handleLimitReached($feature, $redirectUrl) {
        $_SESSION['warning'] = "You have reached your daily limit for {$feature}. Upgrade your plan for unlimited access.";
        header('Location: ' . BASE_URL . $redirectUrl);
        exit;
    }
    
    /**
     * Get user's remaining quota for a feature
     */
    public static function getRemainingQuota($feature, $userId = null) {
        if (!$userId) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return 0;
        }
        
        $packageModel = self::getPackageModel();
        $limit = $packageModel->getUserFeatureLimit($userId, $feature);
        $usage = $packageModel->getCurrentUsage($userId, $feature);
        
        if ($limit === PHP_INT_MAX) {
            return 'unlimited';
        }
        
        return max(0, $limit - $usage);
    }
    
    /**
     * Get user's package information
     */
    public static function getUserPackageInfo($userId = null) {
        if (!$userId) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return null;
        }
        
        $packageModel = self::getPackageModel();
        return $packageModel->getUserActivePackage($userId);
    }

    /**
     * Get user's feature limit
     */
    public static function getUserFeatureLimit($userId, $feature) {
        if (!$userId) {
            return 0;
        }
        
        $packageModel = self::getPackageModel();
        return $packageModel->getUserFeatureLimit($userId, $feature);
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin($userId = null) {
        return self::hasRole('admin', $userId);
    }
    
    /**
     * Check if user is premium
     */
    public static function isPremium($userId = null) {
        $package = self::getUserPackageInfo($userId);
        return $package && $package['slug'] !== 'basic';
    }
    
    /**
     * Get user permissions array
     */
    public static function getUserPermissions($userId = null) {
        if (!$userId) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return [];
        }
        
        $roleModel = self::getRoleModel();
        return $roleModel->getUserPermissions($userId);
    }
    
    /**
     * Log permission check for audit
     */
    public static function logPermissionCheck($permission, $granted, $userId = null) {
        if (!$userId) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return;
        }
        
        require_once __DIR__ . '/../models/ActivityLogModel.php';
        $activityLog = new ActivityLogModel();
        
        $activityLog->logActivity($userId, 'permission_check', [
            'permission' => $permission,
            'granted' => $granted,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
} 