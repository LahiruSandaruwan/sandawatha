<?php
require_once 'BaseModel.php';

class FeedbackModel extends BaseModel {
    protected $table = 'feedback';
    
    public function submitFeedback($data) {
        return $this->create([
            'user_id' => $data['user_id'] ?? null,
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'rating' => $data['rating'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'is_public' => isset($data['is_public']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function getAllFeedback($page = 1, $ratingFilter = '', $limit = 20) {
        $offset = ($page - 1) * $limit;
        $where = '1=1';
        $params = [];
        
        if (!empty($ratingFilter)) {
            $where .= ' AND rating = :rating';
            $params[':rating'] = $ratingFilter;
        }
        
        $sql = "SELECT f.*, up.first_name, up.last_name
                FROM {$this->table} f
                LEFT JOIN user_profiles up ON f.user_id = up.user_id
                WHERE {$where}
                ORDER BY f.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        return $this->fetchAll($sql, $params);
    }
    
    public function getPublicFeedback($limit = 10) {
        $sql = "SELECT f.*, up.first_name, up.last_name
                FROM {$this->table} f
                LEFT JOIN user_profiles up ON f.user_id = up.user_id
                WHERE f.is_public = 1 AND f.rating >= 4
                ORDER BY f.created_at DESC
                LIMIT :limit";
        
        return $this->fetchAll($sql, [':limit' => $limit]);
    }
    
    public function getFeedbackStats() {
        $sql = "SELECT 
                    COUNT(*) as total_feedback,
                    AVG(rating) as average_rating,
                    COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                    COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                    COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                    COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                    COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star,
                    COUNT(CASE WHEN is_public = 1 THEN 1 END) as public_feedback,
                    COUNT(CASE WHEN admin_response IS NOT NULL THEN 1 END) as responded_feedback
                FROM {$this->table}";
        
        return $this->fetchOne($sql);
    }
    
    public function getRecentFeedback($limit = 5) {
        $sql = "SELECT f.*, up.first_name, up.last_name
                FROM {$this->table} f
                LEFT JOIN user_profiles up ON f.user_id = up.user_id
                ORDER BY f.created_at DESC
                LIMIT :limit";
        
        return $this->fetchAll($sql, [':limit' => $limit]);
    }
    
    public function respondToFeedback($feedbackId, $response) {
        return $this->update($feedbackId, [
            'admin_response' => $response,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function getUserFeedback($userId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";
        
        return $this->fetchAll($sql, [':user_id' => $userId]);
    }
    
    public function getRatingDistribution() {
        $sql = "SELECT rating, COUNT(*) as count
                FROM {$this->table}
                GROUP BY rating
                ORDER BY rating DESC";
        
        return $this->fetchAll($sql);
    }
}
?>