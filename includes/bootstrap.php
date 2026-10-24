<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (getenv('SESSION_DRIVER') === 'database') {
        require_once __DIR__ . '/database_session_handler.php';
        session_set_save_handler(new DatabaseSessionHandler($conn), true);
    }

    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https',
    ]);
    session_start();
}
