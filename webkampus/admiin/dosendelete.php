<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include "koneksi.php";

$nidn = $_GET['nidn'] ?? '';

if ($nidn !== '') {
    $stmt = $koneksi->prepare("DELETE FROM tbldosen WHERE nidn = ?");
    $stmt->bind_param("s", $nidn);
    $resultdelete = $stmt->execute();
} else {
    $resultdelete = false;
}

if ($resultdelete) {
    echo "<script>alert('Data berhasil dihapus'); window.location.href='dosen.php';</script>";
} else {
    echo "<script>alert('Data gagal dihapus'); window.location.href='dosen.php';</script>";
}
?>
