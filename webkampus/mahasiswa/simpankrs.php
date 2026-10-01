<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Gunakan formulir KRS yang terbaru.');
}
header('Location: buatkrs.php', true, 303);
exit;