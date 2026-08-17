<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$currentPage = 'dashboard';
$page_title  = 'Dashboard';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Hitung data asli dari database biar stats akurat & otomatis
$hit_mhs   = $koneksi->query("SELECT COUNT(*) AS j FROM tblmhs2")->fetch_assoc()['j'];
$hit_dosen = $koneksi->query("SELECT COUNT(*) AS j FROM tbldosen")->fetch_assoc()['j'];
$hit_mk    = $koneksi->query("SELECT COUNT(*) AS j FROM tblmatkul")->fetch_assoc()['j'];
$hit_prodi = $koneksi->query("SELECT COUNT(*) AS j FROM tblprodi")->fetch_assoc()['j'];
?>

<!-- Welcome hero -->
<div class="welcome-hero">
  <div class="eyebrow"><i class="fas fa-star"></i> Academic Management System</div>
  <h2>Selamat Datang, Administrator 👋</h2>
  <p>Kelola seluruh data akademik Universitas Teknologi Mataram melalui dashboard terintegrasi ini. Pilih menu di samping kiri untuk mengelola data mahasiswa, dosen, matakuliah, jadwal, KRS, KHS, dan user sistem.</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-user-graduate"></i></div>
    <div>
      <div class="stat-label">Total Mahasiswa</div>
      <div class="stat-value"><?php echo $hit_mhs; ?></div>
      <div class="stat-delta"><i class="fas fa-arrow-up"></i> Aktif</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon indigo"><i class="fas fa-chalkboard-teacher"></i></div>
    <div>
      <div class="stat-label">Total Dosen</div>
      <div class="stat-value"><?php echo $hit_dosen; ?></div>
      <div class="stat-delta"><i class="fas fa-arrow-up"></i> Aktif</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-book-open"></i></div>
    <div>
      <div class="stat-label">Matakuliah</div>
      <div class="stat-value"><?php echo $hit_mk; ?></div>
      <div class="stat-delta"><i class="fas fa-check"></i> Semester ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon amber"><i class="fas fa-book"></i></div>
    <div>
      <div class="stat-label">Program Studi</div>
      <div class="stat-value"><?php echo $hit_prodi; ?></div>
      <div class="stat-delta"><i class="fas fa-check"></i> Terakreditasi</div>
    </div>
  </div>
</div>

<!-- Info box -->
<div class="info-box">
  <div class="info-head">
    <div class="info-icon"><i class="fas fa-info-circle"></i></div>
    <div>
      <h4>Informasi Dashboard</h4>
      <div class="info-sub">Panduan singkat penggunaan panel administrator</div>
    </div>
  </div>

  <p>
    Anda telah login sebagai <strong>Administrator</strong>.
    Melalui menu di sebelah kiri, Anda dapat mengelola seluruh data akademik secara terintegrasi dan profesional.
  </p>

  <ul>
    <li><strong>Data Master:</strong> Mahasiswa, Dosen, Program Studi, Matakuliah, Ruangan, dan Kelas.</li>
    <li><strong>Kegiatan Akademik:</strong> Jadwal Kuliah, KRS, KRS Detail, dan KHS.</li>
    <li><strong>Sistem:</strong> Data User dan hak akses pengguna.</li>
  </ul>

  <ul>
    <li>Pastikan data yang Anda input sudah benar dan sesuai dengan ketentuan akademik.</li>
    <li>Gunakan fitur pencarian dan filter pada setiap menu untuk memudahkan pengelolaan data.</li>
    <li>Jika mengalami kendala, silakan hubungi tim IT atau administrator sistem.</li>
  </ul>

  <div class="info-note">
    <i class="fas fa-university"></i>
    Sistem ini dirancang untuk mendukung kelancaran proses administrasi akademik di lingkungan Universitas Teknologi Mataram.
  </div>
</div>

<?php include '../includes/footer.php'; ?>