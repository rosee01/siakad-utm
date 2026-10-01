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
    $kode_mk     = mysqli_real_escape_string($koneksi, trim($_POST["kode_mk"]));
    $nama_mk     = mysqli_real_escape_string($koneksi, trim($_POST["nama_mk"]));
    $sks         = intval($_POST["sks"]);
    $semester    = intval($_POST["semester"]);
    $id_prodi    = mysqli_real_escape_string($koneksi, trim($_POST["id_prodi"]));
    $nama_dosen  = mysqli_real_escape_string($koneksi, trim($_POST["nama_dosen"]));
    $hari        = mysqli_real_escape_string($koneksi, $_POST["hari"]);
    $jam_mulai   = mysqli_real_escape_string($koneksi, $_POST["jam_mulai"]);
    $jam_selesai = mysqli_real_escape_string($koneksi, $_POST["jam_selesai"]);
    $ruang       = mysqli_real_escape_string($koneksi, $_POST["ruang"]);

    // ✅ Perbaikan: tambahkan kolom `semester` ke INSERT (di kode asli field ada tapi tidak disimpan)
    // ✅ Perbaikan: `$hari` sekarang pakai quote di query
    $stmt = $koneksi->prepare(
        "INSERT INTO tblmatkul (kode_mk, nama_mk, sks, semester, id_prodi, nama_dosen, hari, jam_mulai, jam_selesai, ruang)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "ssiissssss",
        $kode_mk,
        $nama_mk,
        $sks,
        $semester,
        $id_prodi,
        $nama_dosen,
        $hari,
        $jam_mulai,
        $jam_selesai,
        $ruang
    );
    $result = $stmt->execute();

    if ($result) {
        header("Location: matkul.php");
        exit;
    } else {
        $pesan      = "Gagal menyimpan data. Pastikan Kode MK belum terdaftar.";
        $pesan_type = 'error';
    }
}

/* ---------- DATA DROPDOWN (dengan DISTINCT agar tidak duplikat) ---------- */
$prodi_list  = mysqli_query($koneksi, "SELECT * FROM tblprodi ORDER BY nama_prodi ASC");
$dosen_list  = mysqli_query($koneksi, "SELECT DISTINCT nama_dosen FROM tbldosen ORDER BY nama_dosen ASC");
$hari_list   = mysqli_query($koneksi, "SELECT DISTINCT hari FROM tbljadwalkuliah ORDER BY hari ASC");
$ruang_list  = mysqli_query($koneksi, "SELECT DISTINCT ruang FROM tbljadwalkuliah ORDER BY ruang ASC");
$sem_list    = [1, 2, 3, 4, 5, 6, 7, 8]; // semester tetap 1-8, tidak tergantung data mahasiswa yang ada

$currentPage = 'matkul';
$page_title  = 'Tambah Mata Kuliah';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="matkul.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data Mata Kuliah
  </a>
  <span>›</span>
  <span>Tambah Mata Kuliah</span>
</div>

<div class="data-card" style="max-width:860px;">
  <div class="card-head">
    <h3><i class="fas fa-book-open me-2" style="color:var(--primary)"></i>Form Tambah Mata Kuliah</h3>
  </div>

  <?php if ($pesan): ?>
    <div style="padding:12px 16px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; margin-bottom:18px; font-size:14px;">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($pesan) ?>
    </div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <?= csrf_field() ?>
    <!-- Baris 1: Kode MK, Nama MK -->
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Kode MK</label>
        <input type="text" name="kode_mk" class="form-control" required placeholder="Contoh: TI201" />
      </div>
      <div class="col-md-8 mb-3">
        <label class="form-label">Nama Mata Kuliah</label>
        <input type="text" name="nama_mk" class="form-control" required placeholder="Contoh: Algoritma dan Pemrograman" />
      </div>
    </div>

    <!-- Baris 2: SKS, Semester, Prodi -->
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">SKS</label>
        <select name="sks" class="form-select" required>
          <option value="" disabled selected>-- Pilih SKS --</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
        </select>
      </div>
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
        <label class="form-label">Program Studi</label>
        <select name="id_prodi" class="form-select" required>
          <option value="" disabled selected>-- Pilih Prodi --</option>
          <?php while ($p = mysqli_fetch_array($prodi_list)): ?>
            <option value="<?= htmlspecialchars($p['kode_prodi']) ?>">
              <?= htmlspecialchars($p['kode_prodi']) ?> - <?= htmlspecialchars($p['nama_prodi']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <!-- Baris 3: Dosen -->
    <div class="mb-3">
      <label class="form-label">Nama Dosen Pengampu</label>
      <select name="nama_dosen" class="form-select" required>
        <option value="" disabled selected>-- Pilih Dosen --</option>
        <?php while ($d = mysqli_fetch_array($dosen_list)): ?>
          <option value="<?= htmlspecialchars($d['nama_dosen']) ?>">
            <?= htmlspecialchars($d['nama_dosen']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <!-- Baris 4: Hari, Jam Mulai, Jam Selesai, Ruang -->
    <div class="row">
      <div class="col-md-3 mb-3">
        <label class="form-label">Hari</label>
        <select name="hari" class="form-select" required>
          <option value="" disabled selected>-- Pilih Hari --</option>
          <?php while ($h = mysqli_fetch_array($hari_list)): ?>
            <option value="<?= htmlspecialchars($h['hari']) ?>"><?= htmlspecialchars($h['hari']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Jam Mulai</label>
        <input type="time" name="jam_mulai" class="form-control" required />
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Jam Selesai</label>
        <input type="time" name="jam_selesai" class="form-control" required />
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Ruang</label>
        <select name="ruang" class="form-select" required>
          <option value="" disabled selected>-- Pilih Ruang --</option>
          <?php while ($r = mysqli_fetch_array($ruang_list)): ?>
            <option value="<?= htmlspecialchars($r['ruang']) ?>"><?= htmlspecialchars($r['ruang']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="simpan" class="btn-app">
        <i class="fas fa-save"></i> Simpan
      </button>
      <a href="matkul.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>