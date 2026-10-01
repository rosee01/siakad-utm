<?php
require_once __DIR__ . '/../includes/security.php';

$host = "localhost";
$nama ="root";
$pass ="";
$db ="db_kampus";

$koneksi = mysqli_connect($host, $nama, $pass, $db);
?>