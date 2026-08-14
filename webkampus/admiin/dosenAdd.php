<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pesan = '';
$pesan_type = '';

if (isset($_POST["simpan"])) {
    $nidn        = mysqli_real_escape_string($koneksi, trim($_POST["nidn"]));
    $nama_dosen  = mysqli_real_escape_string($koneksi, trim($_POST["nama_dosen"]));
    $email       = mysqli_real_escape_string($koneksi, trim($_POST["email"]));
    $jns_kelamin = mysqli_real_escape_string($koneksi, $_POST["jns_kelamin"]);
    $telpn       = mysqli_real_escape_string($koneksi, trim($_POST["telpn"]));

    $query  = "INSERT INTO tbldosen (nidn, nama_dosen, email, jns_kelamin, telpn) 
               VALUES ('$nidn', '$nama_dosen', '$email', '$jns_kelamin', '$telpn')";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        header("Location: dosen.php");
        exit;
    } else {
        $pesan = "Data gagal disimpan. Pastikan NIDN belum terdaftar.";
        $pesan_type = 'error';
    }
}

$currentPage = 'dosen';
$page_title  = 'Tambah Dosen';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb kecil -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="dosen.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data Dosen
  </a>
  <span>›</span>
  <span>Tambah Dosen</span>
</div>

<div class="data-card" style="max-width:720px;">
  <div class="card-head">
    <h3><i class="fas fa-user-plus me-2" style="color:var(--primary)"></i>Form Tambah Dosen</h3>
  </div>

  <?php if ($pesan): ?>
    <div style="padding:12px 16px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; margin-bottom:18px; font-size:14px;">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($pesan) ?>
    </div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <div class="mb-3">
      <label class="form-label">Nomor Induk Dosen Nasional (NIDN)</label>
      <input type="text" id="nidn" name="nidn" class="form-control" required />
    </div>

    <div class="mb-3">
      <label class="form-label">Nama Dosen</label>
      <input type="text" id="nama_dosen" name="nama_dosen" class="form-control" required />
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" id="email" name="email" class="form-control" required />
    </div>

    <div class="mb-3">
      <label class="form-label">Jenis Kelamin</label>
      <div class="d-flex gap-4 mt-1">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="jns_kelamin" id="laki" value="L" checked>
          <label class="form-check-label" for="laki">Laki-laki</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="jns_kelamin" id="perempuan" value="P">
          <label class="form-check-label" for="perempuan">Perempuan</label>
        </div>
      </div>
    </div>

    <div class="mb-4">
      <label class="form-label">No. Telepon</label>
      <input type="text" id="telpn" name="telpn" class="form-control" required />
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="simpan" class="btn-app">
        <i class="fas fa-save"></i> Simpan
      </button>
      <a href="dosen.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>