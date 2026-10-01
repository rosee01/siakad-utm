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
    $id_krs       = mysqli_real_escape_string($koneksi, trim($_POST["id_krs"]));
    $nidn         = mysqli_real_escape_string($koneksi, trim($_POST["nidn"]));
    $nim          = mysqli_real_escape_string($koneksi, trim($_POST["nim"]));
    $id_jadwal    = mysqli_real_escape_string($koneksi, trim($_POST["id_jadwal"]));
    $semester     = intval($_POST["semester"]);
    $tahun_ajaran = mysqli_real_escape_string($koneksi, trim($_POST["tahun_ajaran"]));

    $stmt = $koneksi->prepare(
        "INSERT INTO tblkrs (id_krs, nidn, nim, id_jadwal, semester, tahun_ajaran) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssss", $id_krs, $nidn, $nim, $id_jadwal, $semester, $tahun_ajaran);
    $result = $stmt->execute();

    if ($result) {
        header("Location: krs.php");
        exit;
    } else {
        $pesan      = "Gagal menyimpan data. Pastikan ID KRS belum terdaftar.";
        $pesan_type = 'error';
    }
}

/* ---------- DATA DROPDOWN ---------- */
$dosen_list  = mysqli_query($koneksi, "SELECT nidn, nama_dosen FROM tbldosen ORDER BY nama_dosen ASC");
$mhs_list    = mysqli_query($koneksi, "SELECT nim, nama_mhs FROM tblmhs2 ORDER BY nama_mhs ASC");
$jadwal_list = mysqli_query($koneksi, "SELECT id_jadwal, matakuliah, hari, jam_mulai FROM tbljadwalkuliah ORDER BY id_jadwal ASC");

$currentPage = 'krs';
$page_title  = 'Tambah KRS';
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
  <span>Tambah KRS</span>
</div>

<div class="data-card" style="max-width:780px;">
  <div class="card-head">
    <h3><i class="fas fa-file-signature me-2" style="color:var(--primary)"></i>Form Tambah KRS</h3>
  </div>

  <?php if ($pesan): ?>
    <div style="padding:12px 16px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; margin-bottom:18px; font-size:14px;">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($pesan) ?>
    </div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <?= csrf_field() ?>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">ID KRS</label>
        <input type="text" name="id_krs" class="form-control" required placeholder="Contoh: KRS001" />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Semester</label>
        <select name="semester" class="form-select" required>
          <option value="" disabled selected>-- Pilih Semester --</option>
          <?php for ($i = 1; $i <= 14; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">NIDN Dosen</label>
        <select name="nidn" class="form-select" required>
          <option value="" disabled selected>-- Pilih Dosen --</option>
          <?php while ($d = mysqli_fetch_array($dosen_list)): ?>
            <option value="<?= htmlspecialchars($d['nidn']) ?>">
              <?= htmlspecialchars($d['nidn']) ?> - <?= htmlspecialchars($d['nama_dosen']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-6 mb-3">
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
    </div>

    <div class="row">
      <div class="col-md-8 mb-3">
        <label class="form-label">ID Jadwal Kuliah</label>
        <select name="id_jadwal" class="form-select" required>
          <option value="" disabled selected>-- Pilih Jadwal --</option>
          <?php while ($j = mysqli_fetch_array($jadwal_list)): ?>
            <option value="<?= htmlspecialchars($j['id_jadwal']) ?>">
              <?= htmlspecialchars($j['id_jadwal']) ?> — <?= htmlspecialchars($j['matakuliah']) ?> (<?= htmlspecialchars($j['hari']) ?>, <?= htmlspecialchars($j['jam_mulai']) ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Tahun Ajaran</label>
        <select name="tahun_ajaran" class="form-select" required>
          <option value="" disabled selected>-- Pilih --</option>
          <option value="2024/2025">2024/2025</option>
          <option value="2025/2026">2025/2026</option>
          <option value="2026/2027">2026/2027</option>
        </select>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="simpan" class="btn-app">
        <i class="fas fa-save"></i> Simpan
      </button>
      <a href="krs.php" class="btn-app outline">
        <i class="fas fa-times"></i> Batal
      </a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>