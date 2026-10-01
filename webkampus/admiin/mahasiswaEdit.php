<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pesan      = '';
$pesan_type = '';

// Ambil NIM dari URL
$nim_get = $_GET['nim'] ?? '';
if (!is_string($nim_get)) {
    $nim_get = '';
}

if ($nim_get === '') {
    header("Location: mahasiswa.php");
    exit;
}

// Ambil data mahasiswa
$stmt_edit = $koneksi->prepare("SELECT * FROM tblmhs2 WHERE nim = ?");
$stmt_edit->bind_param("s", $nim_get);
$stmt_edit->execute();
$resultEdit = $stmt_edit->get_result();

if (!$resultEdit || mysqli_num_rows($resultEdit) === 0) {
    header("Location: mahasiswa.php");
    exit;
}

$dataEdit = mysqli_fetch_array($resultEdit);

// Proses update
if (isset($_POST["ubah"])) {
    $nim         = mysqli_real_escape_string($koneksi, trim($_POST["nim"]));
    $nama_mhs    = mysqli_real_escape_string($koneksi, trim($_POST["nama_mhs"]));
    $prodi       = mysqli_real_escape_string($koneksi, trim($_POST["prodi"]));
    $semester    = intval($_POST["semester"]);
    $jns_kelamin = mysqli_real_escape_string($koneksi, $_POST["jns_kelamin"]);
    $alamat      = mysqli_real_escape_string($koneksi, trim($_POST["alamat"]));

    $stmt_update = $koneksi->prepare(
        "UPDATE tblmhs2 SET nama_mhs=?, prodi=?, semester=?, jns_kelamin=?, alamat=? WHERE nim=?"
    );
    $stmt_update->bind_param("ssssss", $nama_mhs, $prodi, $semester, $jns_kelamin, $alamat, $nim_get);
    $result = $stmt_update->execute();

    if ($result) {
        $pesan      = "Data mahasiswa berhasil diperbarui.";
        $pesan_type = 'success';
        // Refresh data
        $dataEdit = array_merge($dataEdit, $_POST);
    } else {
        $pesan      = "Data gagal diperbarui.";
        $pesan_type = 'error';
    }
}

/* ---------- DATA DROPDOWN PRODI ---------- */
$prodi_list = mysqli_query($koneksi, "SELECT * FROM tblprodi ORDER BY nama_prodi ASC");

$currentPage = 'mahasiswa';
$page_title  = 'Edit Mahasiswa';
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
  <span>Edit Mahasiswa</span>
</div>

<div class="data-card" style="max-width:780px;">
  <div class="card-head">
    <h3><i class="fas fa-user-edit me-2" style="color:var(--primary)"></i>Form Edit Mahasiswa</h3>
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
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">NIM</label>
        <input type="text" name="nim" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['nim']) ?>" readonly 
               style="background:#f3f4f6; color:var(--muted);" />
        <small class="form-text text-muted" style="font-size:12px;">NIM tidak dapat diubah.</small>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Nama Mahasiswa</label>
        <input type="text" name="nama_mhs" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['nama_mhs']) ?>" required />
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Program Studi</label>
        <select name="prodi" class="form-select" required>
          <option value="" disabled>-- Pilih Program Studi --</option>
          <?php while ($p = mysqli_fetch_array($prodi_list)): 
            $selected = ($p['nama_prodi'] === $dataEdit['prodi']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($p['nama_prodi']) ?>" <?= $selected ?>>
              <?= htmlspecialchars($p['kode_prodi']) ?> - <?= htmlspecialchars($p['nama_prodi']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Semester</label>
        <select name="semester" class="form-select" required>
          <?php for ($i = 1; $i <= 14; $i++): 
            $selected = ($i == $dataEdit['semester']) ? 'selected' : '';
          ?>
            <option value="<?= $i ?>" <?= $selected ?>><?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Jenis Kelamin</label>
      <div class="d-flex gap-4 mt-1">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="jns_kelamin" id="jk_l" value="L" 
                 <?= ($dataEdit['jns_kelamin'] === 'L') ? 'checked' : '' ?> />
          <label class="form-check-label" for="jk_l">Laki-laki</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="jns_kelamin" id="jk_p" value="P" 
                 <?= ($dataEdit['jns_kelamin'] === 'P') ? 'checked' : '' ?> />
          <label class="form-check-label" for="jk_p">Perempuan</label>
        </div>
      </div>
    </div>

    <div class="mb-4">
      <label class="form-label">Alamat Lengkap</label>
      <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($dataEdit['alamat']) ?></textarea>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="ubah" class="btn-app">
        <i class="fas fa-save"></i> Ubah
      </button>
      <a href="mahasiswa.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>