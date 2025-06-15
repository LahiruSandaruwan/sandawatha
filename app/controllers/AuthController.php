<?php
require_once 'BaseController.php';
require_once SITE_ROOT . '/app/core/Container.php';

use App\Core\Container;
use App\Services\UserService;

class AuthController extends BaseController {
    private $userService;
    
    public function __construct() {
        parent::__construct();
        $this->userService = Container::make(UserService::class);
    }
    
    public function loginForm() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }
        
        $data = [
            'title' => 'Login - Sandawatha.lk',
            'csrf_token' => $this->generateCsrf()
        ];
        
        $this->layout('main', 'auth/login', $data);
    }
    
    public function login() {
        if (!$this->validateCsrf()) {
            $this->redirectWithMessage('/login', 'Invalid security token.', 'error');
        }
        
        try {
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            $result = $this->userService->authenticate($email, $password, $_SERVER['REMOTE_ADDR']);
            $user = $result['user'];
            $profile = $result['profile'];
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['dark_mode'] = $user['dark_mode'];
            $_SESSION['user_name'] = $profile ? trim($profile['first_name'] . ' ' . $profile['last_name']) : null;
            $_SESSION['first_name'] = $profile ? $profile['first_name'] : '';
            $_SESSION['last_name'] = $profile ? $profile['last_name'] : '';
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Log the login
            $this->userService->logLogin(
                $user['id'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            );
            
            // Set remember me cookie if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (86400 * 30); // 30 days
                setcookie('remember_token', $token, $expires, '/', '', true, true); // Secure and HttpOnly
                $this->userService->storeRememberToken($user['id'], $token, $expires);
            }
            
            // Update user status to active if pending
            if ($user['status'] === 'pending') {
                $this->userService->updateStatus($user['id'], 'active');
            }
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                $this->redirectWithMessage('/admin', 'Welcome back, Admin!', 'success');
            } else {
                $this->redirectWithMessage('/dashboard', 'Welcome back!', 'success');
            }
        } catch (\Exception $e) {
            $this->redirectWithMessage('/login', $e->getMessage(), 'error');
        }
    }
    
    public function registerForm() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }
        
        $data = [
            'title' => 'Register - Sandawatha.lk',
            'csrf_token' => $this->generateCsrf()
        ];
        
        $this->layout('main', 'auth/register', $data);
    }
    
    public function register() {
        if (!$this->validateCsrf()) {
            $this->redirectWithMessage('/register', 'Invalid security token.', 'error');
        }
        
        try {
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $phone = $this->sanitizeInput($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $terms = isset($_POST['terms']);
            
            // Validation
            if (empty($email) || empty($phone) || empty($password) || empty($confirmPassword)) {
                throw new \Exception('Please fill in all fields.');
            }
            
            if (!$this->validateEmail($email)) {
                throw new \Exception('Invalid email format.');
            }
            
            if (!$this->validatePhone($phone)) {
                throw new \Exception('Invalid phone number format. Use +94XXXXXXXXX or 0XXXXXXXXX');
            }
            
            if (strlen($password) < 8) {
                throw new \Exception('Password must be at least 8 characters long.');
            }
            
            if ($password !== $confirmPassword) {
                throw new \Exception('Passwords do not match.');
            }
            
            if (!$terms) {
                throw new \Exception('You must accept the terms and conditions.');
            }
            
            $userId = $this->userService->register($email, $phone, $password);
            
            // Send verification email
            $user = $this->userService->find($userId);
            $this->sendVerificationEmail($user);
            
            $_SESSION['temp_user_id'] = $userId;
            $this->redirectWithMessage('/verify-email', 'Account created successfully! Please check your email to verify your account.', 'success');
        } catch (\Exception $e) {
            $this->redirectWithMessage('/register', $e->getMessage(), 'error');
        }
    }
    
    public function verifyEmail() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $data = [
                'title' => 'Verify Email - Sandawatha.lk',
                'message' => 'Please check your email for the verification link.'
            ];
            $this->layout('main', 'auth/verify-email', $data);
            return;
        }
        
        if ($this->userService->verifyEmail($token)) {
            $this->redirectWithMessage('/verify-phone', 'Email verified successfully! Now please verify your phone number.', 'success');
        } else {
            $this->redirectWithMessage('/register', 'Invalid or expired verification token.', 'error');
        }
    }
    
    public function verifyPhoneForm() {
        if (!isset($_SESSION['temp_user_id'])) {
            $this->redirect('/register');
        }
        
        $data = [
            'title' => 'Verify Phone - Sandawatha.lk',
            'csrf_token' => $this->generateCsrf()
        ];
        
        $this->layout('main', 'auth/verify-phone', $data);
    }
    
    public function verifyPhone() {
        if (!$this->validateCsrf() || !isset($_SESSION['temp_user_id'])) {
            $this->redirectWithMessage('/register', 'Invalid request.', 'error');
        }
        
        $code = $this->sanitizeInput($_POST['code'] ?? '');
        
        if (empty($code)) {
            $this->redirectWithMessage('/verify-phone', 'Please enter the verification code.', 'error');
        }
        
        if ($this->userService->verifyPhone($_SESSION['temp_user_id'], $code)) {
            unset($_SESSION['temp_user_id']);
            $this->redirectWithMessage('/login', 'Phone verified successfully! You can now login.', 'success');
        } else {
            $this->redirectWithMessage('/verify-phone', 'Invalid verification code.', 'error');
        }
    }
    
    public function forgotPasswordForm() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }
        
        $data = [
            'title' => 'Forgot Password - Sandawatha.lk',
            'csrf_token' => $this->generateCsrf()
        ];
        
        $this->layout('main', 'auth/forgot-password', $data);
    }
    
    public function forgotPassword() {
        if (!$this->validateCsrf()) {
            $this->redirectWithMessage('/forgot-password', 'Invalid security token.', 'error');
        }
        
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        
        if (empty($email) || !$this->validateEmail($email)) {
            $this->redirectWithMessage('/forgot-password', 'Please enter a valid email address.', 'error');
        }
        
        $user = $this->userService->findByEmail($email);
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $this->userService->setResetToken($email, $token, $expires);
            $this->sendPasswordResetEmail($user, $token);
        }
        
        // Always show success message for security
        $this->redirectWithMessage('/forgot-password', 'If an account with that email exists, a password reset link has been sent.', 'success');
    }
    
    public function resetPasswordForm() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $this->redirectWithMessage('/forgot-password', 'Invalid reset token.', 'error');
        }
        
        $data = [
            'title' => 'Reset Password - Sandawatha.lk',
            'token' => $token,
            'csrf_token' => $this->generateCsrf()
        ];
        
        $this->layout('main', 'auth/reset-password', $data);
    }
    
    public function resetPassword() {
        if (!$this->validateCsrf()) {
            $this->redirectWithMessage('/forgot-password', 'Invalid security token.', 'error');
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $this->redirectWithMessage('/forgot-password', 'Missing required fields.', 'error');
        }
        
        if (strlen($password) < 8) {
            $this->redirectWithMessage("/reset-password?token={$token}", 'Password must be at least 8 characters long.', 'error');
        }
        
        if ($password !== $confirmPassword) {
            $this->redirectWithMessage("/reset-password?token={$token}", 'Passwords do not match.', 'error');
        }
        
        if ($this->userService->resetPassword($token, $password)) {
            $this->redirectWithMessage('/login', 'Password reset successfully! You can now login.', 'success');
        } else {
            $this->redirectWithMessage('/forgot-password', 'Invalid or expired reset token.', 'error');
        }
    }
    
    public function logout() {
        session_destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        $this->redirectWithMessage('/', 'You have been logged out successfully.', 'success');
    }
    
    private function sendVerificationEmail($user) {
        $verificationUrl = BASE_URL . '/verify-email?token=' . $user['email_verification_token'];
        
        $subject = 'Verify Your Email - Sandawatha.lk';
        $body = "
            <h2>Welcome to Sandawatha.lk!</h2>
            <p>Thank you for registering with us. Please click the link below to verify your email address:</p>
            <p><a href='{$verificationUrl}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Email</a></p>
            <p>If the button doesn't work, copy and paste this link into your browser:</p>
            <p>{$verificationUrl}</p>
            <p>This link will expire in 24 hours.</p>
            <p>Best regards,<br>Sandawatha.lk Team</p>
        ";
        
        return $this->sendEmail($user['email'], $subject, $body);
    }
    
    private function sendPasswordResetEmail($user, $token) {
        $resetUrl = BASE_URL . '/reset-password?token=' . $token;
        
        $subject = 'Reset Your Password - Sandawatha.lk';
        $body = "
            <h2>Password Reset Request</h2>
            <p>You have requested to reset your password. Click the link below to proceed:</p>
            <p><a href='{$resetUrl}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
            <p>If the button doesn't work, copy and paste this link into your browser:</p>
            <p>{$resetUrl}</p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
            <p>Best regards,<br>Sandawatha.lk Team</p>
        ";
        
        return $this->sendEmail($user['email'], $subject, $body);
    }
}
?>