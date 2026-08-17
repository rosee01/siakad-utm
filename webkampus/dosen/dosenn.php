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

// Query data dosen
$sql = "SELECT * FROM tbldosen WHERE nidn = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("s", $nidn);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "Data dosen tidak ditemukan.";
    exit;
}

$currentPage = 'profil';
$page_title  = 'Profil Dosen';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Hero Profil Dosen -->
<div class="lecturer-hero">
  <div class="lecturer-avatar">
    <?= strtoupper(substr($row['nama_dosen'] ?? 'D', 0, 1)) ?>
  </div>
  <div class="lecturer-info">
    <div class="eyebrow"><i class="fas fa-user"></i> Profil Dosen</div>
    <h2><?= htmlspecialchars($row['nama_dosen']) ?></h2>
    <p><?= htmlspecialchars($row['prodi'] ?? 'Dosen Universitas Teknologi Mataram') ?></p>
    <span class="nidn-badge"><i class="fas fa-id-card"></i> NIDN: <?= htmlspecialchars($row['nidn']) ?></span>
  </div>
</div>

<div class="data-card" style="max-width:860px;">
  <div class="card-head">
    <h3><i class="fas fa-user-circle me-2" style="color:var(--primary)"></i>Informasi Pribadi</h3>
    <a href="dosenedit.php" class="btn-app">
      <i class="fas fa-edit"></i> Edit Profil
    </a>
  </div>

  <div class="profile-card" style="box-shadow:none; border:none; padding:0;">
    <div class="profile-avatar-wrap">
      <div class="profile-avatar">
        <?= strtoupper(substr($row['nama_dosen'] ?? 'D', 0, 1)) ?>
      </div>
    </div>
    <div class="profile-detail">
      <h3><?= htmlspecialchars($row['nama_dosen']) ?></h3>
      <span class="role-chip"><i class="fas fa-chalkboard-teacher"></i> Dosen</span>

      <div class="profile-grid">
        <div class="field">
          <div class="field-label">NIDN</div>
          <div class="field-value"><i class="fas fa-id-card"></i> <?= htmlspecialchars($row['nidn']) ?></div>
        </div>
        <div class="field">
          <div class="field-label">Email</div>
          <div class="field-value"><i class="fas fa-envelope"></i> <?= htmlspecialchars($row['email']) ?></div>
        </div>
        <div class="field">
          <div class="field-label">Jenis Kelamin</div>
          <div class="field-value">
            <i class="fas <?= $row['jns_kelamin'] === 'L' ? 'fa-mars' : 'fa-venus' ?>"></i>
            <?= $row['jns_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
          </div>
        </div>
        <div class="field">
          <div class="field-label">No. Telepon</div>
          <div class="field-value"><i class="fas fa-phone"></i> <?= htmlspecialchars($row['telpn']) ?></div>
        </div>
        <div class="field" style="grid-column: span 2;">
          <div class="field-label">Program Studi</div>
          <div class="field-value">
            <i class="fas fa-graduation-cap"></i>
            <?= htmlspecialchars($row['prodi'] ?? '-') ?>
          </div>
        </div>
        <div class="field" style="grid-column: span 2;">
          <div class="field-label">Alamat</div>
          <div class="field-value" style="align-items:flex-start;">
            <i class="fas fa-map-marker-alt" style="margin-top:3px;"></i>
            <span><?= htmlspecialchars($row['alamat'] ?? '-') ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Quick actions untuk dosen -->
<div class="quick-actions" style="margin-top:28px;">
  <a href="dashboard.php" class="qa-card">
    <div class="qa-icon indigo"><i class="fas fa-home"></i></div>
    <div>
      <div class="qa-title">Dashboard</div>
      <div class="qa-desc">Kembali ke halaman utama dosen</div>
    </div>
  </a>
  <a href="jadwal.php" class="qa-card">
    <div class="qa-icon emerald"><i class="fas fa-calendar-alt"></i></div>
    <div>
      <div class="qa-title">Jadwal Mengajar</div>
      <div class="qa-desc">Lihat jadwal kelas Anda</div>
    </div>
  </a>
  <a href="inputnilai.php" class="qa-card">
    <div class="qa-icon amber"><i class="fas fa-file-signature"></i></div>
    <div>
      <div class="qa-title">Input Nilai</div>
      <div class="qa-desc">Kelola nilai mahasiswa</div>
    </div>
  </a>
</div>

<?php include '../includes/footer.php'; ?>