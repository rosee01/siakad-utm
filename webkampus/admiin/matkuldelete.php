<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$kode_mk = mysqli_real_escape_string($koneksi, $_GET['kode_mk'] ?? '');

if ($kode_mk !== '') {
    $query  = "DELETE FROM tblmatkul WHERE kode_mk='$kode_mk'";
    $result = mysqli_query($koneksi, $query);
    
    if ($result) {
        // Pakai session flash message (lebih clean daripada alert)
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data mata kuliah berhasil dihapus.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Data gagal dihapus. Mungkin masih digunakan di tabel lain.'];
    }
}

header("Location: matkul.php");
exit;
?>