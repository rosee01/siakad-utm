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
    $kode_prodi = mysqli_real_escape_string($koneksi, trim($_POST["kode_prodi"]));
    $nama_prodi = mysqli_real_escape_string($koneksi, trim($_POST["nama_prodi"]));

    $query  = "INSERT INTO tblprodi (kode_prodi, nama_prodi) VALUES ('$kode_prodi', '$nama_prodi')";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        header("Location: prodi.php");
        exit;
    } else {
        $pesan      = "Gagal menyimpan data. Pastikan Kode Prodi belum terdaftar.";
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
$page_title  = 'Tambah Prodi';
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
  <span>Tambah Prodi</span>
</div>

<div class="data-card" style="max-width:640px;">
  <div class="card-head">
    <h3><i class="fas fa-book me-2" style="color:var(--primary)"></i>Form Tambah Prodi</h3>
  </div>

  <?php if ($pesan): ?>
    <div style="padding:12px 16px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; margin-bottom:18px; font-size:14px;">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($pesan) ?>
    </div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <div class="mb-3">
      <label class="form-label">Kode Prodi</label>
      <input type="text" name="kode_prodi" class="form-control" required placeholder="Contoh: TI" />
      <small class="form-text text-muted" style="font-size:12px;">Kode singkat, misalnya TI, SI, HI.</small>
    </div>

    <div class="mb-4">
      <label class="form-label">Nama Program Studi</label>
      <select name="nama_prodi" class="form-select" required>
        <option value="" disabled selected>-- Pilih Program Studi --</option>
        <?php foreach ($listProdi as $key => $label): ?>
          <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="simpan" class="btn-app">
        <i class="fas fa-save"></i> Simpan
      </button>
      <a href="prodi.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>