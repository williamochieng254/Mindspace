<?php
/**
 * Session bootstrap â€” call at the top of every PHP page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function auth_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_auth(): void {
    if (!auth_user()) {
        if (is_api_request()) {
            http_response_code(401);
            die(json_encode(['success' => false, 'error' => 'Not authenticated.']));
        }
        header('Location: /mindspace/login.php');
        exit;
    }
}

function require_admin(): void {
    require_auth();
    if ((auth_user()['role'] ?? '') !== 'admin') {
        if (is_api_request()) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => 'Forbidden.']));
        }
        header('Location: /mindspace/dashboard.php');
        exit;
    }
}

function is_api_request(): bool {
    return (
        (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) ||
        (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) ||
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    );
}

