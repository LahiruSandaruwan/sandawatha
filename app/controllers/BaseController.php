<?php
abstract class BaseController {
    
    protected function view($view, $data = []) {
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = SITE_ROOT . '/app/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View file not found: " . $view);
        }
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }
    
    protected function layout($layout, $view, $data = []) {
        $data['content_view'] = $view;
        $this->view('layouts/' . $layout, $data);
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
    
    protected function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        require_once SITE_ROOT . '/app/models/UserModel.php';
        $userModel = new UserModel();
        return $userModel->find($_SESSION['user_id']);
    }
    
    protected function getCurrentUserProfile() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        require_once SITE_ROOT . '/app/models/ProfileModel.php';
        $profileModel = new ProfileModel();
        return $profileModel->findByUserId($_SESSION['user_id']);
    }
    
    protected function validateCsrf() {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
    
    protected function generateCsrf() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
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
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error');
        }
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('File size too large');
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
    
    protected function logUserActivity($userId, $action, $details = null) {
        require_once SITE_ROOT . '/app/models/ActivityLogModel.php';
        $activityModel = new ActivityLogModel();
        
        $activityModel->create([
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
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
}
?>