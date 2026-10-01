<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

/* ---------- DATA TABEL ---------- */
$query  = "SELECT * FROM tbl_kelas ORDER BY kode_kelas ASC";
$result = mysqli_query($koneksi, $query);

$currentPage = 'kelas';
$page_title  = 'Data Kelas';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-chalkboard me-2" style="color:var(--primary)"></i>Data Kelas</h3>
    <a href="kelasAdd.php" class="btn-app">
      <i class="fas fa-plus-circle"></i> Tambah Kelas
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th>Kode Kelas</th>
          <th>Nama Kelas</th>
          <th>Kode Prodi</th>
          <th style="width:170px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="5" style="text-align:center; padding:24px; color:var(--muted);">Tidak ada data kelas.</td></tr>
        <?php endif; ?>
        <?php $no = 1; while ($data = mysqli_fetch_array($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><strong><?= htmlspecialchars($data['kode_kelas']); ?></strong></td>
            <td><?= htmlspecialchars($data['nama_kelas']); ?></td>
            <td><span class="badge-app green"><?= htmlspecialchars($data['kode_prodi']); ?></span></td>
            <td>
              <a href="kelasEdit.php?kode_kelas=<?= urlencode($data['kode_kelas']); ?>" class="btn-app btn-sm-app outline">
                <i class="fas fa-edit"></i> Edit
              </a>
              <form method="post" action="kelasdelete.php" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                <?= csrf_field() ?>
                <input type="hidden" name="kode_kelas" value="<?= htmlspecialchars($data['kode_kelas'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
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