<?php
include "koneksi.php";
$kode_kelas = $_GET['kode_kelas'];
$queryDelete = "DELETE FROM tbl_kelas WHERE kode_kelas='$kode_kelas'";
$resultdelete = mysqli_query($koneksi, $queryDelete);
if ($resultdelete){
    echo "<script>alert('Data berhasil dihapus'); window.location.href='kelas.php';</script>";
} else{
     echo "<script>alert('Data gagal dihapus'); window.location.href='kelas.php';</script>";
}
?>