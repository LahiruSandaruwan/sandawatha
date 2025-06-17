<?php

use App\Core\Router;

// Auth routes
Router::get('/login', ['AuthController', 'loginForm']);
Router::post('/login', ['AuthController', 'login']);
Router::get('/register', ['AuthController', 'registerForm']);
Router::post('/register', ['AuthController', 'register']);
Router::get('/verify-email', ['AuthController', 'verifyEmail']);
Router::get('/verify-phone', ['AuthController', 'verifyPhoneForm']);
Router::post('/verify-phone', ['AuthController', 'verifyPhone']);
Router::get('/forgot-password', ['AuthController', 'forgotPasswordForm']);
Router::post('/forgot-password', ['AuthController', 'forgotPassword']);
Router::get('/reset-password', ['AuthController', 'resetPasswordForm']);
Router::post('/reset-password', ['AuthController', 'resetPassword']);
Router::get('/logout', ['AuthController', 'logout']);

// Dashboard routes
Router::get('/dashboard', ['DashboardController', 'index']);
Router::get('/profile', ['ProfileController', 'index']);
Router::post('/profile/update', ['ProfileController', 'update']);
Router::post('/profile/photo', ['ProfileController', 'updatePhoto']);

// Chat routes
Router::get('/chat', ['ChatController', 'index']);
Router::post('/chat/send', ['ChatController', 'send']);
Router::get('/chat/messages', ['ChatController', 'getMessages']);

// Admin routes
Router::get('/admin', ['AdminController', 'index']);
Router::get('/admin/users', ['AdminController', 'users']);
Router::post('/admin/user/block', ['AdminController', 'blockUser']);
Router::post('/admin/user/unblock', ['AdminController', 'unblockUser']);

// Home route
Router::get('/', ['HomeController', 'index']);

// 404 handler
Router::notFound(function() {
    http_response_code(404);
    require_once SITE_ROOT . '/app/views/errors/404.php';
});

// Dispatch the route
Router::dispatch($url); 