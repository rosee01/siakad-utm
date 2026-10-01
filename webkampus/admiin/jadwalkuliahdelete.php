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

$id_jadwal = $_POST['id_jadwal'] ?? '';
if (is_scalar($id_jadwal) && ctype_digit((string) $id_jadwal) && (int) $id_jadwal > 0) {
    $stmt = $koneksi->prepare("DELETE FROM tbljadwalkuliah WHERE id_jadwal = ?");
    $id_jadwal = (int) $id_jadwal;
    $stmt->bind_param("i", $id_jadwal);
    $resultdelete = $stmt->execute();
} else {
    $resultdelete = false;
}

$_SESSION['flash'] = [
    'type' => $resultdelete ? 'success' : 'error',
    'message' => $resultdelete ? 'Jadwal berhasil dihapus.' : 'Jadwal gagal dihapus.',
];
header("Location: jadwalkuliah.php", true, 303);
exit;
?>
