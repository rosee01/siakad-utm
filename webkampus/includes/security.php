<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
}

function require_valid_csrf_token(): void
{
    $submitted = $_POST['csrf_token'] ?? null;
    $expected = $_SESSION['csrf_token'] ?? null;

    if (
        !is_string($submitted)
        || !is_string($expected)
        || !hash_equals($expected, $submitted)
    ) {
        http_response_code(403);
        exit('Permintaan tidak valid. Muat ulang halaman dan coba lagi.');
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_valid_csrf_token();
}
