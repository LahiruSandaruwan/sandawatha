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
        $this->addRoute('GET', '/profile/([0-9]+)', 'ProfileController', 'viewProfile');
        
        // Protected routes (require authentication)
        $this->addRoute('GET', '/dashboard', 'DashboardController', 'index', ['auth']);
        
        // Profile routes
        $this->addRoute('GET', '/profile/edit', 'ProfileController', 'edit', ['auth']);
        $this->addRoute('POST', '/profile/update', 'ProfileController', 'update', ['auth']);
        $this->addRoute('POST', '/profile/upload-photo', 'ProfileController', 'uploadPhoto', ['auth']);
        $this->addRoute('POST', '/profile/upload-horoscope', 'ProfileController', 'uploadHoroscope', ['auth']);
        $this->addRoute('POST', '/profile/privacy-settings', 'ProfileController', 'updatePrivacySettings', ['auth']);
        $this->addRoute('POST', '/profile/search', 'ProfileController', 'search', ['auth']);
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
        try {
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
            
            error_log("Router: Processing request for {$requestMethod} {$requestUri}");
            
            foreach ($this->routes as $route) {
                if ($route['method'] !== $requestMethod) {
                    continue;
                }
                
                // Convert route pattern to regex
                $pattern = str_replace('/', '\/', $route['pattern']);
                $pattern = '#^' . $pattern . '$#';
                
                error_log("Router: Testing pattern {$pattern} against {$requestUri}");
                
                if (preg_match($pattern, $requestUri, $matches)) {
                    array_shift($matches); // Remove full match
                    
                    error_log("Router: Route matched! Controller: {$route['controller']}, Action: {$route['action']}, Params: " . json_encode($matches));
                    
                    // Check middlewares
                    if (!$this->checkMiddlewares($route['middlewares'])) {
                        error_log("Router: Middleware check failed");
                        return;
                    }
                    
                    $controllerName = $route['controller'];
                    $actionName = $route['action'];
                    
                    // Load and instantiate controller
                    $controllerFile = SITE_ROOT . '/app/controllers/' . $controllerName . '.php';
                    if (!file_exists($controllerFile)) {
                        error_log("Router: Controller file not found: {$controllerFile}");
                        $this->show404();
                        return;
                    }
                    
                    require_once $controllerFile;
                    
                    if (!class_exists($controllerName)) {
                        error_log("Router: Controller class not found: {$controllerName}");
                        $this->show404();
                        return;
                    }
                    
                    $controller = new $controllerName();
                    
                    if (!method_exists($controller, $actionName)) {
                        error_log("Router: Action method not found: {$actionName}");
                        $this->show404();
                        return;
                    }
                    
                    // Call the action with matches as parameters
                    error_log("Router: Calling {$controllerName}::{$actionName} with params: " . json_encode($matches));
                    call_user_func_array([$controller, $actionName], $matches);
                    return;
                }
            }
            
            error_log("Router: No matching route found for {$requestUri}");
            $this->show404();
        } catch (Exception $e) {
            error_log("Router: Uncaught exception in dispatch");
            $this->show500($e);
        }
    }
    
    private function checkMiddlewares($middlewares) {
        error_log("Router: Checking middlewares: " . json_encode($middlewares));
        
        foreach ($middlewares as $middleware) {
            error_log("Router: Processing middleware: {$middleware}");
            
            try {
                switch ($middleware) {
                    case 'auth':
                        if (!isset($_SESSION['user_id'])) {
                            error_log("Router: Auth middleware failed - user not logged in");
                            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
                            header('Location: ' . BASE_URL . '/login');
                            exit;
                        }
                        
                        // Check if user is active
                        require_once SITE_ROOT . '/app/models/UserModel.php';
                        $userModel = new UserModel();
                        $user = $userModel->find($_SESSION['user_id']);
                        
                        if (!$user || $user['status'] !== 'active') {
                            error_log("Router: Auth middleware failed - user not active");
                            session_destroy();
                            header('Location: ' . BASE_URL . '/login');
                            exit;
                        }
                        break;
                        
                    case 'admin':
                        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
                            error_log("Router: Admin middleware failed - user not admin");
                            header('Location: ' . BASE_URL . '/dashboard');
                            exit;
                        }
                        break;
                        
                    case 'premium':
                        if (!isset($_SESSION['user_id'])) {
                            error_log("Router: Premium middleware failed - user not logged in");
                            header('Location: ' . BASE_URL . '/login');
                            exit;
                        }
                        
                        require_once SITE_ROOT . '/app/models/PremiumModel.php';
                        $premiumModel = new PremiumModel();
                        $features = $premiumModel->getUserFeatures($_SESSION['user_id']);
                        
                        if (!$features || $features['plan'] === 'basic') {
                            error_log("Router: Premium middleware failed - user not premium");
                            header('Location: ' . BASE_URL . '/premium');
                            exit;
                        }
                        break;
                        
                    default:
                        error_log("Router: Unknown middleware: {$middleware}");
                        return false;
                }
            } catch (Exception $e) {
                error_log("Router: Error in middleware {$middleware}: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                header('Location: ' . BASE_URL . '/error');
                exit;
            }
        }
        
        return true;
    }
    
    private function show404() {
        error_log("Router: 404 Not Found - " . $_SERVER['REQUEST_URI']);
        http_response_code(404);
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Page not found']);
        } else {
            require_once SITE_ROOT . '/app/views/errors/404.php';
        }
        exit;
    }
    
    private function show500($error = null) {
        error_log("Router: 500 Internal Server Error");
        if ($error) {
            error_log("Error details: " . $error->getMessage());
            error_log("Stack trace: " . $error->getTraceAsString());
        }
        
        http_response_code(500);
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        } else {
            require_once SITE_ROOT . '/app/views/errors/500.php';
        }
        exit;
    }
    
    private function show403() {
        error_log("Router: 403 Forbidden - " . $_SERVER['REQUEST_URI']);
        http_response_code(403);
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Access denied']);
        } else {
            require_once SITE_ROOT . '/app/views/errors/403.php';
        }
        exit;
    }
}
?>