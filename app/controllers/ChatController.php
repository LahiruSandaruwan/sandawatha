<?php

class ChatController extends BaseController {
    private $chatModel;
    private $userModel;

    public function __construct() {
        $this->chatModel = new ChatModel();
        $this->userModel = new UserModel();
    }

    public function index() {
        // Get all conversations for the current user
        $conversations = $this->chatModel->getConversations($_SESSION['user_id']);
        
        $this->view('messages/chat', [
            'conversations' => $conversations
        ]);
    }

    public function conversation($conversationId) {
        // Get messages for the conversation
        $messages = $this->chatModel->getMessages($conversationId);
        
        // Mark messages as read
        $this->chatModel->markMessagesAsRead($conversationId, $_SESSION['user_id']);
        
        $this->view('messages/conversation', [
            'messages' => $messages,
            'conversationId' => $conversationId
        ]);
    }

    public function sendMessage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $conversationId = $_POST['conversation_id'];
        $content = $_POST['content'];
        $messageType = $_POST['type'] ?? 'text';
        $fileUrl = null;

        // Handle file uploads
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = SITE_ROOT . '/public/uploads/chat/';
            $fileExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $fileName)) {
                $fileUrl = '/uploads/chat/' . $fileName;
            }
        }

        try {
            $messageId = $this->chatModel->sendMessage(
                $conversationId, 
                $_SESSION['user_id'],
                $messageType,
                $content,
                $fileUrl
            );

            echo json_encode([
                'success' => true,
                'message_id' => $messageId
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to send message']);
        }
    }

    public function initiateCall() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $conversationId = $_POST['conversation_id'];
        $receiverId = $_POST['receiver_id'];
        $callType = $_POST['call_type']; // 'audio' or 'video'

        try {
            $callId = $this->chatModel->logCall(
                $conversationId,
                $_SESSION['user_id'],
                $receiverId,
                $callType
            );

            // Generate WebRTC credentials and room info
            $callData = [
                'call_id' => $callId,
                'ice_servers' => [
                    ['urls' => ['stun:stun.l.google.com:19302']]
                ],
                'room' => 'sandawatha_' . $callId
            ];

            echo json_encode([
                'success' => true,
                'call_data' => $callData
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to initiate call']);
        }
    }

    public function updateCallStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $callId = $_POST['call_id'];
        $status = $_POST['status'];
        $endTime = isset($_POST['end_time']) ? true : false;

        try {
            $this->chatModel->updateCallStatus($callId, $status, $endTime);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update call status']);
        }
    }

    public function updateOnlineStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $isOnline = $_POST['is_online'] === 'true';
        
        try {
            $this->chatModel->updateOnlineStatus($_SESSION['user_id'], $isOnline);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update online status']);
        }
    }
}
