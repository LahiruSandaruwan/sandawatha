<?php

/**
 * Convert a timestamp to a human-readable time ago string
 * 
 * @param string|null $datetime MySQL datetime string
 * @return string Human readable time difference
 */
function timeAgo($datetime) {
    if (!$datetime) {
        return 'unknown time';
    }
    
    try {
        $date = new DateTime($datetime);
        $now = new DateTime();
        $difference = $now->getTimestamp() - $date->getTimestamp();
    
    if ($difference < 60) {
        return 'just now';
    }
    
    $intervals = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute'
    ];
    
    foreach ($intervals as $seconds => $label) {
        $quotient = floor($difference / $seconds);
        if ($quotient > 0) {
            if ($quotient === 1) {
                return "1 {$label} ago";
            }
            return "{$quotient} {$label}s ago";
        }
    }
    
    return 'just now';
    } catch (Exception $e) {
        return 'unknown time';
    }
}

/**
 * Format a number to a human-readable string with K/M/B suffix
 * 
 * @param int $number The number to format
 * @return string Formatted number
 */
function formatNumber($number) {
    if ($number >= 1000000000) {
        return round($number / 1000000000, 1) . 'B';
    }
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    }
    if ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return $number;
}

/**
 * Get file extension from a filename
 * 
 * @param string $filename The filename
 * @return string The file extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Generate a random string
 * 
 * @param int $length Length of the string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Truncate a string to a specified length
 * 
 * @param string $string String to truncate
 * @param int $length Maximum length
 * @param string $append String to append if truncated
 * @return string Truncated string
 */
function truncateString($string, $length = 100, $append = '...') {
    if (strlen($string) > $length) {
        return substr($string, 0, $length) . $append;
    }
    return $string;
}

/**
 * Format a date according to the application's default format
 * 
 * @param string $date Date string
 * @param string $format Optional format string
 * @return string Formatted date
 */
function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

/**
 * Check if a string starts with another string
 * 
 * @param string $haystack The string to search in
 * @param string $needle The string to search for
 * @return bool True if haystack starts with needle
 */
function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

/**
 * Check if a string ends with another string
 * 
 * @param string $haystack The string to search in
 * @param string $needle The string to search for
 * @return bool True if haystack ends with needle
 */
function endsWith($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
}

/**
 * Convert bytes to human readable format
 * 
 * @param int $bytes Number of bytes
 * @return string Human readable size
 */
function formatBytes($bytes) {
    if ($bytes > 0) {
        $unit = intval(log($bytes, 1024));
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        return round($bytes / pow(1024, $unit), 2) . ' ' . $units[$unit];
    }
    return '0 B';
}

/**
 * Get a gravatar URL for an email address
 * 
 * @param string $email The email address
 * @param int $size Size in pixels
 * @return string Gravatar URL
 */
function getGravatar($email, $size = 80) {
    $hash = md5(strtolower(trim($email)));
    return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
}

/**
 * Clean a string for use in URLs
 * 
 * @param string $string String to clean
 * @return string URL-safe string
 */
function slugify($string) {
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    $string = preg_replace('~[^-\w]+~', '', $string);
    $string = trim($string, '-');
    $string = preg_replace('~-+~', '-', $string);
    $string = strtolower($string);
    return $string ?: 'n-a';
} 