#!/usr/bin/env php
<?php

/**
 * Sandawatha Development Server Manager
 * 
 * This script starts both the web application server and WebSocket server
 * for local development with proper process management.
 */

class DevServerManager {
    private $webServerPort = 8000;
    private $websocketPort = 8080;
    private $processes = [];
    private $logDir;
    
    public function __construct() {
        $this->logDir = __DIR__ . '/../logs';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    public function start() {
        $this->printHeader();
        $this->checkDependencies();
        $this->checkPorts();
        $this->startServers();
        $this->printStatus();
        $this->waitForShutdown();
    }
    
    private function printHeader() {
        echo "\033[1;36m"; // Cyan color
        echo "========================================\n";
        echo "  Sandawatha Dating App - Dev Server\n";
        echo "========================================\033[0m\n\n";
    }
    
    private function checkDependencies() {
        echo "🔍 Checking dependencies...\n";
        
        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            echo "\033[1;31m❌ Error: Composer dependencies not installed.\033[0m\n";
            echo "Run: composer install\n";
            exit(1);
        }
        
        if (!file_exists(__DIR__ . '/websocket_server.php')) {
            echo "\033[1;31m❌ Error: WebSocket server file not found.\033[0m\n";
            exit(1);
        }
        
        echo "✅ Dependencies check passed\n\n";
    }
    
    private function checkPorts() {
        echo "🔍 Checking ports...\n";
        
        if ($this->isPortInUse($this->webServerPort)) {
            echo "⚠️  Port {$this->webServerPort} is already in use.\n";
            if ($this->askYesNo("Kill existing process and continue?")) {
                $this->killProcessOnPort($this->webServerPort);
            } else {
                exit(1);
            }
        }
        
        if ($this->isPortInUse($this->websocketPort)) {
            echo "⚠️  Port {$this->websocketPort} is already in use.\n";
            if ($this->askYesNo("Kill existing process and continue?")) {
                $this->killProcessOnPort($this->websocketPort);
            } else {
                exit(1);
            }
        }
        
        echo "✅ Ports are available\n\n";
    }
    
    private function startServers() {
        echo "🚀 Starting services...\n";
        
        // Start WebSocket server
        echo "💬 Starting WebSocket server on port {$this->websocketPort}...\n";
        $this->startWebSocketServer();
        
        if (isset($this->processes['websocket'])) {
            echo "   WebSocket PID: {$this->processes['websocket']}\n";
        }
        
        sleep(3); // Give it more time to start
        
        // Start web application server
        echo "🌐 Starting web application server on port {$this->webServerPort}...\n";
        $this->startWebServer();
        
        if (isset($this->processes['webserver'])) {
            echo "   Web Server PID: {$this->processes['webserver']}\n";
        }
        
        sleep(3); // Give it more time to start
        
        echo "\n";
    }
    
    private function startWebSocketServer() {
        $logFile = $this->logDir . '/websocket.log';
        $projectRoot = dirname(__DIR__);
        $serverScript = __DIR__ . "/websocket_server.php";
        
        // Change to project root directory and start WebSocket server
        $command = "cd '$projectRoot' && php '$serverScript' > '$logFile' 2>&1 & echo $!";
        
        $pid = trim(shell_exec($command));
        if ($pid && is_numeric($pid)) {
            $this->processes['websocket'] = (int)$pid;
        } else {
            echo "❌ Failed to start WebSocket server\n";
        }
    }
    
    private function startWebServer() {
        $logFile = $this->logDir . '/app.log';
        $projectRoot = dirname(__DIR__);
        $publicDir = $projectRoot . '/public';
        
        // Change to project root directory and start web server
        $command = "cd '$projectRoot' && php -S localhost:{$this->webServerPort} -t '$publicDir' > '$logFile' 2>&1 & echo $!";
        
        $pid = trim(shell_exec($command));
        if ($pid && is_numeric($pid)) {
            $this->processes['webserver'] = (int)$pid;
        } else {
            echo "❌ Failed to start web server\n";
        }
    }
    
    private function printStatus() {
        echo "\033[1;32m"; // Green color
        echo "========================================\n";
        echo "  🚀 Sandawatha is now running!\n";
        echo "========================================\033[0m\n\n";
        
        echo "📱 \033[1;34mWeb Application:\033[0m http://localhost:{$this->webServerPort}\n";
        echo "💬 \033[1;34mWebSocket Server:\033[0m ws://localhost:{$this->websocketPort}\n\n";
        
        echo "📝 \033[1;33mLogs:\033[0m\n";
        echo "   App logs: tail -f {$this->logDir}/app.log\n";
        echo "   WebSocket logs: tail -f {$this->logDir}/websocket.log\n\n";
        
        echo "💡 \033[1;36mPress Ctrl+C to stop all servers\033[0m\n\n";
        
        // Verify servers are running
        sleep(1);
        $this->verifyServers();
    }
    
    private function verifyServers() {
        $webRunning = $this->isPortInUse($this->webServerPort);
        $wsRunning = $this->isPortInUse($this->websocketPort);
        
        echo "🔍 Server Status:\n";
        echo "   Web Server: " . ($webRunning ? "\033[1;32m✅ Running\033[0m" : "\033[1;31m❌ Failed\033[0m") . "\n";
        echo "   WebSocket:  " . ($wsRunning ? "\033[1;32m✅ Running\033[0m" : "\033[1;31m❌ Failed\033[0m") . "\n\n";
        
        if (!$webRunning || !$wsRunning) {
            echo "\033[1;31m⚠️  Some servers failed to start. Check the logs for details.\033[0m\n";
        }
    }
    
    private function waitForShutdown() {
        // Register signal handlers if available
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, [$this, 'shutdown']);
            pcntl_signal(SIGTERM, [$this, 'shutdown']);
        }
        
        $consecutiveFailures = 0;
        
        // Keep the script running
        while (true) {
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
            
            sleep(2); // Check every 2 seconds
            
            // Check if processes are still running
            $runningProcesses = 0;
            foreach ($this->processes as $name => $pid) {
                if ($this->isProcessRunning($pid)) {
                    $runningProcesses++;
                } else {
                    echo "\033[1;31m⚠️  $name server (PID: $pid) has stopped unexpectedly\033[0m\n";
                    unset($this->processes[$name]);
                }
            }
            
            if (empty($this->processes)) {
                echo "All servers have stopped.\n";
                break;
            }
            
            // If some processes are missing, show current status
            if ($runningProcesses < 2 && $runningProcesses > 0) {
                $consecutiveFailures++;
                if ($consecutiveFailures >= 3) {
                    echo "\033[1;33m⚠️  Not all servers are running. Current status:\033[0m\n";
                    $this->verifyServers();
                    $consecutiveFailures = 0;
                }
            } else {
                $consecutiveFailures = 0;
            }
        }
    }
    
    public function shutdown($signal = null) {
        echo "\n\n🛑 Shutting down services...\n";
        
        foreach ($this->processes as $name => $pid) {
            echo "   Stopping $name (PID: $pid)...\n";
            
            if (function_exists('posix_kill')) {
                posix_kill($pid, SIGTERM);
            } else {
                // Fallback for systems without posix extension
                if (PHP_OS_FAMILY === 'Windows') {
                    exec("taskkill /PID $pid /F 2>NUL");
                } else {
                    exec("kill -TERM $pid 2>/dev/null");
                }
            }
        }
        
        // Wait a moment for graceful shutdown
        sleep(2);
        
        // Force kill if still running
        foreach ($this->processes as $name => $pid) {
            if ($this->isProcessRunning($pid)) {
                echo "   Force stopping $name (PID: $pid)...\n";
                
                if (function_exists('posix_kill')) {
                    posix_kill($pid, SIGKILL);
                } else {
                    // Fallback for systems without posix extension
                    if (PHP_OS_FAMILY === 'Windows') {
                        exec("taskkill /PID $pid /F 2>NUL");
                    } else {
                        exec("kill -9 $pid 2>/dev/null");
                    }
                }
            }
        }
        
        echo "✅ All services stopped.\n";
        exit(0);
    }
    
    private function isPortInUse($port) {
        $connection = @fsockopen('localhost', $port, $errno, $errstr, 1);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
        return false;
    }
    
    private function killProcessOnPort($port) {
        if (PHP_OS_FAMILY === 'Windows') {
            $command = "for /f \"tokens=5\" %a in ('netstat -aon ^| findstr :$port') do taskkill /f /pid %a";
        } else {
            $command = "lsof -ti :$port | xargs kill -9 2>/dev/null";
        }
        shell_exec($command);
        echo "🔄 Killed existing process on port $port\n";
    }
    
    private function isProcessRunning($pid) {
        if (PHP_OS_FAMILY === 'Windows') {
            $result = shell_exec("tasklist /FI \"PID eq $pid\" 2>NUL | find /I \"$pid\"");
            return !empty($result);
        } else {
            $result = shell_exec("ps -p $pid 2>/dev/null");
            return strpos($result, $pid) !== false;
        }
    }
    
    private function askYesNo($question) {
        echo "$question (y/n): ";
        $handle = fopen("php://stdin", "r");
        $response = trim(fgets($handle));
        fclose($handle);
        return strtolower($response) === 'y' || strtolower($response) === 'yes';
    }
}

// Check if script is run directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $manager = new DevServerManager();
    $manager->start();
} 