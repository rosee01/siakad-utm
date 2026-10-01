<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$kode_ruangan = $_POST['kode_ruangan'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

if (is_string($kode_ruangan) && $kode_ruangan !== '') {
    $stmt = $koneksi->prepare("DELETE FROM tbl_ruangan WHERE kode_ruangan = ?");
    $stmt->bind_param("s", $kode_ruangan);
    $result = $stmt->execute();
} else {
    $result = false;
}

$_SESSION['flash'] = [
    'type' => $result ? 'success' : 'error',
    'message' => $result ? 'Data ruangan berhasil dihapus.' : 'Data gagal dihapus. Mungkin masih digunakan di jadwal kuliah.',
];
header("Location: ruangan.php", true, 303);
exit;
?>