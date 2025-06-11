<?php
// Dynamically detect base URL and site root (built-in server or Apache)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($scriptDir === '/' || $scriptDir === '\\') {
    $scriptDir = '';
}
define('BASE_URL', $protocol . '://' . $host . $scriptDir);
define('SITE_ROOT', realpath(__DIR__ . '/../'));
define('UPLOAD_PATH', SITE_ROOT . '/public/uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_PORT', 587);
define('FROM_EMAIL', 'noreply@sandawatha.lk');
define('FROM_NAME', 'Sandawatha.lk');

// SMS configuration (for phone verification)
define('SMS_API_KEY', 'your-sms-api-key');
define('SMS_API_URL', 'https://api.textit.lk/send-sms');

// Security settings
define('JWT_SECRET_KEY', 'your-super-secret-jwt-key-change-this');
define('PASSWORD_SALT', 'your-password-salt-change-this');
define('SESSION_LIFETIME', 3600 * 24 * 30); // 30 days

// File upload settings
define('MAX_PROFILE_PHOTO_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_VIDEO_SIZE', 50 * 1024 * 1024); // 50MB
define('MAX_VOICE_SIZE', 10 * 1024 * 1024); // 10MB
define('MAX_DOCUMENT_SIZE', 5 * 1024 * 1024); // 5MB

define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm']);
define('ALLOWED_AUDIO_TYPES', ['audio/mp3', 'audio/wav', 'audio/mpeg']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'image/jpeg', 'image/png']);

// AI and matching settings
define('AI_API_KEY', 'your-ai-api-key');
define('HOROSCOPE_API_KEY', 'your-horoscope-api-key');
define('COMPATIBILITY_THRESHOLD', 70);

// Premium membership features
define('PREMIUM_FEATURES', [
    'basic' => [
        'contact_requests_per_day' => 3,
        'profile_views_per_day' => 20,
        'message_limit_per_day' => 5,
        'can_see_who_viewed' => false,
        'priority_listing' => false,
        'ai_matching' => false
    ],
    'premium' => [
        'contact_requests_per_day' => 10,
        'profile_views_per_day' => 100,
        'message_limit_per_day' => 25,
        'can_see_who_viewed' => true,
        'priority_listing' => true,
        'ai_matching' => true
    ],
    'platinum' => [
        'contact_requests_per_day' => 'unlimited',
        'profile_views_per_day' => 'unlimited',
        'message_limit_per_day' => 'unlimited',
        'can_see_who_viewed' => true,
        'priority_listing' => true,
        'ai_matching' => true
    ]
]);

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Error logging
ini_set('log_errors', 1);
ini_set('error_log', SITE_ROOT . '/logs/php_errors.log');

// Timezone
date_default_timezone_set('Asia/Colombo');

// Session configuration
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_httponly', true);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration for XAMPP
define('DB_HOST', 'localhost');  // XAMPP uses localhost
define('DB_PORT', '3306');       // Default XAMPP MySQL port
define('DB_NAME', 'sandawatha_lk');
define('DB_USER', 'root');       // Default XAMPP user
define('DB_PASS', '');           // Default XAMPP has no password

// Upload configuration
define('UPLOAD_DIR', __DIR__ . '/../public/uploads');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_DOC_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Security configuration
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_MAX_LENGTH', 72);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 15 * 60); // 15 minutes

// Premium features configuration
define('FREE_CONTACT_REQUESTS_PER_DAY', 5);
define('PREMIUM_CONTACT_REQUESTS_PER_DAY', 50);
define('FREE_MESSAGES_PER_DAY', 10);
define('PREMIUM_MESSAGES_PER_DAY', 100);

// Load database class
require_once __DIR__ . '/database.php';  // Updated to lowercase
?>