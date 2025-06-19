<?php
namespace App\models;

use App\models\BaseModel;

class MessageModel extends BaseModel {
    protected $table = 'messages';
    
    protected function getAllowedColumns() {
        return [
            'sender_id', 'receiver_id', 'subject', 'message', 
            'is_admin_message', 'parent_message_id', 'created_at', 
            'updated_at', 'is_read'
        ];
    }
    
    public function sendMessage($senderId, $receiverId, $subject, $message, $isAdminMessage = false, $parentMessageId = null) {
        return $this->create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'subject' => $subject,
            'message' => $message,
            'is_admin_message' => $isAdminMessage ? 1 : 0,
            'parent_message_id' => $parentMessageId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function getInboxMessages($userId, $page = 1, $limit = 20, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT m.*, 
                       up.first_name as sender_first_name, 
                       up.last_name as sender_last_name,
                       up.profile_photo as sender_photo,
                       COUNT(replies.id) as reply_count
                FROM {$this->table} m
                LEFT JOIN user_profiles up ON m.sender_id = up.user_id
                LEFT JOIN {$this->table} replies ON replies.parent_message_id = m.id
                WHERE m.receiver_id = :user_id AND m.parent_message_id IS NULL";

        $params = [
            ':user_id' => $userId
        ];

        if (!empty($search)) {
            $sql .= " AND (m.subject LIKE :search OR m.message LIKE :search 
                          OR CONCAT(up.first_name, ' ', up.last_name) LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " GROUP BY m.id
                  ORDER BY m.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        return $this->fetchAll($sql, $params);
    }
    
    public function getSentMessages($userId, $page = 1, $limit = 20, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT m.*, 
                       up.first_name as receiver_first_name, 
                       up.last_name as receiver_last_name,
                       up.profile_photo as receiver_photo,
                       COUNT(replies.id) as reply_count
                FROM {$this->table} m
                LEFT JOIN user_profiles up ON m.receiver_id = up.user_id
                LEFT JOIN {$this->table} replies ON replies.parent_message_id = m.id
                WHERE m.sender_id = :user_id AND m.parent_message_id IS NULL";

        $params = [
            ':user_id' => $userId
        ];

        if (!empty($search)) {
            $sql .= " AND (m.subject LIKE :search OR m.message LIKE :search 
                          OR CONCAT(up.first_name, ' ', up.last_name) LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " GROUP BY m.id
                  ORDER BY m.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        return $this->fetchAll($sql, $params);
    }
    
    public function getConversation($messageId, $userId) {
        // Get the main message
        $mainMessage = $this->getMessageWithParticipants($messageId);
        
        if (!$mainMessage) {
            return null;
        }
        
        // Check if user is participant
        if ($mainMessage['sender_id'] != $userId && $mainMessage['receiver_id'] != $userId) {
            return null;
        }
        
        // Get all replies
        $sql = "SELECT m.*, 
                       up.first_name, up.last_name, up.profile_photo
                FROM {$this->table} m
                LEFT JOIN user_profiles up ON m.sender_id = up.user_id
                WHERE m.parent_message_id = :message_id1 OR m.id = :message_id2
                ORDER BY m.created_at ASC";
        
        $conversation = $this->fetchAll($sql, [':message_id1' => $messageId, ':message_id2' => $messageId]);
        
        // Mark messages as read if user is receiver
        $this->markConversationAsRead($messageId, $userId);
        
        return $conversation;
    }
    
    public function getMessageWithParticipants($messageId) {
        $sql = "SELECT m.*,
                       sender.first_name as sender_first_name,
                       sender.last_name as sender_last_name,
                       sender.profile_photo as sender_photo,
                       receiver.first_name as receiver_first_name,
                       receiver.last_name as receiver_last_name,
                       receiver.profile_photo as receiver_photo
                FROM {$this->table} m
                LEFT JOIN user_profiles sender ON m.sender_id = sender.user_id
                LEFT JOIN user_profiles receiver ON m.receiver_id = receiver.user_id
                WHERE m.id = :message_id";
        
        return $this->fetchOne($sql, [':message_id' => $messageId]);
    }
    
    public function markAsRead($messageId, $userId) {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1, updated_at = NOW()
                WHERE id = :message_id AND receiver_id = :user_id";
        
        return $this->execute($sql, [
            ':message_id' => $messageId,
            ':user_id' => $userId
        ]);
    }
    
    public function markConversationAsRead($messageId, $userId) {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1, updated_at = NOW()
                WHERE (id = :message_id1 OR parent_message_id = :message_id2) 
                AND receiver_id = :user_id AND is_read = 0";
        
        return $this->execute($sql, [
            ':message_id1' => $messageId,
            ':message_id2' => $messageId,
            ':user_id' => $userId
        ]);
    }
    
    public function deleteMessage($messageId, $userId) {
        // Check if user owns the message (sender or receiver)
        $sql = "DELETE FROM {$this->table} 
                WHERE id = :message_id 
                AND (sender_id = :user_id1 OR receiver_id = :user_id2)";
        
        return $this->execute($sql, [
            ':message_id' => $messageId,
            ':user_id1' => $userId,
            ':user_id2' => $userId
        ]);
    }
    
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE receiver_id = :user_id 
                AND is_read = 0 
                AND parent_message_id IS NULL";
                
        $result = $this->fetchOne($sql, [':user_id' => $userId]);
        return $result ? $result['count'] : 0;
    }
    
    public function getMessageStats($userId) {
        // Use numbered parameters to avoid PDO parameter reuse issue
        $sql = "SELECT
                    SUM(CASE WHEN receiver_id = :uid1 AND is_read = 0 THEN 1 ELSE 0 END) as unread_count,
                    SUM(CASE WHEN receiver_id = :uid2 THEN 1 ELSE 0 END) as inbox_count,
                    SUM(CASE WHEN sender_id = :uid3 THEN 1 ELSE 0 END) as sent_count,
                    SUM(CASE WHEN receiver_id = :uid4 AND is_admin_message = 1 THEN 1 ELSE 0 END) as admin_count
                FROM {$this->table}
                WHERE sender_id = :uid5 OR receiver_id = :uid6";
        
        $result = $this->fetchOne($sql, [
            ':uid1' => $userId,
            ':uid2' => $userId,
            ':uid3' => $userId,
            ':uid4' => $userId,
            ':uid5' => $userId,
            ':uid6' => $userId
        ]);
        
        return [
            'unread' => (int)($result['unread_count'] ?? 0),
            'inbox' => (int)($result['inbox_count'] ?? 0),
            'sent' => (int)($result['sent_count'] ?? 0),
            'admin' => (int)($result['admin_count'] ?? 0)
        ];
    }
    
    public function searchMessages($userId, $query, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT m.*, 
                       up.first_name, up.last_name, up.profile_photo
                FROM {$this->table} m
                LEFT JOIN user_profiles up ON (
                    CASE WHEN m.sender_id = :user_id1 THEN m.receiver_id ELSE m.sender_id END = up.user_id
                )
                WHERE (m.sender_id = :user_id2 OR m.receiver_id = :user_id3)
                AND (m.subject LIKE :query OR m.message LIKE :query)
                ORDER BY m.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->fetchAll($sql, [
            ':user_id1' => $userId,
            ':user_id2' => $userId,
            ':user_id3' => $userId,
            ':query' => "%{$query}%",
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }
    
    public function getRecentConversations($userId, $limit = 10) {
        $sql = "SELECT DISTINCT
                    CASE WHEN m.sender_id = :user_id1 THEN m.receiver_id ELSE m.sender_id END as other_user_id,
                    MAX(m.created_at) as last_message_time,
                    up.first_name, up.last_name, up.profile_photo,
                    (SELECT subject FROM {$this->table} m2 
                     WHERE (m2.sender_id = :user_id2 AND m2.receiver_id = other_user_id) 
                        OR (m2.receiver_id = :user_id3 AND m2.sender_id = other_user_id)
                     ORDER BY m2.created_at DESC LIMIT 1) as last_subject,
                    (SELECT COUNT(*) FROM {$this->table} m3 
                     WHERE m3.receiver_id = :user_id4 
                       AND m3.sender_id = other_user_id 
                       AND m3.is_read = 0) as unread_count
                FROM {$this->table} m
                LEFT JOIN user_profiles up ON (
                    CASE WHEN m.sender_id = :user_id5 THEN m.receiver_id ELSE m.sender_id END = up.user_id
                )
                WHERE m.sender_id = :user_id6 OR m.receiver_id = :user_id7
                GROUP BY other_user_id
                ORDER BY last_message_time DESC
                LIMIT :limit";
        
        return $this->fetchAll($sql, [
            ':user_id1' => $userId,
            ':user_id2' => $userId,
            ':user_id3' => $userId,
            ':user_id4' => $userId,
            ':user_id5' => $userId,
            ':user_id6' => $userId,
            ':user_id7' => $userId,
            ':limit' => $limit
        ]);
    }
    
    public function checkDailyLimit($userId, $limit) {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE sender_id = :user_id 
                AND DATE(created_at) = CURDATE()
                AND parent_message_id IS NULL";
        
        $result = $this->fetchOne($sql, [':user_id' => $userId]);
        return $result['count'] < $limit;
    }
    
    public function getAdminMessages($userId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT m.*, 
                       up.first_name as sender_first_name, 
                       up.last_name as sender_last_name,
                       up.profile_photo as sender_photo,
                       COUNT(replies.id) as reply_count
                FROM {$this->table} m
                LEFT JOIN user_profiles up ON m.sender_id = up.user_id
                LEFT JOIN {$this->table} replies ON replies.parent_message_id = m.id
                WHERE m.receiver_id = :user_id 
                AND m.is_admin_message = 1 
                AND m.parent_message_id IS NULL
                GROUP BY m.id
                ORDER BY m.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }
    
    public function broadcastAdminMessage($subject, $message, $adminId) {
        // Get all active users
        $sql = "SELECT id FROM users WHERE status = 'active' AND role = 'user'";
        $users = $this->fetchAll($sql);
        
        $insertedCount = 0;
        foreach ($users as $user) {
            $messageId = $this->sendMessage($adminId, $user['id'], $subject, $message, true);
            if ($messageId) {
                $insertedCount++;
            }
        }
        
        return $insertedCount;
    }
    
    public function getConversationBetweenUsers($userId1, $userId2, $limit = 50) {
        $sql = "SELECT m.*, 
                       sender.first_name as sender_first_name,
                       sender.last_name as sender_last_name,
                       sender.profile_photo as sender_photo,
                       receiver.first_name as receiver_first_name,
                       receiver.last_name as receiver_last_name,
                       receiver.profile_photo as receiver_photo
                FROM {$this->table} m
                LEFT JOIN user_profiles sender ON m.sender_id = sender.user_id
                LEFT JOIN user_profiles receiver ON m.receiver_id = receiver.user_id
                WHERE ((m.sender_id = :user1_id1 AND m.receiver_id = :user2_id1) 
                    OR (m.sender_id = :user2_id2 AND m.receiver_id = :user1_id2))
                ORDER BY m.created_at ASC
                LIMIT :limit";
        
        $messages = $this->fetchAll($sql, [
            ':user1_id1' => $userId1,
            ':user2_id1' => $userId2,
            ':user2_id2' => $userId2,
            ':user1_id2' => $userId1,
            ':limit' => $limit
        ]);
        
        // Mark messages as read for the current user
        $this->markMessagesAsReadBetweenUsers($userId1, $userId2);
        
        return $messages;
    }
    
    private function markMessagesAsReadBetweenUsers($currentUserId, $otherUserId) {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1, updated_at = NOW()
                WHERE sender_id = :other_user_id 
                AND receiver_id = :current_user_id 
                AND is_read = 0";
        
        return $this->execute($sql, [
            ':other_user_id' => $otherUserId,
            ':current_user_id' => $currentUserId
        ]);
    }
}
?>