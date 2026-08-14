<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

/* ---------- DATA TABEL ---------- */
$query  = "SELECT * FROM tblmhs2 ORDER BY nim ASC";
$result = mysqli_query($koneksi, $query);

$currentPage = 'mahasiswa';
$page_title  = 'Data Mahasiswa';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-user-graduate me-2" style="color:var(--primary)"></i>Data Mahasiswa</h3>
    <a href="mahasiswaAdd.php" class="btn-app">
      <i class="fas fa-plus-circle"></i> Tambah Mahasiswa
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th>NIM</th>
          <th>Nama</th>
          <th>Prodi</th>
          <th style="width:90px">Semester</th>
          <th style="width:70px">JK</th>
          <th>Alamat</th>
          <th style="width:170px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="8" style="text-align:center; padding:24px; color:var(--muted);">Tidak ada data mahasiswa.</td></tr>
        <?php endif; ?>
        <?php $no = 1; while ($m = mysqli_fetch_array($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><strong><?= htmlspecialchars($m['nim']); ?></strong></td>
            <td><?= htmlspecialchars($m['nama_mhs']); ?></td>
            <td><span class="badge-app green"><?= htmlspecialchars($m['prodi']); ?></span></td>
            <td style="text-align:center"><?= htmlspecialchars($m['semester']); ?></td>
            <td><?= htmlspecialchars($m['jns_kelamin']); ?></td>
            <td><?= htmlspecialchars($m['alamat']); ?></td>
            <td>
              <a href="mahasiswaEdit.php?nim=<?= urlencode($m['nim']); ?>" class="btn-app btn-sm-app outline">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a href="mahasiswadelete.php?nim=<?= urlencode($m['nim']); ?>" class="btn-app btn-sm-app danger"
                 onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                <i class="fas fa-trash-alt"></i> Hapus
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>