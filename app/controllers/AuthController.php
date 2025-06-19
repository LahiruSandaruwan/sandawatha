<?php

namespace App\controllers;

use App\core\Container;
use App\services\UserService;
use App\models\UserModel;
use App\models\ProfileModel;
use App\helpers\PermissionMiddleware;
use App\helpers\SocialAuth;

class AuthController extends BaseController {
    private $userService;
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userService = $this->container->make(UserService::class);
        $this->userModel = $this->container->make(UserModel::class);
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
        
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($email) || empty($password)) {
            $this->redirectWithMessage('/login', 'Please fill in all fields.', 'error');
        }
        
        if (!$this->validateEmail($email)) {
            $this->redirectWithMessage('/login', 'Invalid email format.', 'error');
        }
        
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !$this->verifyPassword($password, $user['password'])) {
            $this->redirectWithMessage('/login', 'Invalid email or password.', 'error');
        }
        
        if ($user['status'] === 'blocked') {
            $this->redirectWithMessage('/login', 'Your account has been blocked. Please contact support.', 'error');
        }
        
        if (!$user['email_verified']) {
            $this->redirectWithMessage('/verify-email', 'Please verify your email address first.', 'warning');
        }
        
        // Get user profile information
        $profileModel = new ProfileModel();
        $profile = $profileModel->findByUserId($user['id']);
        
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
        $this->userModel->logLogin(
            $user['id'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        );
        
        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (86400 * 30); // 30 days
            setcookie('remember_token', $token, $expires, '/', '', true, true); // Secure and HttpOnly
        }
        
        // Update user status to active if pending
        if ($user['status'] === 'pending') {
            $this->userModel->updateStatus($user['id'], 'active');
        }
        
        // Check if user is admin using PermissionMiddleware
        if (PermissionMiddleware::isAdmin($user['id'])) {
            $_SESSION['user_role'] = 'admin';
            $this->redirectWithMessage('/admin', 'Welcome back, Admin!', 'success');
        } else {
            $_SESSION['user_role'] = 'user';
            $this->redirectWithMessage('/dashboard', 'Welcome back!', 'success');
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
    
    // ===== SOCIAL LOGIN METHODS =====
    
    /**
     * Redirect to Google OAuth
     */
    public function googleLogin() {
        try {
            $socialAuth = new SocialAuth();
            $authUrl = $socialAuth->getGoogleAuthUrl();
            header('Location: ' . $authUrl);
            exit;
        } catch (Exception $e) {
            $this->redirectWithMessage('/login', 'Google login is not available: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Handle Google OAuth callback
     */
    public function googleCallback() {
        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';
        
        if (empty($code)) {
            $this->redirectWithMessage('/login', 'Google login was cancelled or failed.', 'error');
        }
        
        try {
            $socialAuth = new SocialAuth();
            $user = $socialAuth->handleGoogleCallback($code, $state);
            
            if ($user) {
                $this->loginSocialUser($user);
            } else {
                $this->redirectWithMessage('/login', 'Failed to login with Google.', 'error');
            }
        } catch (Exception $e) {
            $this->redirectWithMessage('/login', 'Google login failed: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Redirect to Facebook OAuth
     */
    public function facebookLogin() {
        try {
            $socialAuth = new SocialAuth();
            $authUrl = $socialAuth->getFacebookAuthUrl();
            header('Location: ' . $authUrl);
            exit;
        } catch (Exception $e) {
            $this->redirectWithMessage('/login', 'Facebook login is not available: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Handle Facebook OAuth callback
     */
    public function facebookCallback() {
        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';
        
        if (empty($code)) {
            $this->redirectWithMessage('/login', 'Facebook login was cancelled or failed.', 'error');
        }
        
        try {
            $socialAuth = new SocialAuth();
            $user = $socialAuth->handleFacebookCallback($code, $state);
            
            if ($user) {
                $this->loginSocialUser($user);
            } else {
                $this->redirectWithMessage('/login', 'Failed to login with Facebook.', 'error');
            }
        } catch (Exception $e) {
            $this->redirectWithMessage('/login', 'Facebook login failed: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Login user from social authentication
     */
    private function loginSocialUser($user) {
        if ($user['status'] === 'blocked') {
            $this->redirectWithMessage('/login', 'Your account has been blocked. Please contact support.', 'error');
        }
        
        // Get user profile information
        $profileModel = new ProfileModel();
        $profile = $profileModel->findByUserId($user['id']);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['dark_mode'] = $user['dark_mode'] ?? 0;
        $_SESSION['user_name'] = $profile ? trim($profile['first_name'] . ' ' . $profile['last_name']) : null;
        $_SESSION['first_name'] = $profile ? $profile['first_name'] : '';
        $_SESSION['last_name'] = $profile ? $profile['last_name'] : '';
        
        // Log the login
        $this->userModel->logLogin(
            $user['id'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        );
        
        // Check if user is admin using PermissionMiddleware
        if (PermissionMiddleware::isAdmin($user['id'])) {
            $_SESSION['user_role'] = 'admin';
            $this->redirectWithMessage('/admin', 'Welcome back, Admin!', 'success');
        } else {
            $_SESSION['user_role'] = 'user';
            $this->redirectWithMessage('/dashboard', 'Welcome back!', 'success');
        }
    }
}
?>