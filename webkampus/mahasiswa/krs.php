<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$nim = $_SESSION['nim'] ?? '';
if (empty($nim)) die("NIM tidak ditemukan.");

$query = "SELECT k.id_krs, k.nim, k.semester, k.tahun_ajaran, d.nama_dosen, j.kelas AS nama_kelas,
                 (SELECT COUNT(*) FROM tblkrsdetail kd WHERE kd.id_krs = k.id_krs) AS jumlah_mk
          FROM tblkrs k
          LEFT JOIN tbldosen d ON k.nidn = d.nidn
          LEFT JOIN tbljadwalkuliah j ON k.id_jadwal = j.id_jadwal
          WHERE k.nim = ?
          ORDER BY k.tahun_ajaran DESC, k.semester DESC";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 's', $nim);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$currentPage = 'krs';
$page_title  = 'KRS Saya';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Hero -->
<div class="student-hero" style="margin-bottom:24px;">
  <div class="student-avatar"><i class="fas fa-file-signature"></i></div>
  <div class="student-info">
    <div class="eyebrow"><i class="fas fa-clipboard-list"></i> Kartu Rencana Studi</div>
    <h2>KRS Saya</h2>
    <p>Kelola dan lihat Kartu Rencana Studi Anda per semester.</p>
  </div>
</div>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-list me-2" style="color:var(--primary)"></i>Daftar KRS</h3>
    <a href="buatkrs.php" class="btn-app">
      <i class="fas fa-plus-circle"></i> Isi KRS Baru
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th>NIM</th>
          <th style="width:100px">Semester</th>
          <th>Tahun Akademik</th>
          <th>Dosen Wali</th>
          <th>Kelas</th>
          <th>Mata Kuliah</th>
          <th style="width:160px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr>
            <td colspan="8" style="text-align:center; padding:32px; color:var(--muted);">
              <i class="fas fa-folder-open" style="font-size:32px; color:var(--muted); opacity:.3; display:block; margin-bottom:10px;"></i>
              Belum ada KRS. Silakan isi KRS baru.
            </td>
          </tr>
        <?php else: ?>
          <?php $no = 1; while ($data = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><strong><?= htmlspecialchars($data['nim']); ?></strong></td>
            <td style="text-align:center"><span class="badge-app green"><?= htmlspecialchars($data['semester']); ?></span></td>
            <td><?= htmlspecialchars($data['tahun_ajaran']); ?></td>
            <td><?= htmlspecialchars($data['nama_dosen'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($data['nama_kelas'] ?? '-'); ?></td>
            <td><span class="badge-app green"><?= (int)$data['jumlah_mk']; ?> mata kuliah</span></td>
            <td>
              <a href="krsdetail.php?nim=<?= urlencode($data['nim']); ?>&semester=<?= urlencode($data['semester']); ?>" class="btn-app btn-sm-app outline">
                <i class="fas fa-eye"></i> Detail
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>