<?php
session_start();
include "koneksi.php";

// Cek login dan role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$pesan      = '';
$pesan_type = '';

// Ambil NIM dari URL
$nim_param = $_GET['nim'] ?? '';
$nim_get = is_string($nim_param) ? trim($nim_param) : '';

// ✅ Keamanan: pastikan mahasiswa hanya bisa edit profil sendiri
$nim_session = $_SESSION['nim'] ?? ($_SESSION['ref_id'] ?? '');
if (!is_string($nim_session) || $nim_get === '' || $nim_get !== $nim_session) {
    header("Location: profil_mahasiswa.php");
    exit;
}

// Ambil data mahasiswa
$queryEdit  = "SELECT * FROM tblmhs2 WHERE nim = ?";
$stmt       = $koneksi->prepare($queryEdit);
$stmt->bind_param("s", $nim_get);
$stmt->execute();
$resultEdit = $stmt->get_result();

if (mysqli_num_rows($resultEdit) === 0) {
    header("Location: profil_mahasiswa.php");
    exit;
}

$dataEdit = mysqli_fetch_array($resultEdit);

// Proses update
if (isset($_POST["ubah"])) {
    $nama_mhs_input = $_POST["nama_mhs"] ?? null;
    $jns_kelamin = $_POST["jns_kelamin"] ?? null;
    $alamat_input = $_POST["alamat"] ?? null;
    $nama_mhs = is_string($nama_mhs_input) ? trim($nama_mhs_input) : null;
    $alamat = is_string($alamat_input) ? trim($alamat_input) : null;

    if (
        !is_string($nama_mhs)
        || $nama_mhs === ''
        || strlen($nama_mhs) > 150
        || !is_string($jns_kelamin)
        || !in_array($jns_kelamin, ['L', 'P'], true)
        || !is_string($alamat)
        || strlen($alamat) > 255
    ) {
        $pesan = "Data profil tidak valid.";
        $pesan_type = 'error';
    } else {
        $stmt2 = $koneksi->prepare(
            "UPDATE tblmhs2 SET nama_mhs=?, jns_kelamin=?, alamat=? WHERE nim=?"
        );
        $stmt2->bind_param("ssss", $nama_mhs, $jns_kelamin, $alamat, $nim_session);
        $result = $stmt2->execute();

        if ($result) {
            $pesan = "Profil berhasil diperbarui.";
            $pesan_type = 'success';
            $stmt = $koneksi->prepare("SELECT * FROM tblmhs2 WHERE nim = ?");
            $stmt->bind_param("s", $nim_session);
            $stmt->execute();
            $dataEdit = $stmt->get_result()->fetch_assoc();
        } else {
            $pesan = "Profil gagal diperbarui.";
            $pesan_type = 'error';
        }
    }
}

$currentPage = 'profil';
$page_title  = 'Edit Profil';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="profil_mahasiswa.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
  </a>
  <span>›</span>
  <span>Edit Profil</span>
</div>

<div class="data-card" style="max-width:860px;">
  <div class="card-head">
    <h3><i class="fas fa-user-edit me-2" style="color:var(--primary)"></i>Form Edit Profil</h3>
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
        <input type="text" class="form-control" value="<?= htmlspecialchars($dataEdit['prodi']) ?>" readonly />
        <small class="form-text text-muted" style="font-size:12px;">Perubahan program studi harus melalui administrator.</small>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Semester</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars((string) $dataEdit['semester']) ?>" readonly />
        <small class="form-text text-muted" style="font-size:12px;">Perubahan semester harus melalui administrator.</small>
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
        <i class="fas fa-save"></i> Simpan Perubahan
      </button>
      <a href="profil_mahasiswa.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>