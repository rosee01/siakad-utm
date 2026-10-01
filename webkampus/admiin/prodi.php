<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$query  = "SELECT * FROM tblprodi ORDER BY kode_prodi ASC";
$result = mysqli_query($koneksi, $query);

$currentPage = 'prodi';
$page_title  = 'Data Prodi';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-book me-2" style="color:var(--primary)"></i>Data Program Studi</h3>
    <a href="prodiadd.php" class="btn-app">
      <i class="fas fa-plus-circle"></i> Tambah Prodi
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th>Kode Prodi</th>
          <th>Nama Prodi</th>
          <th style="width:170px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="4" style="text-align:center; padding:24px; color:var(--muted);">Tidak ada data prodi.</td></tr>
        <?php endif; ?>
        <?php $no = 1; while ($p = mysqli_fetch_array($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><span class="badge-app green"><?= htmlspecialchars($p['kode_prodi']); ?></span></td>
            <td><strong><?= htmlspecialchars($p['nama_prodi']); ?></strong></td>
            <td>
              <a href="prodiEdit.php?kode_prodi=<?= urlencode($p['kode_prodi']); ?>" class="btn-app btn-sm-app outline">
                <i class="fas fa-edit"></i> Edit
              </a>
              <form method="post" action="prodidelete.php" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                <?= csrf_field() ?>
                <input type="hidden" name="kode_prodi" value="<?= htmlspecialchars($p['kode_prodi'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <button type="submit" class="btn-app btn-sm-app danger"><i class="fas fa-trash-alt"></i> Hapus</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>