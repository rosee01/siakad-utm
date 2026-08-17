<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$nim = $_SESSION['nim'] ?? '';
if (empty($nim)) die("NIM tidak ditemukan di session!");

// Data mahasiswa
$stmt = $koneksi->prepare("SELECT * FROM tblmhs2 WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$mhs = $stmt->get_result()->fetch_assoc();
if (!$mhs) die("Data mahasiswa tidak ditemukan.");

// KRS terbaru mahasiswa (jadi patokan semester yang ditampilkan di KHS)
$krs_terbaru = null;
$stmt_krs = $koneksi->prepare("SELECT id_krs, tahun_ajaran, nidn
                                FROM tblkrs
                                WHERE nim = ?
                                ORDER BY tahun_ajaran DESC, semester DESC
                                LIMIT 1");
$stmt_krs->bind_param("s", $nim);
$stmt_krs->execute();
$krs_terbaru = $stmt_krs->get_result()->fetch_assoc();

// Mata kuliah yang benar-benar diambil mahasiswa ini — hanya dari KRS terbaru
$matakuliah = [];
if ($krs_terbaru) {
    $stmt_mk = $koneksi->prepare("SELECT DISTINCT mk.kode_mk, mk.nama_mk, mk.sks
                                   FROM tblkrsdetail kd
                                   JOIN tblmatkul mk ON kd.kode_mk = mk.kode_mk
                                   WHERE kd.id_krs = ?
                                   ORDER BY mk.nama_mk");
    $stmt_mk->bind_param("i", $krs_terbaru['id_krs']);
    $stmt_mk->execute();
    $result_mk = $stmt_mk->get_result();
    while ($row = $result_mk->fetch_assoc()) $matakuliah[] = $row;
}

// Nilai mahasiswa
$stmt2 = $koneksi->prepare("SELECT n.nilai, m.kode_mk
                            FROM tblnilai n
                            JOIN tbljadwalkuliah j ON n.id_jadwal = j.id_jadwal
                            JOIN tblmatkul m ON j.matakuliah = m.nama_mk
                            WHERE n.nim = ?");
$stmt2->bind_param("s", $nim);
$stmt2->execute();
$result_nilai = $stmt2->get_result();
$nilai_map = [];
while ($row = $result_nilai->fetch_assoc()) $nilai_map[$row['kode_mk']] = $row['nilai'];

// Dosen wali (dari KRS terbaru mahasiswa)
$dosen_wali = '-';
if ($krs_terbaru && $krs_terbaru['nidn']) {
    $stmt3 = $koneksi->prepare("SELECT nama_dosen FROM tbldosen WHERE nidn = ?");
    $stmt3->bind_param("s", $krs_terbaru['nidn']);
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
$page_title  = 'KHS Saya';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<style>
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
  .khs-kop {
    display: flex; align-items: center; gap: 16px;
    border-bottom: 4px double #000; padding-bottom: 10px;
  }
  .kop-logo {
    width: 62px; height: 62px; border: 2px solid #000; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;
  }
  .kop-text { flex: 1; text-align: center; }
  .kop-title { font-size: 19px; font-weight: bold; letter-spacing: 1px; }
  .kop-sub { font-size: 11.5px; margin-top: 2px; }
  .doc-title {
    text-align: center; font-weight: bold; font-size: 14px;
    letter-spacing: 1px; margin: 14px 0 12px; text-decoration: underline;
  }
  .info-table { width: 100%; font-size: 12.5px; border-collapse: collapse; margin-bottom: 14px; }
  .info-table td { padding: 2px 4px; vertical-align: top; }
  .nilai-table { width: 100%; border-collapse: collapse; font-size: 12px; }
  .nilai-table th, .nilai-table td { border: 1px solid #000; padding: 5px 8px; }
  .nilai-table th { background: #f0f0f0; text-align: center; font-weight: bold; }
  .nilai-table td.c { text-align: center; }
  .nilai-table td.l { text-align: left; }
  .sig-table { width: 100%; margin-top: 20px; font-size: 12px; border-collapse: collapse; }
  .sig-table td { vertical-align: top; padding: 4px; }
  .sig-space { height: 58px; }

  @media print {
    @page { size: A4; margin: 12mm 14mm; }
    body { background: #fff !important; }
    .sidebar, .topbar, .site-footer, .student-hero, .no-print, .data-card { display: none !important; }
    .main { margin: 0 !important; }
    .content { padding: 0 !important; }
    .khs-doc {
      border: none !important; border-radius: 0 !important;
      box-shadow: none !important; padding: 0 !important;
      margin: 0 !important; max-width: 100% !important;
    }
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

<!-- Tombol aksi (tidak ikut tercetak) -->
<div class="d-flex gap-2 no-print" style="justify-content:center; margin-bottom:18px;">
  <button onclick="window.print()" class="btn-app">
    <i class="fas fa-print"></i> Cetak KHS
  </button>
  <a href="profil_mahasiswa.php" class="btn-app outline">
    <i class="fas fa-arrow-left"></i> Kembali
  </a>
</div>

<!-- Dokumen KHS -->
<div class="khs-doc" id="khs-area">
  <div class="khs-kop">
    <div class="kop-logo"><i class="fas fa-graduation-cap"></i></div>
    <div class="kop-text">
      <div class="kop-title">UNIVERSITAS TEKNOLOGI MATARAM</div>
      <div class="kop-sub">Jalan Raya Panca Usaha No. 88, Mataram, NTB<br>Telp. (0370) 123456, Email: info@utm.ac.id</div>
    </div>
    <div style="width:62px;"></div>
  </div>

  <div class="doc-title">KARTU HASIL STUDI (KHS) MAHASISWA</div>

  <table class="info-table">
    <tr>
      <td style="width:15%">NIM</td><td style="width:35%">: <?= htmlspecialchars($mhs['nim']) ?></td>
      <td style="width:15%">TA - SMT</td><td>: <?= htmlspecialchars($krs_terbaru['tahun_ajaran'] ?? '-') ?> - <?= htmlspecialchars($mhs['semester']) ?></td>
    </tr>
    <tr>
      <td>NAMA</td><td>: <?= htmlspecialchars($mhs['nama_mhs']) ?></td>
      <td>Fakultas</td><td>: TEKNIK INFORMATIKA</td>
    </tr>
    <tr>
      <td>Dosen Wali/PA</td><td>: <?= htmlspecialchars($dosen_wali) ?></td>
      <td>Program Studi</td><td>: <?= htmlspecialchars($mhs['prodi']) ?></td>
    </tr>
    <tr>
      <td>Jenjang</td><td>: S1</td>
      <td>Semester</td><td>: <?= htmlspecialchars($mhs['semester']) ?></td>
    </tr>
  </table>

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
      $no = 1; $total_sks = 0; $total_bobot = 0;
      foreach ($matakuliah as $mk):
          $nilai = isset($nilai_map[$mk['kode_mk']]) ? $nilai_map[$mk['kode_mk']] : '';
          $huruf = ''; $bobot = 0;
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

  <table class="sig-table">
    <tr>
      <td style="width:60%;">
        Keterangan:<br>
        1 lembar untuk Mahasiswa &nbsp;&nbsp; 1 lembar untuk Fakultas<br>
        1 lembar untuk Program Studi &nbsp;&nbsp; 1 lembar untuk Dosen Wali/PA
      </td>
      <td style="text-align:center;">
        Mataram, <?= date('d F Y') ?><br>
        Dekan,
        <div class="sig-space"></div>
        <strong><u>Nama Dekan</u></strong><br>
        NIP. 123456789
      </td>
    </tr>
  </table>
</div>

<p class="no-print" style="text-align:center; font-size:12.5px; color:var(--muted); margin-top:12px;">
  <i class="fas fa-info-circle"></i>
  Tips: pada dialog cetak, buka <strong>More settings</strong> lalu matikan <strong>"Headers and footers"</strong>.
</p>

<?php include '../includes/footer.php'; ?>