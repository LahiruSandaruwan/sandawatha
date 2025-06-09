<?php
// Configure session first, before starting it
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Lax');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/upload.php';
require_once __DIR__ . '/../routes/router.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS headers for AJAX requests
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
    $allowedOrigins = [
        'http://localhost:8000',
        'http://127.0.0.1:8000'
    ];
    
    if (in_array($origin, $allowedOrigins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
    }
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Set content type based on request
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json; charset=UTF-8');
} else {
    header('Content-Type: text/html; charset=UTF-8');
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Initialize and dispatch router
$router = new Router();
$router->dispatch();
?>