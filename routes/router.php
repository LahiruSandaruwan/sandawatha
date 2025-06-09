<?php
class Router {
    private $routes = [];
    private $middlewares = [];
    
    public function __construct() {
        $this->initializeRoutes();
    }
    
    private function initializeRoutes() {
        // Public routes
        $this->addRoute('GET', '/', 'HomeController', 'index');
        $this->addRoute('GET', '/login', 'AuthController', 'loginForm');
        $this->addRoute('POST', '/login', 'AuthController', 'login');
        $this->addRoute('GET', '/register', 'AuthController', 'registerForm');
        $this->addRoute('POST', '/register', 'AuthController', 'register');
        $this->addRoute('GET', '/logout', 'AuthController', 'logout');
        $this->addRoute('GET', '/verify-email', 'AuthController', 'verifyEmail');
        $this->addRoute('GET', '/verify-phone', 'AuthController', 'verifyPhoneForm');
        $this->addRoute('POST', '/verify-phone', 'AuthController', 'verifyPhone');
        $this->addRoute('GET', '/forgot-password', 'AuthController', 'forgotPasswordForm');
        $this->addRoute('POST', '/forgot-password', 'AuthController', 'forgotPassword');
        $this->addRoute('GET', '/reset-password', 'AuthController', 'resetPasswordForm');
        $this->addRoute('POST', '/reset-password', 'AuthController', 'resetPassword');
        
        // Public browsing and search
        $this->addRoute('GET', '/browse', 'ProfileController', 'browse');
        $this->addRoute('POST', '/search', 'ProfileController', 'search');
        
        // Protected routes (require authentication)
        $this->addRoute('GET', '/dashboard', 'DashboardController', 'index', ['auth']);
        
        // Profile routes (all require auth)
        $this->addRoute('GET', '/profile/edit', 'ProfileController', 'edit', ['auth']);
        $this->addRoute('POST', '/profile/update', 'ProfileController', 'update', ['auth']);
        $this->addRoute('POST', '/profile/upload-photo', 'ProfileController', 'uploadPhoto', ['auth']);
        $this->addRoute('POST', '/profile/upload-horoscope', 'ProfileController', 'uploadHoroscope', ['auth']);
        $this->addRoute('POST', '/profile/search', 'ProfileController', 'search', ['auth']);
        $this->addRoute('GET', '/profile/(\d+)', 'ProfileController', 'viewProfile', ['auth']);
        $this->addRoute('GET', '/profile', 'ProfileController', 'index', ['auth']);
        
        // Newsletter and feedback
        $this->addRoute('POST', '/newsletter/subscribe', 'NewsletterController', 'subscribe');
        $this->addRoute('GET', '/feedback', 'FeedbackController', 'form');
        $this->addRoute('POST', '/feedback', 'FeedbackController', 'submit');
        
        // Static pages
        $this->addRoute('GET', '/privacy-policy', 'PageController', 'privacyPolicy');
        $this->addRoute('GET', '/terms-conditions', 'PageController', 'termsConditions');
        $this->addRoute('GET', '/about', 'PageController', 'about');
        $this->addRoute('GET', '/contact', 'PageController', 'contact');
        $this->addRoute('POST', '/toggle-dark-mode', 'PageController', 'toggleDarkMode');
        
        // Contact requests
        $this->addRoute('POST', '/contact-request/send', 'ContactController', 'send', ['auth']);
        $this->addRoute('POST', '/contact-request/respond', 'ContactController', 'respond', ['auth']);
        $this->addRoute('GET', '/contact-requests', 'ContactController', 'list', ['auth']);
        
        // Favorites
        $this->addRoute('POST', '/favorites/add', 'FavoriteController', 'add', ['auth']);
        $this->addRoute('POST', '/favorites/remove', 'FavoriteController', 'remove', ['auth']);
        $this->addRoute('GET', '/favorites', 'FavoriteController', 'list', ['auth']);
        
        // Messages
        $this->addRoute('GET', '/messages', 'MessageController', 'inbox', ['auth']);
        $this->addRoute('GET', '/messages/(\d+)', 'MessageController', 'viewMessage', ['auth']);
        $this->addRoute('POST', '/messages/send', 'MessageController', 'send', ['auth']);
        $this->addRoute('POST', '/messages/reply', 'MessageController', 'reply', ['auth']);
        $this->addRoute('POST', '/messages/delete', 'MessageController', 'delete', ['auth']);
        
        // Premium membership
        $this->addRoute('GET', '/premium', 'PremiumController', 'plans', ['auth']);
        $this->addRoute('POST', '/premium/subscribe', 'PremiumController', 'subscribe', ['auth']);
        
        // AI matching
        $this->addRoute('GET', '/ai-matches', 'AIController', 'matches', ['auth', 'premium']);
        $this->addRoute('POST', '/ai-compatibility', 'AIController', 'calculateCompatibility', ['auth']);
        
        // Gift suggestions
        $this->addRoute('GET', '/gifts', 'GiftController', 'suggestions', ['auth']);
        
        // Admin routes
        $this->addRoute('GET', '/admin', 'AdminController', 'dashboard', ['auth', 'admin']);
        $this->addRoute('GET', '/admin/users', 'AdminController', 'users', ['auth', 'admin']);
        $this->addRoute('POST', '/admin/users/(\d+)/approve', 'AdminController', 'approveUser', ['auth', 'admin']);
        $this->addRoute('POST', '/admin/users/(\d+)/block', 'AdminController', 'blockUser', ['auth', 'admin']);
        $this->addRoute('POST', '/admin/users/(\d+)/unblock', 'AdminController', 'unblockUser', ['auth', 'admin']);
        $this->addRoute('GET', '/admin/feedback', 'AdminController', 'feedback', ['auth', 'admin']);
        $this->addRoute('GET', '/admin/messages', 'AdminController', 'messages', ['auth', 'admin']);
        $this->addRoute('POST', '/admin/message/send', 'AdminController', 'sendMessage', ['auth', 'admin']);
        $this->addRoute('GET', '/admin/settings', 'AdminController', 'settings', ['auth', 'admin']);
        $this->addRoute('POST', '/admin/settings/update', 'AdminController', 'updateSettings', ['auth', 'admin']);
        
        // API routes
        $this->addRoute('GET', '/api/districts', 'ApiController', 'districts');
        $this->addRoute('GET', '/api/religions', 'ApiController', 'religions');
        $this->addRoute('GET', '/api/castes', 'ApiController', 'castes');
        $this->addRoute('POST', '/api/check-compatibility', 'ApiController', 'checkCompatibility', ['auth']);
    }
    
    public function addRoute($method, $pattern, $controller, $action, $middlewares = []) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action,
            'middlewares' => $middlewares
        ];
    }
    
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if running in subdirectory
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        $basePath = rtrim(str_replace('\\', '/', $scriptName), '/');
        if ($basePath !== '' && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        if (empty($requestUri)) {
            $requestUri = '/';
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }
            
            // Convert named parameters to regex pattern
            $pattern = preg_replace('/:[a-zA-Z]+/', '([^/]+)', $route['pattern']);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Check middlewares
                if (!$this->checkMiddlewares($route['middlewares'])) {
                    return;
                }
                
                $controllerName = $route['controller'];
                $actionName = $route['action'];
                
                // Load and instantiate controller
                $controllerFile = SITE_ROOT . '/app/controllers/' . $controllerName . '.php';
                if (!file_exists($controllerFile)) {
                    $this->show404();
                    return;
                }
                
                require_once $controllerFile;
                
                if (!class_exists($controllerName)) {
                    $this->show404();
                    return;
                }
                
                $controller = new $controllerName();
                
                if (!method_exists($controller, $actionName)) {
                    $this->show404();
                    return;
                }
                
                // Call the action with matches as parameters
                call_user_func_array([$controller, $actionName], $matches);
                return;
            }
        }
        
        $this->show404();
    }
    
    private function checkMiddlewares($middlewares) {
        foreach ($middlewares as $middleware) {
            switch ($middleware) {
                case 'auth':
                    if (!isset($_SESSION['user_id'])) {
                        header('Location: ' . BASE_URL . '/login');
                        exit;
                    }
                    break;
                    
                case 'admin':
                    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
                        header('Location: ' . BASE_URL . '/dashboard');
                        exit;
                    }
                    break;
                    
                case 'premium':
                    // Check if user has active premium membership
                    require_once SITE_ROOT . '/app/models/PremiumModel.php';
                    $premiumModel = new PremiumModel();
                    if (!$premiumModel->hasActivePremium($_SESSION['user_id'])) {
                        header('Location: ' . BASE_URL . '/premium');
                        exit;
                    }
                    break;
                    
                case 'guest':
                    if (isset($_SESSION['user_id'])) {
                        header('Location: ' . BASE_URL . '/dashboard');
                        exit;
                    }
                    break;
            }
        }
        return true;
    }
    
    private function show404() {
        http_response_code(404);
        require_once SITE_ROOT . '/app/views/errors/404.php';
    }
}
?>