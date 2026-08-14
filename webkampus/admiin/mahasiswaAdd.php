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
    $nim         = mysqli_real_escape_string($koneksi, trim($_POST["nim"]));
    $nama_mhs    = mysqli_real_escape_string($koneksi, trim($_POST["nama_mhs"]));
    $prodi       = mysqli_real_escape_string($koneksi, trim($_POST["prodi"]));
    $semester    = intval($_POST["semester"]);
    $jns_kelamin = mysqli_real_escape_string($koneksi, $_POST["jns_kelamin"]);
    $alamat      = mysqli_real_escape_string($koneksi, trim($_POST["alamat"]));

    $query  = "INSERT INTO tblmhs2 (nim, nama_mhs, prodi, semester, jns_kelamin, alamat) 
               VALUES ('$nim', '$nama_mhs', '$prodi', '$semester', '$jns_kelamin', '$alamat')";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        header("Location: mahasiswa.php");
        exit;
    } else {
        $pesan      = "Gagal menyimpan data. Pastikan NIM belum terdaftar.";
        $pesan_type = 'error';
    }
}

$currentPage = 'mahasiswa';
$page_title  = 'Tambah Mahasiswa';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="mahasiswa.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data Mahasiswa
  </a>
  <span>›</span>
  <span>Tambah Mahasiswa</span>
</div>

<div class="data-card" style="max-width:780px;">
  <div class="card-head">
    <h3><i class="fas fa-user-plus me-2" style="color:var(--primary)"></i>Form Tambah Mahasiswa</h3>
  </div>

  <?php if ($pesan): ?>
    <div style="padding:12px 16px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; margin-bottom:18px; font-size:14px;">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($pesan) ?>
    </div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">NIM</label>
        <input type="text" name="nim" class="form-control" required placeholder="Contoh: 2023010001" />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Nama Mahasiswa</label>
        <input type="text" name="nama_mhs" class="form-control" required placeholder="Nama lengkap" />
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Program Studi</label>
        <input type="text" name="prodi" class="form-control" required placeholder="Contoh: Teknik Informatika" />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Semester</label>
        <select name="semester" class="form-select" required>
          <option value="" disabled selected>-- Pilih Semester --</option>
          <?php for ($i = 1; $i <= 14; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Jenis Kelamin</label>
      <div class="d-flex gap-4 mt-1">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="jns_kelamin" id="jk_l" value="L" checked>
          <label class="form-check-label" for="jk_l">Laki-laki</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="jns_kelamin" id="jk_p" value="P">
          <label class="form-check-label" for="jk_p">Perempuan</label>
        </div>
      </div>
    </div>

    <div class="mb-4">
      <label class="form-label">Alamat</label>
      <textarea name="alamat" class="form-control" rows="3" required placeholder="Alamat lengkap mahasiswa"></textarea>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="simpan" class="btn-app">
        <i class="fas fa-save"></i> Simpan
      </button>
      <a href="mahasiswa.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>