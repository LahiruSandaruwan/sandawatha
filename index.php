<?php
// Load Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
require_once __DIR__ . '/config/config.php';

// Get the request URL
$url = $_SERVER['REQUEST_URI'] ?? '/';

// Load and execute routes
require_once __DIR__ . '/routes/web.php';
