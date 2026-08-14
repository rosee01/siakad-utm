<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pesan      = '';
$pesan_type = '';

// Ambil semua mahasiswa untuk dropdown
$mhs_list = [];
$res_mhs = $koneksi->query("SELECT nim, nama_mhs FROM tblmhs2 ORDER BY nama_mhs");
while ($row = $res_mhs->fetch_assoc()) {
    $mhs_list[] = $row;
}

// Ambil NIM yang dipilih
$nim = isset($_GET['nim']) ? $_GET['nim'] : '';

// Proses update data KHS
if (isset($_POST['update_khs'])) {
    $nim       = $_POST['nim'];
    $nama_mhs  = $_POST['nama_mhs'];
    $semester  = $_POST['semester'];
    $prodi     = $_POST['prodi'];
    $alamat    = $_POST['alamat'];

    // Update data mahasiswa
    $stmt = $koneksi->prepare("UPDATE tblmhs2 SET nama_mhs=?, semester=?, prodi=?, alamat=? WHERE nim=?");
    $stmt->bind_param("sssss", $nama_mhs, $semester, $prodi, $alamat, $nim);
    $stmt->execute();

    // Update nilai matakuliah
    if (isset($_POST['nilai']) && is_array($_POST['nilai'])) {
        foreach ($_POST['nilai'] as $kode_mk => $nilai) {
            if ($nilai === '' || $nilai === null) continue;

            // Cari id_jadwal
            $sql_jadwal = "SELECT j.id_jadwal FROM tbljadwalkuliah j
                           JOIN tblmatkul m ON j.matakuliah = m.nama_mk
                           WHERE m.kode_mk = ? LIMIT 1";
            $stmt_jadwal = $koneksi->prepare($sql_jadwal);
            $stmt_jadwal->bind_param("s", $kode_mk);
            $stmt_jadwal->execute();
            $result_jadwal = $stmt_jadwal->get_result();
            $jadwal = $result_jadwal->fetch_assoc();
            if (!$jadwal) continue;
            $id_jadwal = $jadwal['id_jadwal'];

            // Cek apakah sudah ada nilai
            $cek = $koneksi->prepare("SELECT * FROM tblnilai WHERE nim = ? AND id_jadwal = ?");
            $cek->bind_param("si", $nim, $id_jadwal);
            $cek->execute();
            $cek_result = $cek->get_result();
            if ($cek_result->fetch_assoc()) {
                $update = $koneksi->prepare("UPDATE tblnilai SET nilai = ? WHERE nim = ? AND id_jadwal = ?");
                $update->bind_param("isi", $nilai, $nim, $id_jadwal);
                $update->execute();
            } else {
                $insert = $koneksi->prepare("INSERT INTO tblnilai (nim, id_jadwal, nilai) VALUES (?, ?, ?)");
                $insert->bind_param("sii", $nim, $id_jadwal, $nilai);
                $insert->execute();
            }
        }
    }

    $pesan      = "KHS berhasil diperbarui.";
    $pesan_type = 'success';
}

// Ambil data mahasiswa yang dipilih
$mhs = null;
if ($nim) {
    $stmt = $koneksi->prepare("SELECT * FROM tblmhs2 WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $mhs = $stmt->get_result()->fetch_assoc();
}

// Ambil semua matakuliah
$sql_mk = "SELECT kode_mk, nama_mk, sks FROM tblmatkul ORDER BY nama_mk";
$result_mk = $koneksi->query($sql_mk);
$matakuliah = [];
while ($row = $result_mk->fetch_assoc()) {
    $matakuliah[] = $row;
}

// Ambil nilai mahasiswa
$nilai_map = [];
if ($nim) {
    $sql_nilai = "SELECT n.nilai, m.kode_mk
                  FROM tblnilai n
                  JOIN tbljadwalkuliah j ON n.id_jadwal = j.id_jadwal
                  JOIN tblmatkul m ON j.matakuliah = m.nama_mk
                  WHERE n.nim = ?";
    $stmt2 = $koneksi->prepare($sql_nilai);
    $stmt2->bind_param("s", $nim);
    $stmt2->execute();
    $result_nilai = $stmt2->get_result();
    while ($row = $result_nilai->fetch_assoc()) {
        $nilai_map[$row['kode_mk']] = $row['nilai'];
    }
}

// Fungsi konversi nilai ke huruf mutu dan bobot
function konversiHuruf($nilai) {
    if ($nilai >= 85) return ['A', 4.00];
    if ($nilai >= 80) return ['A-', 3.50];
    if ($nilai >= 75) return ['B+', 3.25];
    if ($nilai >= 70) return ['B', 3.00];
    if ($nilai >= 65) return ['B-', 2.75];
    if ($nilai >= 60) return ['C+', 2.50];
    if ($nilai >= 55) return ['C', 2.00];
    if ($nilai >= 40) return ['D', 1.00];
    return ['E', 0.00];
}

$currentPage = 'khs';
$page_title  = 'KHS Mahasiswa';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Form Pencarian Mahasiswa -->
<div class="data-card" style="max-width:640px; margin-bottom:24px;">
  <div class="card-head">
    <h3><i class="fas fa-search me-2" style="color:var(--primary)"></i>Pilih Mahasiswa</h3>
  </div>
  <form method="get" action="khs.php">
    <label class="form-label">Cari Mahasiswa</label>
    <div class="d-flex gap-2">
      <select name="nim" class="form-select" required style="flex:1;" onchange="this.form.submit()">
        <option value="">-- Pilih Nama / NIM --</option>
        <?php foreach ($mhs_list as $m): ?>
          <option value="<?= htmlspecialchars($m['nim']) ?>" <?= ($nim === $m['nim']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($m['nama_mhs']) ?> (<?= htmlspecialchars($m['nim']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-app">
        <i class="fas fa-search"></i> Cari
      </button>
    </div>
  </form>
</div>

<?php if ($pesan): ?>
  <div style="padding:14px 18px; border-radius:12px; margin-bottom:20px; font-size:14px;
              background:<?= $pesan_type === 'success' ? '#f0fdf4' : '#fef2f2' ?>;
              border:1px solid <?= $pesan_type === 'success' ? '#bbf7d0' : '#fecaca' ?>;
              color:<?= $pesan_type === 'success' ? '#166534' : '#b91c1c' ?>;">
    <i class="fas <?= $pesan_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
    <?= htmlspecialchars($pesan) ?>
  </div>
<?php endif; ?>

<?php if ($mhs): ?>

  <!-- Form Edit Data Mahasiswa -->
  <form method="post">
    <input type="hidden" name="nim" value="<?= htmlspecialchars($mhs['nim']) ?>">

    <div class="data-card" style="margin-bottom:24px;">
      <div class="card-head">
        <h3><i class="fas fa-user-graduate me-2" style="color:var(--primary)"></i>Data Mahasiswa</h3>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">NIM</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($mhs['nim']) ?>" readonly
                 style="background:#f3f4f6; color:var(--muted);" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Nama Mahasiswa</label>
          <input type="text" name="nama_mhs" class="form-control" value="<?= htmlspecialchars($mhs['nama_mhs']) ?>" required />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Semester</label>
          <input type="text" name="semester" class="form-control" value="<?= htmlspecialchars($mhs['semester']) ?>" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Program Studi</label>
          <input type="text" name="prodi" class="form-control" value="<?= htmlspecialchars($mhs['prodi']) ?>" />
        </div>
        <div class="col-12 mb-3">
          <label class="form-label">Alamat</label>
          <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($mhs['alamat']) ?></textarea>
        </div>
      </div>
    </div>

    <!-- Tabel Nilai KHS -->
    <div class="data-card">
      <div class="card-head">
        <h3><i class="fas fa-file-contract me-2" style="color:var(--primary)"></i>Kartu Hasil Studi (KHS)</h3>
      </div>
      <div class="table-responsive">
        <table class="table-app">
          <thead>
            <tr>
              <th style="width:50px">No</th>
              <th>Kode</th>
              <th>Mata Kuliah</th>
              <th style="width:70px">SKS</th>
              <th style="width:100px">Huruf Mutu</th>
              <th style="width:90px">Bobot</th>
              <th style="width:120px">Nilai</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            $total_sks   = 0;
            $total_bobot = 0;
            foreach ($matakuliah as $mk):
                $nilai = isset($nilai_map[$mk['kode_mk']]) ? $nilai_map[$mk['kode_mk']] : '';
                $huruf = '';
                $bobot = 0;
                if ($nilai !== '' && $nilai !== null) {
                    list($huruf, $bobot) = konversiHuruf($nilai);
                    $total_sks   += $mk['sks'];
                    $total_bobot += $bobot * $mk['sks'];
                }
            ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><span class="badge-app green"><?= htmlspecialchars($mk['kode_mk']) ?></span></td>
              <td><?= htmlspecialchars($mk['nama_mk']) ?></td>
              <td style="text-align:center"><?= $mk['sks'] ?></td>
              <td style="text-align:center">
                <?php if ($huruf): ?>
                  <span style="display:inline-block; padding:4px 10px; border-radius:6px; font-weight:700; font-size:13px;
                               background:<?= in_array($huruf, ['A','A-']) ? '#d1fae5' : (in_array($huruf, ['B+','B','B-']) ? '#dbeafe' : (in_array($huruf, ['C+','C']) ? '#fef3c7' : '#fee2e2')) ?>;
                               color:<?= in_array($huruf, ['A','A-']) ? '#047857' : (in_array($huruf, ['B+','B','B-']) ? '#1e40af' : (in_array($huruf, ['C+','C']) ? '#b45309' : '#b91c1c')) ?>;">
                    <?= $huruf ?>
                  </span>
                <?php else: ?>
                  <span style="color:var(--muted);">—</span>
                <?php endif; ?>
              </td>
              <td style="text-align:center">
                <?= $nilai !== '' ? number_format($bobot, 2) : '<span style="color:var(--muted);">—</span>' ?>
              </td>
              <td>
                <input type="number" name="nilai[<?= htmlspecialchars($mk['kode_mk']) ?>]"
                       value="<?= $nilai !== '' ? intval($nilai) : '' ?>"
                       min="0" max="100"
                       class="form-control form-control-sm"
                       style="text-align:center; border-radius:8px;"
                       placeholder="0-100" />
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr style="background:var(--hover-soft); font-weight:700;">
              <td colspan="3" style="text-align:right; padding:12px 14px;">Jumlah SKS</td>
              <td style="text-align:center; padding:12px 14px; color:var(--navy);"><?= $total_sks ?></td>
              <td colspan="3"></td>
            </tr>
            <tr style="background:var(--primary); color:#fff; font-weight:800;">
              <td colspan="3" style="text-align:right; padding:14px;">IP Semester</td>
              <td style="text-align:center; padding:14px; font-size:18px;"><?= $total_sks ? number_format($total_bobot / $total_sks, 2) : '0.00' ?></td>
              <td colspan="3"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="d-flex gap-2 mt-3">
        <button type="submit" name="update_khs" class="btn-app">
          <i class="fas fa-save"></i> Simpan Perubahan KHS
        </button>
      </div>
    </div>
  </form>

<?php elseif ($nim): ?>
  <div class="data-card" style="text-align:center; padding:32px; color:var(--muted);">
    <i class="fas fa-exclamation-triangle" style="font-size:32px; color:#f59e0b; margin-bottom:12px;"></i>
    <p style="margin:0;">Data mahasiswa dengan NIM <strong><?= htmlspecialchars($nim) ?></strong> tidak ditemukan.</p>
  </div>
<?php else: ?>
  <div class="data-card" style="text-align:center; padding:40px; color:var(--muted);">
    <i class="fas fa-user-graduate" style="font-size:48px; color:var(--primary); opacity:0.3; margin-bottom:14px;"></i>
    <p style="margin:0; font-size:15px;">Pilih mahasiswa pada form di atas untuk melihat dan mengelola KHS.</p>
  </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>