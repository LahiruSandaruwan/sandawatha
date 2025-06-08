<?php
// Upload directory paths
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', SITE_ROOT . '/public/uploads/');
}
if (!defined('UPLOAD_URL')) {
    define('UPLOAD_URL', BASE_URL . '/uploads/');
}

// Maximum file sizes (in bytes)
if (!defined('MAX_PROFILE_PHOTO_SIZE')) {
    define('MAX_PROFILE_PHOTO_SIZE', 5 * 1024 * 1024);  // 5MB
}
if (!defined('MAX_DOCUMENT_SIZE')) {
    define('MAX_DOCUMENT_SIZE', 10 * 1024 * 1024);      // 10MB
}

// Allowed file types
if (!defined('ALLOWED_IMAGE_TYPES')) {
    define('ALLOWED_IMAGE_TYPES', [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif'
    ]);
}

if (!defined('ALLOWED_DOCUMENT_TYPES')) {
    define('ALLOWED_DOCUMENT_TYPES', [
        'application/pdf',
        'image/jpeg',
        'image/jpg',
        'image/png'
    ]);
}