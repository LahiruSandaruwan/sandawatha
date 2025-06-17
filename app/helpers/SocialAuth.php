<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ProfileModel.php';

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Facebook;

class SocialAuth {
    
    private $googleProvider;
    private $facebookProvider;
    private $userModel;
    private $profileModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
        $this->profileModel = new ProfileModel();
        
        // Load environment variables if .env file exists
        $this->loadEnvVariables();
        
        // Initialize Google OAuth provider
        $this->googleProvider = new Google([
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
            'redirectUri'  => BASE_URL . '/auth/google/callback',
        ]);
        
        // Initialize Facebook OAuth provider (optional - keeping for future use)
        $this->facebookProvider = new Facebook([
            'clientId'     => $_ENV['FACEBOOK_APP_ID'] ?? '',
            'clientSecret' => $_ENV['FACEBOOK_APP_SECRET'] ?? '',
            'redirectUri'  => BASE_URL . '/auth/facebook/callback',
            'graphApiVersion' => 'v18.0',
        ]);
    }
    
    /**
     * Load environment variables from .env file
     */
    private function loadEnvVariables() {
        $envFile = SITE_ROOT . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        } else {
            // Fallback: Set Google credentials directly for testing
            $_ENV['GOOGLE_CLIENT_ID'] = '345740611184-p044aq3dv421cupbnujeh5ldg4kmuj.apps.googleusercontent.com';
            $_ENV['GOOGLE_CLIENT_SECRET'] = 'GQCSPX-IVI7Mhv9Aq-mmt3HyFQSB5_uRUk6';
        }
    }
    
    /**
     * Get Google OAuth authorization URL
     */
    public function getGoogleAuthUrl() {
        $options = [
            'scope' => ['openid', 'email', 'profile']
        ];
        
        $authUrl = $this->googleProvider->getAuthorizationUrl($options);
        $_SESSION['oauth2state'] = $this->googleProvider->getState();
        
        return $authUrl;
    }
    
    /**
     * Get Facebook OAuth authorization URL
     */
    public function getFacebookAuthUrl() {
        $options = [
            'scope' => ['email', 'public_profile']
        ];
        
        $authUrl = $this->facebookProvider->getAuthorizationUrl($options);
        $_SESSION['oauth2state'] = $this->facebookProvider->getState();
        
        return $authUrl;
    }
    
    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback($code, $state) {
        // Check state to prevent CSRF attacks
        if (empty($state) || (isset($_SESSION['oauth2state']) && $state !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            throw new Exception('Invalid state parameter');
        }
        
        try {
            // Get access token
            $token = $this->googleProvider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            
            // Get user details
            $user = $this->googleProvider->getResourceOwner($token);
            $userData = $user->toArray();
            
            return $this->processUserData($userData, 'google');
            
        } catch (Exception $e) {
            throw new Exception('Failed to authenticate with Google: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback($code, $state) {
        // Check state to prevent CSRF attacks
        if (empty($state) || (isset($_SESSION['oauth2state']) && $state !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            throw new Exception('Invalid state parameter');
        }
        
        try {
            // Get access token
            $token = $this->facebookProvider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            
            // Get user details
            $user = $this->facebookProvider->getResourceOwner($token);
            $userData = $user->toArray();
            
            return $this->processUserData($userData, 'facebook');
            
        } catch (Exception $e) {
            throw new Exception('Failed to authenticate with Facebook: ' . $e->getMessage());
        }
    }
    
    /**
     * Process user data from social provider
     */
    private function processUserData($userData, $provider) {
        $email = $userData['email'] ?? null;
        
        if (!$email) {
            throw new Exception('Email not provided by ' . ucfirst($provider));
        }
        
        // Check if user already exists
        $existingUser = $this->userModel->findByEmail($email);
        
        if ($existingUser) {
            // User exists, update social login info if needed
            $this->updateSocialLoginInfo($existingUser['id'], $provider, $userData);
            return $existingUser;
        } else {
            // Create new user
            return $this->createSocialUser($userData, $provider);
        }
    }
    
    /**
     * Create new user from social login
     */
    private function createSocialUser($userData, $provider) {
        $email = $userData['email'];
        $firstName = '';
        $lastName = '';
        
        // Extract name information based on provider
        if ($provider === 'google') {
            $firstName = $userData['given_name'] ?? '';
            $lastName = $userData['family_name'] ?? '';
        } elseif ($provider === 'facebook') {
            $firstName = $userData['first_name'] ?? '';
            $lastName = $userData['last_name'] ?? '';
        }
        
        // Generate a random password for social users
        $randomPassword = bin2hex(random_bytes(16));
        
        try {
            // Create user account
            $userId = $this->userModel->createSocialUser(
                $email,
                $randomPassword,
                $provider,
                $userData['id'] ?? null
            );
            
            if ($userId) {
                // Create profile
                $this->profileModel->createFromSocialData($userId, [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'profile_picture_url' => $userData['picture'] ?? null,
                    'social_provider' => $provider
                ]);
                
                return $this->userModel->find($userId);
            }
            
            throw new Exception('Failed to create user account');
            
        } catch (Exception $e) {
            throw new Exception('Registration failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Update social login information for existing user
     */
    private function updateSocialLoginInfo($userId, $provider, $userData) {
        // Update social provider info in user table
        $this->userModel->updateSocialProvider($userId, $provider, $userData['id'] ?? null);
        
        // Update profile picture if available and not already set
        if (isset($userData['picture'])) {
            $profile = $this->profileModel->findByUserId($userId);
            if ($profile && empty($profile['profile_picture'])) {
                $this->profileModel->updateProfilePicture($userId, $userData['picture']);
            }
        }
    }
    
    /**
     * Check if social login is configured
     */
    public static function isConfigured($provider = null) {
        if ($provider === 'google') {
            return !empty($_ENV['GOOGLE_CLIENT_ID']) && !empty($_ENV['GOOGLE_CLIENT_SECRET']);
        } elseif ($provider === 'facebook') {
            return !empty($_ENV['FACEBOOK_APP_ID']) && !empty($_ENV['FACEBOOK_APP_SECRET']);
        }
        
        return self::isConfigured('google') || self::isConfigured('facebook');
    }
    
    /**
     * Get available social providers
     */
    public static function getAvailableProviders() {
        $providers = [];
        
        if (self::isConfigured('google')) {
            $providers[] = 'google';
        }
        
        if (self::isConfigured('facebook')) {
            $providers[] = 'facebook';
        }
        
        return $providers;
    }
} 