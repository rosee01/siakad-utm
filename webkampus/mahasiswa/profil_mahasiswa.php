<?php
session_start();
include "koneksi.php";

// Cek login dan role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

// Ambil NIM dari session
$nim = $_SESSION['nim'] ?? ($_SESSION['ref_id'] ?? '');
if (empty($nim)) {
    die("NIM tidak ditemukan di session!");
}

// Ambil data mahasiswa
$query = "SELECT * FROM tblmhs2 WHERE nim = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
$mahasiswa = $result->fetch_assoc();

if (!$mahasiswa) {
    die("<div style='background:white;color:red;padding:20px;margin:10px;'>
         <h3>Data mahasiswa tidak ditemukan untuk NIM: " . htmlspecialchars($nim) . "</h3>
         <a href='../login.php'>Kembali ke Login</a>
         </div>");
}

// Foto profil (path relatif terhadap folder mahasiswa/, sesuai struktur asli)
$foto    = !empty($mahasiswa['foto']) ? 'uploads/' . $mahasiswa['foto'] : '';
$inisial = strtoupper(substr($mahasiswa['nama_mhs'] ?? 'M', 0, 1));

$currentPage = 'profil';
$page_title  = 'Dashboard Mahasiswa';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Hero Mahasiswa -->
<div class="student-hero">
  <div class="student-avatar">
    <?php if ($foto): ?>
      <img src="<?= htmlspecialchars($foto) ?>" alt="Foto Profil">
    <?php else: ?>
      <?= $inisial ?>
    <?php endif; ?>
  </div>
  <div class="student-info">
    <div class="eyebrow"><i class="fas fa-user-graduate"></i> Portal Mahasiswa</div>
    <h2>Halo, <?= htmlspecialchars($mahasiswa['nama_mhs']) ?> 👋</h2>
    <p>Selamat datang di Sistem Informasi Akademik Universitas Teknologi Mataram.</p>
    <div class="info-chips">
      <span class="nim-badge"><i class="fas fa-id-card"></i> NIM: <?= htmlspecialchars($mahasiswa['nim']) ?></span>
      <span class="chip"><i class="fas fa-book"></i> <?= htmlspecialchars($mahasiswa['prodi'] ?? '-') ?></span>
      <span class="chip"><i class="fas fa-layer-group"></i> Semester <?= htmlspecialchars($mahasiswa['semester'] ?? '-') ?></span>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
  <a href="profile_mahasiswaedit.php?nim=<?= urlencode($mahasiswa['nim']) ?>" class="qa-card">
    <div class="qa-icon indigo"><i class="fas fa-user-edit"></i></div>
    <div>
      <div class="qa-title">Edit Profil</div>
      <div class="qa-desc">Perbarui data pribadi Anda</div>
    </div>
  </a>
  <a href="khs.php" class="qa-card">
    <div class="qa-icon emerald"><i class="fas fa-file-contract"></i></div>
    <div>
      <div class="qa-title">KHS</div>
      <div class="qa-desc">Lihat Kartu Hasil Studi</div>
    </div>
  </a>
  <a href="buatkrs.php" class="qa-card">
    <div class="qa-icon amber"><i class="fas fa-file-signature"></i></div>
    <div>
      <div class="qa-title">Isi KRS</div>
      <div class="qa-desc">Rencanakan mata kuliah semester ini</div>
    </div>
  </a>
</div>

<!-- Profil Lengkap -->
<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-user me-2" style="color:var(--primary)"></i>Profil Lengkap</h3>
  </div>

  <div class="student-profile-card">
      <div class="profile-avatar-wrap">
        <div class="profile-avatar">
          <?php if ($foto): ?>
            <img src="<?= htmlspecialchars($foto) ?>" alt="Foto Mahasiswa">
          <?php else: ?>
            <?= $inisial ?>
          <?php endif; ?>
        </div>
        <a href="profile_mahasiswaedit.php?nim=<?= urlencode($mahasiswa['nim']) ?>"
           class="btn-app" style="font-size:13px; padding:8px 16px;">
          <i class="fas fa-edit"></i> Edit Profil
        </a>
      </div>
      <div class="profile-detail">
        <h3><?= htmlspecialchars($mahasiswa['nama_mhs']) ?></h3>
        <span class="role-chip"><i class="fas fa-user-graduate"></i> Mahasiswa</span>

        <div class="profile-grid">
          <div class="field">
            <div class="field-label">NIM</div>
            <div class="field-value"><i class="fas fa-id-card"></i> <?= htmlspecialchars($mahasiswa['nim']) ?></div>
          </div>
          <div class="field">
            <div class="field-label">Program Studi</div>
            <div class="field-value"><i class="fas fa-book"></i> <?= htmlspecialchars($mahasiswa['prodi'] ?? '-') ?></div>
          </div>
          <div class="field">
            <div class="field-label">Semester</div>
            <div class="field-value"><i class="fas fa-layer-group"></i> <?= htmlspecialchars($mahasiswa['semester'] ?? '-') ?></div>
          </div>
          <div class="field">
            <div class="field-label">Jenis Kelamin</div>
            <div class="field-value">
              <i class="fas <?= ($mahasiswa['jns_kelamin'] ?? '') === 'L' ? 'fa-mars' : 'fa-venus' ?>"></i>
              <?= ($mahasiswa['jns_kelamin'] ?? '') === 'L' ? 'Laki-laki' : 'Perempuan' ?>
            </div>
          </div>
          <div class="field" style="grid-column: span 2;">
            <div class="field-label">Alamat</div>
            <div class="field-value" style="align-items:flex-start;">
              <i class="fas fa-map-marker-alt" style="margin-top:3px;"></i>
              <span><?= htmlspecialchars($mahasiswa['alamat'] ?? '-') ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>