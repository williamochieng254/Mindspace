<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function auth_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_auth(): void {
    if (php_sapi_name() === 'cli') {
        return;
    }

    if (!auth_user()) {
        header('Location: login.php');
        exit;
    }
}
