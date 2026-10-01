<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pesan      = '';
$pesan_type = '';

$kode_get = $_GET['kode_prodi'] ?? '';
if (!is_string($kode_get)) {
    $kode_get = '';
}

if ($kode_get === '') {
    header("Location: prodi.php");
    exit;
}

$stmt_edit = $koneksi->prepare("SELECT * FROM tblprodi WHERE kode_prodi = ?");
$stmt_edit->bind_param("s", $kode_get);
$stmt_edit->execute();
$resultEdit = $stmt_edit->get_result();

if (!$resultEdit || mysqli_num_rows($resultEdit) === 0) {
    header("Location: prodi.php");
    exit;
}

$dataEdit = mysqli_fetch_array($resultEdit);

if (isset($_POST["ubah"])) {
    $nama_prodi = trim($_POST["nama_prodi"] ?? '');
    $stmt_update = $koneksi->prepare("UPDATE tblprodi SET nama_prodi=? WHERE kode_prodi=?");
    $stmt_update->bind_param("ss", $nama_prodi, $kode_get);
    $result = $stmt_update->execute();

    if ($result) {
        $pesan      = "Data prodi berhasil diperbarui.";
        $pesan_type = 'success';
        $dataEdit   = array_merge($dataEdit, $_POST);
    } else {
        $pesan      = "Data gagal diperbarui.";
        $pesan_type = 'error';
    }
}

$listProdi = [
    "S1 TEKNIK INFORMATIKA"   => "Teknik Informatika",
    "S1 SISTEM INFORMASI"     => "Sistem Informasi",
    "S1 TEKNOLOGI INFORMASI"  => "Teknologi Informasi",
    "S1 MANAJEMEN INFORMASI"  => "Manajemen Informasi",
    "S1 HUKUM"                => "Hukum"
];

$currentPage = 'prodi';
$page_title  = 'Edit Prodi';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="prodi.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data Prodi
  </a>
  <span>›</span>
  <span>Edit Prodi</span>
</div>

<div class="data-card" style="max-width:640px;">
  <div class="card-head">
    <h3><i class="fas fa-edit me-2" style="color:var(--primary)"></i>Form Edit Prodi</h3>
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
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label">Kode Prodi</label>
      <input type="text" name="kode_prodi" class="form-control" 
             value="<?= htmlspecialchars($dataEdit['kode_prodi']) ?>" readonly 
             style="background:#f3f4f6; color:var(--muted);" />
      <small class="form-text text-muted" style="font-size:12px;">Kode Prodi tidak dapat diubah.</small>
    </div>

    <div class="mb-4">
      <label class="form-label">Nama Program Studi</label>
      <select name="nama_prodi" class="form-select" required>
        <option value="" disabled>-- Pilih Program Studi --</option>
        <?php foreach ($listProdi as $key => $label): 
          $selected = ($dataEdit['nama_prodi'] === $key) ? 'selected' : '';
        ?>
          <option value="<?= htmlspecialchars($key) ?>" <?= $selected ?>>
            <?= htmlspecialchars($label) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="ubah" class="btn-app">
        <i class="fas fa-save"></i> Ubah
      </button>
      <a href="prodi.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>