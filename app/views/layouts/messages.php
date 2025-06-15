<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark' : 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Sandawatha.lk</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Common CSS -->
    <link href="<?= BASE_URL ?>/assets/css/common/common.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/messages/style.css" rel="stylesheet">
    
    <!-- Meta tags -->
    <meta name="base-url" content="<?= BASE_URL ?>">
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
</head>
<body data-user-id="<?= htmlspecialchars($_SESSION['user_id'] ?? '') ?>"
      data-user-first-name="<?= htmlspecialchars($_SESSION['first_name'] ?? '') ?>"
      data-user-last-name="<?= htmlspecialchars($_SESSION['last_name'] ?? '') ?>">
    
    <?php include SITE_ROOT . '/app/views/partials/navbar.php'; ?>
    
    <main class="container-fluid">
        <?php include SITE_ROOT . '/app/views/' . $content_view . '.php'; ?>
    </main>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Scripts -->
    <script src="<?= BASE_URL ?>/assets/js/chat/connected-users.js"></script>
    <?php include SITE_ROOT . '/app/views/partials/chat-scripts.php'; ?>
</body>
</html> 