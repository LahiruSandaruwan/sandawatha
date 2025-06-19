<?php

namespace App\helpers;

class CsrfProtection {
    private const TOKEN_LENGTH = 32;
    private const TOKEN_NAME = 'csrf_token';
    
    public static function generateToken() {
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            $_SESSION[self::TOKEN_NAME] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return $_SESSION[self::TOKEN_NAME];
    }
    
    public static function validateToken($token) {
        if (!isset($_SESSION[self::TOKEN_NAME]) || empty($token)) {
            return false;
        }
        
        $result = hash_equals($_SESSION[self::TOKEN_NAME], $token);
        
        // Regenerate token after validation
        self::regenerateToken();
        
        return $result;
    }
    
    public static function regenerateToken() {
        $_SESSION[self::TOKEN_NAME] = bin2hex(random_bytes(self::TOKEN_LENGTH));
    }
    
    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
    
    public static function getTokenHeader() {
        return 'X-CSRF-Token: ' . self::generateToken();
    }
} 