<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

/* ---------- DATA TABEL ---------- */
$query  = "SELECT * FROM tbljadwalkuliah ORDER BY id_jadwal ASC";
$result = mysqli_query($koneksi, $query);

$currentPage = 'jadwal';
$page_title  = 'Jadwal Kuliah';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-calendar-alt me-2" style="color:var(--primary)"></i>Data Jadwal Kuliah</h3>
    <a href="jadwalkuliahAdd.php" class="btn-app">
      <i class="fas fa-plus-circle"></i> Tambah Jadwal
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th>ID Jadwal</th>
          <th>Mata Kuliah</th>
          <th>Dosen</th>
          <th>Hari</th>
          <th>Jam Mulai</th>
          <th>Jam Selesai</th>
          <th>Semester</th>
          <th>Ruang</th>
          <th>Kelas</th>
          <th style="width:170px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="11" style="text-align:center; padding:24px; color:var(--muted);">Tidak ada data jadwal kuliah.</td></tr>
        <?php endif; ?>
        <?php $no = 1; while ($data = mysqli_fetch_array($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><strong><?= htmlspecialchars($data['id_jadwal']); ?></strong></td>
            <td><?= htmlspecialchars($data['matakuliah']); ?></td>
            <td><?= htmlspecialchars($data['dosen']); ?></td>
            <td><span class="badge-app green"><?= htmlspecialchars($data['hari']); ?></span></td>
            <td><?= htmlspecialchars($data['jam_mulai']); ?></td>
            <td><?= htmlspecialchars($data['jam_selesai']); ?></td>
            <td><?= htmlspecialchars($data['semester']); ?></td>
            <td><?= htmlspecialchars($data['ruang']); ?></td>
            <td><?= htmlspecialchars($data['kelas']); ?></td>
            <td>
              <a href="jadwalkuliahEdit.php?id_jadwal=<?= urlencode($data['id_jadwal']); ?>" class="btn-app btn-sm-app outline">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a href="jadwalkuliahDelete.php?id_jadwal=<?= urlencode($data['id_jadwal']); ?>" class="btn-app btn-sm-app danger"
                 onclick="return confirm('Yakin ingin menghapus jadwal ini?')">
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