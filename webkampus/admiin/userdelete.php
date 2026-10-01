<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$id_user = $_POST['id_user'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

if (is_string($id_user) && $id_user !== '') {
    $query  = "DELETE FROM user WHERE id_user = ?";
    $stmt   = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $id_user);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'User berhasil dihapus.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'User gagal dihapus.'];
    }
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID user tidak valid.'];
}

header("Location: user.php", true, 303);
exit;
?>