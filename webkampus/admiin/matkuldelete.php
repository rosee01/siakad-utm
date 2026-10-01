<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$kode_mk = $_POST['kode_mk'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

if (is_string($kode_mk) && $kode_mk !== '') {
    $stmt = $koneksi->prepare("DELETE FROM tblmatkul WHERE kode_mk = ?");
    $stmt->bind_param("s", $kode_mk);
    $result = $stmt->execute();
} else {
    $result = false;
}

$_SESSION['flash'] = [
    'type' => $result ? 'success' : 'error',
    'message' => $result ? 'Data mata kuliah berhasil dihapus.' : 'Data gagal dihapus. Mungkin masih digunakan di tabel lain.',
];
header("Location: matkul.php", true, 303);
exit;
?>