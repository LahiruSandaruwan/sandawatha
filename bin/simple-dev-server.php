#!/usr/bin/env php
<?php

/**
 * Simple Sandawatha Development Server
 * A simpler, more reliable version for starting both servers
 */

// Colors for terminal output
define('GREEN', "\033[1;32m");
define('RED', "\033[1;31m");
define('YELLOW', "\033[1;33m");
define('CYAN', "\033[1;36m");
define('RESET', "\033[0m");

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

function printHeader() {
    echo CYAN . "========================================\n";
    echo "  Sandawatha Dating App - Dev Server\n";
    echo "========================================" . RESET . "\n\n";
}

function checkDependencies() {
    echo "🔍 Checking dependencies...\n";
    
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        echo RED . "❌ Error: Composer dependencies not installed." . RESET . "\n";
        echo "Run: composer install\n";
        exit(1);
    }
    
    if (!file_exists(__DIR__ . '/websocket_server.php')) {
        echo RED . "❌ Error: WebSocket server file not found." . RESET . "\n";
        exit(1);
    }
    
    // Check if logs directory exists and is writable
    $logsDir = __DIR__ . '/../logs';
    if (!is_dir($logsDir)) {
        if (!@mkdir($logsDir, 0755, true)) {
            echo RED . "❌ Error: Could not create logs directory." . RESET . "\n";
            exit(1);
        }
    } elseif (!is_writable($logsDir)) {
        echo RED . "❌ Error: Logs directory is not writable." . RESET . "\n";
        exit(1);
    }
    
    echo GREEN . "✅ Dependencies check passed" . RESET . "\n\n";
}

function isPortInUse($port) {
    $connection = @fsockopen('localhost', $port, $errno, $errstr, 1);
    if (is_resource($connection)) {
        fclose($connection);
        return true;
    }
    return false;
}

function killProcessOnPort($port) {
    if (PHP_OS_FAMILY === 'Windows') {
        exec("for /f \"tokens=5\" %a in ('netstat -aon | findstr :$port') do taskkill /f /pid %a 2>NUL");
    } else {
        exec("fuser -k $port/tcp 2>/dev/null");
        sleep(1); // Give the process time to die
    }
    echo "🔄 Killed existing process on port $port\n";
}

function checkPorts() {
    echo "🔍 Checking ports...\n";
    
    if (isPortInUse(8000)) {
        echo YELLOW . "⚠️  Port 8000 is already in use." . RESET . "\n";
        killProcessOnPort(8000);
        sleep(1);
    }
    
    if (isPortInUse(8082)) {
        echo YELLOW . "⚠️  Port 8082 is already in use." . RESET . "\n";
        killProcessOnPort(8082);
        sleep(1);
    }
    
    // Double check ports are free
    if (isPortInUse(8000) || isPortInUse(8082)) {
        echo RED . "❌ Could not free up required ports." . RESET . "\n";
        exit(1);
    }
    
    echo GREEN . "✅ Ports are available" . RESET . "\n\n";
}

function startServers() {
    echo "🚀 Starting services...\n";
    
    $projectRoot = dirname(__DIR__);
    $logsDir = $projectRoot . '/logs';
    
    // Start WebSocket server
    echo "💬 Starting WebSocket server on port 8082...\n";
    $websocketLog = $logsDir . '/websocket.log';
    $websocketScript = __DIR__ . '/websocket_server.php';
    
    if (PHP_OS_FAMILY === 'Windows') {
        $cmd = "start /B php \"$websocketScript\" > \"$websocketLog\" 2>&1";
        exec($cmd);
    } else {
        $cmd = "php '$websocketScript' > '$websocketLog' 2>&1 & echo $!";
        $wsockPid = exec($cmd);
        if (!$wsockPid) {
            echo RED . "❌ Failed to start WebSocket server" . RESET . "\n";
            exit(1);
        }
        // Store WebSocket PID for cleanup
        file_put_contents($logsDir . '/websocket.pid', $wsockPid);
    }
    
    sleep(2);
    
    // Start web server
    echo "🌐 Starting web application server on port 8000...\n";
    $appLog = $logsDir . '/app.log';
    $publicDir = $projectRoot . '/public';
    $routerScript = $publicDir . '/router.php';
    
    if (PHP_OS_FAMILY === 'Windows') {
        $cmd = "start /B php -S localhost:8000 -t \"$publicDir\" \"$routerScript\" > \"$appLog\" 2>&1";
        exec($cmd);
    } else {
        $cmd = "php -S localhost:8000 -t '$publicDir' '$routerScript' > '$appLog' 2>&1 & echo $!";
        $webPid = exec($cmd);
        if (!$webPid) {
            echo RED . "❌ Failed to start web server" . RESET . "\n";
            // Kill WebSocket server before exiting
            exec("kill " . file_get_contents($logsDir . '/websocket.pid') . " 2>/dev/null");
            exit(1);
        }
        // Store Web server PID for cleanup
        file_put_contents($logsDir . '/web.pid', $webPid);
    }
    
    sleep(2);
    echo "\n";
}

function verifyServers() {
    echo "🔍 Server Status:\n";
    
    $webRunning = isPortInUse(8000);
    $wsRunning = isPortInUse(8082);
    
    echo "   Web Server: " . ($webRunning ? GREEN . "✅ Running" . RESET : RED . "❌ Failed" . RESET) . "\n";
    echo "   WebSocket:  " . ($wsRunning ? GREEN . "✅ Running" . RESET : RED . "❌ Failed" . RESET) . "\n\n";
    
    return $webRunning && $wsRunning;
}

function printStatus() {
    echo GREEN . "========================================\n";
    echo "  🚀 Sandawatha is now running!\n";
    echo "========================================" . RESET . "\n\n";
    
    echo "📱 " . CYAN . "Web Application:" . RESET . " http://localhost:8000\n";
    echo "💬 " . CYAN . "WebSocket Server:" . RESET . " ws://localhost:8082\n\n";
    
    echo "📝 " . YELLOW . "Logs:" . RESET . "\n";
    echo "   App logs: tail -f logs/app.log\n";
    echo "   WebSocket logs: tail -f logs/websocket.log\n\n";
    
    echo "💡 " . CYAN . "Note: Run this in the background to keep both servers running" . RESET . "\n";
    echo "💡 " . CYAN . "To stop: pkill -f 'php.*localhost:8000' && pkill -f 'php.*websocket'" . RESET . "\n\n";
}

// Main execution
printHeader();
checkDependencies();
checkPorts();
startServers();

if (verifyServers()) {
    printStatus();
    echo GREEN . "✅ Development environment started successfully!" . RESET . "\n";
} else {
    echo RED . "❌ Some servers failed to start. Check the logs for details." . RESET . "\n";
    echo "Log files:\n";
    echo "  - logs/app.log\n";
    echo "  - logs/websocket.log\n";
    exit(1);
}

// Get the requested URI
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If no path is specified, default to index
if ($uri === '') {
    $uri = '/';
}

// If the request is for a static file in public/assets or public/uploads
if (preg_match('/^\/assets\//', $uri) || preg_match('/^\/uploads\//', $uri)) {
    $file = __DIR__ . '/../public' . $uri;
    if (file_exists($file)) {
        // Get file extension
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        
        // Set content type based on file extension
        switch ($ext) {
            case 'css':
                header('Content-Type: text/css');
                break;
            case 'js':
                header('Content-Type: application/javascript');
                break;
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;
            case 'png':
                header('Content-Type: image/png');
                break;
            case 'gif':
                header('Content-Type: image/gif');
                break;
            case 'svg':
                header('Content-Type: image/svg+xml');
                break;
            case 'webp':
                header('Content-Type: image/webp');
                break;
            case 'woff':
                header('Content-Type: font/woff');
                break;
            case 'woff2':
                header('Content-Type: font/woff2');
                break;
            case 'ttf':
                header('Content-Type: font/ttf');
                break;
            case 'eot':
                header('Content-Type: application/vnd.ms-fontobject');
                break;
            case 'otf':
                header('Content-Type: font/otf');
                break;
            default:
                header('Content-Type: application/octet-stream');
        }
        
        readfile($file);
        return true;
    }
}

// For all other requests, route through index.php
require __DIR__ . '/../index.php'; 