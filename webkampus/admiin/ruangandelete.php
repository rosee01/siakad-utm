<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$kode_ruangan = mysqli_real_escape_string($koneksi, $_GET['kode_ruangan'] ?? '');

if ($kode_ruangan !== '') {
    $query  = "DELETE FROM tbl_ruangan WHERE kode_ruangan='$kode_ruangan'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data ruangan berhasil dihapus.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Data gagal dihapus. Mungkin masih digunakan di jadwal kuliah.'];
    }
}

header("Location: ruangan.php");
exit;
?>