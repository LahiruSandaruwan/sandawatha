<?php
require_once 'BaseController.php';
require_once SITE_ROOT . '/app/models/FeedbackModel.php';

class FeedbackController extends BaseController {
    private $feedbackModel;
    
    public function __construct() {
        $this->feedbackModel = new FeedbackModel();
    }
    
    public function form() {
        $data = [
            'title' => 'Feedback - Sandawatha.lk',
            'csrf_token' => $this->generateCsrf(),
            'scripts' => ['feedback']
        ];
        
        $this->layout('main', 'feedback/form', $data);
    }
    
    public function submit() {
        if (!$this->validateCsrf()) {
            $this->redirectWithMessage('/feedback', 'Invalid security token.', 'error');
        }
        
        $currentUser = $this->getCurrentUser();
        
        $data = [
            'user_id' => $currentUser['id'] ?? null,
            'name' => $this->sanitizeInput($_POST['name'] ?? ''),
            'email' => $this->sanitizeInput($_POST['email'] ?? ''),
            'rating' => (int)($_POST['rating'] ?? 0),
            'subject' => $this->sanitizeInput($_POST['subject'] ?? ''),
            'message' => $this->sanitizeInput($_POST['message'] ?? ''),
            'is_public' => isset($_POST['is_public'])
        ];
        
        // Validation
        if (empty($data['name']) || empty($data['email']) || empty($data['subject']) || empty($data['message'])) {
            $this->redirectWithMessage('/feedback', 'Please fill in all required fields.', 'error');
        }
        
        if (!$this->validateEmail($data['email'])) {
            $this->redirectWithMessage('/feedback', 'Please enter a valid email address.', 'error');
        }
        
        if ($data['rating'] < 1 || $data['rating'] > 5) {
            $this->redirectWithMessage('/feedback', 'Please select a valid rating.', 'error');
        }
        
        try {
            $feedbackId = $this->feedbackModel->submitFeedback($data);
            
            if ($feedbackId) {
                // Log activity if user is logged in
                if ($currentUser) {
                    $this->logUserActivity($currentUser['id'], 'feedback_submitted', "Submitted feedback with rating: {$data['rating']}");
                }
                
                $this->redirectWithMessage('/feedback', 'Thank you for your feedback! We appreciate your input.', 'success');
            } else {
                $this->redirectWithMessage('/feedback', 'Failed to submit feedback. Please try again.', 'error');
            }
        } catch (Exception $e) {
            $this->redirectWithMessage('/feedback', 'Error submitting feedback: ' . $e->getMessage(), 'error');
        }
    }
}
?>