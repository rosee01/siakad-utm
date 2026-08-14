<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../login.php");
    exit;
}

$nidn = $_SESSION['ref_id'] ?? '';
if (empty($nidn)) {
    echo "NIDN tidak ditemukan di session!";
    exit;
}

$sql = "SELECT * FROM tbldosen WHERE nidn = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("s", $nidn);
$stmt->execute();
$dosen = $stmt->get_result()->fetch_assoc();

if (!$dosen) {
    die("Data dosen tidak ditemukan.");
}

$nama_dosen = $dosen['nama_dosen'];
$query = "SELECT j.*, m.kode_mk 
          FROM tbljadwalkuliah j
          LEFT JOIN tblmatkul m ON j.matakuliah = m.nama_mk
          WHERE j.dosen = ?
          ORDER BY 
            FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'),
            j.jam_mulai";
$stmt2 = $koneksi->prepare($query);
$stmt2->bind_param("s", $nama_dosen);
$stmt2->execute();
$jadwal = $stmt2->get_result();

$currentPage = 'jadwal';
$page_title  = 'Jadwal Mengajar';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Hero -->
<div class="lecturer-hero">
  <div class="lecturer-avatar">
    <?= strtoupper(substr($dosen['nama_dosen'], 0, 1)) ?>
  </div>
  <div class="lecturer-info">
    <div class="eyebrow"><i class="fas fa-calendar-alt"></i> Jadwal Mengajar</div>
    <h2><?= htmlspecialchars($dosen['nama_dosen']) ?></h2>
    <p>Daftar jadwal kelas yang Andaampu semester ini.</p>
    <span class="nidn-badge"><i class="fas fa-id-card"></i> NIDN: <?= htmlspecialchars($dosen['nidn']) ?></span>
  </div>
</div>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-calendar-week me-2" style="color:var(--primary)"></i>Jadwal Kelas Anda</h3>
    <span class="badge-app green"><?= $jadwal->num_rows ?> Jadwal</span>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th>Mata Kuliah</th>
          <th>Kode MK</th>
          <th style="width:110px">Hari</th>
          <th style="width:150px">Jam</th>
          <th style="width:110px">Ruang</th>
          <th style="width:100px">Kelas</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($jadwal->num_rows === 0): ?>
          <tr>
            <td colspan="7" style="text-align:center; padding:32px; color:var(--muted);">
              <i class="fas fa-calendar-times" style="font-size:32px; color:var(--muted); opacity:.3; display:block; margin-bottom:10px;"></i>
              Belum ada jadwal mengajar.
            </td>
          </tr>
        <?php else: ?>
          <?php $no = 1; while ($data = $jadwal->fetch_assoc()): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><strong><?= htmlspecialchars($data['matakuliah']); ?></strong></td>
            <td><span class="badge-app"><?= htmlspecialchars($data['kode_mk'] ?? '-'); ?></span></td>
            <td><span class="badge-app green"><?= htmlspecialchars($data['hari']); ?></span></td>
            <td style="text-align:center">
              <i class="far fa-clock" style="color:var(--primary); margin-right:4px;"></i>
              <?= htmlspecialchars($data['jam_mulai']) ?> - <?= htmlspecialchars($data['jam_selesai']) ?>
            </td>
            <td><span class="badge-app"><?= htmlspecialchars($data['ruang']); ?></span></td>
            <td style="text-align:center"><strong><?= htmlspecialchars($data['kelas']); ?></strong></td>
          </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>