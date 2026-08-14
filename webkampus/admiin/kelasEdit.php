<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pesan      = '';
$pesan_type = '';

// Ambil kode dari URL
$kode_get = mysqli_real_escape_string($koneksi, $_GET['kode_kelas'] ?? '');

if ($kode_get === '') {
    header("Location: kelas.php");
    exit;
}

// Ambil data kelas
$queryEdit  = "SELECT * FROM tbl_kelas WHERE kode_kelas='$kode_get'";
$resultEdit = mysqli_query($koneksi, $queryEdit);

if (!$resultEdit || mysqli_num_rows($resultEdit) === 0) {
    header("Location: kelas.php");
    exit;
}

$dataEdit = mysqli_fetch_array($resultEdit);

// Proses update
if (isset($_POST["ubah"])) {
    $kode_kelas = mysqli_real_escape_string($koneksi, trim($_POST["kode_kelas"]));
    $nama_kelas = mysqli_real_escape_string($koneksi, trim($_POST["nama_kelas"]));
    $kode_prodi = mysqli_real_escape_string($koneksi, trim($_POST["kode_prodi"]));

    $query = "UPDATE tbl_kelas SET nama_kelas='$nama_kelas', kode_prodi='$kode_prodi' WHERE kode_kelas='$kode_kelas'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $pesan      = "Data berhasil diperbarui.";
        $pesan_type = 'success';
        // Refresh data
        $dataEdit = array_merge($dataEdit, $_POST);
    } else {
        $pesan      = "Data gagal diperbarui.";
        $pesan_type = 'error';
    }
}

/* ---------- DATA DROPDOWN PRODI ---------- */
$prodi_list = mysqli_query($koneksi, "SELECT * FROM tblprodi ORDER BY kode_prodi ASC");

$currentPage = 'kelas';
$page_title  = 'Edit Kelas';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="kelas.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data Kelas
  </a>
  <span>›</span>
  <span>Edit Kelas</span>
</div>

<div class="data-card" style="max-width:640px;">
  <div class="card-head">
    <h3><i class="fas fa-edit me-2" style="color:var(--primary)"></i>Form Edit Kelas</h3>
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
      <label class="form-label">Kode Kelas</label>
      <input type="text" name="kode_kelas" class="form-control" 
             value="<?= htmlspecialchars($dataEdit['kode_kelas']) ?>" readonly 
             style="background:#f3f4f6; color:var(--muted);" />
      <small class="form-text text-muted" style="font-size:12px;">Kode kelas tidak dapat diubah.</small>
    </div>

    <div class="mb-3">
      <label class="form-label">Nama Kelas</label>
      <input type="text" name="nama_kelas" class="form-control" 
             value="<?= htmlspecialchars($dataEdit['nama_kelas']) ?>" required />
    </div>

    <div class="mb-4">
      <label class="form-label">Program Studi</label>
      <select name="kode_prodi" class="form-select" required>
        <option value="" disabled>-- Pilih Program Studi --</option>
        <?php while ($p = mysqli_fetch_array($prodi_list)): 
          $selected = ($p['kode_prodi'] === $dataEdit['kode_prodi']) ? 'selected' : '';
        ?>
          <option value="<?= htmlspecialchars($p['kode_prodi']) ?>" <?= $selected ?>>
            <?= htmlspecialchars($p['kode_prodi']) ?> - <?= htmlspecialchars($p['nama_prodi']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="ubah" class="btn-app">
        <i class="fas fa-save"></i> Simpan Perubahan
      </button>
      <a href="kelas.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>