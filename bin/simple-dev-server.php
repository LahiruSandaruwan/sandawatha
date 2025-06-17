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
        exec("lsof -ti :$port | xargs kill -9 2>/dev/null");
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
    
    if (isPortInUse(8080)) {
        echo YELLOW . "⚠️  Port 8080 is already in use." . RESET . "\n";
        killProcessOnPort(8080);
        sleep(1);
    }
    
    echo GREEN . "✅ Ports are available" . RESET . "\n\n";
}

function startServers() {
    echo "🚀 Starting services...\n";
    
    $projectRoot = dirname(__DIR__);
    $logsDir = $projectRoot . '/logs';
    
    // Create logs directory if it doesn't exist
    if (!is_dir($logsDir)) {
        mkdir($logsDir, 0755, true);
    }
    
    // Start WebSocket server
    echo "💬 Starting WebSocket server on port 8080...\n";
    $websocketLog = $logsDir . '/websocket.log';
    $websocketScript = __DIR__ . '/websocket_server.php';
    
    if (PHP_OS_FAMILY === 'Windows') {
        $cmd = "start /B php \"$websocketScript\" > \"$websocketLog\" 2>&1";
    } else {
        $cmd = "cd '$projectRoot' && php '$websocketScript' > '$websocketLog' 2>&1 &";
    }
    exec($cmd);
    
    sleep(2);
    
    // Start web server
    echo "🌐 Starting web application server on port 8000...\n";
    $appLog = $logsDir . '/app.log';
    $publicDir = $projectRoot . '/public';
    
    if (PHP_OS_FAMILY === 'Windows') {
        $cmd = "start /B php -S localhost:8000 -t \"$publicDir\" > \"$appLog\" 2>&1";
    } else {
        $cmd = "cd '$projectRoot' && php -S localhost:8000 -t '$publicDir' > '$appLog' 2>&1 &";
    }
    exec($cmd);
    
    sleep(2);
    echo "\n";
}

function verifyServers() {
    echo "🔍 Server Status:\n";
    
    $webRunning = isPortInUse(8000);
    $wsRunning = isPortInUse(8080);
    
    echo "   Web Server: " . ($webRunning ? GREEN . "✅ Running" . RESET : RED . "❌ Failed" . RESET) . "\n";
    echo "   WebSocket:  " . ($wsRunning ? GREEN . "✅ Running" . RESET : RED . "❌ Failed" . RESET) . "\n\n";
    
    return $webRunning && $wsRunning;
}

function printStatus() {
    echo GREEN . "========================================\n";
    echo "  🚀 Sandawatha is now running!\n";
    echo "========================================" . RESET . "\n\n";
    
    echo "📱 " . CYAN . "Web Application:" . RESET . " http://localhost:8000\n";
    echo "💬 " . CYAN . "WebSocket Server:" . RESET . " ws://localhost:8080\n\n";
    
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