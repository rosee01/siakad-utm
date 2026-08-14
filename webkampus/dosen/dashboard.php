<?php
session_start();
include "../koneksi.php";

// Pastikan role dosen
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
$result = $stmt->get_result();
$dosen = $result->fetch_assoc();

// Ambil semua mahasiswa dari tabel tblmhs2
$queryMhs = "SELECT nim, nama_mhs, prodi, semester, jns_kelamin, alamat FROM tblmhs2 ORDER BY nama_mhs";
$daftarMhs = $koneksi->query($queryMhs);
$totalMhs = mysqli_num_rows($daftarMhs);
$daftarMhs->data_seek(0);

/* ---------- STATISTIK DOSEN (sesuai data jadwalnya) ---------- */
// Jumlah mata kuliah yang diampu (unik)
$stmt_mk = $koneksi->prepare("SELECT COUNT(DISTINCT matakuliah) AS jml FROM tbljadwalkuliah WHERE dosen = ?");
$stmt_mk->bind_param("s", $dosen['nama_dosen']);
$stmt_mk->execute();
$totalMK = (int) ($stmt_mk->get_result()->fetch_assoc()['jml'] ?? 0);

// Jumlah kelas aktif (unik)
$stmt_kelas = $koneksi->prepare("SELECT COUNT(DISTINCT kelas) AS jml FROM tbljadwalkuliah WHERE dosen = ?");
$stmt_kelas->bind_param("s", $dosen['nama_dosen']);
$stmt_kelas->execute();
$totalKelas = (int) ($stmt_kelas->get_result()->fetch_assoc()['jml'] ?? 0);

$currentPage = 'dashboard';
$page_title  = 'Dashboard Dosen';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Hero Dosen -->
<div class="lecturer-hero">
  <div class="lecturer-avatar">
    <?= strtoupper(substr($dosen['nama_dosen'] ?? 'D', 0, 1)) ?>
  </div>
  <div class="lecturer-info">
    <div class="eyebrow"><i class="fas fa-chalkboard-teacher"></i> Panel Dosen</div>
    <h2>Selamat Datang, <?= htmlspecialchars($dosen['nama_dosen'] ?? 'Dosen') ?> 👋</h2>
    <p>Kelola jadwal mengajar, input nilai mahasiswa, dan lihat KHS melalui dashboard terintegrasi ini.</p>
    <span class="nidn-badge"><i class="fas fa-id-card"></i> NIDN: <?= htmlspecialchars($dosen['nidn']) ?></span>
  </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
  <a href="jadwal.php" class="qa-card">
    <div class="qa-icon indigo"><i class="fas fa-calendar-alt"></i></div>
    <div>
      <div class="qa-title">Jadwal Mengajar</div>
      <div class="qa-desc">Lihat jadwal kelas Anda minggu ini</div>
    </div>
  </a>
  <a href="inputnilai.php" class="qa-card">
    <div class="qa-icon emerald"><i class="fas fa-file-signature"></i></div>
    <div>
      <div class="qa-title">Input Nilai</div>
      <div class="qa-desc">Kelola nilai mahasiswa per mata kuliah</div>
    </div>
  </a>
  <a href="khs.php" class="qa-card">
    <div class="qa-icon amber"><i class="fas fa-file-contract"></i></div>
    <div>
      <div class="qa-title">KHS Mahasiswa</div>
      <div class="qa-desc">Lihat kartu hasil studi mahasiswa</div>
    </div>
  </a>
</div>

<!-- Statistik (semua angka dari database) -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom:28px;">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-user-graduate"></i></div>
    <div>
      <div class="stat-label">Total Mahasiswa</div>
      <div class="stat-value"><?= $totalMhs ?></div>
      <div class="stat-delta"><i class="fas fa-check"></i> Terdaftar</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon indigo"><i class="fas fa-book-open"></i></div>
    <div>
      <div class="stat-label">Mata Kuliah Diampu</div>
      <div class="stat-value"><?= $totalMK ?></div>
      <div class="stat-delta"><i class="fas fa-info-circle"></i> Semester ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-chalkboard"></i></div>
    <div>
      <div class="stat-label">Kelas Aktif</div>
      <div class="stat-value"><?= $totalKelas ?></div>
      <div class="stat-delta"><i class="fas fa-check"></i> Berjalan</div>
    </div>
  </div>
</div>

<!-- Tab Section -->
<div class="data-card">
  <div class="tab-bar">
    <button class="tab-btn active" data-tab="profil">
      <i class="fas fa-user"></i> Profil Dosen
    </button>
    <button class="tab-btn" data-tab="mahasiswa">
      <i class="fas fa-users"></i> Daftar Mahasiswa (<?= $totalMhs ?>)
    </button>
  </div>

  <!-- Tab Profil -->
  <div id="tab-profil" class="tab-panel active">
    <div class="profile-card">
      <div class="profile-avatar-wrap">
        <div class="profile-avatar">
          <?= strtoupper(substr($dosen['nama_dosen'] ?? 'D', 0, 1)) ?>
        </div>
        <a href="dosenedit.php" class="btn-app" style="font-size:13px; padding:8px 16px;">
          <i class="fas fa-edit"></i> Edit Profil
        </a>
      </div>
      <div class="profile-detail">
        <h3><?= htmlspecialchars($dosen['nama_dosen']) ?></h3>
        <span class="role-chip"><i class="fas fa-chalkboard-teacher"></i> Dosen</span>

        <div class="profile-grid">
          <div class="field">
            <div class="field-label">NIDN</div>
            <div class="field-value"><i class="fas fa-id-card"></i> <?= htmlspecialchars($dosen['nidn']) ?></div>
          </div>
          <div class="field">
            <div class="field-label">Email</div>
            <div class="field-value"><i class="fas fa-envelope"></i> <?= htmlspecialchars($dosen['email']) ?></div>
          </div>
          <div class="field">
            <div class="field-label">Jenis Kelamin</div>
            <div class="field-value">
              <i class="fas <?= $dosen['jns_kelamin'] === 'L' ? 'fa-mars' : 'fa-venus' ?>"></i>
              <?= $dosen['jns_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
            </div>
          </div>
          <div class="field">
            <div class="field-label">No. Telepon</div>
            <div class="field-value"><i class="fas fa-phone"></i> <?= htmlspecialchars($dosen['telpn']) ?></div>
          </div>
          <div class="field" style="grid-column: span 2;">
            <div class="field-label">Program Studi</div>
            <div class="field-value"><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($dosen['prodi'] ?? '-') ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tab Mahasiswa -->
  <div id="tab-mahasiswa" class="tab-panel">
    <div class="card-head" style="margin-bottom:16px;">
      <h3><i class="fas fa-user-graduate me-2" style="color:var(--primary)"></i>Daftar Mahasiswa</h3>
    </div>
    <div class="table-responsive">
      <table class="table-app">
        <thead>
          <tr>
            <th style="width:50px">No</th>
            <th>NIM</th>
            <th>Nama Mahasiswa</th>
            <th>Program Studi</th>
            <th style="width:90px">Semester</th>
            <th style="width:80px">JK</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($totalMhs === 0): ?>
            <tr><td colspan="6" style="text-align:center; padding:24px; color:var(--muted);">Belum ada data mahasiswa.</td></tr>
          <?php else: ?>
            <?php $no = 1; while ($mhs = $daftarMhs->fetch_assoc()): ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><strong><?= htmlspecialchars($mhs['nim']); ?></strong></td>
                <td><?= htmlspecialchars($mhs['nama_mhs']); ?></td>
                <td><span class="badge-app green"><?= htmlspecialchars($mhs['prodi']); ?></span></td>
                <td style="text-align:center"><?= htmlspecialchars($mhs['semester']); ?></td>
                <td><?= htmlspecialchars($mhs['jns_kelamin']); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  // Tab switching
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const tabId = this.dataset.tab;

      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

      this.classList.add('active');
      document.getElementById('tab-' + tabId).classList.add('active');
    });
  });
</script>

<?php include '../includes/footer.php'; ?>