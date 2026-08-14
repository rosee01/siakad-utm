<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

/* ---------- DATA TABEL ---------- */
$query  = "SELECT * FROM tblkrs ORDER BY id_krs ASC";
$result = mysqli_query($koneksi, $query);

$currentPage = 'krs';
$page_title  = 'Data KRS';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-file-signature me-2" style="color:var(--primary)"></i>Data KRS</h3>
    <a href="krsadd.php" class="btn-app">
      <i class="fas fa-plus-circle"></i> Tambah KRS
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th>ID KRS</th>
          <th>NIDN</th>
          <th>NIM</th>
          <th>ID Jadwal</th>
          <th>Semester</th>
          <th>Tahun Ajaran</th>
          <th style="width:170px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="8" style="text-align:center; padding:24px; color:var(--muted);">Tidak ada data KRS.</td></tr>
        <?php endif; ?>
        <?php $no = 1; while ($data = mysqli_fetch_array($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><strong><?= htmlspecialchars($data['id_krs']); ?></strong></td>
            <td><?= htmlspecialchars($data['nidn']); ?></td>
            <td><span class="badge-app green"><?= htmlspecialchars($data['nim']); ?></span></td>
            <td><?= htmlspecialchars($data['id_jadwal']); ?></td>
            <td><?= htmlspecialchars($data['semester']); ?></td>
            <td><?= htmlspecialchars($data['tahun_ajaran']); ?></td>
            <td>
              <a href="krsEdit.php?id_krs=<?= urlencode($data['id_krs']); ?>" class="btn-app btn-sm-app outline">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a href="krsdelete.php?id_krs=<?= urlencode($data['id_krs']); ?>" class="btn-app btn-sm-app danger"
                 onclick="return confirm('Yakin hapus data KRS ini?')">
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