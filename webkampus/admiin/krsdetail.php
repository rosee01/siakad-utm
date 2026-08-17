<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

/* ---------- DATA TABEL ---------- */
$query  = "SELECT * FROM tblkrsdetail ORDER BY id_krsdetail ASC";
$result = mysqli_query($koneksi, $query);

$currentPage = 'krsdetail';
$page_title  = 'KRS Detail';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-file-alt me-2" style="color:var(--primary)"></i>Data KRS Detail</h3>
    <a href="krsdetailAdd.php" class="btn-app">
      <i class="fas fa-plus-circle"></i> Tambah KRS Detail
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th>ID KRS Detail</th>
          <th>ID KRS</th>
          <th>NIM</th>
          <th>Kode MK</th>
          <th style="width:170px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="6" style="text-align:center; padding:24px; color:var(--muted);">Tidak ada data KRS Detail.</td></tr>
        <?php endif; ?>
        <?php $no = 1; while ($datakrs = mysqli_fetch_array($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><strong><?= htmlspecialchars($datakrs['id_krsdetail']); ?></strong></td>
            <td><?= htmlspecialchars($datakrs['id_krs'] ?? '-'); ?></td>
            <td><span class="badge-app green"><?= htmlspecialchars($datakrs['nim']); ?></span></td>
            <td><span class="badge-app"><?= htmlspecialchars($datakrs['kode_mk']); ?></span></td>
            <td>
              <a href="krsdetailEdit.php?id_krsdetail=<?= urlencode($datakrs['id_krsdetail']); ?>" class="btn-app btn-sm-app outline">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a href="krsdetaildelete.php?id_krsdetail=<?= urlencode($datakrs['id_krsdetail']); ?>" class="btn-app btn-sm-app danger"
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