<?php
// Upload directory paths
define('UPLOAD_PATH', SITE_ROOT . '/public/uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// Maximum file sizes (in bytes)
define('MAX_PROFILE_PHOTO_SIZE', 5 * 1024 * 1024);  // 5MB
define('MAX_VIDEO_SIZE', 50 * 1024 * 1024);         // 50MB
define('MAX_VOICE_SIZE', 10 * 1024 * 1024);         // 10MB
define('MAX_DOCUMENT_SIZE', 10 * 1024 * 1024);      // 10MB

// Allowed file types
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/gif'
]);

define('ALLOWED_VIDEO_TYPES', [
    'video/mp4',
    'video/webm',
    'video/ogg'
]);

define('ALLOWED_AUDIO_TYPES', [
    'audio/mpeg',
    'audio/ogg',
    'audio/wav'
]);

define('ALLOWED_DOCUMENT_TYPES', [
    'application/pdf',
    'image/jpeg',
    'image/jpg',
    'image/png'
]); 