<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

$nidn = $_POST['nidn'] ?? '';

if (is_string($nidn) && $nidn !== '') {
    $stmt = $koneksi->prepare("DELETE FROM tbldosen WHERE nidn = ?");
    $stmt->bind_param("s", $nidn);
    $resultdelete = $stmt->execute();
} else {
    $resultdelete = false;
}

$_SESSION['flash'] = [
    'type' => $resultdelete ? 'success' : 'error',
    'message' => $resultdelete ? 'Data dosen berhasil dihapus.' : 'Data dosen gagal dihapus.',
];
header("Location: dosen.php", true, 303);
exit;
?>
