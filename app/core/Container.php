<?php

namespace App\Core;

class Container {
    private static $instances = [];
    private static $bindings = [];
    
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
            $dependency = $parameter->getClass();
            
            if (is_null($dependency)) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve dependency \${$parameter->getName()}");
                }
            } else {
                $dependencies[] = self::make($dependency->name);
            }
        }
        
        return $reflector->newInstanceArgs($dependencies);
    }
    
    public static function registerDefaultBindings() {
        // Register core services
        self::singleton('App\Services\UserService');
        self::singleton('App\Services\ProfileService');
        self::singleton('App\Services\ChatService');
        self::singleton('App\Services\MessageService');
        
        // Register models
        self::singleton('App\Models\UserModel');
        self::singleton('App\Models\ProfileModel');
        self::singleton('App\Models\ChatModel');
        self::singleton('App\Models\MessageModel');
        
        // Register helpers
        self::singleton('App\Helpers\RateLimiter');
        self::singleton('App\Helpers\CsrfProtection');
        self::singleton('App\Helpers\FileUploadValidator');
    }
} 