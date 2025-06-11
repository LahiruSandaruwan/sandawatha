<?php
require_once 'BaseModel.php';

class ActivityLogModel extends BaseModel {
    protected $table = 'activity_logs';
    
    protected function getAllowedColumns() {
        return [
            'id', 'user_id', 'action', 'details', 'ip_address', 
            'user_agent', 'created_at', 'updated_at'
        ];
    }
    
    public function logActivity($userId, $action, $details = null) {
        try {
            return $this->create([
                'user_id' => $userId,
                'action' => $action,
                'details' => $details,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error in logActivity: " . $e->getMessage());
            return false;
        }
    }
    
    public function createLog($data) {
        try {
            return $this->create($data);
        } catch (Exception $e) {
            error_log("Error in createLog: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserActivity($userId, $limit = 20, $offset = 0) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE user_id = :user_id 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            return $this->fetchAll($sql, [
                ':user_id' => $userId,
                ':limit' => $limit,
                ':offset' => $offset
            ]);
        } catch (Exception $e) {
            error_log("Error in getUserActivity: " . $e->getMessage());
            return [];
        }
    }
    
    public function getRecentActivity($limit = 20) {
        try {
            $sql = "SELECT al.*, u.email, up.first_name, up.last_name 
                    FROM {$this->table} al
                    LEFT JOIN users u ON al.user_id = u.id
                    LEFT JOIN user_profiles up ON al.user_id = up.user_id
                    ORDER BY al.created_at DESC 
                    LIMIT :limit";
            
            return $this->fetchAll($sql, [':limit' => $limit]);
        } catch (Exception $e) {
            error_log("Error in getRecentActivity: " . $e->getMessage());
            return [];
        }
    }
    
    public function deleteOldLogs($days = 30) {
        try {
            $sql = "DELETE FROM {$this->table} 
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
            
            return $this->execute($sql, [':days' => $days]);
        } catch (Exception $e) {
            error_log("Error in deleteOldLogs: " . $e->getMessage());
            return false;
        }
    }
    
    public function createActivityLog($userId, $action, $details = null) {
        try {
            return $this->create([
                'user_id' => $userId,
                'action' => $action,
                'details' => $details,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error in createActivityLog: " . $e->getMessage());
            return false;
        }
    }
} 