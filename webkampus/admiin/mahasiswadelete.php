<?php
include "koneksi.php";
$nim = $_GET['nim'];
$queryDelete = "DELETE FROM tblmhs2 WHERE nim='$nim'";
$resultdelete = mysqli_query($koneksi, $queryDelete);
if ($resultdelete){
    echo "<script>alert('Data berhasil dihapus'); window.location.href='mahasiswa.php';</script>";
} else{
     echo "<script>alert('Data gagal dihapus'); window.location.href='mahasiswa.php';</script>";
}
?>