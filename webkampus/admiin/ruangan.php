<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$query  = "SELECT * FROM tbl_ruangan ORDER BY kode_ruangan ASC";
$result = mysqli_query($koneksi, $query);

$currentPage = 'ruangan';
$page_title  = 'Data Ruangan';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-door-closed me-2" style="color:var(--primary)"></i>Data Ruangan</h3>
    <a href="ruanganAdd.php" class="btn-app">
      <i class="fas fa-plus-circle"></i> Tambah Ruangan
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th>Kode Ruangan</th>
          <th>Nama Ruangan</th>
          <th style="width:170px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="4" style="text-align:center; padding:24px; color:var(--muted);">Tidak ada data ruangan.</td></tr>
        <?php endif; ?>
        <?php $no = 1; while ($r = mysqli_fetch_array($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><span class="badge-app green"><?= htmlspecialchars($r['kode_ruangan']); ?></span></td>
            <td><strong><?= htmlspecialchars($r['nama_ruangan']); ?></strong></td>
            <td>
              <a href="ruanganEdit.php?kode_ruangan=<?= urlencode($r['kode_ruangan']); ?>" class="btn-app btn-sm-app outline">
                <i class="fas fa-edit"></i> Edit
              </a>
              <form method="post" action="ruangandelete.php" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                <?= csrf_field() ?>
                <input type="hidden" name="kode_ruangan" value="<?= htmlspecialchars($r['kode_ruangan'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
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