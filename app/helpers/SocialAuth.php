<?php

namespace App\helpers;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Facebook;
use App\models\UserModel;
use App\models\ProfileModel;
use Exception;

class SocialAuth {
    
    private static $googleProvider;
    private static $facebookProvider;
    private static $userModel;
    private static $profileModel;
    
    public function __construct() {
        self::$userModel = new UserModel();
        self::$profileModel = new ProfileModel();
        
        // Load environment variables if .env file exists
        self::loadEnvVariables();
        
        // Initialize Google OAuth provider
        self::$googleProvider = new Google([
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
            'redirectUri'  => BASE_URL . '/auth/google/callback',
        ]);
        
        // Initialize Facebook OAuth provider (optional - keeping for future use)
        self::$facebookProvider = new Facebook([
            'clientId'     => $_ENV['FACEBOOK_APP_ID'] ?? '',
            'clientSecret' => $_ENV['FACEBOOK_APP_SECRET'] ?? '',
            'redirectUri'  => BASE_URL . '/auth/facebook/callback',
            'graphApiVersion' => 'v18.0',
        ]);
    }
    
    /**
     * Get list of available social login providers
     */
    public static function getAvailableProviders() {
        $providers = [];
        
        // Check Google configuration
        if (self::isConfigured('google')) {
            $providers[] = 'google';
        }
        
        // Check Facebook configuration
        if (self::isConfigured('facebook')) {
            $providers[] = 'facebook';
        }
        
        return $providers;
    }
    
    /**
     * Check if a provider is configured
     */
    public static function isConfigured($provider = null) {
        // Load environment variables if needed
        if (!isset($_ENV['GOOGLE_CLIENT_ID'])) {
            self::loadEnvVariables();
        }
        
        switch ($provider) {
            case 'google':
                return !empty($_ENV['GOOGLE_CLIENT_ID']) && !empty($_ENV['GOOGLE_CLIENT_SECRET']);
            case 'facebook':
                return !empty($_ENV['FACEBOOK_APP_ID']) && !empty($_ENV['FACEBOOK_APP_SECRET']);
            default:
                return false;
        }
    }
    
    /**
     * Load environment variables from .env file
     */
    private static function loadEnvVariables() {
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
        
        $authUrl = self::$googleProvider->getAuthorizationUrl($options);
        $_SESSION['oauth2state'] = self::$googleProvider->getState();
        
        return $authUrl;
    }
    
    /**
     * Get Facebook OAuth authorization URL
     */
    public function getFacebookAuthUrl() {
        $options = [
            'scope' => ['email', 'public_profile']
        ];
        
        $authUrl = self::$facebookProvider->getAuthorizationUrl($options);
        $_SESSION['oauth2state'] = self::$facebookProvider->getState();
        
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
            $token = self::$googleProvider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            
            // Get user details
            $user = self::$googleProvider->getResourceOwner($token);
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
            $token = self::$facebookProvider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            
            // Get user details
            $user = self::$facebookProvider->getResourceOwner($token);
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
        $existingUser = self::$userModel->findByEmail($email);
        
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
            $userId = self::$userModel->createSocialUser(
                $email,
                $randomPassword,
                $provider,
                $userData['id'] ?? null
            );
            
            if ($userId) {
                // Create profile
                self::$profileModel->createFromSocialData($userId, [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'profile_picture_url' => $userData['picture'] ?? null,
                    'social_provider' => $provider
                ]);
                
                return self::$userModel->find($userId);
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
        self::$userModel->updateSocialProvider($userId, $provider, $userData['id'] ?? null);
        
        // Update profile picture if available and not already set
        if (isset($userData['picture'])) {
            $profile = self::$profileModel->findByUserId($userId);
            if ($profile && empty($profile['profile_picture'])) {
                self::$profileModel->updateProfilePicture($userId, $userData['picture']);
            }
        }
    }
} 