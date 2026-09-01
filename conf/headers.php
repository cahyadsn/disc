<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header("Content-Security-Policy: default-src 'self';");
}
