<?php

use App\core\Router;

// Handle static files for development server
if (php_sapi_name() === 'cli-server') {
    try {
        $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $filePath = SITE_ROOT . '/public' . $uri;
        
        // Check if file exists in public directory
        if ($uri !== '/' && file_exists($filePath)) {
            // Get the file extension
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            // Set the content type based on file extension
            switch ($ext) {
                case 'css':
                    header('Content-Type: text/css; charset=UTF-8');
                    break;
                case 'js':
                    header('Content-Type: application/javascript; charset=UTF-8');
                    break;
                case 'jpg':
                case 'jpeg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'png':
                    header('Content-Type: image/png');
                    break;
                case 'gif':
                    header('Content-Type: image/gif');
                    break;
                case 'svg':
                    header('Content-Type: image/svg+xml; charset=UTF-8');
                    break;
                case 'webp':
                    header('Content-Type: image/webp');
                    break;
                case 'woff':
                    header('Content-Type: font/woff');
                    break;
                case 'woff2':
                    header('Content-Type: font/woff2');
                    break;
                case 'ttf':
                    header('Content-Type: font/ttf');
                    break;
                case 'eot':
                    header('Content-Type: application/vnd.ms-fontobject');
                    break;
                default:
                    // For unknown types, let the server guess
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    header('Content-Type: ' . finfo_file($finfo, $filePath));
                    finfo_close($finfo);
            }
            
            // Cache control for static assets
            $expires = 60 * 60 * 24 * 7; // 1 week
            header("Cache-Control: public, max-age=$expires");
            header('Pragma: public');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
            
            // Disable output buffering
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Read file in chunks to handle large files
            $handle = fopen($filePath, 'rb');
            while (!feof($handle)) {
                echo fread($handle, 8192);
                flush();
            }
            fclose($handle);
            return true;
        }
    } catch (Exception $e) {
        error_log("Error serving static file: " . $e->getMessage());
        return false;
    }
}

// Auth routes
Router::get('/login', ['AuthController', 'loginForm']);
Router::post('/login', ['AuthController', 'login']);
Router::get('/register', ['AuthController', 'registerForm']);
Router::post('/register', ['AuthController', 'register']);
Router::get('/verify-email', ['AuthController', 'verifyEmail']);
Router::get('/verify-phone', ['AuthController', 'verifyPhoneForm']);
Router::post('/verify-phone', ['AuthController', 'verifyPhone']);
Router::get('/forgot-password', ['AuthController', 'forgotPasswordForm']);
Router::post('/forgot-password', ['AuthController', 'forgotPassword']);
Router::get('/reset-password', ['AuthController', 'resetPasswordForm']);
Router::post('/reset-password', ['AuthController', 'resetPassword']);
Router::get('/logout', ['AuthController', 'logout']);

// Theme routes
Router::post('/toggle-dark-mode', ['HomeController', 'toggleDarkMode']);

// Dashboard routes
Router::get('/dashboard', ['DashboardController', 'index']);

// Profile routes
Router::get('/profile', ['ProfileController', 'index']);
Router::get('/profile/{id}', ['ProfileController', 'viewProfile']);
Router::get('/profile/edit', ['ProfileController', 'edit']);
Router::post('/profile/update', ['ProfileController', 'update']);
Router::post('/profile/photo', ['ProfileController', 'updatePhoto']);
Router::get('/browse', ['ProfileController', 'browse']);

// Contact request routes
Router::get('/contact-requests', ['ContactController', 'list']);
Router::post('/contact-request/send', ['ContactController', 'send']);
Router::post('/contact-request/respond', ['ContactController', 'respond']);

// Favorites routes
Router::get('/favorites', ['FavoriteController', 'list']);
Router::post('/favorites/add', ['FavoriteController', 'add']);
Router::post('/favorites/remove', ['FavoriteController', 'remove']);

// Message routes
Router::get('/messages', ['MessageController', 'inbox']);
Router::get('/messages/view/{id}', ['MessageController', 'viewMessage']);
Router::post('/messages/send', ['MessageController', 'send']);
Router::post('/messages/reply', ['MessageController', 'reply']);
Router::post('/messages/delete', ['MessageController', 'delete']);
Router::post('/messages/mark-read', ['MessageController', 'markAsRead']);

// Chat routes
Router::get('/chat', ['ChatController', 'index']);
Router::post('/chat/send', ['ChatController', 'send']);
Router::get('/chat/messages', ['ChatController', 'getMessages']);

// Admin routes
Router::get('/admin', ['AdminController', 'index']);
Router::get('/admin/users', ['AdminController', 'users']);
Router::post('/admin/user/block', ['AdminController', 'blockUser']);
Router::post('/admin/user/unblock', ['AdminController', 'unblockUser']);

// Home route
Router::get('/', ['HomeController', 'index']);

// 404 handler
Router::notFound(function() {
    http_response_code(404);
    require_once SITE_ROOT . '/app/views/errors/404.php';
});

try {
    // Dispatch the route
    Router::dispatch($url);
} catch (Exception $e) {
    error_log("Routing error: " . $e->getMessage());
    http_response_code(500);
    require_once SITE_ROOT . '/app/views/errors/500.php';
} 