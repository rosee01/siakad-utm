<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pesan      = '';
$pesan_type = '';

if (isset($_POST["simpan"])) {
    $id_jadwal   = mysqli_real_escape_string($koneksi, trim($_POST["id_jadwal"]));
    $matakuliah  = mysqli_real_escape_string($koneksi, trim($_POST["matakuliah"]));
    $dosen       = mysqli_real_escape_string($koneksi, trim($_POST["dosen"]));
    $hari        = mysqli_real_escape_string($koneksi, $_POST["hari"]);
    $jam_mulai   = mysqli_real_escape_string($koneksi, $_POST["jam_mulai"]);
    $jam_selesai = mysqli_real_escape_string($koneksi, $_POST["jam_selesai"]);
    $semester    = intval($_POST["semester"]);
    $ruang       = mysqli_real_escape_string($koneksi, $_POST["ruang"]);
    $kelas       = mysqli_real_escape_string($koneksi, trim($_POST["kelas"]));

    $query = "INSERT INTO tbljadwalkuliah (id_jadwal, matakuliah, dosen, hari, jam_mulai, jam_selesai, semester, ruang, kelas) 
              VALUES ('$id_jadwal', '$matakuliah', '$dosen', '$hari', '$jam_mulai', '$jam_selesai', '$semester', '$ruang', '$kelas')";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        header("Location: jadwalkuliah.php");
        exit;
    } else {
        $pesan      = "Data gagal disimpan. Pastikan ID Jadwal belum terdaftar.";
        $pesan_type = 'error';
    }
}

/* ---------- DATA DROPDOWN ---------- */
$mk_list    = mysqli_query($koneksi, "SELECT * FROM tblmatkul ORDER BY nama_mk ASC");
$dosen_list = mysqli_query($koneksi, "SELECT * FROM tbldosen ORDER BY nama_dosen ASC");
$sem_list   = [1, 2, 3, 4, 5, 6, 7, 8]; // semester tetap 1-8, tidak tergantung data mahasiswa yang ada
$kls_list   = mysqli_query($koneksi, "SELECT * FROM tbl_kelas ORDER BY nama_kelas ASC");

$currentPage = 'jadwal';
$page_title  = 'Tambah Jadwal Kuliah';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="jadwalkuliah.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data Jadwal
  </a>
  <span>›</span>
  <span>Tambah Jadwal Kuliah</span>
</div>

<div class="data-card" style="max-width:780px;">
  <div class="card-head">
    <h3><i class="fas fa-calendar-plus me-2" style="color:var(--primary)"></i>Form Tambah Jadwal Kuliah</h3>
  </div>

  <?php if ($pesan): ?>
    <div style="padding:12px 16px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; margin-bottom:18px; font-size:14px;">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($pesan) ?>
    </div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">ID Jadwal</label>
        <input type="text" name="id_jadwal" class="form-control" required />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Hari</label>
        <select name="hari" class="form-select" required>
          <option value="" disabled selected>-- Pilih Hari --</option>
          <option value="Senin">Senin</option>
          <option value="Selasa">Selasa</option>
          <option value="Rabu">Rabu</option>
          <option value="Kamis">Kamis</option>
          <option value="Jumat">Jumat</option>
          <option value="Sabtu">Sabtu</option>
          <option value="Minggu">Minggu</option>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Mata Kuliah</label>
        <select name="matakuliah" class="form-select" required>
          <option value="" disabled selected>-- Pilih Mata Kuliah --</option>
          <?php while ($mk = mysqli_fetch_array($mk_list)): ?>
            <option value="<?= htmlspecialchars($mk['nama_mk']) ?>"><?= htmlspecialchars($mk['nama_mk']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Dosen</label>
        <select name="dosen" class="form-select" required>
          <option value="" disabled selected>-- Pilih Dosen --</option>
          <?php while ($d = mysqli_fetch_array($dosen_list)): ?>
            <option value="<?= htmlspecialchars($d['nama_dosen']) ?>"><?= htmlspecialchars($d['nama_dosen']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Jam Mulai</label>
        <input type="time" name="jam_mulai" class="form-control" required />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Jam Selesai</label>
        <input type="time" name="jam_selesai" class="form-control" required />
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Semester</label>
        <select name="semester" class="form-select" required>
          <option value="" disabled selected>-- Pilih Semester --</option>
          <?php foreach ($sem_list as $s): ?>
            <option value="<?= $s ?>"><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Ruang</label>
        <select name="ruang" class="form-select" required>
          <option value="" disabled selected>-- Pilih Ruang --</option>
          <option value="Dahlia">Dahlia</option>
          <option value="Flamboyan">Flamboyan</option>
          <option value="Edelwais">Edelwais</option>
          <option value="Bugenvil">Bugenvil</option>
          <option value="Sakura">Sakura</option>
          <option value="Anggrek">Anggrek</option>
          <option value="Kamboja">Kamboja</option>
          <option value="L2">L2</option>
          <option value="L3">L3</option>
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Kelas</label>
        <select name="kelas" class="form-select" required>
          <option value="" disabled selected>-- Pilih Kelas --</option>
          <?php while ($k = mysqli_fetch_array($kls_list)): ?>
            <option value="<?= htmlspecialchars($k['nama_kelas']) ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="simpan" class="btn-app">
        <i class="fas fa-save"></i> Simpan
      </button>
      <a href="jadwalkuliah.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>