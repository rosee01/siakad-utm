<?php
include "koneksi.php";

$pesan = "";

if (isset($_GET['id_krs'])) {
    $id_krs = mysqli_real_escape_string($koneksi, $_GET['id_krs']);
    $query = "DELETE FROM tblkrs WHERE id_krs = '$id_krs'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $pesan = "<div style='color:green;'>Data KRS dengan ID <strong>$id_krs</strong> berhasil dihapus.</div>";
    } else {
        $pesan = "<div style='color:red;'>Data gagal dihapus: " . mysqli_error($koneksi) . "</div>";
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
