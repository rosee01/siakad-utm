<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pesan      = '';
$pesan_type = '';

// Ambil NIDN dari URL
$nidn_get = mysqli_real_escape_string($koneksi, $_GET['nidn'] ?? '');

// Jika tidak ada NIDN, kembali ke daftar
if ($nidn_get === '') {
    header("Location: dosen.php");
    exit;
}

// Ambil data dosen
$queryEdit  = "SELECT * FROM tbldosen WHERE nidn='$nidn_get'";
$resultEdit = mysqli_query($koneksi, $queryEdit);

if (!$resultEdit || mysqli_num_rows($resultEdit) === 0) {
    header("Location: dosen.php");
    exit;
}

$dataEdit = mysqli_fetch_array($resultEdit);

// Proses update
if (isset($_POST["ubah"])) {
    $nidn        = mysqli_real_escape_string($koneksi, trim($_POST["nidn"]));
    $nama_dosen  = mysqli_real_escape_string($koneksi, trim($_POST["nama_dosen"]));
    $email       = mysqli_real_escape_string($koneksi, trim($_POST["email"]));
    $jns_kelamin = mysqli_real_escape_string($koneksi, $_POST["jns_kelamin"]);
    $telpn       = mysqli_real_escape_string($koneksi, trim($_POST["telpn"]));

    $query = "UPDATE tbldosen SET nama_dosen='$nama_dosen', email='$email', jns_kelamin='$jns_kelamin',
              telpn='$telpn' WHERE nidn='$nidn'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $pesan = "Data berhasil diperbarui.";
        $pesan_type = 'success';
    } else {
        $pesan = "Data gagal disimpan.";
        $pesan_type = 'error';
    }
}

$currentPage = 'dosen';
$page_title  = 'Edit Dosen';
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
  <span>Edit Dosen</span>
</div>

<div class="data-card" style="max-width:720px;">
  <div class="card-head">
    <h3><i class="fas fa-edit me-2" style="color:var(--primary)"></i>Form Edit Dosen</h3>
  </div>

  <?php if ($pesan): ?>
    <div style="padding:12px 16px; border-radius:10px; margin-bottom:18px; font-size:14px;
                background:<?= $pesan_type === 'success' ? '#f0fdf4' : '#fef2f2' ?>;
                border:1px solid <?= $pesan_type === 'success' ? '#bbf7d0' : '#fecaca' ?>;
                color:<?= $pesan_type === 'success' ? '#166534' : '#b91c1c' ?>;">
      <i class="fas <?= $pesan_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
      <?= htmlspecialchars($pesan) ?>
    </div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <div class="mb-3">
      <label class="form-label">Nomor Induk Dosen Nasional (NIDN)</label>
      <input type="text" id="nidn" name="nidn" class="form-control" 
             value="<?= htmlspecialchars($dataEdit['nidn']) ?>" readonly 
             style="background:#f3f4f6; color:var(--muted);" />
      <small class="form-text text-muted" style="font-size:12px;">NIDN tidak bisa diubah.</small>
    </div>

    <div class="mb-3">
      <label class="form-label">Nama Dosen</label>
      <input type="text" id="nama_dosen" name="nama_dosen" class="form-control" 
             value="<?= htmlspecialchars($dataEdit['nama_dosen']) ?>" required />
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" id="email" name="email" class="form-control" 
             value="<?= htmlspecialchars($dataEdit['email']) ?>" required />
    </div>

    <div class="mb-3">
      <label class="form-label">Jenis Kelamin</label>
      <div class="d-flex gap-4 mt-1">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="jns_kelamin" id="laki" value="L" 
                 <?= ($dataEdit['jns_kelamin'] === 'L') ? 'checked' : '' ?>>
          <label class="form-check-label" for="laki">Laki-laki</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="jns_kelamin" id="perempuan" value="P" 
                 <?= ($dataEdit['jns_kelamin'] === 'P') ? 'checked' : '' ?>>
          <label class="form-check-label" for="perempuan">Perempuan</label>
        </div>
      </div>
    </div>

    <div class="mb-4">
      <label class="form-label">No. Telepon</label>
      <input type="text" id="telpn" name="telpn" class="form-control" 
             value="<?= htmlspecialchars($dataEdit['telpn']) ?>" required />
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="ubah" class="btn-app">
        <i class="fas fa-save"></i> Simpan Perubahan
      </button>
      <a href="dosen.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>