<?php

namespace App\Helpers;

class FileUploadValidator {
    private $allowedTypes;
    private $maxSize;
    private $errors = [];
    
    public function __construct(array $allowedTypes = [], int $maxSize = 5242880) { // 5MB default
        $this->allowedTypes = $allowedTypes;
        $this->maxSize = $maxSize;
    }
    
    public function validate($file) {
        $this->errors = [];
        
        if (!isset($file) || !is_array($file)) {
            $this->errors[] = 'No file uploaded';
            return false;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }
        
        // Check file size
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = 'File size exceeds limit of ' . $this->formatSize($this->maxSize);
            return false;
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!empty($this->allowedTypes) && !in_array($mimeType, $this->allowedTypes)) {
            $this->errors[] = 'Invalid file type. Allowed types: ' . implode(', ', $this->allowedTypes);
            return false;
        }
        
        // Check for malicious content in images
        if (strpos($mimeType, 'image/') === 0) {
            if (!$this->validateImage($file['tmp_name'])) {
                $this->errors[] = 'Invalid or corrupted image file';
                return false;
            }
        }
        
        return true;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    private function validateImage($filepath) {
        $imageInfo = getimagesize($filepath);
        if ($imageInfo === false) {
            return false;
        }
        
        // Check for PHP code in image
        $content = file_get_contents($filepath);
        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
            return false;
        }
        
        return true;
    }
    
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }
    
    private function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 