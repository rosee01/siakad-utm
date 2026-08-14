<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$nim = $_SESSION['nim'] ?? '';
if (empty($nim)) {
    die("NIM tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester     = $_POST['semester'] ?? '';
    $tahun_ajaran = $_POST['tahun_ajaran'] ?? '';
    $matkul       = $_POST['matkul'] ?? [];

    if (empty($matkul)) {
        echo "<script>alert('Anda belum memilih mata kuliah.'); window.location='isikrs.php';</script>";
        exit;
    }

    $query_krs = "INSERT INTO tblkrs (nim, semester, tahun_ajaran) VALUES (?, ?, ?)";
    $stmt_krs  = mysqli_prepare($koneksi, $query_krs);
    mysqli_stmt_bind_param($stmt_krs, 'sss', $nim, $semester, $tahun_ajaran);

    if (mysqli_stmt_execute($stmt_krs)) {
        $id_krs = mysqli_insert_id($koneksi);

        $query_detail = "INSERT INTO tblkrsdetail (id_krs, kode_mk) VALUES (?, ?)";
        $stmt_detail  = mysqli_prepare($koneksi, $query_detail);

        foreach ($matkul as $kode_mk) {
            mysqli_stmt_bind_param($stmt_detail, 'is', $id_krs, $kode_mk);
            mysqli_stmt_execute($stmt_detail);
        }

        echo "<script>alert('KRS berhasil disimpan!'); window.location='krs.php';</script>";
        exit;
    } else {
        die("Gagal menyimpan KRS: " . mysqli_error($koneksi));
    }
} else {
    header("Location: isikrs.php");
    exit;
}