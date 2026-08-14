<?php
session_start();

// Periksa apakah pengguna sudah login
if (isset($_SESSION['username'])) {
    // Jika sudah login, arahkan ke halaman profil mahasiswa (misalnya 'mahasiswa.php')
    header("Location: login.php");
    exit;
} else {
    // Jika belum login, tampilkan halaman login (file 'login.php')
    include "login.php";
}
?>
