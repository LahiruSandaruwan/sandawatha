<?php
// Get current page from constant
$currentPage = defined('CURRENT_PAGE') ? CURRENT_PAGE : 'home';

// Common meta tags
$title = isset($title) ? $title : 'Sandawatha.lk - Sri Lankan Matrimony';
$description = isset($description) ? $description : 'Find your perfect match on Sandawatha.lk - Sri Lanka\'s trusted matrimonial platform';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <title><?php echo htmlspecialchars($title); ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    
    <!-- Base CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Component-specific CSS -->
    <?php if (isset($component_css) && is_array($component_css)): ?>
        <?php foreach ($component_css as $css): ?>
            <link rel="stylesheet" href="/assets/css/<?php echo htmlspecialchars($css); ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="main-header">
        <nav class="main-nav">
            <div class="nav-brand">
                <a href="/">Sandawatha.lk</a>
            </div>
            <ul class="nav-links">
                <li class="<?php echo $currentPage === 'home' ? 'active' : ''; ?>">
                    <a href="/">Home</a>
                </li>
                <li class="<?php echo $currentPage === 'browse' ? 'active' : ''; ?>">
                    <a href="/browse">Browse</a>
                </li>
                <li class="<?php echo $currentPage === 'messages' ? 'active' : ''; ?>">
                    <a href="/messages">Messages</a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <a href="/dashboard">Dashboard</a>
                    </li>
                    <li class="<?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
                        <a href="/profile">Profile</a>
                    </li>
                    <li>
                        <a href="/auth/logout" class="btn-logout">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="<?php echo $currentPage === 'login' ? 'active' : ''; ?>">
                        <a href="/auth/login" class="btn-login">Login</a>
                    </li>
                    <li class="<?php echo $currentPage === 'register' ? 'active' : ''; ?>">
                        <a href="/auth/register" class="btn-register">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main class="main-content">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="flash-message <?php echo $_SESSION['flash_type'] ?? 'info'; ?>">
                <?php 
                echo htmlspecialchars($_SESSION['flash_message']); 
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_type']);
                ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html> 