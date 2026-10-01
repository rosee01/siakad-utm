<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$kode_prodi = $_POST['kode_prodi'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

if (is_string($kode_prodi) && $kode_prodi !== '') {
    $stmt = $koneksi->prepare("DELETE FROM tblprodi WHERE kode_prodi = ?");
    $stmt->bind_param("s", $kode_prodi);
    $result = $stmt->execute();
} else {
    $result = false;
}

$_SESSION['flash'] = [
    'type' => $result ? 'success' : 'error',
    'message' => $result ? 'Data prodi berhasil dihapus.' : 'Data gagal dihapus. Mungkin masih digunakan oleh mahasiswa atau kelas.',
];
header("Location: prodi.php", true, 303);
exit;
?>