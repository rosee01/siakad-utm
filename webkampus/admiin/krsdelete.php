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

$id_krs = $_POST['id_krs'] ?? '';
if (is_scalar($id_krs) && ctype_digit((string) $id_krs) && (int) $id_krs > 0) {
    $id_krs = (int) $id_krs;
    try {
        $koneksi->begin_transaction();
        $stmt_detail = $koneksi->prepare("DELETE FROM tblkrsdetail WHERE id_krs = ?");
        $stmt_detail->bind_param("i", $id_krs);
        $stmt_detail->execute();

        $stmt = $koneksi->prepare("DELETE FROM tblkrs WHERE id_krs = ?");
        $stmt->bind_param("i", $id_krs);
        $stmt->execute();
        $koneksi->commit();
        $result = true;
    } catch (mysqli_sql_exception $exception) {
        $koneksi->rollback();
        error_log('Gagal menghapus KRS: ' . $exception->getMessage());
        $result = false;
    }
} else {
    $result = false;
}

$_SESSION['flash'] = [
    'type' => $result ? 'success' : 'error',
    'message' => $result ? 'Data KRS beserta detailnya berhasil dihapus.' : 'Data KRS gagal dihapus.',
];
header("Location: krs.php", true, 303);
exit;
