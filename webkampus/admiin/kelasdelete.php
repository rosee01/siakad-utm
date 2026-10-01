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

$kode_kelas = $_POST['kode_kelas'] ?? '';
if (is_string($kode_kelas) && $kode_kelas !== '') {
    $stmt = $koneksi->prepare("DELETE FROM tbl_kelas WHERE kode_kelas = ?");
    $stmt->bind_param("s", $kode_kelas);
    $resultdelete = $stmt->execute();
} else {
    $resultdelete = false;
}

$_SESSION['flash'] = [
    'type' => $resultdelete ? 'success' : 'error',
    'message' => $resultdelete ? 'Data kelas berhasil dihapus.' : 'Data kelas gagal dihapus.',
];
header("Location: kelas.php", true, 303);
exit;
?>
