<?php
require_once 'BaseController.php';
require_once SITE_ROOT . '/app/models/MessageModel.php';
require_once SITE_ROOT . '/app/models/PremiumModel.php';
require_once SITE_ROOT . '/app/models/ProfileModel.php';

class MessageController extends BaseController {
    private $messageModel;
    private $premiumModel;
    
    public function __construct() {
        $this->messageModel = new MessageModel();
        $this->premiumModel = new PremiumModel();
    }
    
    public function inbox() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->redirect('/login');
        }
        
        // Load user profile information
        $profileModel = new ProfileModel();
        $userProfile = $profileModel->findByUserId($currentUser['id']);
        
        // Set user profile information in session
        $_SESSION['first_name'] = $userProfile['first_name'] ?? '';
        $_SESSION['last_name'] = $userProfile['last_name'] ?? '';
        
        $tab = $_GET['tab'] ?? 'inbox';
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        
        $messages = [];
        $sentMessages = [];
        $adminMessages = [];
        $conversations = [];
        $stats = [];
        
        if ($tab === 'inbox' || $tab === 'all') {
            $messages = $this->messageModel->getInboxMessages($currentUser['id'], $page, 20, $search);
        }
        
        if ($tab === 'sent' || $tab === 'all') {
            $sentMessages = $this->messageModel->getSentMessages($currentUser['id'], $page, 20, $search);
        }
        
        if ($tab === 'admin' || $tab === 'all') {
            $adminMessages = $this->messageModel->getAdminMessages($currentUser['id'], $page, 20);
        }
        
        if ($tab === 'conversations') {
            $conversations = $this->messageModel->getConversations($currentUser['id'], $page, 20);
        }
        
        $stats = $this->messageModel->getMessageStats($currentUser['id']);
        
        $this->layout('main', 'messages/inbox', [
            'messages' => $messages,
            'sentMessages' => $sentMessages,
            'adminMessages' => $adminMessages,
            'conversations' => $conversations,
            'stats' => $stats,
            'current_tab' => $tab,
            'search' => $search,
            'title' => 'Messages - Sandawatha.lk',
            'description' => 'View and manage your messages on Sandawatha.lk',
            'component_css' => [
                'chat/chat',
                'chat/connected-users'
            ],
            'scripts' => [
                'chat/chat',
                'chat/connected-users'
            ],
            'unread_count' => $stats['unread'] ?? 0,
            'csrf_token' => $this->generateCsrf()
        ]);
    }
    
    public function viewMessage($messageId) {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->redirect('/login');
        }
        
        $conversation = $this->messageModel->getConversation($messageId, $currentUser['id']);
        
        if (!$conversation) {
            $this->redirectWithMessage('/messages', 'Message not found or access denied.', 'error');
        }
        
        $mainMessage = $conversation[0];
        
        $data = [
            'title' => 'Message: ' . htmlspecialchars($mainMessage['subject']) . ' - Sandawatha.lk',
            'conversation' => $conversation,
            'main_message' => $mainMessage,
            'current_user_id' => $currentUser['id'],
            'csrf_token' => $this->generateCsrf(),
            'component_css' => ['chat/chat'],
            'scripts' => ['chat/message-view']
        ];
        
        $this->layout('main', 'messages/view', $data);
    }
    
    public function send() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $receiverId = $_POST['receiver_id'] ?? '';
        $subject = $this->sanitizeInput($_POST['subject'] ?? '');
        $message = $this->sanitizeInput($_POST['message'] ?? '');
        
        if (empty($receiverId) || empty($subject) || empty($message)) {
            $this->json(['success' => false, 'message' => 'All fields are required'], 400);
        }
        
        // Check if user is trying to message themselves
        if ($currentUser['id'] == $receiverId) {
            $this->json(['success' => false, 'message' => 'Cannot send message to yourself'], 400);
        }
        
        // Check daily limit based on premium status
        $features = $this->premiumModel->getUserFeatures($currentUser['id']);
        $dailyLimit = $features['message_limit_per_day'];
        
        if ($dailyLimit !== 'unlimited') {
            if (!$this->messageModel->checkDailyLimit($currentUser['id'], $dailyLimit)) {
                $this->json(['success' => false, 'message' => 'Daily message limit reached. Upgrade to premium for unlimited messaging.'], 429);
            }
        }
        
        try {
            $messageId = $this->messageModel->sendMessage(
                $currentUser['id'],
                $receiverId,
                $subject,
                $message
            );
            
            // Log activity
            $this->logUserActivity($currentUser['id'], 'message_sent', "Sent message to user ID: {$receiverId}");
            
            $this->json([
                'success' => true,
                'message' => 'Message sent successfully!',
                'message_id' => $messageId
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function reply() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $parentMessageId = $_POST['parent_message_id'] ?? '';
        $message = $this->sanitizeInput($_POST['message'] ?? '');
        
        if (empty($parentMessageId) || empty($message)) {
            $this->json(['success' => false, 'message' => 'Parent message ID and message are required'], 400);
        }
        
        // Get parent message to determine receiver
        $parentMessage = $this->messageModel->getMessageWithParticipants($parentMessageId);
        if (!$parentMessage) {
            $this->json(['success' => false, 'message' => 'Parent message not found'], 404);
        }
        
        // Determine receiver (the other participant)
        $receiverId = ($parentMessage['sender_id'] == $currentUser['id']) ? 
                     $parentMessage['receiver_id'] : $parentMessage['sender_id'];
        
        // Check daily limit
        $features = $this->premiumModel->getUserFeatures($currentUser['id']);
        $dailyLimit = $features['message_limit_per_day'];
        
        if ($dailyLimit !== 'unlimited') {
            if (!$this->messageModel->checkDailyLimit($currentUser['id'], $dailyLimit)) {
                $this->json(['success' => false, 'message' => 'Daily message limit reached. Upgrade to premium for unlimited messaging.'], 429);
            }
        }
        
        try {
            $messageId = $this->messageModel->sendMessage(
                $currentUser['id'],
                $receiverId,
                'Re: ' . $parentMessage['subject'],
                $message,
                false,
                $parentMessageId
            );
            
            // Log activity
            $this->logUserActivity($currentUser['id'], 'message_replied', "Replied to message ID: {$parentMessageId}");
            
            $this->json([
                'success' => true,
                'message' => 'Reply sent successfully!',
                'message_id' => $messageId
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function delete() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $messageId = $_POST['message_id'] ?? '';
        
        if (empty($messageId)) {
            $this->json(['success' => false, 'message' => 'Message ID is required'], 400);
        }
        
        try {
            $success = $this->messageModel->deleteMessage($messageId, $currentUser['id']);
            
            if ($success) {
                // Log activity
                $this->logUserActivity($currentUser['id'], 'message_deleted', "Deleted message ID: {$messageId}");
                
                $this->json([
                    'success' => true,
                    'message' => 'Message deleted successfully!'
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Message not found or access denied'], 404);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function markAsRead() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $messageId = $_POST['message_id'] ?? '';
        
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
    
    public function chat($userId) {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->redirect('/login');
        }
        
        // Load user profile information
        $profileModel = new ProfileModel();
        $userProfile = $profileModel->findByUserId($currentUser['id']);
        $otherUserProfile = $profileModel->getProfileWithUser($userId, $currentUser['id']);
        
        if (!$otherUserProfile) {
            $this->redirectWithMessage('/messages', 'User not found.', 'error');
        }
        
        // Set user profile information in session
        $_SESSION['first_name'] = $userProfile['first_name'] ?? '';
        $_SESSION['last_name'] = $userProfile['last_name'] ?? '';
        
        // Get conversation history between these two users
        $messages = $this->messageModel->getConversationBetweenUsers($currentUser['id'], $userId);
        
        
        $this->layout('main', 'messages/chat', [
            'other_user' => $otherUserProfile,
            'messages' => $messages,
            'current_user_id' => $currentUser['id'],
            'csrf_token' => $this->generateCsrf(),
            'title' => 'Chat with ' . htmlspecialchars($otherUserProfile['first_name']) . ' - Sandawatha.lk',
            'description' => 'Chat with ' . htmlspecialchars($otherUserProfile['first_name']) . ' on Sandawatha.lk',
            'component_css' => [
                'chat/chat',
                'chat/connected-users'
            ]
        ]);
    }
}
?>