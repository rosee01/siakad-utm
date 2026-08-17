<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include "koneksi.php";

$kode_kelas = $_GET['kode_kelas'] ?? '';

if ($kode_kelas !== '') {
    $stmt = $koneksi->prepare("DELETE FROM tbl_kelas WHERE kode_kelas = ?");
    $stmt->bind_param("s", $kode_kelas);
    $resultdelete = $stmt->execute();
} else {
    $resultdelete = false;
}

if ($resultdelete) {
    echo "<script>alert('Data berhasil dihapus'); window.location.href='kelas.php';</script>";
} else {
    echo "<script>alert('Data gagal dihapus'); window.location.href='kelas.php';</script>";
}
?>
