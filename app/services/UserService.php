<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\ProfileModel;
use App\Helpers\RateLimiter;
use App\Helpers\CsrfProtection;

class UserService {
    private $userModel;
    private $profileModel;
    private $rateLimiter;
    
    public function __construct(
        UserModel $userModel,
        ProfileModel $profileModel,
        RateLimiter $rateLimiter
    ) {
        $this->userModel = $userModel;
        $this->profileModel = $profileModel;
        $this->rateLimiter = $rateLimiter;
    }
    
    public function authenticate($email, $password, $ip) {
        $key = 'login_attempts_' . $ip;
        
        if ($this->rateLimiter->tooManyAttempts($key)) {
            $waitTime = $this->rateLimiter->availableIn($key);
            throw new \Exception("Too many login attempts. Please try again in {$waitTime} seconds.");
        }
        
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            $this->rateLimiter->hit($key);
            throw new \Exception('Invalid email or password.');
        }
        
        if ($user['status'] === 'blocked') {
            $this->rateLimiter->hit($key);
            throw new \Exception('Your account has been blocked. Please contact support.');
        }
        
        if (!$user['email_verified']) {
            $this->rateLimiter->hit($key);
            throw new \Exception('Please verify your email address first.');
        }
        
        $this->rateLimiter->resetAttempts($key);
        
        $profile = $this->profileModel->findByUserId($user['id']);
        
        return [
            'user' => $user,
            'profile' => $profile
        ];
    }
    
    public function register($email, $phone, $password) {
        if ($this->userModel->findByEmail($email)) {
            throw new \Exception('An account with this email already exists.');
        }
        
        if ($this->userModel->findByPhone($phone)) {
            throw new \Exception('An account with this phone number already exists.');
        }
        
        $userId = $this->userModel->createUser($email, $phone, $password);
        
        if (!$userId) {
            throw new \Exception('Registration failed. Please try again.');
        }
        
        return $userId;
    }
    
    public function updateProfile($userId, $data) {
        $profile = $this->profileModel->findByUserId($userId);
        
        if ($profile) {
            return $this->profileModel->update($profile['id'], $data);
        }
        
        return $this->profileModel->create($userId, $data);
    }
    
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->userModel->find($userId);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            throw new \Exception('Current password is incorrect.');
        }
        
        return $this->userModel->updatePassword($userId, $newPassword);
    }
    
    public function requestPasswordReset($email) {
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            throw new \Exception('No account found with this email address.');
        }
        
        $token = bin2hex(random_bytes(32));
        $expires = time() + (3600 * 24); // 24 hours
        
        if (!$this->userModel->storePasswordResetToken($user['id'], $token, $expires)) {
            throw new \Exception('Failed to generate password reset token.');
        }
        
        return [
            'user' => $user,
            'token' => $token
        ];
    }
    
    public function resetPassword($token, $newPassword) {
        $user = $this->userModel->findByPasswordResetToken($token);
        
        if (!$user) {
            throw new \Exception('Invalid or expired password reset token.');
        }
        
        if ($user['password_reset_expires'] < time()) {
            throw new \Exception('Password reset token has expired.');
        }
        
        if (!$this->userModel->updatePassword($user['id'], $newPassword)) {
            throw new \Exception('Failed to update password.');
        }
        
        $this->userModel->clearPasswordResetToken($user['id']);
        
        return true;
    }
    
    public function verifyEmail($token) {
        return $this->userModel->verifyEmail($token);
    }
    
    public function verifyPhone($userId, $code) {
        return $this->userModel->verifyPhone($userId, $code);
    }
    
    public function logLogin($userId, $ip, $userAgent) {
        return $this->userModel->logLogin($userId, $ip, $userAgent);
    }
    
    public function storeRememberToken($userId, $token, $expires) {
        return $this->userModel->storeRememberToken($userId, $token, $expires);
    }
} 