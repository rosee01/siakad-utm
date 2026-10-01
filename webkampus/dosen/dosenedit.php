<?php
session_start();
include "../koneksi.php";

// Cek login dan pastikan role dosen
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../login.php");
    exit;
}

// Ambil NIDN dari session
$nidn = $_SESSION['ref_id'] ?? '';
if (empty($nidn)) {
    die("<div class='alert alert-danger'>NIDN tidak ditemukan di session.</div>");
}

// Ambil data dosen dari database
$queryEdit = "SELECT * FROM tbldosen WHERE nidn=?";
$stmt = $koneksi->prepare($queryEdit);
$stmt->bind_param("s", $nidn);
$stmt->execute();
$dataEdit = $stmt->get_result()->fetch_assoc();

if (!$dataEdit) {
    die("<div class='alert alert-warning'>Data dosen tidak ditemukan.</div>");
}

$pesan      = '';
$pesan_type = '';

// Handle form update
if (isset($_POST["ubah"])) {
    $nama_dosen  = trim($_POST["nama_dosen"]);
    $email       = trim($_POST["email"]);
    $jns_kelamin = $_POST["jns_kelamin"];
    $telpn       = trim($_POST["telpn"]);

    $query = "UPDATE tbldosen SET nama_dosen=?, email=?, jns_kelamin=?, telpn=? WHERE nidn=?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("sssss", $nama_dosen, $email, $jns_kelamin, $telpn, $nidn);
    $result = $stmt->execute();

    if ($result) {
        $pesan      = "Profil berhasil diperbarui.";
        $pesan_type = 'success';
        // Refresh data
        $dataEdit["nama_dosen"]  = $nama_dosen;
        $dataEdit["email"]       = $email;
        $dataEdit["jns_kelamin"] = $jns_kelamin;
        $dataEdit["telpn"]       = $telpn;
    } else {
        $pesan      = "Profil gagal diperbarui.";
        $pesan_type = 'error';
    }
}

$currentPage = 'profil';
$page_title  = 'Edit Profil Dosen';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="dashboard.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
  </a>
  <span>›</span>
  <span>Edit Profil</span>
</div>

<div class="data-card" style="max-width:780px;">
  <div class="card-head">
    <h3><i class="fas fa-user-edit me-2" style="color:var(--primary)"></i>Edit Profil Dosen</h3>
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
        <label class="form-label">NIDN</label>
        <input type="text" name="nidn" class="form-control"
               value="<?= htmlspecialchars($dataEdit['nidn']) ?>" readonly
               style="background:#f3f4f6; color:var(--muted);" />
        <small class="form-text text-muted" style="font-size:12px;">NIDN tidak dapat diubah.</small>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Nama Dosen</label>
        <input type="text" name="nama_dosen" class="form-control"
               value="<?= htmlspecialchars($dataEdit['nama_dosen']) ?>" required />
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control"
               value="<?= htmlspecialchars($dataEdit['email']) ?>" required />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">No. Telepon</label>
        <input type="text" name="telpn" class="form-control"
               value="<?= htmlspecialchars($dataEdit['telpn']) ?>" required />
      </div>
    </div>

    <div class="mb-4">
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

    <div class="d-flex gap-2">
      <button type="submit" name="ubah" class="btn-app">
        <i class="fas fa-save"></i> Simpan Perubahan
      </button>
      <a href="dashboard.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>