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
$id_get = mysqli_real_escape_string($koneksi, $_GET['id_jadwal'] ?? '');

if ($id_get === '') {
    header("Location: jadwalkuliah.php");
    exit;
}

// Ambil data jadwal
$stmt_edit = $koneksi->prepare("SELECT * FROM tbljadwalkuliah WHERE id_jadwal = ?");
$stmt_edit->bind_param("s", $id_get);
$stmt_edit->execute();
$resultEdit = $stmt_edit->get_result();

if (!$resultEdit || mysqli_num_rows($resultEdit) === 0) {
    header("Location: jadwalkuliah.php");
    exit;
}

$dataEdit = mysqli_fetch_array($resultEdit);

// Proses update
if (isset($_POST["ubah"])) {
    $id_jadwal   = mysqli_real_escape_string($koneksi, trim($_POST["id_jadwal"]));
    $matakuliah  = mysqli_real_escape_string($koneksi, trim($_POST["matakuliah"]));
    $dosen       = mysqli_real_escape_string($koneksi, trim($_POST["dosen"]));
    $hari        = mysqli_real_escape_string($koneksi, $_POST["hari"]);
    $jam_mulai   = mysqli_real_escape_string($koneksi, $_POST["jam_mulai"]);
    $jam_selesai = mysqli_real_escape_string($koneksi, $_POST["jam_selesai"]);
    $semester    = intval($_POST["semester"]);
    $ruang       = mysqli_real_escape_string($koneksi, $_POST["ruang"]);
    $kelas       = mysqli_real_escape_string($koneksi, trim($_POST["kelas"]));

    $stmt_update = $koneksi->prepare(
        "UPDATE tbljadwalkuliah
         SET matakuliah=?, dosen=?, hari=?, jam_mulai=?, jam_selesai=?, semester=?, ruang=?, kelas=?
         WHERE id_jadwal=?"
    );
    $stmt_update->bind_param(
        "sssssisss",
        $matakuliah,
        $dosen,
        $hari,
        $jam_mulai,
        $jam_selesai,
        $semester,
        $ruang,
        $kelas,
        $id_get
    );
    $result = $stmt_update->execute();

    if ($result) {
        $pesan      = "Data berhasil diperbarui.";
        $pesan_type = 'success';
        // Refresh data
        $dataEdit = array_merge($dataEdit, $_POST);
    } else {
        $pesan      = "Data gagal disimpan.";
        $pesan_type = 'error';
    }
}

/* ---------- DATA DROPDOWN ---------- */
$mk_list    = mysqli_query($koneksi, "SELECT * FROM tblmatkul ORDER BY nama_mk ASC");
$dosen_list = mysqli_query($koneksi, "SELECT * FROM tbldosen ORDER BY nama_dosen ASC");
$sem_list   = [1, 2, 3, 4, 5, 6, 7, 8]; // semester tetap 1-8, tidak tergantung data mahasiswa yang ada
$kls_list   = mysqli_query($koneksi, "SELECT * FROM tbl_kelas ORDER BY nama_kelas ASC");

$currentPage = 'jadwal';
$page_title  = 'Edit Jadwal Kuliah';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="jadwalkuliah.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data Jadwal
  </a>
  <span>›</span>
  <span>Edit Jadwal Kuliah</span>
</div>

<div class="data-card" style="max-width:780px;">
  <div class="card-head">
    <h3><i class="fas fa-calendar-check me-2" style="color:var(--primary)"></i>Form Edit Jadwal Kuliah</h3>
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
        <label class="form-label">ID Jadwal</label>
        <input type="text" name="id_jadwal" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['id_jadwal']) ?>" readonly 
               style="background:#f3f4f6; color:var(--muted);" />
        <small class="form-text text-muted" style="font-size:12px;">ID tidak dapat diubah.</small>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Hari</label>
        <select name="hari" class="form-select" required>
          <?php
          $days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
          foreach ($days as $d):
            $selected = ($d === $dataEdit['hari']) ? 'selected' : '';
          ?>
            <option value="<?= $d ?>" <?= $selected ?>><?= $d ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Mata Kuliah</label>
        <select name="matakuliah" class="form-select" required>
          <option value="" disabled>-- Pilih Mata Kuliah --</option>
          <?php while ($mk = mysqli_fetch_array($mk_list)): 
            $selected = ($mk['nama_mk'] === $dataEdit['matakuliah']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($mk['nama_mk']) ?>" <?= $selected ?>>
              <?= htmlspecialchars($mk['nama_mk']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Dosen</label>
        <select name="dosen" class="form-select" required>
          <option value="" disabled>-- Pilih Dosen --</option>
          <?php while ($d = mysqli_fetch_array($dosen_list)): 
            $selected = ($d['nama_dosen'] === $dataEdit['dosen']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($d['nama_dosen']) ?>" <?= $selected ?>>
              <?= htmlspecialchars($d['nama_dosen']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Jam Mulai</label>
        <input type="time" name="jam_mulai" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['jam_mulai']) ?>" required />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Jam Selesai</label>
        <input type="time" name="jam_selesai" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['jam_selesai']) ?>" required />
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
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
      <div class="col-md-4 mb-3">
        <label class="form-label">Ruang</label>
        <select name="ruang" class="form-select" required>
          <option value="" disabled>-- Pilih Ruang --</option>
          <?php
          $rooms = ['Dahlia','Flamboyan','Edelwais','Bugenvil','Sakura','Anggrek','Kamboja','L2','L3'];
          foreach ($rooms as $r):
            $selected = ($r === $dataEdit['ruang']) ? 'selected' : '';
          ?>
            <option value="<?= $r ?>" <?= $selected ?>><?= $r ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Kelas</label>
        <select name="kelas" class="form-select" required>
          <option value="" disabled>-- Pilih Kelas --</option>
          <?php while ($k = mysqli_fetch_array($kls_list)): 
            $selected = ($k['nama_kelas'] === $dataEdit['kelas']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($k['nama_kelas']) ?>" <?= $selected ?>>
              <?= htmlspecialchars($k['nama_kelas']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="ubah" class="btn-app">
        <i class="fas fa-save"></i> Simpan Perubahan
      </button>
      <a href="jadwalkuliah.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>