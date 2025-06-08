<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark' : 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Dashboard - Sandawatha.lk' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    
    <!-- Meta tags -->
    <meta name="description" content="Admin Dashboard for Sandawatha.lk">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/admin">
                <i class="bi bi-speedometer2 text-primary"></i>
                Admin Panel
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/users">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/feedback">
                            <i class="bi bi-chat-square-text"></i> Feedback
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/messages">
                            <i class="bi bi-envelope"></i> Messages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/settings">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <!-- Dark Mode Toggle -->
                    <li class="nav-item">
                        <button class="btn btn-link nav-link" onclick="toggleDarkMode()" title="Toggle Dark Mode">
                            <i class="bi bi-moon-fill" id="darkModeIcon"></i>
                        </button>
                    </li>
                    
                    <!-- View Site -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> View Site
                        </a>
                    </li>
                    
                    <!-- Admin Profile Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/dashboard">
                                <i class="bi bi-speedometer2"></i> User Dashboard
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] === 'error' ? 'danger' : $_SESSION['flash_type'] ?> alert-dismissible fade show m-0" role="alert" style="margin-top: 56px !important;">
            <div class="container">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <h5><i class="bi bi-heart-fill text-danger"></i> Sandawatha.lk</h5>
            <p class="text-muted small">Admin Control Panel</p>
        </div>
        
        <div class="sidebar-menu">
            <a href="<?= BASE_URL ?>/admin" class="sidebar-link <?= strpos($_SERVER['REQUEST_URI'], '/admin') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/') === false ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="<?= BASE_URL ?>/admin/users" class="sidebar-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false ? 'active' : '' ?>">
                <i class="bi bi-people"></i> User Management
            </a>
            <a href="<?= BASE_URL ?>/admin/feedback" class="sidebar-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/feedback') !== false ? 'active' : '' ?>">
                <i class="bi bi-chat-square-text"></i> Feedback
            </a>
            <a href="<?= BASE_URL ?>/admin/messages" class="sidebar-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/messages') !== false ? 'active' : '' ?>">
                <i class="bi bi-envelope"></i> Message Center
            </a>
            <a href="<?= BASE_URL ?>/admin/settings" class="sidebar-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false ? 'active' : '' ?>">
                <i class="bi bi-gear"></i> Site Settings
            </a>
            
            <hr class="my-3">
            
            <a href="<?= BASE_URL ?>/browse" class="sidebar-link" target="_blank">
                <i class="bi bi-search"></i> Browse Profiles
            </a>
            <a href="<?= BASE_URL ?>/dashboard" class="sidebar-link">
                <i class="bi bi-house"></i> User Dashboard
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-content">
        <?php include SITE_ROOT . '/app/views/' . $content_view . '.php'; ?>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    
    <!-- Page specific scripts -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= BASE_URL ?>/assets/js/<?= $script ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Update dark mode icon
        updateDarkModeIcon();
        
        // Auto refresh dashboard data every 5 minutes
        if (window.location.pathname.includes('/admin') && !window.location.pathname.includes('/admin/')) {
            setInterval(function() {
                location.reload();
            }, 300000); // 5 minutes
        }
    </script>
</body>
</html>