<?php

namespace App\Core;

class Autoloader {
    public static function register() {
        spl_autoload_register(function ($class) {
            // Convert namespace to full file path
            $file = SITE_ROOT . '/' . str_replace('\\', '/', $class) . '.php';
            
            // If the file exists, require it
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
            return false;
        });
    }
} 