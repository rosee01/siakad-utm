<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pesan      = '';
$pesan_type = '';

// Ambil kode dari URL
$kode_get = mysqli_real_escape_string($koneksi, $_GET['kode_mk'] ?? '');

if ($kode_get === '') {
    header("Location: matkul.php");
    exit;
}

// Ambil data matkul
$queryEdit  = "SELECT * FROM tblmatkul WHERE kode_mk='$kode_get'";
$resultEdit = mysqli_query($koneksi, $queryEdit);

if (!$resultEdit || mysqli_num_rows($resultEdit) === 0) {
    header("Location: matkul.php");
    exit;
}

$dataEdit = mysqli_fetch_array($resultEdit);

// Proses update
if (isset($_POST["ubah"])) {
    $kode_mk     = mysqli_real_escape_string($koneksi, trim($_POST["kode_mk"]));
    $nama_mk     = mysqli_real_escape_string($koneksi, trim($_POST["nama_mk"]));
    $sks         = intval($_POST["sks"]);
    $semester    = intval($_POST["semester"]);
    $id_prodi    = mysqli_real_escape_string($koneksi, trim($_POST["id_prodi"]));
    $nama_dosen  = mysqli_real_escape_string($koneksi, trim($_POST["nama_dosen"]));
    $hari        = mysqli_real_escape_string($koneksi, $_POST["hari"]);
    $jam_mulai   = mysqli_real_escape_string($koneksi, $_POST["jam_mulai"]);
    $jam_selesai = mysqli_real_escape_string($koneksi, $_POST["jam_selesai"]);
    $ruang       = mysqli_real_escape_string($koneksi, $_POST["ruang"]);

    $query = "UPDATE tblmatkul SET 
                nama_mk='$nama_mk', 
                sks='$sks', 
                semester='$semester',
                id_prodi='$id_prodi', 
                nama_dosen='$nama_dosen', 
                hari='$hari', 
                jam_mulai='$jam_mulai', 
                jam_selesai='$jam_selesai', 
                ruang='$ruang' 
              WHERE kode_mk='$kode_mk'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $pesan      = "Data mata kuliah berhasil diperbarui.";
        $pesan_type = 'success';
        // Refresh data
        $dataEdit = array_merge($dataEdit, $_POST);
    } else {
        $pesan      = "Data gagal diperbarui.";
        $pesan_type = 'error';
    }
}

/* ---------- DATA DROPDOWN (DISTINCT agar tidak duplikat) ---------- */
$prodi_list = mysqli_query($koneksi, "SELECT * FROM tblprodi ORDER BY nama_prodi ASC");
$dosen_list = mysqli_query($koneksi, "SELECT DISTINCT nama_dosen FROM tbldosen ORDER BY nama_dosen ASC");
$sem_list   = [1, 2, 3, 4, 5, 6, 7, 8]; // semester tetap 1-8, tidak tergantung data mahasiswa yang ada
$ruang_list = mysqli_query($koneksi, "SELECT DISTINCT ruang FROM tbljadwalkuliah ORDER BY ruang ASC");

$currentPage = 'matkul';
$page_title  = 'Edit Mata Kuliah';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="matkul.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data Mata Kuliah
  </a>
  <span>›</span>
  <span>Edit Mata Kuliah</span>
</div>

<div class="data-card" style="max-width:860px;">
  <div class="card-head">
    <h3><i class="fas fa-edit me-2" style="color:var(--primary)"></i>Form Edit Mata Kuliah</h3>
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
    <!-- Baris 1: Kode MK (readonly), Nama MK -->
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Kode MK</label>
        <input type="text" name="kode_mk" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['kode_mk']) ?>" readonly 
               style="background:#f3f4f6; color:var(--muted);" />
        <small class="form-text text-muted" style="font-size:12px;">Kode MK tidak dapat diubah.</small>
      </div>
      <div class="col-md-8 mb-3">
        <label class="form-label">Nama Mata Kuliah</label>
        <input type="text" name="nama_mk" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['nama_mk']) ?>" required />
      </div>
    </div>

    <!-- Baris 2: SKS, Semester, Prodi -->
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">SKS</label>
        <select name="sks" class="form-select" required>
          <?php foreach ([2, 3, 4] as $val): 
            $selected = ($dataEdit['sks'] == $val) ? 'selected' : '';
          ?>
            <option value="<?= $val ?>" <?= $selected ?>><?= $val ?></option>
          <?php endforeach; ?>
        </select>
      </div>
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
        <label class="form-label">Program Studi</label>
        <select name="id_prodi" class="form-select" required>
          <option value="" disabled>-- Pilih Prodi --</option>
          <?php while ($p = mysqli_fetch_array($prodi_list)): 
            $selected = ($p['kode_prodi'] == $dataEdit['id_prodi']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($p['kode_prodi']) ?>" <?= $selected ?>>
              <?= htmlspecialchars($p['kode_prodi']) ?> - <?= htmlspecialchars($p['nama_prodi']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <!-- Baris 3: Dosen -->
    <div class="mb-3">
      <label class="form-label">Nama Dosen Pengampu</label>
      <select name="nama_dosen" class="form-select" required>
        <option value="" disabled>-- Pilih Dosen --</option>
        <?php while ($d = mysqli_fetch_array($dosen_list)): 
          $selected = ($d['nama_dosen'] === $dataEdit['nama_dosen']) ? 'selected' : '';
        ?>
          <option value="<?= htmlspecialchars($d['nama_dosen']) ?>" <?= $selected ?>>
            <?= htmlspecialchars($d['nama_dosen']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <!-- Baris 4: Hari, Jam Mulai, Jam Selesai, Ruang -->
    <div class="row">
      <div class="col-md-3 mb-3">
        <label class="form-label">Hari</label>
        <select name="hari" class="form-select" required>
          <option value="" disabled>-- Pilih Hari --</option>
          <?php 
          $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
          foreach ($hariList as $hari): 
            $selected = ($hari === $dataEdit['hari']) ? 'selected' : '';
          ?>
            <option value="<?= $hari ?>" <?= $selected ?>><?= $hari ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Jam Mulai</label>
        <input type="time" name="jam_mulai" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['jam_mulai']) ?>" required />
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Jam Selesai</label>
        <input type="time" name="jam_selesai" class="form-control" 
               value="<?= htmlspecialchars($dataEdit['jam_selesai']) ?>" required />
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Ruang</label>
        <select name="ruang" class="form-select" required>
          <option value="" disabled>-- Pilih Ruang --</option>
          <?php while ($r = mysqli_fetch_array($ruang_list)): 
            $selected = ($r['ruang'] === $dataEdit['ruang']) ? 'selected' : '';
          ?>
            <option value="<?= htmlspecialchars($r['ruang']) ?>" <?= $selected ?>>
              <?= htmlspecialchars($r['ruang']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="ubah" class="btn-app">
        <i class="fas fa-save"></i> Simpan Perubahan
      </button>
      <a href="matkul.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>