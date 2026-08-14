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
    $kode_ruangan = mysqli_real_escape_string($koneksi, trim($_POST["kode_ruangan"]));
    $nama_ruangan = mysqli_real_escape_string($koneksi, trim($_POST["nama_ruangan"]));

    $query  = "INSERT INTO tbl_ruangan (kode_ruangan, nama_ruangan) 
               VALUES ('$kode_ruangan', '$nama_ruangan')";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        header("Location: ruangan.php");
        exit;
    } else {
        $pesan      = "Gagal menyimpan data. Pastikan Kode Ruangan belum terdaftar.";
        $pesan_type = 'error';
    }
}

// Ambil daftar ruang dari tbljadwalkuliah (sesuai logika asli)
$ruang_list = mysqli_query($koneksi, "SELECT DISTINCT ruang FROM tbljadwalkuliah ORDER BY ruang ASC");

$currentPage = 'ruangan';
$page_title  = 'Tambah Ruangan';
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
  <span>Tambah Ruangan</span>
</div>

<div class="data-card" style="max-width:640px;">
  <div class="card-head">
    <h3><i class="fas fa-door-closed me-2" style="color:var(--primary)"></i>Form Tambah Ruangan</h3>
  </div>

  <?php if ($pesan): ?>
    <div style="padding:12px 16px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; margin-bottom:18px; font-size:14px;">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($pesan) ?>
    </div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Kode Ruangan</label>
        <input type="text" name="kode_ruangan" class="form-control" required placeholder="Contoh: R001" />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Nama Ruangan</label>
        <select name="nama_ruangan" class="form-select" required>
          <option value="" disabled selected>-- Pilih Ruangan --</option>
          <?php while ($r = mysqli_fetch_array($ruang_list)): ?>
            <option value="<?= htmlspecialchars($r['ruang']) ?>">
              <?= htmlspecialchars($r['ruang']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="simpan" class="btn-app">
        <i class="fas fa-save"></i> Simpan
      </button>
      <a href="ruangan.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>