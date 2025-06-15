<?php
require_once SITE_ROOT . '/app/helpers/functions.php';
require_once SITE_ROOT . '/app/helpers/CsrfProtection.php';
require_once SITE_ROOT . '/app/helpers/FileUploadValidator.php';

abstract class BaseController {
    
    protected function view($view, $data = []) {
        extract($data);
        
        // Include the view file
        $viewFile = SITE_ROOT . '/app/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View file not found: " . $view);
        }
    }
    
    protected function layout($layout, $view, $data = []) {
        $data['content_view'] = $view;
        
        // Extract data to make variables available in the layout
        extract($data);
        
        // Include the layout file
        $layoutFile = SITE_ROOT . '/app/views/layouts/' . $layout . '.php';
        
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            throw new Exception("Layout file not found: " . $layout);
        }
    }
    
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url, $status = 302) {
        http_response_code($status);
        header('Location: ' . BASE_URL . $url);
        exit;
    }
    
    protected function redirectWithMessage($url, $message, $type = 'success') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
        $this->redirect($url);
    }
    
    protected function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically to prevent session fixation
        if (!isset($_SESSION['last_regeneration']) || 
            (time() - $_SESSION['last_regeneration']) > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    protected function validateCsrf() {
        try {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            return \App\Helpers\CsrfProtection::validateToken($token);
        } catch (Exception $e) {
            error_log("Error in validateCsrf: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    protected function generateCsrf() {
        try {
            return \App\Helpers\CsrfProtection::generateToken();
        } catch (Exception $e) {
            error_log("Error in generateCsrf: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }
    
    protected function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        try {
            require_once SITE_ROOT . '/app/models/UserModel.php';
            $userModel = new UserModel();
            $user = $userModel->find($_SESSION['user_id']);
            
            if (!$user || $user['status'] !== 'active') {
                $this->logout();
                return null;
            }
            
            return $user;
        } catch (Exception $e) {
            error_log("Error in getCurrentUser: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }
    
    protected function logout() {
        // Clear all session data
        $_SESSION = [];
        
        // Delete the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy the session
        session_destroy();
    }
    
    protected function getCurrentUserProfile() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        require_once SITE_ROOT . '/app/models/ProfileModel.php';
        $profileModel = new ProfileModel();
        return $profileModel->findByUserId($_SESSION['user_id']);
    }
    
    protected function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    protected function validatePhone($phone) {
        // Sri Lankan phone number validation
        $pattern = '/^(\+94|0)[0-9]{9}$/';
        return preg_match($pattern, $phone);
    }
    
    protected function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    protected function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    protected function uploadFile($file, $directory, $allowedTypes, $maxSize) {
        $validator = new \App\Helpers\FileUploadValidator($allowedTypes, $maxSize);
        
        if (!$validator->validate($file)) {
            throw new Exception(implode(', ', $validator->getErrors()));
        }
        
        $uploadDir = UPLOAD_PATH . $directory . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        return $directory . '/' . $filename;
    }
    
    protected function deleteFile($filepath) {
        $fullPath = UPLOAD_PATH . $filepath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
    
    protected function logUserActivity($userId = null, $action = null, $details = null) {
        try {
            if (!$userId) {
                $userId = $_SESSION['user_id'] ?? null;
            }
            
            if (!$userId || !$action) {
                return false;
            }
            
            require_once __DIR__ . '/../models/ActivityLogModel.php';
            $activityModel = new ActivityLogModel();
            
            return $activityModel->createActivityLog($userId, $action, $details);
        } catch (Exception $e) {
            error_log("Error in logUserActivity: " . $e->getMessage());
            return false;
        }
    }
    
    protected function sendEmail($to, $subject, $body, $isHtml = true) {
        // Simple email sending function
        // In production, use PHPMailer or similar library
        $headers = [
            'From: ' . FROM_EMAIL,
            'Reply-To: ' . FROM_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        if ($isHtml) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=UTF-8';
        }
        
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
    
    protected function sendSms($phone, $message) {
        // Mock SMS sending
        // In production, integrate with actual SMS service
        return true;
    }
    
    protected function checkRateLimit($key, $limit, $window = 3600) {
        $cacheKey = 'rate_limit_' . $key;
        $current = $_SESSION[$cacheKey] ?? 0;
        
        if ($current >= $limit) {
            return false;
        }
        
        $_SESSION[$cacheKey] = $current + 1;
        return true;
    }
    
    protected function getPremiumFeatures($userId) {
        require_once SITE_ROOT . '/app/models/PremiumModel.php';
        $premiumModel = new PremiumModel();
        
        $membership = $premiumModel->getActiveMembership($userId);
        
        if (!$membership) {
            return PREMIUM_FEATURES['basic'];
        }
        
        return PREMIUM_FEATURES[$membership['plan_type']] ?? PREMIUM_FEATURES['basic'];
    }
    
    protected function requirePremium($feature = null) {
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->redirect('/login');
        }
        
        $features = $this->getPremiumFeatures($user['id']);
        
        if ($feature && !($features[$feature] ?? false)) {
            $this->redirectWithMessage('/premium', 'This feature requires a premium membership.', 'warning');
        }
    }
    
    protected function getUrlParameters() {
        $uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($uri, PHP_URL_PATH);
        $path = trim($path, '/');
        $segments = explode('/', $path);
        
        // Remove the first segment (controller name)
        array_shift($segments);
        
        return $segments;
    }
}
?>