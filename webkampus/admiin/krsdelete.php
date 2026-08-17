<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include "koneksi.php";

$pesan = "";

if (isset($_GET['id_krs'])) {
    $id_krs = $_GET['id_krs'];

    // Hapus dulu detail mata kuliahnya (tblkrsdetail) supaya tidak jadi data
    // yatim (yang nunjuk ke id_krs yang sudah tidak ada).
    $stmt_detail = $koneksi->prepare("DELETE FROM tblkrsdetail WHERE id_krs = ?");
    $stmt_detail->bind_param("i", $id_krs);
    $stmt_detail->execute();

    $stmt = $koneksi->prepare("DELETE FROM tblkrs WHERE id_krs = ?");
    $stmt->bind_param("i", $id_krs);
    $result = $stmt->execute();

    if ($result) {
        $pesan = "<div style='color:green;'>Data KRS dengan ID <strong>" . htmlspecialchars($id_krs) . "</strong> beserta detail mata kuliahnya berhasil dihapus.</div>";
    } else {
        $pesan = "<div style='color:red;'>Data gagal dihapus: " . htmlspecialchars($koneksi->error) . "</div>";
    }
} else {
    $pesan = "<div style='color:red;'>ID KRS tidak ditemukan di URL.</div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hapus Data KRS</title>
</head>
<body>
    <h2 align="center">HAPUS DATA KRS</h2>
    <div align="center">
        <?= $pesan ?><br><br>
        <a href="krs.php">← Kembali ke Data KRS</a>
    </div>
</body>
</html>
