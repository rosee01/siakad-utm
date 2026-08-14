<?php
include "koneksi.php";
$id_jadwal = $_GET['id_jadwal'];
$queryDelete = "DELETE FROM tbljadwalkuliah WHERE id_jadwal='$id_jadwal'";
$resultdelete = mysqli_query($koneksi, $queryDelete);
if ($resultdelete){
    echo "<script>alert('Data berhasil dihapus'); window.location.href='jadwalkuliah.php';</script>";
} else{
     echo "<script>alert('Data gagal dihapus'); window.location.href=jadwalkuliah.php';</script>";
}
?>