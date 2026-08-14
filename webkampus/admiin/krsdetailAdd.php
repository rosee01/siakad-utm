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
    $id_krsdetail = mysqli_real_escape_string($koneksi, trim($_POST["id_krsdetail"]));
    $nim          = mysqli_real_escape_string($koneksi, trim($_POST["nim"]));
    $kode_mk      = mysqli_real_escape_string($koneksi, trim($_POST["kode_mk"]));

    $query  = "INSERT INTO tblkrsdetail (id_krsdetail, nim, kode_mk) 
               VALUES ('$id_krsdetail', '$nim', '$kode_mk')";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        header("Location: krsdetail.php");
        exit;
    } else {
        $pesan      = "Data gagal disimpan. Pastikan ID KRS Detail belum terdaftar.";
        $pesan_type = 'error';
    }
}

/* ---------- DATA DROPDOWN ---------- */
$mk_list  = mysqli_query($koneksi, "SELECT * FROM tblmatkul ORDER BY kode_mk ASC");
$mhs_list = mysqli_query($koneksi, "SELECT nim, nama_mhs FROM tblmhs2 ORDER BY nim ASC");

$currentPage = 'krsdetail';
$page_title  = 'Tambah KRS Detail';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="krsdetail.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data KRS Detail
  </a>
  <span>›</span>
  <span>Tambah KRS Detail</span>
</div>

<div class="data-card" style="max-width:640px;">
  <div class="card-head">
    <h3><i class="fas fa-file-alt me-2" style="color:var(--primary)"></i>Form Tambah KRS Detail</h3>
  </div>

  <?php if ($pesan): ?>
    <div style="padding:12px 16px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; margin-bottom:18px; font-size:14px;">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($pesan) ?>
    </div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <div class="mb-3">
      <label class="form-label">ID KRS Detail</label>
      <input type="text" name="id_krsdetail" class="form-control" required placeholder="Contoh: KD001" />
    </div>

    <div class="mb-3">
      <label class="form-label">NIM Mahasiswa</label>
      <select name="nim" class="form-select" required>
        <option value="" disabled selected>-- Pilih Mahasiswa --</option>
        <?php while ($m = mysqli_fetch_array($mhs_list)): ?>
          <option value="<?= htmlspecialchars($m['nim']) ?>">
            <?= htmlspecialchars($m['nim']) ?> - <?= htmlspecialchars($m['nama_mhs']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-4">
      <label class="form-label">Kode Mata Kuliah</label>
      <select name="kode_mk" class="form-select" required>
        <option value="" disabled selected>-- Pilih Mata Kuliah --</option>
        <?php while ($mk = mysqli_fetch_array($mk_list)): ?>
          <option value="<?= htmlspecialchars($mk['kode_mk']) ?>">
            <?= htmlspecialchars($mk['kode_mk']) ?> - <?= htmlspecialchars($mk['nama_mk']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="simpan" class="btn-app">
        <i class="fas fa-save"></i> Simpan
      </button>
      <a href="krsdetail.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>