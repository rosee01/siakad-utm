<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

/* ---------- DATA TABEL ---------- */
$query  = "SELECT * FROM tblmatkul ORDER BY kode_mk ASC";
$result = mysqli_query($koneksi, $query);

$currentPage = 'matkul';
$page_title  = 'Data Mata Kuliah';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-book-open me-2" style="color:var(--primary)"></i>Data Mata Kuliah</h3>
    <a href="matkulAdd.php" class="btn-app">
      <i class="fas fa-plus-circle"></i> Tambah Mata Kuliah
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th>Kode MK</th>
          <th>Nama Mata Kuliah</th>
          <th style="width:70px">SKS</th>
          <th style="width:90px">Semester</th>
          <th>Nama Dosen</th>
          <th style="width:100px">Hari</th>
          <th style="width:100px">Jam Mulai</th>
          <th style="width:100px">Jam Selesai</th>
          <th style="width:110px">Ruang</th>
          <th style="width:170px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="10" style="text-align:center; padding:24px; color:var(--muted);">Tidak ada data mata kuliah.</td></tr>
        <?php endif; ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><span class="badge-app green"><?= htmlspecialchars($row['kode_mk']) ?></span></td>
            <td><strong><?= htmlspecialchars($row['nama_mk']) ?></strong></td>
            <td style="text-align:center"><?= htmlspecialchars($row['sks']) ?></td>
            <td style="text-align:center"><?= htmlspecialchars($row['semester']) ?></td>
            <td><?= htmlspecialchars($row['nama_dosen']) ?></td>
            <td><span class="badge-app"><?= htmlspecialchars($row['hari']) ?></span></td>
            <td style="text-align:center"><?= htmlspecialchars($row['jam_mulai']) ?></td>
            <td style="text-align:center"><?= htmlspecialchars($row['jam_selesai']) ?></td>
            <td><span class="badge-app"><?= htmlspecialchars($row['ruang']) ?></span></td>
            <td>
              <a href="matkulEdit.php?kode_mk=<?= urlencode($row['kode_mk']) ?>" class="btn-app btn-sm-app outline">
                <i class="fas fa-edit"></i> Edit
              </a>
              <form method="post" action="matkuldelete.php" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus?')">
                <?= csrf_field() ?>
                <input type="hidden" name="kode_mk" value="<?= htmlspecialchars($row['kode_mk'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
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