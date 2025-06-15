<?php

namespace App\Core;

class Router {
    private static $routes = [];
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
    
    public static function dispatch($url) {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = parse_url($url, PHP_URL_PATH);
        $url = rtrim($url, '/');
        
        if (empty($url)) {
            $url = '/';
        }
        
        if (isset(self::$routes[$method][$url])) {
            $callback = self::$routes[$method][$url];
            
            if (is_array($callback)) {
                $controller = new $callback[0]();
                $method = $callback[1];
                return $controller->$method();
            }
            
            if (is_callable($callback)) {
                return $callback();
            }
        }
        
        if (self::$notFoundCallback) {
            return call_user_func(self::$notFoundCallback);
        }
        
        http_response_code(404);
        echo '404 Not Found';
    }
} 