<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$kode_prodi = mysqli_real_escape_string($koneksi, $_GET['kode_prodi'] ?? '');

if ($kode_prodi !== '') {
    // Cek dulu apakah prodi masih dipakai di tabel lain (opsional, untuk safety)
    $query  = "DELETE FROM tblprodi WHERE kode_prodi='$kode_prodi'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data prodi berhasil dihapus.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Data gagal dihapus. Mungkin masih digunakan oleh mahasiswa atau kelas.'];
    }
}

header("Location: prodi.php");
exit;
?>