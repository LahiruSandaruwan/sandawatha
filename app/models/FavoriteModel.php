<?php
require_once 'BaseModel.php';

class FavoriteModel extends BaseModel {
    protected $table = 'favorites';
    
    protected function getAllowedColumns() {
        return [
            'id', 'user_id', 'favorite_user_id', 'created_at', 'updated_at'
        ];
    }
    
    public function addFavorite($userId, $favoriteUserId) {
        // Check if already favorited
        if ($this->isFavorite($userId, $favoriteUserId)) {
            throw new Exception('Profile already in favorites');
        }
        
        return $this->create([
            'user_id' => $userId,
            'favorite_user_id' => $favoriteUserId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function removeFavorite($userId, $favoriteUserId) {
        $sql = "DELETE FROM {$this->table} 
                WHERE user_id = :user_id AND favorite_user_id = :favorite_user_id";
        
        return $this->execute($sql, [
            ':user_id' => $userId,
            ':favorite_user_id' => $favoriteUserId
        ]);
    }
    
    public function isFavorite($userId, $favoriteUserId) {
        $sql = "SELECT id FROM {$this->table} 
                WHERE user_id = :user_id AND favorite_user_id = :favorite_user_id";
        
        $result = $this->fetchOne($sql, [
            ':user_id' => $userId,
            ':favorite_user_id' => $favoriteUserId
        ]);
        
        return !empty($result);
    }
    
    public function getUserFavorites($userId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT f.*, 
                       up.first_name, up.last_name, up.profile_photo, up.district, up.religion,
                       TIMESTAMPDIFF(YEAR, up.date_of_birth, CURDATE()) as age,
                       u.status as user_status
                FROM {$this->table} f
                LEFT JOIN user_profiles up ON f.favorite_user_id = up.user_id
                LEFT JOIN users u ON f.favorite_user_id = u.id
                WHERE f.user_id = :user_id AND u.status = 'active'
                ORDER BY f.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }
    
    public function getFavoriteCount($userId) {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} f
                LEFT JOIN users u ON f.favorite_user_id = u.id
                WHERE f.user_id = :user_id AND u.status = 'active'";
        
        $result = $this->fetchOne($sql, [':user_id' => $userId]);
        return $result['count'];
    }
    
    public function getWhoFavoritedUser($userId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT f.*, 
                       up.first_name, up.last_name, up.profile_photo, up.district,
                       TIMESTAMPDIFF(YEAR, up.date_of_birth, CURDATE()) as age
                FROM {$this->table} f
                LEFT JOIN user_profiles up ON f.user_id = up.user_id
                LEFT JOIN users u ON f.user_id = u.id
                WHERE f.favorite_user_id = :user_id AND u.status = 'active'
                ORDER BY f.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }
    
    public function getFavoriteStats($userId) {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM {$this->table} f 
                     LEFT JOIN users u ON f.favorite_user_id = u.id 
                     WHERE f.user_id = :user_id1 AND u.status = 'active') as my_favorites,
                    (SELECT COUNT(*) FROM {$this->table} f 
                     LEFT JOIN users u ON f.user_id = u.id 
                     WHERE f.favorite_user_id = :user_id2 AND u.status = 'active') as favorited_by";
        
        return $this->fetchOne($sql, [
            ':user_id1' => $userId,
            ':user_id2' => $userId
        ]);
    }
    
    public function getMutualFavorites($userId) {
        $sql = "SELECT f1.favorite_user_id as mutual_user_id,
                       up.first_name, up.last_name, up.profile_photo
                FROM {$this->table} f1
                INNER JOIN {$this->table} f2 ON f1.favorite_user_id = f2.user_id 
                                              AND f1.user_id = f2.favorite_user_id
                LEFT JOIN user_profiles up ON f1.favorite_user_id = up.user_id
                LEFT JOIN users u ON f1.favorite_user_id = u.id
                WHERE f1.user_id = :user_id AND u.status = 'active'
                ORDER BY f1.created_at DESC";
        
        return $this->fetchAll($sql, [':user_id' => $userId]);
    }
    
    public function getRecentFavorites($userId, $days = 7) {
        $sql = "SELECT f.*, 
                       up.first_name, up.last_name, up.profile_photo
                FROM {$this->table} f
                LEFT JOIN user_profiles up ON f.favorite_user_id = up.user_id
                LEFT JOIN users u ON f.favorite_user_id = u.id
                WHERE f.user_id = :user_id 
                AND f.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                AND u.status = 'active'
                ORDER BY f.created_at DESC";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':days' => $days
        ]);
    }
    
    public function getMostFavoritedProfiles($limit = 10) {
        $sql = "SELECT f.favorite_user_id as user_id, COUNT(*) as favorite_count,
                       up.first_name, up.last_name, up.profile_photo
                FROM {$this->table} f
                LEFT JOIN user_profiles up ON f.favorite_user_id = up.user_id
                LEFT JOIN users u ON f.favorite_user_id = u.id
                WHERE u.status = 'active'
                GROUP BY f.favorite_user_id
                ORDER BY favorite_count DESC
                LIMIT :limit";
        
        return $this->fetchAll($sql, [':limit' => $limit]);
    }
}
?>