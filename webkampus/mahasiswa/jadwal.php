<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$nim = $_SESSION['nim'] ?? '';
if (empty($nim)) die("NIM tidak ditemukan.");

// Ambil data mahasiswa
$stmt = $koneksi->prepare("SELECT nama_mhs, semester, prodi FROM tblmhs2 WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$mhs = $stmt->get_result()->fetch_assoc();

// Ambil jadwal — semua jadwal yang tersedia (atau bisa difilter sesuai prodi mahasiswa)
$query = "SELECT * FROM tbljadwalkuliah ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), jam_mulai";
$result = mysqli_query($koneksi, $query);

// Kelompokkan per hari
$jadwal_per_hari = [];
while ($data = mysqli_fetch_assoc($result)) {
    $jadwal_per_hari[$data['hari']][] = $data;
}

$currentPage = 'jadwal';
$page_title  = 'Jadwal Kuliah';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Hero -->
<div class="student-hero" style="margin-bottom:24px;">
  <div class="student-avatar"><i class="fas fa-calendar-alt"></i></div>
  <div class="student-info">
    <div class="eyebrow"><i class="fas fa-calendar-week"></i> Jadwal Kuliah</div>
    <h2><?= htmlspecialchars($mhs['nama_mhs']) ?></h2>
    <p>Jadwal kuliah semester <?= htmlspecialchars($mhs['semester']) ?> — <?= htmlspecialchars($mhs['prodi']) ?></p>
  </div>
</div>

<?php if (empty($jadwal_per_hari)): ?>
  <div class="data-card" style="text-align:center; padding:40px; color:var(--muted);">
    <i class="fas fa-calendar-times" style="font-size:48px; color:var(--primary); opacity:.3; margin-bottom:14px;"></i>
    <p style="margin:0;">Belum ada jadwal kuliah tersedia.</p>
  </div>
<?php else: ?>

  <?php foreach ($jadwal_per_hari as $hari => $list): ?>
    <div class="data-card" style="margin-bottom:20px;">
      <div class="card-head">
        <h3><i class="far fa-calendar me-2" style="color:var(--primary)"></i><?= htmlspecialchars($hari) ?></h3>
        <span class="badge-app green"><?= count($list) ?> Kelas</span>
      </div>

      <div class="jadwal-grid">
        <?php foreach ($list as $j): ?>
          <div class="jadwal-card">
            <span class="day-badge"><?= htmlspecialchars($hari) ?></span>
            <h4><?= htmlspecialchars($j['matakuliah']) ?></h4>
            <div class="meta">
              <div><i class="far fa-clock"></i> <?= htmlspecialchars($j['jam_mulai']) ?> — <?= htmlspecialchars($j['jam_selesai']) ?></div>
              <div><i class="fas fa-chalkboard-teacher"></i> <?= htmlspecialchars($j['dosen']) ?></div>
              <div><i class="fas fa-door-open"></i> Ruang <?= htmlspecialchars($j['ruang']) ?></div>
              <div><i class="fas fa-users"></i> Kelas <?= htmlspecialchars($j['kelas']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>