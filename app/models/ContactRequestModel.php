<?php
require_once 'BaseModel.php';

class ContactRequestModel extends BaseModel {
    protected $table = 'contact_requests';
    
    protected function getAllowedColumns() {
        return [
            'id', 'sender_id', 'receiver_id', 'message', 'status', 
            'sent_at', 'responded_at', 'created_at', 'updated_at'
        ];
    }
    
    public function sendRequest($senderId, $receiverId, $message = null) {
        // Check if request already exists
        $existing = $this->getRequestBetweenUsers($senderId, $receiverId);
        if ($existing) {
            throw new Exception('Contact request already exists');
        }
        
        return $this->create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $message,
            'status' => 'pending',
            'sent_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function getRequestBetweenUsers($userId1, $userId2) {
        $sql = "SELECT * FROM contact_requests 
                WHERE (sender_id = :sender1 AND receiver_id = :receiver1)
                OR (sender_id = :sender2 AND receiver_id = :receiver2)
                ORDER BY sent_at DESC LIMIT 1";
        
        $params = [
            ':sender1' => $userId1,
            ':receiver1' => $userId2,
            ':sender2' => $userId2,
            ':receiver2' => $userId1
        ];
        
        return $this->fetchOne($sql, $params);
    }
    
    public function respondToRequest($requestId, $userId, $status) {
        if (!in_array($status, ['accepted', 'rejected'])) {
            throw new Exception('Invalid status');
        }
        
        try {
            $this->db->beginTransaction();
            
            // First, check if the request exists and is pending
            $request = $this->find($requestId);
            if (!$request) {
                throw new Exception('Contact request not found');
            }
            
            if ($request['receiver_id'] !== $userId) {
                throw new Exception('You are not authorized to respond to this request');
            }
            
            if ($request['status'] !== 'pending') {
                throw new Exception('This request has already been ' . $request['status']);
            }
            
            $sql = "UPDATE {$this->table} 
                    SET status = :status, responded_at = NOW()
                    WHERE id = :request_id AND receiver_id = :user_id AND status = 'pending'";
            
            $params = [
                ':status' => $status,
                ':request_id' => $requestId,
                ':user_id' => $userId
            ];
            
            $stmt = $this->execute($sql, $params);
            $success = $stmt->rowCount() > 0;
            
            if ($success) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                throw new Exception('Failed to update contact request status');
            }
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Database error in respondToRequest(): " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw new Exception('Failed to respond to contact request');
        }
    }
    
    public function getReceivedRequests($userId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT cr.*, 
                       up.first_name, up.last_name, up.profile_photo, up.district,
                       TIMESTAMPDIFF(YEAR, up.date_of_birth, CURDATE()) as age
                FROM {$this->table} cr
                LEFT JOIN user_profiles up ON cr.sender_id = up.user_id
                LEFT JOIN users u ON cr.sender_id = u.id
                WHERE cr.receiver_id = :user_id AND u.status = 'active'
                ORDER BY cr.sent_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }
    
    public function getSentRequests($userId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT cr.*, 
                       up.first_name, up.last_name, up.profile_photo, up.district,
                       TIMESTAMPDIFF(YEAR, up.date_of_birth, CURDATE()) as age
                FROM {$this->table} cr
                LEFT JOIN user_profiles up ON cr.receiver_id = up.user_id
                LEFT JOIN users u ON cr.receiver_id = u.id
                WHERE cr.sender_id = :user_id AND u.status = 'active'
                ORDER BY cr.sent_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }
    
    public function getAcceptedContacts($userId) {
        $sql = "SELECT DISTINCT 
                    CASE 
                        WHEN cr.sender_id = :user_id THEN cr.receiver_id 
                        ELSE cr.sender_id 
                    END as contact_user_id,
                    up.first_name, up.last_name, up.profile_photo,
                    u.email, u.phone
                FROM {$this->table} cr
                LEFT JOIN user_profiles up ON (
                    CASE 
                        WHEN cr.sender_id = :user_id THEN cr.receiver_id 
                        ELSE cr.sender_id 
                    END = up.user_id
                )
                LEFT JOIN users u ON up.user_id = u.id
                WHERE (cr.sender_id = :user_id OR cr.receiver_id = :user_id)
                AND cr.status = 'accepted'
                AND u.status = 'active'
                ORDER BY cr.responded_at DESC";
        
        return $this->fetchAll($sql, [':user_id' => $userId]);
    }
    
    public function getRequestStats($userId) {
        $sql = "SELECT 
                    COUNT(CASE WHEN receiver_id = :user_id1 AND status = 'pending' THEN 1 END) as pending_received,
                    COUNT(CASE WHEN sender_id = :user_id2 AND status = 'pending' THEN 1 END) as pending_sent,
                    COUNT(CASE WHEN (sender_id = :user_id3 OR receiver_id = :user_id4) AND status = 'accepted' THEN 1 END) as accepted,
                    COUNT(CASE WHEN (sender_id = :user_id5 OR receiver_id = :user_id6) AND status = 'rejected' THEN 1 END) as rejected
                FROM {$this->table}";
        
        return $this->fetchOne($sql, [
            ':user_id1' => $userId,
            ':user_id2' => $userId,
            ':user_id3' => $userId,
            ':user_id4' => $userId,
            ':user_id5' => $userId,
            ':user_id6' => $userId
        ]);
    }
    
    public function checkDailyLimit($userId, $limit = 10) {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE sender_id = :user_id 
                AND DATE(sent_at) = CURDATE()";
        
        $result = $this->fetchOne($sql, [':user_id' => $userId]);
        return $result['count'] < $limit;
    }
    
    public function getRequestHistory($userId, $days = 30) {
        $sql = "SELECT DATE(sent_at) as date, COUNT(*) as requests_sent
                FROM {$this->table}
                WHERE sender_id = :user_id 
                AND sent_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(sent_at)
                ORDER BY date DESC";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':days' => $days
        ]);
    }
    
    public function getPopularProfiles($days = 7, $limit = 10) {
        $sql = "SELECT receiver_id as user_id, COUNT(*) as request_count,
                       up.first_name, up.last_name, up.profile_photo
                FROM {$this->table} cr
                LEFT JOIN user_profiles up ON cr.receiver_id = up.user_id
                LEFT JOIN users u ON cr.receiver_id = u.id
                WHERE cr.sent_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                AND u.status = 'active'
                GROUP BY receiver_id
                ORDER BY request_count DESC
                LIMIT :limit";
        
        return $this->fetchAll($sql, [
            ':days' => $days,
            ':limit' => $limit
        ]);
    }
}
?>