<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

/* ---------- DATA TABEL ---------- */
$query  = "SELECT * FROM tbldosen ORDER BY nidn ASC";
$result = mysqli_query($koneksi, $query);

$currentPage = 'dosen';
$page_title  = 'Data Dosen';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-chalkboard-teacher me-2" style="color:var(--primary)"></i>Data Dosen</h3>
    <a href="dosenAdd.php" class="btn-app">
      <i class="fas fa-plus-circle"></i> Tambah Dosen
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th>NIDN</th>
          <th>Nama Dosen</th>
          <th>Email</th>
          <th>Jenis Kelamin</th>
          <th>Telepon</th>
          <th style="width:170px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="7" style="text-align:center; padding:24px; color:var(--muted);">Tidak ada data dosen.</td></tr>
        <?php endif; ?>
        <?php $no = 1; while ($d = mysqli_fetch_array($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><strong><?= htmlspecialchars($d['nidn']); ?></strong></td>
            <td><?= htmlspecialchars($d['nama_dosen']); ?></td>
            <td><?= htmlspecialchars($d['email']); ?></td>
            <td><?= htmlspecialchars($d['jns_kelamin']); ?></td>
            <td><?= htmlspecialchars($d['telpn']); ?></td>
            <td>
              <a href="dosenEdit.php?nidn=<?= urlencode($d['nidn']); ?>" class="btn-app btn-sm-app outline">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a href="dosendelete.php?nidn=<?= urlencode($d['nidn']); ?>" class="btn-app btn-sm-app danger"
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