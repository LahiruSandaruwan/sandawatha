<?php
require_once 'BaseController.php';
require_once SITE_ROOT . '/app/models/ContactRequestModel.php';
require_once SITE_ROOT . '/app/models/ProfileModel.php';
require_once SITE_ROOT . '/app/models/PremiumModel.php';

class ContactController extends BaseController {
    private $contactModel;
    private $profileModel;
    private $premiumModel;
    
    public function __construct() {
        $this->contactModel = new ContactRequestModel();
        $this->profileModel = new ProfileModel();
        $this->premiumModel = new PremiumModel();
    }
    
    public function send() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $receiverId = $_POST['profile_id'] ?? '';
        $message = $this->sanitizeInput($_POST['message'] ?? '');
        
        if (empty($receiverId)) {
            $this->json(['success' => false, 'message' => 'Profile ID is required'], 400);
        }
        
        // Check if user is trying to send request to themselves
        if ($currentUser['id'] == $receiverId) {
            $this->json(['success' => false, 'message' => 'Cannot send request to yourself'], 400);
        }
        
        // Check daily limit based on premium status
        $features = $this->premiumModel->getUserFeatures($currentUser['id']);
        $dailyLimit = $features['contact_requests_per_day'];
        
        if ($dailyLimit !== 'unlimited') {
            if (!$this->contactModel->checkDailyLimit($currentUser['id'], $dailyLimit)) {
                $this->json(['success' => false, 'message' => 'Daily contact request limit reached. Upgrade to premium for more requests.'], 429);
            }
        }
        
        try {
            $requestId = $this->contactModel->sendRequest($currentUser['id'], $receiverId, $message);
            
            // Log activity
            $this->logUserActivity($currentUser['id'], 'contact_request_sent', "Sent contact request to user ID: {$receiverId}");
            
            $this->json([
                'success' => true,
                'message' => 'Contact request sent successfully!',
                'request_id' => $requestId
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function respond() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
            return;
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
            return;
        }
        
        $requestId = $_POST['request_id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        if (empty($requestId) || empty($status)) {
            $this->json(['success' => false, 'message' => 'Request ID and status are required'], 400);
            return;
        }
        
        if (!in_array($status, ['accepted', 'rejected'])) {
            $this->json(['success' => false, 'message' => 'Invalid status'], 400);
            return;
        }
        
        try {
            $request = $this->contactModel->find($requestId);
            if (!$request) {
                $this->json(['success' => false, 'message' => 'Contact request not found'], 404);
                return;
            }
            
            if ($request['receiver_id'] !== $currentUser['id']) {
                $this->json(['success' => false, 'message' => 'You are not authorized to respond to this request'], 403);
                return;
            }
            
            if ($request['status'] !== 'pending') {
                $this->json(['success' => false, 'message' => 'This request has already been ' . $request['status']], 400);
                return;
            }
            
            $success = $this->contactModel->respondToRequest($requestId, $currentUser['id'], $status);
            
            if ($success) {
                // Log activity
                $this->logUserActivity($currentUser['id'], 'contact_request_responded', "Responded to contact request ID: {$requestId} with status: {$status}");
                
                $this->json([
                    'success' => true,
                    'message' => "Contact request {$status} successfully!",
                    'status' => $status
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to respond to contact request'], 400);
            }
        } catch (Exception $e) {
            error_log("Error in ContactController::respond: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->json(['success' => false, 'message' => 'An error occurred while processing your request'], 500);
        }
    }
    
    public function list() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->redirect('/login');
        }
        
        $tab = $_GET['tab'] ?? 'received';
        $page = $_GET['page'] ?? 1;
        
        $receivedRequests = [];
        $sentRequests = [];
        $acceptedContacts = [];
        
        if ($tab === 'received' || $tab === 'all') {
            $receivedRequests = $this->contactModel->getReceivedRequests($currentUser['id'], $page, 20);
        }
        
        if ($tab === 'sent' || $tab === 'all') {
            $sentRequests = $this->contactModel->getSentRequests($currentUser['id'], $page, 20);
        }
        
        if ($tab === 'contacts' || $tab === 'all') {
            $acceptedContacts = $this->contactModel->getAcceptedContacts($currentUser['id']);
        }
        
        $stats = $this->contactModel->getRequestStats($currentUser['id']);
        
        $data = [
            'title' => 'Contact Requests - Sandawatha.lk',
            'received_requests' => $receivedRequests,
            'sent_requests' => $sentRequests,
            'accepted_contacts' => $acceptedContacts,
            'stats' => $stats,
            'current_tab' => $tab,
            'current_page' => $page,
            'csrf_token' => $this->generateCsrf(),
            'scripts' => ['contact-requests']
        ];
        
        $this->layout('main', 'contacts/list', $data);
    }
}
?>