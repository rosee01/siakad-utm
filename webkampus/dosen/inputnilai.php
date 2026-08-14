<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dosen') {
    header('Location: ../login.php');
    exit;
}

$ref_id = $_SESSION['ref_id'];

$stmt_nidn = $koneksi->prepare("SELECT nidn, nama_dosen FROM tbldosen WHERE nidn = ? LIMIT 1");
$stmt_nidn->bind_param("s", $ref_id);
$stmt_nidn->execute();
$row_nidn = $stmt_nidn->get_result()->fetch_assoc();
$nidn = $row_nidn ? $row_nidn['nidn'] : '';
$nama_dosen = $row_nidn ? $row_nidn['nama_dosen'] : '';

if (!$nidn) {
    echo '<div class="data-card">Data dosen tidak ditemukan.</div>';
    exit;
}

$jadwal = $koneksi->prepare("SELECT j.id_jadwal, j.hari, j.jam_mulai, j.jam_selesai, j.matakuliah, j.kelas, j.ruang, m.nama_mk 
                             FROM tbljadwalkuliah j
                             JOIN tblmatkul m ON j.matakuliah = m.nama_mk
                             WHERE j.dosen = ?
                             ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai");
$jadwal->bind_param("s", $nama_dosen);
$jadwal->execute();
$jadwal_list = $jadwal->get_result()->fetch_all(MYSQLI_ASSOC);

$selected_jadwal = isset($_GET['jadwal']) ? $_GET['jadwal'] : '';
$mahasiswa = [];
$selected_info = null;

if ($selected_jadwal) {
    // Info jadwal terpilih
    foreach ($jadwal_list as $j) {
        if ($j['id_jadwal'] == $selected_jadwal) {
            $selected_info = $j;
            break;
        }
    }
    
    $sql = "SELECT m.nim, m.nama_mhs, m.prodi, n.nilai
            FROM tblmhs2 m
            LEFT JOIN tblnilai n ON n.nim = m.nim AND n.id_jadwal = ?
            ORDER BY m.nama_mhs";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("s", $selected_jadwal);
    $stmt->execute();
    $mahasiswa = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$pesan = '';
$pesan_type = '';

if (isset($_POST['simpan_nilai']) && $selected_jadwal) {
    $success = 0;
    foreach ($_POST['nilai'] as $nim => $nilai) {
        if ($nilai === '' || $nilai === null) continue;
        $nilai = intval($nilai);
        
        $cek = $koneksi->prepare("SELECT * FROM tblnilai WHERE nim = ? AND id_jadwal = ?");
        $cek->bind_param("ss", $nim, $selected_jadwal);
        $cek->execute();
        if ($cek->get_result()->fetch_assoc()) {
            $update = $koneksi->prepare("UPDATE tblnilai SET nilai = ? WHERE nim = ? AND id_jadwal = ?");
            $update->bind_param("iss", $nilai, $nim, $selected_jadwal);
            $update->execute();
        } else {
            $insert = $koneksi->prepare("INSERT INTO tblnilai (nim, id_jadwal, nilai) VALUES (?, ?, ?)");
            $insert->bind_param("ssi", $nim, $selected_jadwal, $nilai);
            $insert->execute();
        }
        $success++;
    }
    
    $pesan = "$success nilai mahasiswa berhasil disimpan.";
    $pesan_type = 'success';
    
    // Reload data mahasiswa
    $sql = "SELECT m.nim, m.nama_mhs, m.prodi, n.nilai
            FROM tblmhs2 m
            LEFT JOIN tblnilai n ON n.nim = m.nim AND n.id_jadwal = ?
            ORDER BY m.nama_mhs";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("s", $selected_jadwal);
    $stmt->execute();
    $mahasiswa = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$currentPage = 'nilai';
$page_title  = 'Input Nilai';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<!-- Hero -->
<div class="lecturer-hero">
  <div class="lecturer-avatar">
    <i class="fas fa-file-signature"></i>
  </div>
  <div class="lecturer-info">
    <div class="eyebrow"><i class="fas fa-edit"></i> Input Nilai</div>
    <h2>Kelola Nilai Mahasiswa</h2>
    <p>Pilih jadwal mengajar untuk melihat dan mengisi nilai mahasiswa per mata kuliah.</p>
  </div>
</div>

<!-- Pilih Jadwal -->
<div class="data-card" style="max-width:860px; margin-bottom:24px;">
  <div class="card-head">
    <h3><i class="fas fa-filter me-2" style="color:var(--primary)"></i>Pilih Jadwal Kuliah</h3>
  </div>
  <form method="get">
    <label class="form-label">Jadwal Mengajar</label>
    <div class="d-flex gap-2">
      <select name="jadwal" class="form-select" required onchange="this.form.submit()" style="flex:1;">
        <option value="">-- Pilih Jadwal --</option>
        <?php foreach ($jadwal_list as $j): ?>
          <option value="<?= htmlspecialchars($j['id_jadwal']) ?>" 
                  <?= $selected_jadwal == $j['id_jadwal'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($j['hari']) ?>, <?= htmlspecialchars($j['jam_mulai']) ?>-<?= htmlspecialchars($j['jam_selesai']) ?>
            — <?= htmlspecialchars($j['nama_mk']) ?> (Kelas <?= htmlspecialchars($j['kelas']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-app">
        <i class="fas fa-search"></i> Lihat
      </button>
    </div>
  </form>
</div>

<?php if ($pesan): ?>
  <div style="padding:14px 18px; border-radius:12px; margin-bottom:20px; font-size:14px;
              background:#f0fdf4; border:1px solid #bbf7d0; color:#166534;">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($pesan) ?>
  </div>
<?php endif; ?>

<?php if ($selected_jadwal && $selected_info): ?>
  <div class="data-card">
    <div class="card-head">
      <div>
        <h3><i class="fas fa-users me-2" style="color:var(--primary)"></i><?= htmlspecialchars($selected_info['nama_mk']) ?></h3>
        <div style="font-size:13px; color:var(--muted); margin-top:4px;">
          <i class="far fa-calendar"></i> <?= htmlspecialchars($selected_info['hari']) ?>,
          <?= htmlspecialchars($selected_info['jam_mulai']) ?>-<?= htmlspecialchars($selected_info['jam_selesai']) ?>
          &nbsp;•&nbsp;
          <i class="fas fa-door-open"></i> Ruang <?= htmlspecialchars($selected_info['ruang']) ?>
          &nbsp;•&nbsp;
          <i class="fas fa-chalkboard"></i> Kelas <?= htmlspecialchars($selected_info['kelas']) ?>
          &nbsp;•&nbsp;
          <span class="badge-app green"><?= count($mahasiswa) ?> Mahasiswa</span>
        </div>
      </div>
    </div>

    <?php if (empty($mahasiswa)): ?>
      <div style="text-align:center; padding:32px; color:var(--muted);">
        <i class="fas fa-user-slash" style="font-size:40px; opacity:.3; display:block; margin-bottom:12px;"></i>
        <p style="margin:0;">Tidak ada mahasiswa yang mengambil jadwal ini.</p>
      </div>
    <?php else: ?>
      <form method="post">
        <div class="table-responsive">
          <table class="table-app">
            <thead>
              <tr>
                <th style="width:50px">No</th>
                <th>NIM</th>
                <th>Nama Mahasiswa</th>
                <th>Prodi</th>
                <th style="width:140px">Nilai (0-100)</th>
                <th style="width:100px">Huruf Mutu</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach ($mahasiswa as $mhs): 
                $nilai = $mhs['nilai'];
                $huruf = '-';
                $warna = '#f3f4f6';
                $warnaText = 'var(--muted)';
                if ($nilai !== null && $nilai !== '') {
                    if ($nilai >= 85) { $huruf = 'A';  $warna = '#d1fae5'; $warnaText = '#047857'; }
                    elseif ($nilai >= 80) { $huruf = 'A-'; $warna = '#d1fae5'; $warnaText = '#047857'; }
                    elseif ($nilai >= 75) { $huruf = 'B+'; $warna = '#dbeafe'; $warnaText = '#1e40af'; }
                    elseif ($nilai >= 70) { $huruf = 'B';  $warna = '#dbeafe'; $warnaText = '#1e40af'; }
                    elseif ($nilai >= 65) { $huruf = 'B-'; $warna = '#dbeafe'; $warnaText = '#1e40af'; }
                    elseif ($nilai >= 60) { $huruf = 'C+'; $warna = '#fef3c7'; $warnaText = '#b45309'; }
                    elseif ($nilai >= 55) { $huruf = 'C';  $warna = '#fef3c7'; $warnaText = '#b45309'; }
                    elseif ($nilai >= 40) { $huruf = 'D';  $warna = '#fee2e2'; $warnaText = '#b91c1c'; }
                    else { $huruf = 'E'; $warna = '#fee2e2'; $warnaText = '#b91c1c'; }
                }
              ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><strong><?= htmlspecialchars($mhs['nim']) ?></strong></td>
                <td><?= htmlspecialchars($mhs['nama_mhs']) ?></td>
                <td><span class="badge-app green"><?= htmlspecialchars($mhs['prodi']) ?></span></td>
                <td>
                  <input type="number" name="nilai[<?= htmlspecialchars($mhs['nim']) ?>]" 
                         value="<?= $nilai !== null && $nilai !== '' ? intval($nilai) : '' ?>"
                         min="0" max="100"
                         class="form-control form-control-sm nilai-input"
                         data-huruf-cell="huruf-<?= $mhs['nim'] ?>"
                         style="text-align:center; border-radius:8px;"
                         placeholder="0-100" />
                </td>
                <td style="text-align:center;" id="huruf-<?= $mhs['nim'] ?>">
                  <span class="huruf-badge" style="display:inline-block; padding:4px 12px; border-radius:8px; font-weight:700; font-size:13px; background:<?= $warna ?>; color:<?= $warnaText ?>;">
                    <?= $huruf ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button type="submit" name="simpan_nilai" class="btn-app">
            <i class="fas fa-save"></i> Simpan Semua Nilai
          </button>
          <a href="inputnilai.php" class="btn-app outline">
            <i class="fas fa-redo"></i> Reset
          </a>
        </div>
      </form>
    <?php endif; ?>
  </div>
<?php elseif ($selected_jadwal): ?>
  <div class="data-card" style="text-align:center; padding:32px; color:var(--muted);">
    <i class="fas fa-exclamation-triangle" style="font-size:32px; color:#f59e0b; margin-bottom:12px;"></i>
    <p style="margin:0;">Jadwal yang dipilih tidak ditemukan.</p>
  </div>
<?php else: ?>
  <div class="data-card" style="text-align:center; padding:40px; color:var(--muted);">
    <i class="fas fa-hand-pointer" style="font-size:48px; color:var(--primary); opacity:.3; margin-bottom:14px;"></i>
    <p style="margin:0; font-size:15px;">Pilih jadwal mengajar pada form di atas untuk mulai menginput nilai mahasiswa.</p>
  </div>
<?php endif; ?>

<script>
// Live update huruf mutu saat user mengetik nilai
document.querySelectorAll('.nilai-input').forEach(function (input) {
  input.addEventListener('input', function () {
    const nilai = parseInt(this.value);
    const cell = document.getElementById(this.dataset.hurufCell);
    const badge = cell.querySelector('.huruf-badge');
    
    let huruf = '-', bg = '#f3f4f6', color = 'var(--muted)';
    if (!isNaN(nilai)) {
      if (nilai >= 85) { huruf = 'A';  bg = '#d1fae5'; color = '#047857'; }
      else if (nilai >= 80) { huruf = 'A-'; bg = '#d1fae5'; color = '#047857'; }
      else if (nilai >= 75) { huruf = 'B+'; bg = '#dbeafe'; color = '#1e40af'; }
      else if (nilai >= 70) { huruf = 'B';  bg = '#dbeafe'; color = '#1e40af'; }
      else if (nilai >= 65) { huruf = 'B-'; bg = '#dbeafe'; color = '#1e40af'; }
      else if (nilai >= 60) { huruf = 'C+'; bg = '#fef3c7'; color = '#b45309'; }
      else if (nilai >= 55) { huruf = 'C';  bg = '#fef3c7'; color = '#b45309'; }
      else if (nilai >= 40) { huruf = 'D';  bg = '#fee2e2'; color = '#b91c1c'; }
      else { huruf = 'E'; bg = '#fee2e2'; color = '#b91c1c'; }
    }
    badge.textContent = huruf;
    badge.style.background = bg;
    badge.style.color = color;
  });
});
</script>

<?php include '../includes/footer.php'; ?>