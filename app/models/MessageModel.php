<?php
require_once 'BaseModel.php';

class MessageModel extends BaseModel {
    protected $table = 'messages';
    
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
    
    public function getInbox($userId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT m.*, 
                       up.first_name as sender_first_name, 
                       up.last_name as sender_last_name,
                       up.profile_photo as sender_photo,
                       COUNT(replies.id) as reply_count
                FROM {$this->table} m
                LEFT JOIN user_profiles up ON m.sender_id = up.user_id
                LEFT JOIN {$this->table} replies ON replies.parent_message_id = m.id
                WHERE m.receiver_id = :user_id AND m.parent_message_id IS NULL
                GROUP BY m.id
                ORDER BY m.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }
    
    public function getSentMessages($userId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT m.*, 
                       up.first_name as receiver_first_name, 
                       up.last_name as receiver_last_name,
                       up.profile_photo as receiver_photo
                FROM {$this->table} m
                LEFT JOIN user_profiles up ON m.receiver_id = up.user_id
                WHERE m.sender_id = :user_id AND m.parent_message_id IS NULL
                ORDER BY m.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':limit' => $limit,
            ':offset' => $offset
        ]);
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
                WHERE receiver_id = :user_id AND is_read = 0";
        
        $result = $this->fetchOne($sql, [':user_id' => $userId]);
        return $result['count'];
    }
    
    public function getMessageStats($userId) {
        $sql = "SELECT 
                    COUNT(CASE WHEN receiver_id = :user_id1 THEN 1 END) as received_count,
                    COUNT(CASE WHEN sender_id = :user_id2 THEN 1 END) as sent_count,
                    COUNT(CASE WHEN receiver_id = :user_id3 AND is_read = 0 THEN 1 END) as unread_count,
                    COUNT(CASE WHEN receiver_id = :user_id4 AND is_admin_message = 1 THEN 1 END) as admin_messages
                FROM {$this->table}
                WHERE sender_id = :user_id5 OR receiver_id = :user_id6";
        
        return $this->fetchOne($sql, [
            ':user_id1' => $userId,
            ':user_id2' => $userId,
            ':user_id3' => $userId,
            ':user_id4' => $userId,
            ':user_id5' => $userId,
            ':user_id6' => $userId
        ]);
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
        
        $sql = "SELECT m.*
                FROM {$this->table} m
                WHERE m.receiver_id = :user_id AND m.is_admin_message = 1
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
}
?>