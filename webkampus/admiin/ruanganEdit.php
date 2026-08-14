<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pesan      = '';
$pesan_type = '';

$kode_get = mysqli_real_escape_string($koneksi, $_GET['kode_ruangan'] ?? '');

if ($kode_get === '') {
    header("Location: ruangan.php");
    exit;
}

$queryEdit  = "SELECT * FROM tbl_ruangan WHERE kode_ruangan='$kode_get'";
$resultEdit = mysqli_query($koneksi, $queryEdit);

if (!$resultEdit || mysqli_num_rows($resultEdit) === 0) {
    header("Location: ruangan.php");
    exit;
}

$dataEdit = mysqli_fetch_array($resultEdit);

if (isset($_POST["ubah"])) {
    $kode_ruangan = mysqli_real_escape_string($koneksi, trim($_POST["kode_ruangan"]));
    $nama_ruangan = mysqli_real_escape_string($koneksi, trim($_POST["nama_ruangan"]));

    $query = "UPDATE tbl_ruangan SET nama_ruangan='$nama_ruangan' WHERE kode_ruangan='$kode_ruangan'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $pesan      = "Data ruangan berhasil diperbarui.";
        $pesan_type = 'success';
        $dataEdit   = array_merge($dataEdit, $_POST);
    } else {
        $pesan      = "Data gagal diperbarui.";
        $pesan_type = 'error';
    }
}

// Ambil daftar ruang dari tbljadwalkuliah
$ruang_list = mysqli_query($koneksi, "SELECT DISTINCT ruang FROM tbljadwalkuliah ORDER BY ruang ASC");

$currentPage = 'ruangan';
$page_title  = 'Edit Ruangan';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="ruangan.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data Ruangan
  </a>
  <span>›</span>
  <span>Edit Ruangan</span>
</div>

<div class="data-card" style="max-width:640px;">
  <div class="card-head">
    <h3><i class="fas fa-edit me-2" style="color:var(--primary)"></i>Form Edit Ruangan</h3>
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
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Kode Ruangan</label>
        <input type="text" name="kode_ruangan" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['kode_ruangan']) ?>" readonly 
               style="background:#f3f4f6; color:var(--muted);" />
        <small class="form-text text-muted" style="font-size:12px;">Kode tidak dapat diubah.</small>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Nama Ruangan</label>
        <select name="nama_ruangan" class="form-select" required>
          <option value="" disabled>-- Pilih Ruangan --</option>
          <?php while ($r = mysqli_fetch_array($ruang_list)): 
            $selected = ($r['ruang'] === $dataEdit['nama_ruangan']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($r['ruang']) ?>" <?= $selected ?>>
              <?= htmlspecialchars($r['ruang']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="ubah" class="btn-app">
        <i class="fas fa-save"></i> Simpan
      </button>
      <a href="ruangan.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>