<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include "koneksi.php";

$id_jadwal = $_GET['id_jadwal'] ?? '';

if ($id_jadwal !== '') {
    $stmt = $koneksi->prepare("DELETE FROM tbljadwalkuliah WHERE id_jadwal = ?");
    $stmt->bind_param("i", $id_jadwal);
    $resultdelete = $stmt->execute();
} else {
    $resultdelete = false;
}

if ($resultdelete) {
    echo "<script>alert('Data berhasil dihapus'); window.location.href='jadwalkuliah.php';</script>";
} else {
    echo "<script>alert('Data gagal dihapus'); window.location.href='jadwalkuliah.php';</script>";
}
?>
