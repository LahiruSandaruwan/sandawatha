#!/bin/bash

# Sandawatha Development Environment Startup Script
# This script starts both the PHP application server and WebSocket server

echo "========================================"
echo "  Sandawatha Dating App - Dev Server"
echo "========================================"
echo ""

# Change to the project directory
cd "$(dirname "$0")"

# Function to check if a port is in use
check_port() {
    lsof -i :$1 > /dev/null 2>&1
    return $?
}

# Function to kill process on port
kill_port() {
    local port=$1
    echo "Killing existing process on port $port..."
    lsof -ti :$port | xargs kill -9 2>/dev/null
}

# Check dependencies
echo "Checking dependencies..."
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install
    if [ $? -ne 0 ]; then
        echo "Error: Failed to install composer dependencies."
        exit 1
    fi
fi

# Check for existing processes
if check_port 8000; then
    echo "Port 8000 is already in use."
    read -p "Kill existing process and continue? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        kill_port 8000
    else
        echo "Aborted."
        exit 1
    fi
fi

if check_port 8080; then
    echo "Port 8080 is already in use."
    read -p "Kill existing process and continue? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        kill_port 8080
    else
        echo "Aborted."
        exit 1
    fi
fi

echo ""
echo "Starting services..."
echo ""

# Start WebSocket server in background
echo "Starting WebSocket server on port 8080..."
php bin/websocket_server.php > logs/websocket.log 2>&1 &
WEBSOCKET_PID=$!

# Wait a moment for WebSocket server to start
sleep 2

# Start PHP application server in background
echo "Starting PHP application server on port 8000..."
php -S localhost:8000 -t public > logs/app.log 2>&1 &
APP_PID=$!

# Wait a moment for app server to start
sleep 2

echo ""
echo "========================================"
echo "  🚀 Sandawatha is now running!"
echo "========================================"
echo ""
echo "📱 Web Application: http://localhost:8000"
echo "💬 WebSocket Server: ws://localhost:8080"
echo ""
echo "📝 Logs:"
echo "   App logs: tail -f logs/app.log"
echo "   WebSocket logs: tail -f logs/websocket.log"
echo ""
echo "To stop the servers, press Ctrl+C"
echo ""

# Function to cleanup on exit
cleanup() {
    echo ""
    echo "Shutting down services..."
    kill $APP_PID 2>/dev/null
    kill $WEBSOCKET_PID 2>/dev/null
    echo "Services stopped."
    exit 0
}

# Set trap to cleanup on script exit
trap cleanup SIGINT SIGTERM

# Wait for both processes
wait 