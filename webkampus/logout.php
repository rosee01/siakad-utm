<?php
session_start();
require_once __DIR__ . '/includes/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

session_destroy();
header("Location: login.php");
exit;
?>
