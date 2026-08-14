<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$id_user = mysqli_real_escape_string($koneksi, $_GET['id_user'] ?? '');

if ($id_user !== '') {
    $query  = "DELETE FROM user WHERE id_user = ?";
    $stmt   = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $id_user);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'User berhasil dihapus.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'User gagal dihapus.'];
    }
}

header("Location: user.php");
exit;
?>