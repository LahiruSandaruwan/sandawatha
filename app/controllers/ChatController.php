<?php
require_once 'BaseController.php';
require_once SITE_ROOT . '/app/models/MessageModel.php';
require_once SITE_ROOT . '/app/helpers/PermissionMiddleware.php';

class ChatController extends BaseController {
    private $messageModel;
    
    public function __construct() {
        $this->messageModel = new MessageModel();
    }
    
    /**
     * Send a real-time chat message
     */
    public function sendMessage() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $receiverId = $input['receiver_id'] ?? '';
        $message = $this->sanitizeInput($input['message'] ?? '');
        $messageType = $input['type'] ?? 'text';
        
        if (empty($receiverId) || empty($message)) {
            $this->json(['success' => false, 'message' => 'Receiver ID and message are required'], 400);
        }
        
        // Check if user is trying to message themselves
        if ($currentUser['id'] == $receiverId) {
            $this->json(['success' => false, 'message' => 'Cannot send message to yourself'], 400);
        }
        
        // Check daily message limit
        if (PermissionMiddleware::hasReachedLimit('daily_messages', $currentUser['id'])) {
            $packageInfo = PermissionMiddleware::getUserPackageInfo($currentUser['id']);
            $packageName = $packageInfo ? $packageInfo['name'] : 'Basic';
            
            $this->json([
                'success' => false, 
                'message' => "Daily message limit reached. Upgrade from {$packageName} for unlimited messaging.",
                'action' => 'upgrade_required',
                'upgrade_url' => BASE_URL . '/premium'
            ], 429);
        }
        
        try {
            $messageId = $this->messageModel->sendMessage(
                $currentUser['id'],
                $receiverId,
                'Chat Message',
                $message,
                false,
                null,
                $messageType
            );
            
            // Log activity
            $this->logUserActivity($currentUser['id'], 'chat_message_sent', "Sent chat message to user ID: {$receiverId}");
            
            $this->json([
                'success' => true,
                'message' => 'Message sent successfully!',
                'messageId' => $messageId,
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("Chat message error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to send message. Please try again.'], 500);
        }
    }
    
    /**
     * Upload and send file
     */
    public function uploadFile() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $receiverId = $_POST['receiver_id'] ?? '';
        if (empty($receiverId)) {
            $this->json(['success' => false, 'message' => 'Receiver ID is required'], 400);
        }
        
        // Check daily message limit (file messages count towards limit)
        if (PermissionMiddleware::hasReachedLimit('daily_messages', $currentUser['id'])) {
            $packageInfo = PermissionMiddleware::getUserPackageInfo($currentUser['id']);
            $packageName = $packageInfo ? $packageInfo['name'] : 'Basic';
            
            $this->json([
                'success' => false, 
                'message' => "Daily message limit reached. Upgrade from {$packageName} for unlimited messaging.",
                'action' => 'upgrade_required'
            ], 429);
        }
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'No file uploaded or upload error'], 400);
        }
        
        $file = $_FILES['file'];
        
        // Validate file
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxFileSize) {
            $this->json(['success' => false, 'message' => 'File too large. Maximum size is 10MB.'], 400);
        }
        
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'video/mp4', 'video/webm', 'video/quicktime',
            'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/mpeg',
            'application/pdf', 'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (!in_array($file['type'], $allowedTypes)) {
            $this->json(['success' => false, 'message' => 'File type not allowed'], 400);
        }
        
        try {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('chat_', true) . '.' . $extension;
            $uploadPath = UPLOAD_PATH . '/chat/' . $filename;
            
            // Create directory if it doesn't exist
            $uploadDir = dirname($uploadPath);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('Failed to move uploaded file');
            }
            
            // Create file URL
            $fileUrl = UPLOAD_URL . '/chat/' . $filename;
            
            // Save as message
            $messageId = $this->messageModel->sendMessage(
                $currentUser['id'],
                $receiverId,
                'File Shared',
                $fileUrl,
                false,
                null,
                'file',
                [
                    'filename' => $file['name'],
                    'filesize' => $file['size'],
                    'filetype' => $file['type']
                ]
            );
            
            // Log activity
            $this->logUserActivity($currentUser['id'], 'file_shared', "Shared file: {$file['name']} to user ID: {$receiverId}");
            
            $this->json([
                'success' => true,
                'message' => 'File uploaded successfully!',
                'messageId' => $messageId,
                'fileUrl' => $fileUrl,
                'filename' => $file['name'],
                'filesize' => $file['size'],
                'filetype' => $file['type']
            ]);
            
        } catch (Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            
            // Clean up file if it was created
            if (isset($uploadPath) && file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            
            $this->json(['success' => false, 'message' => 'Failed to upload file. Please try again.'], 500);
        }
    }
    
    /**
     * Update user online status
     */
    public function updateOnlineStatus() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? 'online';
        
        // Update status in session or database
        $_SESSION['user_status'] = $status;
        $_SESSION['last_activity'] = time();
        
        // Here you would typically update the WebSocket server
        // about the status change
        
        $this->json([
            'success' => true,
            'message' => 'Status updated',
            'status' => $status
        ]);
    }
    
    /**
     * Get user's current usage statistics
     */
    public function getUsageStats() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $feature = $_GET['feature'] ?? 'daily_messages';
        
        $limit = PermissionMiddleware::getUserFeatureLimit($currentUser['id'], $feature);
        $remaining = PermissionMiddleware::getRemainingQuota($feature, $currentUser['id']);
        $limitReached = PermissionMiddleware::hasReachedLimit($feature, $currentUser['id']);
        
        $this->json([
            'success' => true,
            'feature' => $feature,
            'limit' => $limit === PHP_INT_MAX ? 'unlimited' : $limit,
            'remaining' => $remaining,
            'limitReached' => $limitReached,
            'currentUsage' => $limit === PHP_INT_MAX ? 0 : ($limit - $remaining)
        ]);
    }
    
    /**
     * Initiate a call (audio or video)
     */
    public function initiateCall() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $receiverId = $input['receiver_id'] ?? '';
        $callType = $input['call_type'] ?? 'audio'; // 'audio' or 'video'
        
        if (empty($receiverId)) {
            $this->json(['success' => false, 'message' => 'Receiver ID is required'], 400);
        }
        
        // Check if user has permission for this call type
        $feature = $callType === 'video' ? 'video_calling' : 'audio_calling';
        
        if (!PermissionMiddleware::hasFeature($feature, $currentUser['id'])) {
            $packageInfo = PermissionMiddleware::getUserPackageInfo($currentUser['id']);
            $packageName = $packageInfo ? $packageInfo['name'] : 'Basic';
            
            $this->json([
                'success' => false,
                'message' => "{$callType} calling requires a premium subscription. Upgrade from {$packageName}.",
                'action' => 'upgrade_required',
                'upgrade_url' => BASE_URL . '/premium'
            ], 403);
        }
        
        // Generate call session
        $callId = uniqid('call_', true);
        
        // Here you would typically:
        // 1. Store call session in database
        // 2. Send call invitation via WebSocket
        // 3. Set up WebRTC signaling
        
        // Log activity
        $this->logUserActivity($currentUser['id'], 'call_initiated', "Initiated {$callType} call to user ID: {$receiverId}");
        
        $this->json([
            'success' => true,
            'message' => 'Call initiated',
            'callId' => $callId,
            'callType' => $callType,
            'callerId' => $currentUser['id'],
            'receiverId' => $receiverId
        ]);
    }
    
    /**
     * Update call status (accept, reject, end)
     */
    public function updateCallStatus() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $callId = $input['call_id'] ?? '';
        $status = $input['status'] ?? ''; // 'accepted', 'rejected', 'ended'
        
        if (empty($callId) || empty($status)) {
            $this->json(['success' => false, 'message' => 'Call ID and status are required'], 400);
        }
        
        // Here you would typically:
        // 1. Update call status in database
        // 2. Notify other participant via WebSocket
        // 3. Handle WebRTC signaling
        
        // Log activity
        $this->logUserActivity($currentUser['id'], 'call_status_updated', "Call {$callId} status: {$status}");
        
        $this->json([
            'success' => true,
            'message' => 'Call status updated',
            'callId' => $callId,
            'status' => $status
        ]);
    }

    /**
     * Handle WebRTC signaling for calls
     */
    public function handleCallSignaling() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $signalType = $input['type'] ?? '';
        $callId = $input['call_id'] ?? '';
        $targetUserId = $input['target_user_id'] ?? '';
        $signalData = $input['data'] ?? null;
        
        if (empty($signalType) || empty($callId)) {
            $this->json(['success' => false, 'message' => 'Signal type and call ID are required'], 400);
        }
        
        // Validate signal types
        $allowedSignals = [
            'offer', 'answer', 'ice_candidate', 
            'call_invitation', 'call_accepted', 'call_rejected', 'call_ended'
        ];
        
        if (!in_array($signalType, $allowedSignals)) {
            $this->json(['success' => false, 'message' => 'Invalid signal type'], 400);
        }
        
        try {
            // In a real implementation, this would be sent through WebSocket server
            // For now, we'll log the signaling attempt
            $signalPayload = [
                'type' => $signalType,
                'call_id' => $callId,
                'from_user_id' => $currentUser['id'],
                'target_user_id' => $targetUserId,
                'data' => $signalData,
                'timestamp' => time()
            ];
            
            // Log WebRTC signaling activity
            $this->logUserActivity(
                $currentUser['id'], 
                'webrtc_signaling', 
                "Signal: {$signalType} for call: {$callId}"
            );
            
            $this->json([
                'success' => true,
                'message' => 'Signal processed',
                'signal_type' => $signalType,
                'call_id' => $callId
            ]);
            
        } catch (Exception $e) {
            error_log("WebRTC signaling error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to process signal'], 500);
        }
    }

    /**
     * Get call history for the user
     */
    public function getCallHistory() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 20), 100); // Cap at 100
        $offset = ($page - 1) * $limit;
        
        try {
            // Get call-related activities from activity logs
            $stmt = $this->db->prepare("
                SELECT 
                    al.action,
                    al.details,
                    al.created_at,
                    CASE 
                        WHEN al.action LIKE '%call%' THEN
                            REGEXP_SUBSTR(al.details, 'user ID: ([0-9]+)', 1, 1, '', 1)
                        ELSE NULL
                    END as other_user_id
                FROM activity_logs al
                WHERE al.user_id = ? 
                AND al.action IN ('call_initiated', 'call_accepted', 'call_rejected', 'call_ended', 'call_status_updated')
                ORDER BY al.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$currentUser['id'], $limit, $offset]);
            $callLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get user info for call participants
            $userIds = array_filter(array_column($callLogs, 'other_user_id'));
            $userInfo = [];
            
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $userStmt = $this->db->prepare("
                    SELECT id, first_name, last_name 
                    FROM users 
                    WHERE id IN ($placeholders)
                ");
                $userStmt->execute($userIds);
                $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($users as $user) {
                    $userInfo[$user['id']] = $user;
                }
            }
            
            // Format call history
            $formattedHistory = [];
            foreach ($callLogs as $log) {
                $otherUserId = $log['other_user_id'];
                $otherUser = $userInfo[$otherUserId] ?? null;
                
                $formattedHistory[] = [
                    'action' => $log['action'],
                    'details' => $log['details'],
                    'timestamp' => $log['created_at'],
                    'formatted_time' => $this->formatTimeAgo($log['created_at']),
                    'other_user' => $otherUser ? [
                        'id' => $otherUser['id'],
                        'name' => trim($otherUser['first_name'] . ' ' . $otherUser['last_name']) ?: 'Unknown User'
                    ] : null
                ];
            }
            
            $this->json([
                'success' => true,
                'call_history' => $formattedHistory,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => count($formattedHistory),
                    'has_more' => count($formattedHistory) === $limit
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Call history error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to retrieve call history'], 500);
        }
    }

    /**
     * Format timestamp to human-readable "time ago"
     */
    private function formatTimeAgo($timestamp) {
        $time = time() - strtotime($timestamp);
        
        if ($time < 60) return 'Just now';
        if ($time < 3600) return floor($time / 60) . ' minutes ago';
        if ($time < 86400) return floor($time / 3600) . ' hours ago';
        if ($time < 2592000) return floor($time / 86400) . ' days ago';
        
        return date('M j, Y', strtotime($timestamp));
    }
    
    /**
     * Get online users for sidebar
     */
    public function getOnlineUsers() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        // This would typically get online users from WebSocket server
        // For now, return empty array
        $onlineUsers = [];
        
        $this->json([
            'success' => true,
            'users' => $onlineUsers,
            'count' => count($onlineUsers)
        ]);
    }
    
    /**
     * Mark message as read
     */
    public function markMessageRead() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $messageId = $input['message_id'] ?? '';
        
        if (empty($messageId)) {
            $this->json(['success' => false, 'message' => 'Message ID is required'], 400);
        }
        
        try {
            $success = $this->messageModel->markAsRead($messageId, $currentUser['id']);
            
            $this->json([
                'success' => true,
                'message' => 'Message marked as read'
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Get conversation history with pagination
     */
    public function getConversationHistory() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $otherUserId = $_GET['user_id'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        
        if (empty($otherUserId)) {
            $this->json(['success' => false, 'message' => 'User ID is required'], 400);
        }
        
        try {
            $messages = $this->messageModel->getConversationBetweenUsers(
                $currentUser['id'], 
                $otherUserId, 
                $page, 
                $limit
            );
            
            $this->json([
                'success' => true,
                'messages' => $messages,
                'page' => $page,
                'hasMore' => count($messages) === $limit
            ]);
            
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
