<?php

class AssetHelper {
    
    private static $preloadedAssets = [];
    
    /**
     * Get optimized asset URL with cache busting
     */
    public static function asset($path, $optimized = true) {
        $basePath = BASE_URL . '/public/assets/';
        
        if ($optimized) {
            $optimizedPath = 'optimized/' . $path;
            $fullPath = SITE_ROOT . '/public/assets/' . $optimizedPath;
            
            if (file_exists($fullPath)) {
                $path = $optimizedPath;
            }
        }
        
        $fullPath = SITE_ROOT . '/public/assets/' . $path;
        $version = file_exists($fullPath) ? filemtime($fullPath) : time();
        
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
            echo "<link rel=\"preload\" href=\"{$url}\" as=\"{$as}\">\n";
        }
    }
    
    /**
     * Generate CSS link with optimization
     */
    public static function css($path, $preload = false) {
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
        $url = self::asset($path);
        $deferAttr = $defer ? ' defer' : '';
        
        return "<script src=\"{$url}\"{$deferAttr}></script>";
    }
    
    /**
     * Generate responsive image with WebP support
     */
    public static function responsiveImage($path, $alt = '', $lazy = true) {
        $webpPath = str_replace('.png', '.webp', $path);
        $webpUrl = self::asset('images/' . $webpPath);
        $fallbackUrl = self::asset('images/' . $path);
        
        $lazyAttr = $lazy ? ' loading="lazy"' : '';
        
        return "<picture>
            <source srcset=\"{$webpUrl}\" type=\"image/webp\">
            <img src=\"{$fallbackUrl}\" alt=\"{$alt}\"{$lazyAttr}>
        </picture>";
    }
}
