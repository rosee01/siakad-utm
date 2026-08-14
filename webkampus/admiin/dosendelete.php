<?php
include "koneksi.php";
$nidn = $_GET['nidn'];
$queryDelete = "DELETE FROM tbldosen WHERE nidn='$nidn'";
$resultdelete = mysqli_query($koneksi, $queryDelete);
if ($resultdelete){
    echo "<script>alert('Data berhasil dihapus'); window.location.href='dosen.php';</script>";
} else{
     echo "<script>alert('Data gagal dihapus'); window.location.href='dosen.php';</script>";
}
?>