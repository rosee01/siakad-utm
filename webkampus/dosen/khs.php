<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../login.php");
    exit;
}

// Ambil data dosen
$nidn = $_SESSION['ref_id'] ?? '';
$sql = "SELECT * FROM tbldosen WHERE nidn = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("s", $nidn);
$stmt->execute();
$dosen = $stmt->get_result()->fetch_assoc();

if (!$dosen) {
    http_response_code(403);
    exit('Data dosen tidak ditemukan.');
}

// Batasi daftar mahasiswa ke peserta pada jadwal dosen ini.
$mhs_list = [];
$stmt_mhs = $koneksi->prepare(
    "SELECT DISTINCT m.nim, m.nama_mhs
     FROM tblmhs2 m
     JOIN tblkrs k ON k.nim = m.nim
     JOIN tbljadwalkuliah j ON j.id_jadwal = k.id_jadwal
     WHERE j.dosen = ?
     ORDER BY m.nama_mhs"
);
$stmt_mhs->bind_param("s", $dosen['nama_dosen']);
$stmt_mhs->execute();
$res_mhs = $stmt_mhs->get_result();
while ($row = $res_mhs->fetch_assoc()) {
    $mhs_list[] = $row;
}

$nim = $_GET['nim'] ?? '';
if (!is_string($nim)) {
    $nim = '';
}

if ($nim !== '' && !in_array($nim, array_column($mhs_list, 'nim'), true)) {
    http_response_code(403);
    exit('Anda tidak memiliki akses ke data mahasiswa ini.');
}

$mhs = null;
if ($nim) {
    $stmt = $koneksi->prepare("SELECT * FROM tblmhs2 WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $mhs = $stmt->get_result()->fetch_assoc();
}

// Mata kuliah yang benar-benar diambil mahasiswa terpilih (dari KRS)
$matakuliah = [];
if ($nim) {
    $sql_mk = "SELECT DISTINCT mk.kode_mk, mk.nama_mk, mk.sks
               FROM tblkrsdetail kd
               JOIN tblmatkul mk ON kd.kode_mk = mk.kode_mk
               WHERE kd.nim = ?
               ORDER BY mk.nama_mk";
    $stmt_mk = $koneksi->prepare($sql_mk);
    $stmt_mk->bind_param("s", $nim);
    $stmt_mk->execute();
    $result_mk = $stmt_mk->get_result();
    while ($row = $result_mk->fetch_assoc()) {
        $matakuliah[] = $row;
    }
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

// Dosen wali (ambil dari KRS terbaru mahasiswa, bukan teks tetap)
$dosen_wali = '-';
if ($nim) {
    $stmt3 = $koneksi->prepare("SELECT d.nama_dosen
                                 FROM tblkrs k
                                 JOIN tbldosen d ON k.nidn = d.nidn
                                 WHERE k.nim = ?
                                 ORDER BY k.tahun_ajaran DESC, k.semester DESC
                                 LIMIT 1");
    $stmt3->bind_param("s", $nim);
    $stmt3->execute();
    $row_wali = $stmt3->get_result()->fetch_assoc();
    if ($row_wali) $dosen_wali = $row_wali['nama_dosen'];
}

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

<style>
  /* ============ DOKUMEN KHS (tampilan layar = tampilan cetak) ============ */
  .khs-doc {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(17,24,39,.06);
    padding: 32px 40px;
    max-width: 860px;
    margin: 0 auto 24px;
    font-family: 'Times New Roman', Times, serif;
    color: #000;
  }

  /* Kop surat */
  .khs-kop {
    display: flex;
    align-items: center;
    gap: 16px;
    border-bottom: 4px double #000;
    padding-bottom: 10px;
  }
  .kop-logo {
    width: 62px; height: 62px;
    border: 2px solid #000; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; flex-shrink: 0;
  }
  .kop-text { flex: 1; text-align: center; }
  .kop-title { font-size: 19px; font-weight: bold; letter-spacing: 1px; }
  .kop-sub { font-size: 11.5px; margin-top: 2px; }
  .doc-title {
    text-align: center;
    font-weight: bold;
    font-size: 14px;
    letter-spacing: 1px;
    margin: 14px 0 12px;
    text-decoration: underline;
  }

  /* Info mahasiswa */
  .info-table { width: 100%; font-size: 12.5px; border-collapse: collapse; margin-bottom: 14px; }
  .info-table td { padding: 2px 4px; vertical-align: top; }
  .info-table td.label { width: 15%; }
  .info-table td.value { width: 35%; }

  /* Tabel nilai */
  .nilai-table { width: 100%; border-collapse: collapse; font-size: 12px; }
  .nilai-table th, .nilai-table td { border: 1px solid #000; padding: 5px 8px; }
  .nilai-table th { background: #f0f0f0; text-align: center; font-weight: bold; }
  .nilai-table td.c { text-align: center; }
  .nilai-table td.l { text-align: left; }
  .nilai-table tfoot th { background: #fff; }

  /* Tanda tangan */
  .sig-table { width: 100%; margin-top: 20px; font-size: 12px; border-collapse: collapse; }
  .sig-table td { vertical-align: top; padding: 4px; }
  .sig-notes { line-height: 1.6; }
  .sig-block { text-align: center; }
  .sig-space { height: 58px; }

  /* ============ ATURAN CETAK — PASTIKAN 1 HALAMAN ============ */
  @media print {
    @page { size: A4; margin: 12mm 14mm; }

    body { background: #fff !important; }

    /* Sembunyikan semua elemen aplikasi (display:none = tidak makan tempat) */
    .sidebar, .topbar, .site-footer, .lecturer-hero,
    .no-print, .data-card { display: none !important; }

    .main { margin: 0 !important; }
    .content { padding: 0 !important; }

    /* Dokumen jadi full halaman, tanpa bayangan/border card */
    .khs-doc {
      border: none !important;
      border-radius: 0 !important;
      box-shadow: none !important;
      padding: 0 !important;
      margin: 0 !important;
      max-width: 100% !important;
    }

    /* Kompakkan agar muat 1 halaman */
    .khs-doc { font-size: 11px; }
    .kop-logo { width: 52px; height: 52px; font-size: 22px; }
    .kop-title { font-size: 16px; }
    .kop-sub { font-size: 10px; }
    .doc-title { font-size: 12px; margin: 10px 0 8px; }
    .info-table { font-size: 11px; margin-bottom: 10px; }
    .nilai-table { font-size: 10.5px; }
    .nilai-table th, .nilai-table td { padding: 3px 6px; }
    .nilai-table th { background: #fff !important; }
    .sig-table { margin-top: 14px; font-size: 10.5px; }
    .sig-space { height: 50px; }
  }
</style>

<!-- Hero (tidak ikut tercetak) -->
<div class="lecturer-hero no-print">
  <div class="lecturer-avatar"><i class="fas fa-file-contract"></i></div>
  <div class="lecturer-info">
    <div class="eyebrow"><i class="fas fa-graduation-cap"></i> Kartu Hasil Studi</div>
    <h2>KHS Mahasiswa</h2>
    <p>Pilih mahasiswa untuk melihat dan mencetak Kartu Hasil Studi.</p>
  </div>
</div>

<!-- Form cari mahasiswa (tidak ikut tercetak) -->
<div class="data-card no-print" style="max-width:720px; margin-bottom:24px;">
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
      <button type="submit" class="btn-app"><i class="fas fa-search"></i> Lihat</button>
    </div>
  </form>
</div>

<?php if ($mhs):
  $total_sks = 0;
  $total_bobot = 0;
?>

  <!-- ============ DOKUMEN KHS ============ -->
  <div class="khs-doc" id="khs-area">

    <!-- KOP SURAT -->
    <div class="khs-kop">
      <div class="kop-logo"><i class="fas fa-graduation-cap"></i></div>
      <div class="kop-text">
        <div class="kop-title">SIAKAD DEMO AKADEMIK</div>
        <div class="kop-sub">Dokumen simulasi portofolio — bukan dokumen akademik resmi</div>
      </div>
      <div style="width:62px;"></div> <!-- penyeimbang agar teks kop benar-benar di tengah -->
    </div>

    <div class="doc-title">KARTU HASIL STUDI (KHS) MAHASISWA</div>

    <!-- INFO MAHASISWA -->
    <table class="info-table">
      <tr>
        <td class="label">NIM</td><td class="value">: <?= htmlspecialchars($mhs['nim']) ?></td>
        <td class="label">TA - SMT</td><td>: 2025/2026 - <?= htmlspecialchars($mhs['semester']) ?></td>
      </tr>
      <tr>
        <td class="label">NAMA</td><td class="value">: <?= htmlspecialchars($mhs['nama_mhs']) ?></td>
        <td class="label">Fakultas</td><td>: TEKNIK INFORMATIKA</td>
      </tr>
      <tr>
        <td class="label">Dosen Wali/PA</td><td class="value">: <?= htmlspecialchars($dosen_wali) ?></td>
        <td class="label">Program Studi</td><td>: <?= htmlspecialchars($mhs['prodi']) ?></td>
      </tr>
      <tr>
        <td class="label">Jenjang</td><td class="value">: S1</td>
        <td class="label">Semester</td><td>: <?= htmlspecialchars($mhs['semester']) ?></td>
      </tr>
    </table>

    <!-- TABEL NILAI -->
    <table class="nilai-table">
      <thead>
        <tr>
          <th style="width:32px">NO</th>
          <th style="width:70px">KODE</th>
          <th>MATAKULIAH</th>
          <th style="width:40px">SKS</th>
          <th style="width:70px">HURUF MUTU</th>
          <th style="width:55px">BOBOT</th>
          <th style="width:50px">NILAI</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 1;
        foreach ($matakuliah as $mk):
            $nilai = isset($nilai_map[$mk['kode_mk']]) ? $nilai_map[$mk['kode_mk']] : '';
            $huruf = '';
            $bobot = 0;
            if ($nilai !== '' && $nilai !== null) {
                list($huruf, $bobot) = konversiHuruf($nilai);
                $total_sks += $mk['sks'];
                $total_bobot += $bobot * $mk['sks'];
            }
        ?>
        <tr>
          <td class="c"><?= $no++ ?></td>
          <td class="c"><?= htmlspecialchars($mk['kode_mk']) ?></td>
          <td class="l"><?= htmlspecialchars($mk['nama_mk']) ?></td>
          <td class="c"><?= $mk['sks'] ?></td>
          <td class="c"><strong><?= $huruf !== '' ? $huruf : '-' ?></strong></td>
          <td class="c"><?= $nilai !== '' ? number_format($bobot, 2) : '-' ?></td>
          <td class="c"><?= $nilai !== '' ? intval($nilai) : '-' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="3" style="text-align:right;">Jumlah SKS</th>
          <th class="c"><?= $total_sks ?></th>
          <th colspan="3"></th>
        </tr>
        <tr>
          <th colspan="3" style="text-align:right;">IP SEMESTER</th>
          <th class="c"><?= $total_sks ? number_format($total_bobot / $total_sks, 2) : '0.00' ?></th>
          <th colspan="3"></th>
        </tr>
      </tfoot>
    </table>

    <!-- TANDA TANGAN -->
    <table class="sig-table">
      <tr>
        <td class="sig-notes" style="width:60%;">
          Keterangan:<br>
          1 lembar untuk Mahasiswa &nbsp;&nbsp; 1 lembar untuk Fakultas<br>
          1 lembar untuk Program Studi &nbsp;&nbsp; 1 lembar untuk Dosen Wali/PA
        </td>
        <td class="sig-block">
          Tanggal: <?= date('d F Y') ?><br>
          Dekan,
          <div class="sig-space"></div>
          <strong><u>Nama Dekan</u></strong><br>
          NIP. 123456789
        </td>
      </tr>
    </table>
  </div>

  <!-- Tombol aksi (tidak ikut tercetak) -->
  <div class="d-flex gap-2 no-print" style="justify-content:center;">
    <button onclick="window.print()" class="btn-app">
      <i class="fas fa-print"></i> Cetak KHS
    </button>
    <a href="khs.php" class="btn-app outline">
      <i class="fas fa-redo"></i> Pilih Mahasiswa Lain
    </a>
  </div>

  <p class="no-print" style="text-align:center; font-size:12.5px; color:var(--muted); margin-top:12px;">
    <i class="fas fa-info-circle"></i>
    Tips: pada dialog cetak, buka <strong>More settings</strong> lalu matikan <strong>"Headers and footers"</strong> agar URL & tanggal tidak ikut tercetak.
  </p>

<?php elseif ($nim): ?>
  <div class="data-card" style="text-align:center; padding:32px; color:var(--muted);">
    <i class="fas fa-exclamation-triangle" style="font-size:32px; color:#f59e0b; margin-bottom:12px;"></i>
    <p style="margin:0;">Data mahasiswa dengan NIM <strong><?= htmlspecialchars($nim) ?></strong> tidak ditemukan.</p>
  </div>
<?php else: ?>
  <div class="data-card" style="text-align:center; padding:40px; color:var(--muted);">
    <i class="fas fa-user-graduate" style="font-size:48px; color:var(--primary); opacity:.3; margin-bottom:14px;"></i>
    <p style="margin:0; font-size:15px;">Pilih mahasiswa pada form di atas untuk melihat Kartu Hasil Studi.</p>
  </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>