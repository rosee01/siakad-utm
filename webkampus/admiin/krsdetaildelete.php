<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include "koneksi.php";

$pesan = "";

if (isset($_GET['id_krsdetail'])) {
    $id_krsdetail = $_GET['id_krsdetail'];

    $stmt = $koneksi->prepare("DELETE FROM tblkrsdetail WHERE id_krsdetail = ?");
    $stmt->bind_param("i", $id_krsdetail);
    $result = $stmt->execute();

    if ($result) {
        $pesan = "<div style='color:green;'>Data KRS Detail dengan ID <strong>" . htmlspecialchars($id_krsdetail) . "</strong> berhasil dihapus.</div>";
    } else {
        $pesan = "<div style='color:red;'>Data gagal dihapus: " . htmlspecialchars($koneksi->error) . "</div>";
    }
} else {
    $pesan = "<div style='color:red;'>ID KRS Detail tidak ditemukan di URL.</div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hapus Data KRS Detail</title>
</head>
<body>
    <h2 align="center">HAPUS DATA KRS DETAIL</h2>
    <div align="center">
        <?= $pesan ?><br><br>
        <a href="krsdetail.php">← Kembali ke Data KRS Detail</a>
    </div>
</body>
</html>
