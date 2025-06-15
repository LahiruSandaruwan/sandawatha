<?php
$currentUser = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$currentUserName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/sandawatha">Sandawatha.lk</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/sandawatha/dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/sandawatha/browse">Browse</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/sandawatha/messages">Messages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/sandawatha/favorites">Favorites</a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <?php if ($currentUser): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($currentUserName) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/sandawatha/profile">My Profile</a></li>
                            <li><a class="dropdown-item" href="/sandawatha/profile/edit">Edit Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/sandawatha/logout">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/sandawatha/login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/sandawatha/register">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 