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
$id_get = $_GET['id_krsdetail'] ?? '';
if (!is_string($id_get)) {
    $id_get = '';
}

if ($id_get === '') {
    header("Location: krsdetail.php");
    exit;
}

// Ambil data krsdetail
$stmt_edit = $koneksi->prepare("SELECT * FROM tblkrsdetail WHERE id_krsdetail = ?");
$stmt_edit->bind_param("s", $id_get);
$stmt_edit->execute();
$resultEdit = $stmt_edit->get_result();

if (!$resultEdit || mysqli_num_rows($resultEdit) === 0) {
    header("Location: krsdetail.php");
    exit;
}

$dataEdit = mysqli_fetch_array($resultEdit);

// Proses update
if (isset($_POST["ubah"])) {
    $id_krsdetail = $id_get;
    $id_krs       = mysqli_real_escape_string($koneksi, trim($_POST["id_krs"]));
    $nim          = mysqli_real_escape_string($koneksi, trim($_POST["nim"]));
    $kode_mk      = mysqli_real_escape_string($koneksi, trim($_POST["kode_mk"]));

    $stmt_update = $koneksi->prepare(
        "UPDATE tblkrsdetail SET id_krs=?, nim=?, kode_mk=? WHERE id_krsdetail=?"
    );
    $stmt_update->bind_param("ssss", $id_krs, $nim, $kode_mk, $id_krsdetail);
    $result = $stmt_update->execute();

    if ($result) {
        $pesan      = "Data berhasil diperbarui.";
        $pesan_type = 'success';
        $dataEdit   = array_merge($dataEdit, $_POST);
    } else {
        $pesan      = "Data gagal diperbarui.";
        $pesan_type = 'error';
    }
}

/* ---------- DATA DROPDOWN ---------- */
$mk_list  = mysqli_query($koneksi, "SELECT * FROM tblmatkul ORDER BY kode_mk ASC");
$mhs_list = mysqli_query($koneksi, "SELECT nim, nama_mhs FROM tblmhs2 ORDER BY nim ASC");
$krs_list = mysqli_query($koneksi, "SELECT k.id_krs, k.nim, k.semester, k.tahun_ajaran FROM tblkrs k ORDER BY k.id_krs ASC");

$currentPage = 'krsdetail';
$page_title  = 'Edit KRS Detail';
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
  <span>Edit KRS Detail</span>
</div>

<div class="data-card" style="max-width:640px;">
  <div class="card-head">
    <h3><i class="fas fa-edit me-2" style="color:var(--primary)"></i>Form Edit KRS Detail</h3>
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
        <label class="form-label">ID KRS Detail</label>
        <input type="text" name="id_krsdetail" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['id_krsdetail']) ?>" readonly 
               style="background:#f3f4f6; color:var(--muted);" />
        <small class="form-text text-muted" style="font-size:12px;">ID tidak dapat diubah.</small>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">NIM Mahasiswa</label>
        <select name="nim" class="form-select" required>
          <option value="" disabled>-- Pilih Mahasiswa --</option>
          <?php while ($m = mysqli_fetch_array($mhs_list)): 
            $selected = ($m['nim'] == $dataEdit['nim']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($m['nim']) ?>" <?= $selected ?>>
              <?= htmlspecialchars($m['nim']) ?> - <?= htmlspecialchars($m['nama_mhs']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">KRS (Mahasiswa — Semester — Tahun Ajaran)</label>
      <select name="id_krs" class="form-select" required>
        <option value="" disabled>-- Pilih KRS --</option>
        <?php while ($k = mysqli_fetch_array($krs_list)): 
          $selected = ($k['id_krs'] == $dataEdit['id_krs']) ? 'selected' : '';
        ?>
          <option value="<?= htmlspecialchars($k['id_krs']) ?>" <?= $selected ?>>
            #<?= htmlspecialchars($k['id_krs']) ?> — <?= htmlspecialchars($k['nim']) ?> (Smt <?= htmlspecialchars($k['semester']) ?>, <?= htmlspecialchars($k['tahun_ajaran']) ?>)
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-4">
      <label class="form-label">Kode Mata Kuliah</label>
      <select name="kode_mk" class="form-select" required>
        <option value="" disabled>-- Pilih Mata Kuliah --</option>
        <?php while ($mk = mysqli_fetch_array($mk_list)): 
          $selected = ($mk['kode_mk'] == $dataEdit['kode_mk']) ? 'selected' : '';
        ?>
          <option value="<?= htmlspecialchars($mk['kode_mk']) ?>" <?= $selected ?>>
            <?= htmlspecialchars($mk['kode_mk']) ?> - <?= htmlspecialchars($mk['nama_mk']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="ubah" class="btn-app">
        <i class="fas fa-save"></i> Simpan Perubahan
      </button>
      <a href="krsdetail.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>