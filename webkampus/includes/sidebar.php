<?php
$role        = $_SESSION['role'] ?? '';
$currentPage = $currentPage ?? '';
function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="mark"><i class="fas fa-graduation-cap"></i></div>
    <div>
      <div class="name">SIAKAD</div>
      <div class="sub">Sistem Informasi Akademik</div>
    </div>
  </div>

  <nav class="sidebar-menu">

    <?php if ($role === 'admin'): ?>
      <div class="menu-title">Utama</div>
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link <?= isActive('dashboard') ?>" href="dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
      </ul>
      <div class="menu-title">Data Akademik</div>
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link <?= isActive('mahasiswa') ?>" href="mahasiswa.php"><i class="fas fa-user-graduate"></i><span>Data Mahasiswa</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('dosen') ?>" href="dosen.php"><i class="fas fa-chalkboard-teacher"></i><span>Data Dosen</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('prodi') ?>" href="prodi.php"><i class="fas fa-book"></i><span>Data Prodi</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('matkul') ?>" href="matkul.php"><i class="fas fa-book-open"></i><span>Data Matakuliah</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('ruangan') ?>" href="ruangan.php"><i class="fas fa-door-closed"></i><span>Ruangan</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('kelas') ?>" href="kelas.php"><i class="fas fa-chalkboard"></i><span>Kelas</span></a></li>
      </ul>
      <div class="menu-title">Kegiatan</div>
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link <?= isActive('jadwal') ?>" href="jadwalkuliah.php"><i class="fas fa-calendar-alt"></i><span>Jadwal Kuliah</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('krs') ?>" href="krs.php"><i class="fas fa-file-signature"></i><span>KRS</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('krsdetail') ?>" href="krsdetail.php"><i class="fas fa-file-signature"></i><span>KRS Detail</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('khs') ?>" href="khs.php"><i class="fas fa-file-contract"></i><span>KHS</span></a></li>
      </ul>
      <div class="menu-title">Pengaturan</div>
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link <?= isActive('user') ?>" href="user.php"><i class="fas fa-user-cog"></i><span>Data User</span></a></li>
      </ul>

    <?php elseif ($role === 'dosen'): ?>
      <!-- ✅ LINK DIPERBAIKI sesuai nama file asli di folder dosen/ -->
      <div class="menu-title">Menu Dosen</div>
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link <?= isActive('dashboard') ?>" href="dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('jadwal') ?>" href="jadwal.php"><i class="fas fa-calendar-alt"></i><span>Jadwal Mengajar</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('nilai') ?>" href="inputnilai.php"><i class="fas fa-file-signature"></i><span>Input Nilai</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('khs') ?>" href="khs.php"><i class="fas fa-file-contract"></i><span>KHS Mahasiswa</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('profil') ?>" href="dosenn.php"><i class="fas fa-user"></i><span>Profil</span></a></li>
      </ul>

    <?php else: ?>
      <!-- Menu mahasiswa — sesuaikan nama file jika berbeda -->
      <div class="menu-title">Menu Mahasiswa</div>
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link <?= isActive('profil') ?>" href="profil_mahasiswa.php"><i class="fas fa-user"></i><span>Profil</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('krs') ?>" href="krs.php"><i class="fas fa-file-signature"></i><span>KRS</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('khs') ?>" href="khs.php"><i class="fas fa-file-contract"></i><span>KHS</span></a></li>
        <li class="nav-item"><a class="nav-link <?= isActive('jadwal') ?>" href="jadwal.php"><i class="fas fa-calendar-alt"></i><span>Jadwal Kuliah</span></a></li>
      </ul>
    <?php endif; ?>

  </nav>

  <div class="sidebar-footer">
    <a href="../logout.php" class="logout-btn">
      <i class="fas fa-sign-out-alt"></i><span>Keluar</span>
    </a>
  </div>
</aside>