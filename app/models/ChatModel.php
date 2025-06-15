<?php

class ChatModel extends BaseModel {
    public function createConversation($participants) {
        try {
            $this->db->beginTransaction();

            // Create conversation
            $sql = "INSERT INTO chat_conversations () VALUES ()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $conversationId = $this->db->lastInsertId();

            // Add participants
            $sql = "INSERT INTO chat_participants (conversation_id, user_id) VALUES (:conversation_id, :user_id)";
            $stmt = $this->db->prepare($sql);
            
            foreach ($participants as $userId) {
                $stmt->execute([
                    ':conversation_id' => $conversationId,
                    ':user_id' => $userId
                ]);
            }

            $this->db->commit();
            return $conversationId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function sendMessage($conversationId, $senderId, $messageType, $content, $fileUrl = null) {
        $sql = "INSERT INTO chat_messages (conversation_id, sender_id, message_type, content, file_url) 
                VALUES (:conversation_id, :sender_id, :message_type, :content, :file_url)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':sender_id' => $senderId,
            ':message_type' => $messageType,
            ':content' => $content,
            ':file_url' => $fileUrl
        ]);

        return $this->db->lastInsertId();
    }

    public function getConversations($userId) {
        $sql = "SELECT 
                    c.id as conversation_id,
                    cm.content as last_message,
                    cm.created_at as last_message_time,
                    cm.message_type,
                    u.first_name,
                    u.profile_photo,
                    us.is_online,
                    us.last_active_at
                FROM chat_conversations c
                JOIN chat_participants cp ON c.id = cp.conversation_id
                JOIN chat_participants other_cp ON c.id = other_cp.conversation_id AND other_cp.user_id != :user_id
                JOIN users u ON other_cp.user_id = u.id
                LEFT JOIN user_online_status us ON u.id = us.user_id
                LEFT JOIN chat_messages cm ON c.id = cm.conversation_id
                WHERE cp.user_id = :user_id
                AND cm.id = (
                    SELECT MAX(id) 
                    FROM chat_messages 
                    WHERE conversation_id = c.id
                )
                ORDER BY cm.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMessages($conversationId, $limit = 50, $offset = 0) {
        $sql = "SELECT 
                    cm.*,
                    u.first_name,
                    u.profile_photo,
                    ma.file_type,
                    ma.file_url as attachment_url,
                    ma.thumbnail_url
                FROM chat_messages cm
                JOIN users u ON cm.sender_id = u.id
                LEFT JOIN message_attachments ma ON cm.id = ma.message_id
                WHERE cm.conversation_id = :conversation_id
                ORDER BY cm.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':conversation_id', $conversationId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markMessagesAsRead($conversationId, $userId) {
        $sql = "UPDATE chat_messages 
                SET read_at = CURRENT_TIMESTAMP
                WHERE conversation_id = :conversation_id 
                AND sender_id != :user_id 
                AND read_at IS NULL";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':conversation_id' => $conversationId,
            ':user_id' => $userId
        ]);
    }

    public function updateOnlineStatus($userId, $isOnline) {
        $sql = "INSERT INTO user_online_status (user_id, is_online, last_active_at) 
                VALUES (:user_id, :is_online, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE 
                is_online = :is_online,
                last_active_at = CURRENT_TIMESTAMP";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':is_online' => $isOnline
        ]);
    }

    public function logCall($conversationId, $callerId, $receiverId, $callType) {
        $sql = "INSERT INTO call_logs (conversation_id, caller_id, receiver_id, call_type, status) 
                VALUES (:conversation_id, :caller_id, :receiver_id, :call_type, 'initiated')";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':conversation_id' => $conversationId,
            ':caller_id' => $callerId,
            ':receiver_id' => $receiverId,
            ':call_type' => $callType
        ]);
    }

    public function updateCallStatus($callId, $status, $endTime = null) {
        $sql = "UPDATE call_logs 
                SET status = :status" .
                ($status === 'accepted' ? ", start_time = CURRENT_TIMESTAMP" : "") .
                ($endTime ? ", end_time = CURRENT_TIMESTAMP" : "") .
                " WHERE id = :call_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':call_id' => $callId,
            ':status' => $status
        ]);
    }
}
