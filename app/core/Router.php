<?php

namespace App\core;

class Router {
    private static $routes = [
        'GET' => [],
        'POST' => []
    ];
    private static $notFoundCallback;
    
    public static function get($path, $callback) {
        self::$routes['GET'][$path] = $callback;
    }
    
    public static function post($path, $callback) {
        self::$routes['POST'][$path] = $callback;
    }
    
    public static function notFound($callback) {
        self::$notFoundCallback = $callback;
    }

    private static function matchRoute($requestUrl, $routePath) {
        // Convert route parameters to regex pattern
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $routePath);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        if (preg_match($pattern, $requestUrl, $matches)) {
            array_shift($matches); // Remove the full match
            return $matches; // Return captured parameters
        }
        
        return false;
    }
    
    public static function dispatch($url) {
        // Remove query string
        $url = parse_url($url, PHP_URL_PATH);
        
        // Remove trailing slash except for home page
        $url = $url === '/' ? '/' : rtrim($url, '/');
        
        // Set current page for navigation
        $currentPage = explode('/', trim($url, '/'))[0] ?: 'home';
        define('CURRENT_PAGE', $currentPage);
        
        // Find matching route
        foreach (self::$routes as $route) {
            if ($route['method'] === $_SERVER['REQUEST_METHOD']) {
                $pattern = self::convertRouteToRegex($route['path']);
                if (preg_match($pattern, $url, $matches)) {
                    array_shift($matches); // Remove full match
                    
                    // Get controller and action
                    list($controller, $action) = $route['handler'];
                    
                    // Create controller instance
                    $controllerClass = "App\\controllers\\$controller";
                    if (!class_exists($controllerClass)) {
                        throw new Exception("Controller $controller not found");
                    }
                    
                    $controllerInstance = new $controllerClass();
                    
                    // Call action with parameters
                    return call_user_func_array([$controllerInstance, $action], $matches);
                }
            }
        }
        
        // No route found
        if (isset(self::$notFoundCallback)) {
            return call_user_func(self::$notFoundCallback);
        }
        
        throw new Exception('Route not found');
    }
    
    private static function executeCallback($callback, $params = []) {
        if (is_array($callback)) {
            // Add namespace to controller if not already namespaced
            $controllerClass = $callback[0];
            if (strpos($controllerClass, '\\') === false) {
                $controllerClass = "App\\controllers\\" . $controllerClass;
            }
            
            if (!class_exists($controllerClass)) {
                error_log("Controller class not found: {$controllerClass}");
                http_response_code(404);
                require_once SITE_ROOT . '/app/views/errors/404.php';
                return;
            }
            
            $controller = new $controllerClass();
            $method = $callback[1];
            
            if (!method_exists($controller, $method)) {
                error_log("Method not found in controller: {$controllerClass}::{$method}");
                http_response_code(404);
                require_once SITE_ROOT . '/app/views/errors/404.php';
                return;
            }
            
            return $controller->$method(...$params);
        }
        
        if (is_callable($callback)) {
            return $callback(...$params);
        }
        
        error_log("Invalid callback type");
        http_response_code(500);
        require_once SITE_ROOT . '/app/views/errors/500.php';
    }
} 