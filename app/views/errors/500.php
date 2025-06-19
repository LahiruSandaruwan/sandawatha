<?php require_once SITE_ROOT . '/app/views/layouts/main.php'; ?>

<div class="error-container">
    <div class="error-content text-center">
        <img src="<?= BASE_URL ?>/assets/images/500-error.svg" alt="500 Error" class="error-image mb-4">
        <h1 class="error-title">Internal Server Error</h1>
        <p class="error-message mb-4">
            Oops! Something went wrong on our end. We're working to fix it.
            Please try again later or contact support if the problem persists.
        </p>
        <div class="error-actions">
            <a href="<?= BASE_URL ?>/" class="btn btn-primary me-3">
                <i class="bi bi-house-door me-2"></i>
                Go Home
            </a>
            <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-primary">
                <i class="bi bi-envelope me-2"></i>
                Contact Support
            </a>
        </div>
    </div>
</div>

<style>
.error-container {
    min-height: calc(100vh - var(--navbar-height) - 2rem);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.error-content {
    max-width: 600px;
    margin: 0 auto;
}

.error-image {
    max-width: 300px;
    height: auto;
    margin-bottom: 2rem;
}

.error-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 1rem;
}

.error-message {
    font-size: 1.1rem;
    color: var(--gray-600);
    line-height: 1.6;
}

.error-actions {
    margin-top: 2rem;
}

[data-bs-theme="dark"] .error-title {
    color: var(--gray-100);
}

[data-bs-theme="dark"] .error-message {
    color: var(--gray-400);
}
</style> 