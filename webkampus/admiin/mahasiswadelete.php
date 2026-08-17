<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include "koneksi.php";

$nim = $_GET['nim'] ?? '';

if ($nim !== '') {
    $stmt = $koneksi->prepare("DELETE FROM tblmhs2 WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $resultdelete = $stmt->execute();
} else {
    $resultdelete = false;
}

if ($resultdelete) {
    echo "<script>alert('Data berhasil dihapus'); window.location.href='mahasiswa.php';</script>";
} else {
    echo "<script>alert('Data gagal dihapus'); window.location.href='mahasiswa.php';</script>";
}
?>
