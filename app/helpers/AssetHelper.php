<?php

namespace App\helpers;

class AssetHelper {
    
    private static $preloadedAssets = [];
    
    /**
     * Get optimized asset URL with cache busting
     */
    public static function asset($path, $optimized = true) {
        // Add .css extension if missing for CSS files
        if (strpos($path, '.css') === false && strpos($path, 'css/') !== false) {
            $path .= '.css';
        }
        
        // Always start with /assets/ for all files
        $basePath = BASE_URL . '/assets/';
        
        if ($optimized) {
            $optimizedPath = 'optimized/' . $path;
            $fullPath = SITE_ROOT . '/public/assets/' . $optimizedPath;
            
            if (file_exists($fullPath)) {
                $path = $optimizedPath;
            }
        }
        
        $fullPath = SITE_ROOT . '/public/assets/' . ($path[0] === '/' ? substr($path, 1) : $path);
        
        // Check if file exists
        if (!file_exists($fullPath)) {
            error_log("Asset not found: " . $fullPath);
            return $basePath . 'css/style.css'; // Fallback to main stylesheet
        }
        
        $version = filemtime($fullPath);
        return $basePath . $path . '?v=' . $version;
    }
    
    /**
     * Preload critical assets
     */
    public static function preload($path, $type = 'style') {
        $url = self::asset($path);
        
        if (!in_array($url, self::$preloadedAssets)) {
            self::$preloadedAssets[] = $url;
            
            $as = $type === 'style' ? 'style' : ($type === 'script' ? 'script' : 'image');
            $crossorigin = $type === 'font' ? ' crossorigin="anonymous"' : '';
            echo "<link rel=\"preload\" href=\"{$url}\" as=\"{$as}\"{$crossorigin}>\n";
        }
    }
    
    /**
     * Generate CSS link with optimization
     */
    public static function css($path, $preload = false) {
        // Ensure path starts with css/ if not already
        if (strpos($path, 'css/') !== 0 && strpos($path, '/css/') !== 0) {
            $path = 'css/' . $path;
        }
        
        $url = self::asset($path);
        
        if ($preload) {
            self::preload($path, 'style');
        }
        
        return "<link rel=\"stylesheet\" href=\"{$url}\">";
    }
    
    /**
     * Generate JS script with optimization
     */
    public static function js($path, $defer = true) {
        // Ensure path starts with js/ if not already
        if (strpos($path, 'js/') !== 0 && strpos($path, '/js/') !== 0) {
            $path = 'js/' . $path;
        }
        
        $url = self::asset($path);
        $deferAttr = $defer ? ' defer' : '';
        
        return "<script src=\"{$url}\"{$deferAttr}></script>";
    }
    
    /**
     * Generate responsive image with WebP support
     */
    public static function responsiveImage($path, $alt = '', $lazy = true) {
        $webpPath = str_replace(['.png', '.jpg', '.jpeg'], '.webp', $path);
        $webpUrl = self::asset('images/' . $webpPath);
        $fallbackUrl = self::asset('images/' . $path);
        
        $lazyAttr = $lazy ? ' loading="lazy"' : '';
        
        return "<picture>
            <source srcset=\"{$webpUrl}\" type=\"image/webp\">
            <img src=\"{$fallbackUrl}\" alt=\"{$alt}\"{$lazyAttr}>
        </picture>";
    }
}
