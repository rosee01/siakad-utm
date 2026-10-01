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
$nim = isset($_GET['nim']) && is_string($_GET['nim']) ? $_GET['nim'] : '';

// Proses update data KHS
if (isset($_POST['update_khs'])) {
    $posted_nim = $_POST['nim'] ?? null;
    $nama_mhs_input = $_POST['nama_mhs'] ?? null;
    $semester = $_POST['semester'] ?? '';
    $prodi_input = $_POST['prodi'] ?? null;
    $alamat_input = $_POST['alamat'] ?? null;
    $nama_mhs = is_string($nama_mhs_input) ? trim($nama_mhs_input) : null;
    $prodi = is_string($prodi_input) ? trim($prodi_input) : null;
    $alamat = is_string($alamat_input) ? trim($alamat_input) : null;
    $nilai_post = $_POST['nilai'] ?? [];
    $nilai_valid = [];

    if (
        !is_string($posted_nim)
        || $posted_nim === ''
        || !is_string($nama_mhs)
        || $nama_mhs === ''
        || strlen($nama_mhs) > 150
        || !is_string($semester)
        || !ctype_digit($semester)
        || (int) $semester < 1
        || (int) $semester > 14
        || !is_string($prodi)
        || $prodi === ''
        || strlen($prodi) > 150
        || !is_string($alamat)
        || strlen($alamat) > 255
        || !is_array($nilai_post)
        || count($nilai_post) > 100
    ) {
        $pesan = 'Data KHS tidak valid.';
        $pesan_type = 'error';
    } else {
        $nim = $posted_nim;
        foreach ($nilai_post as $kode_mk => $nilai) {
            if (!is_string($kode_mk) || $kode_mk === '' || !is_string($nilai)) {
                $nilai_valid = [];
                $pesan_type = 'error';
                break;
            }
            if ($nilai === '') {
                continue;
            }
            if (!preg_match('/^(?:0|[1-9][0-9]{0,2})$/D', $nilai) || (int) $nilai > 100) {
                $pesan_type = 'error';
                break;
            }
            $nilai_valid[$kode_mk] = (int) $nilai;
        }

        $stmt_mhs = $koneksi->prepare("SELECT nim FROM tblmhs2 WHERE nim = ? LIMIT 1");
        $stmt_mhs->bind_param("s", $nim);
        $stmt_mhs->execute();
        $mhs_exists = $stmt_mhs->get_result()->fetch_assoc();

        if ($pesan_type === 'error' || !$mhs_exists) {
            $pesan = !$mhs_exists ? 'Mahasiswa tidak ditemukan.' : 'Nilai harus berupa angka antara 0 dan 100.';
            $pesan_type = 'error';
        } else {
            $stmt_jadwal = $koneksi->prepare(
                "SELECT j.id_jadwal
                 FROM tblkrsdetail kd
                 JOIN tblkrs k ON k.id_krs = kd.id_krs AND k.nim = kd.nim
                 JOIN tbljadwalkuliah kelas_krs ON kelas_krs.id_jadwal = k.id_jadwal
                 JOIN tbljadwalkuliah j ON j.kelas = kelas_krs.kelas
                 JOIN tblmatkul m ON m.nama_mk = j.matakuliah AND m.kode_mk = kd.kode_mk
                 WHERE kd.nim = ? AND kd.kode_mk = ?
                 ORDER BY j.id_jadwal
                 LIMIT 1"
            );
            $grades_with_schedule = [];
            foreach ($nilai_valid as $kode_mk => $nilai) {
                $stmt_jadwal->bind_param("ss", $nim, $kode_mk);
                $stmt_jadwal->execute();
                $jadwal = $stmt_jadwal->get_result()->fetch_assoc();
                if (!$jadwal) {
                    $pesan = 'Nilai hanya dapat diubah untuk mata kuliah yang tercatat pada KRS mahasiswa.';
                    $pesan_type = 'error';
                    break;
                }

                $grades_with_schedule[] = [
                    'id_jadwal' => (int) $jadwal['id_jadwal'],
                    'nilai' => $nilai,
                ];
            }

            if ($pesan_type !== 'error') {
                try {
                    $koneksi->begin_transaction();
                    $stmt_update_mhs = $koneksi->prepare(
                        "UPDATE tblmhs2 SET nama_mhs=?, semester=?, prodi=?, alamat=? WHERE nim=?"
                    );
                    $stmt_update_mhs->bind_param("sssss", $nama_mhs, $semester, $prodi, $alamat, $nim);
                    $stmt_update_mhs->execute();

                    $stmt_check = $koneksi->prepare(
                        "SELECT id_nilai FROM tblnilai WHERE nim = ? AND id_jadwal = ? LIMIT 1"
                    );
                    $stmt_update = $koneksi->prepare(
                        "UPDATE tblnilai SET nilai = ? WHERE nim = ? AND id_jadwal = ?"
                    );
                    $stmt_insert = $koneksi->prepare(
                        "INSERT INTO tblnilai (nim, id_jadwal, nilai) VALUES (?, ?, ?)"
                    );
                    $position = 0;
                    foreach ($nilai_valid as $kode_mk => $nilai) {
                        $id_jadwal = $grades_with_schedule[$position++]['id_jadwal'];
                        $stmt_check->bind_param("si", $nim, $id_jadwal);
                        $stmt_check->execute();
                        if ($stmt_check->get_result()->fetch_assoc()) {
                            $stmt_update->bind_param("isi", $nilai, $nim, $id_jadwal);
                            $stmt_update->execute();
                        } else {
                            $stmt_insert->bind_param("sii", $nim, $id_jadwal, $nilai);
                            $stmt_insert->execute();
                        }
                    }
                    $koneksi->commit();
                    $pesan = 'KHS berhasil diperbarui.';
                    $pesan_type = 'success';
                } catch (mysqli_sql_exception $exception) {
                    $koneksi->rollback();
                    error_log('Gagal memperbarui KHS admin: ' . $exception->getMessage());
                    $pesan = 'KHS gagal diperbarui. Silakan coba lagi.';
                    $pesan_type = 'error';
                }
            }
        }
    }
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
    <?= csrf_field() ?>
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