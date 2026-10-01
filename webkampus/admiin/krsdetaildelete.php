<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

$id_krsdetail = $_POST['id_krsdetail'] ?? '';
if (is_scalar($id_krsdetail) && ctype_digit((string) $id_krsdetail) && (int) $id_krsdetail > 0) {
    $id_krsdetail = (int) $id_krsdetail;
    $stmt = $koneksi->prepare("DELETE FROM tblkrsdetail WHERE id_krsdetail = ?");
    $stmt->bind_param("i", $id_krsdetail);
    $result = $stmt->execute();
} else {
    $result = false;
}

$_SESSION['flash'] = [
    'type' => $result ? 'success' : 'error',
    'message' => $result ? 'Data KRS detail berhasil dihapus.' : 'Data KRS detail gagal dihapus.',
];
header("Location: krsdetail.php", true, 303);
exit;
