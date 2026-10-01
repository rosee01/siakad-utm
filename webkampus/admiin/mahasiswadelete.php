<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? null) !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode permintaan tidak diizinkan.');
}

if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token'])
    || !is_string($_POST['csrf_token'])
    || !is_string($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Permintaan tidak valid. Muat ulang halaman dan coba lagi.');
}

include "koneksi.php";

$nim = $_POST['nim'] ?? '';

if (is_string($nim) && $nim !== '') {
    $stmt = $koneksi->prepare("DELETE FROM tblmhs2 WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $resultdelete = $stmt->execute();
} else {
    $resultdelete = false;
}

if ($resultdelete) {
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data mahasiswa berhasil dihapus.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data mahasiswa gagal dihapus.'];
}

header("Location: mahasiswa.php");
exit;
?>
