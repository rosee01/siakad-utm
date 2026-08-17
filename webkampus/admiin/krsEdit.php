<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pesan      = '';
$pesan_type = '';

// Ambil ID dari URL
$id_get = mysqli_real_escape_string($koneksi, $_GET['id_krs'] ?? '');

if ($id_get === '') {
    header("Location: krs.php");
    exit;
}

// Ambil data krs
$queryEdit  = "SELECT * FROM tblkrs WHERE id_krs='$id_get'";
$resultEdit = mysqli_query($koneksi, $queryEdit);

if (!$resultEdit || mysqli_num_rows($resultEdit) === 0) {
    header("Location: krs.php");
    exit;
}

$dataEdit = mysqli_fetch_array($resultEdit);

// Proses update
if (isset($_POST["ubah"])) {
    $id_krs       = mysqli_real_escape_string($koneksi, trim($_POST["id_krs"]));
    $nidn         = mysqli_real_escape_string($koneksi, trim($_POST["nidn"]));
    $nim          = mysqli_real_escape_string($koneksi, trim($_POST["nim"]));
    $id_jadwal    = mysqli_real_escape_string($koneksi, trim($_POST["id_jadwal"]));
    $semester     = intval($_POST["semester"]);
    $tahun_ajaran = mysqli_real_escape_string($koneksi, trim($_POST["tahun_ajaran"]));

    $query = "UPDATE tblkrs SET nidn='$nidn', nim='$nim', id_jadwal='$id_jadwal', semester='$semester', tahun_ajaran='$tahun_ajaran' WHERE id_krs='$id_krs'";
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

/* ---------- DATA DROPDOWN ---------- */
$dosen_list = mysqli_query($koneksi, "SELECT nidn, nama_dosen FROM tbldosen ORDER BY nama_dosen ASC");
$mhs_list   = mysqli_query($koneksi, "SELECT nim, nama_mhs FROM tblmhs2 ORDER BY nama_mhs ASC");
$sem_list   = [1, 2, 3, 4, 5, 6, 7, 8]; // semester tetap 1-8, tidak tergantung data mahasiswa yang ada

$currentPage = 'krs';
$page_title  = 'Edit KRS';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="krs.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data KRS
  </a>
  <span>›</span>
  <span>Edit KRS</span>
</div>

<div class="data-card" style="max-width:780px;">
  <div class="card-head">
    <h3><i class="fas fa-edit me-2" style="color:var(--primary)"></i>Form Edit KRS</h3>
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
        <label class="form-label">ID KRS</label>
        <input type="text" name="id_krs" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['id_krs']) ?>" readonly 
               style="background:#f3f4f6; color:var(--muted);" />
        <small class="form-text text-muted" style="font-size:12px;">ID tidak dapat diubah.</small>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">ID Jadwal</label>
        <input type="text" name="id_jadwal" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['id_jadwal']) ?>" readonly 
               style="background:#f3f4f6; color:var(--muted);" />
        <small class="form-text text-muted" style="font-size:12px;">ID Jadwal tidak dapat diubah.</small>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">NIDN Dosen</label>
        <select name="nidn" class="form-select" required>
          <option value="" disabled>-- Pilih Dosen --</option>
          <?php while ($d = mysqli_fetch_array($dosen_list)): 
            $selected = ($d['nidn'] === $dataEdit['nidn']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($d['nidn']) ?>" <?= $selected ?>>
              <?= htmlspecialchars($d['nidn']) ?> - <?= htmlspecialchars($d['nama_dosen']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">NIM Mahasiswa</label>
        <select name="nim" class="form-select" required>
          <option value="" disabled>-- Pilih Mahasiswa --</option>
          <?php while ($m = mysqli_fetch_array($mhs_list)): 
            $selected = ($m['nim'] === $dataEdit['nim']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($m['nim']) ?>" <?= $selected ?>>
              <?= htmlspecialchars($m['nim']) ?> - <?= htmlspecialchars($m['nama_mhs']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Semester</label>
        <select name="semester" class="form-select" required>
          <option value="" disabled>-- Pilih Semester --</option>
          <?php foreach ($sem_list as $s): 
            $selected = ($s == $dataEdit['semester']) ? 'selected' : '';
          ?>
            <option value="<?= $s ?>" <?= $selected ?>>
              <?= $s ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Tahun Ajaran</label>
        <input type="text" name="tahun_ajaran" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['tahun_ajaran']) ?>" readonly 
               style="background:#f3f4f6; color:var(--muted);" />
        <small class="form-text text-muted" style="font-size:12px;">Tahun ajaran tidak dapat diubah.</small>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="ubah" class="btn-app">
        <i class="fas fa-save"></i> Simpan Perubahan
      </button>
      <a href="krs.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>