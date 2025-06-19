<?php

namespace App\core;

class Container {
    private static $instance = null;
    private static $instances = [];
    private static $bindings = [];
    
    private function __construct() {
        // Private constructor to prevent direct instantiation
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public static function bind($abstract, $concrete = null) {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }
        
        self::$bindings[$abstract] = $concrete;
    }
    
    public static function singleton($abstract, $concrete = null) {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }
        
        self::$bindings[$abstract] = function () use ($concrete) {
            static $instance = null;
            if (is_null($instance)) {
                $instance = self::build($concrete);
            }
            return $instance;
        };
    }
    
    public static function make($abstract) {
        if (isset(self::$instances[$abstract])) {
            return self::$instances[$abstract];
        }
        
        if (isset(self::$bindings[$abstract])) {
            $concrete = self::$bindings[$abstract];
            
            if (is_callable($concrete)) {
                $instance = $concrete();
            } else {
                $instance = self::build($concrete);
            }
            
            self::$instances[$abstract] = $instance;
            return $instance;
        }
        
        return self::build($abstract);
    }
    
    protected static function build($concrete) {
        if (is_callable($concrete)) {
            return $concrete();
        }
        
        $reflector = new \ReflectionClass($concrete);
        
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Target [$concrete] is not instantiable.");
        }
        
        $constructor = $reflector->getConstructor();
        
        if (is_null($constructor)) {
            return new $concrete;
        }
        
        $dependencies = [];
        
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            
            if (!$type || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve dependency \${$parameter->getName()}");
                }
            } else {
                $dependencies[] = self::make($type->getName());
            }
        }
        
        return $reflector->newInstanceArgs($dependencies);
    }
    
    public static function registerDefaultBindings() {
        // Register core services
        self::singleton('App\services\UserService');
        self::singleton('App\services\ProfileService');
        self::singleton('App\services\ChatService');
        self::singleton('App\services\MessageService');
        
        // Register models
        self::singleton('App\models\UserModel');
        self::singleton('App\models\ProfileModel');
        self::singleton('App\models\ChatModel');
        self::singleton('App\models\MessageModel');
        
        // Register helpers
        self::singleton('App\helpers\RateLimiter');
        self::singleton('App\helpers\CsrfProtection');
        self::singleton('App\helpers\FileUploadValidator');
    }
    
    // Prevent cloning of the instance
    private function __clone() {}
    
    // Prevent unserializing of the instance
    public function __wakeup() {}
} 