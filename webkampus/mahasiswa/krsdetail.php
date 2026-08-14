<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$nim = $_GET['nim'] ?? '';
$semester_filter = $_GET['semester'] ?? '';

// Keamanan: hanya boleh lihat KRS sendiri
$nim_session = $_SESSION['nim'] ?? '';
if ($nim !== $nim_session) {
    header("Location: krs.php");
    exit;
}

// Data header KRS
$query_krs = "SELECT k.nim, m.nama_mhs, m.prodi, k.semester, k.tahun_ajaran, d.nama_dosen, d.nidn, c.nama_kelas
              FROM tblkrs k
              LEFT JOIN tblmhs2 m ON k.nim = m.nim
              LEFT JOIN tbldosen d ON k.nidn = d.nidn
              LEFT JOIN tbl_kelas c ON k.id_jadwal = c.kode_kelas
              WHERE k.nim = ?" . ($semester_filter ? " AND k.semester = ?" : "");
$stmt = mysqli_prepare($koneksi, $query_krs);
if ($semester_filter) {
    mysqli_stmt_bind_param($stmt, 'ss', $nim, $semester_filter);
} else {
    mysqli_stmt_bind_param($stmt, 's', $nim);
}
mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$data) die("Data KRS tidak ditemukan.");

// Daftar mata kuliah (INNER JOIN agar data kotor tidak tampil)
$query_matkul = "SELECT mk.kode_mk, mk.nama_mk, mk.semester, mk.sks
                 FROM tblkrsdetail kd
                 INNER JOIN tblmatkul mk ON kd.kode_mk = mk.kode_mk
                 WHERE kd.nim = ?";
$stmt2 = mysqli_prepare($koneksi, $query_matkul);
mysqli_stmt_bind_param($stmt2, 's', $nim);
mysqli_stmt_execute($stmt2);
$result_matkul = mysqli_stmt_get_result($stmt2);

$total_sks = 0;
$matkul = [];
while ($row = mysqli_fetch_assoc($result_matkul)) {
    $total_sks += (int)$row['sks'];
    $matkul[] = $row;
}

$currentPage = 'krs';
$page_title  = 'Detail KRS';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<style>
  /* ============ DOKUMEN KRS (layar = cetak) ============ */
  .krs-doc {
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
  .krs-kop {
    display: flex; align-items: center; gap: 16px;
    border-bottom: 4px double #000; padding-bottom: 10px;
  }
  .kop-logo {
    width: 62px; height: 62px; border: 2px solid #000; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; flex-shrink: 0;
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
  .mk-table { width: 100%; border-collapse: collapse; font-size: 12px; }
  .mk-table th, .mk-table td { border: 1px solid #000; padding: 5px 8px; }
  .mk-table th { background: #f0f0f0; text-align: center; font-weight: bold; }
  .mk-table td.c { text-align: center; }
  .mk-table td.l { text-align: left; }
  .sig-table { width: 100%; margin-top: 20px; font-size: 12px; border-collapse: collapse; }
  .sig-table td { vertical-align: top; padding: 4px; }
  .sig-space { height: 58px; }

  /* ============ ATURAN CETAK — 1 HALAMAN ============ */
  @media print {
    @page { size: A4; margin: 12mm 14mm; }
    body { background: #fff !important; }

    /* Sembunyikan semua elemen aplikasi */
    .sidebar, .topbar, .site-footer, .no-print, .data-card, .student-hero { display: none !important; }
    .main { margin: 0 !important; }
    .content { padding: 0 !important; }

    /* Dokumen full halaman */
    .krs-doc {
      border: none !important; border-radius: 0 !important;
      box-shadow: none !important; padding: 0 !important;
      margin: 0 !important; max-width: 100% !important;
    }

    /* Kompakkan agar muat 1 halaman */
    .krs-doc { font-size: 11px; }
    .kop-logo { width: 52px; height: 52px; font-size: 22px; }
    .kop-title { font-size: 16px; }
    .kop-sub { font-size: 10px; }
    .doc-title { font-size: 12px; margin: 10px 0 8px; }
    .info-table { font-size: 11px; margin-bottom: 10px; }
    .mk-table { font-size: 10.5px; }
    .mk-table th, .mk-table td { padding: 3px 6px; }
    .mk-table th { background: #fff !important; }
    .sig-table { margin-top: 14px; font-size: 10.5px; }
    .sig-space { height: 50px; }
  }
</style>

<!-- Tombol aksi (tidak ikut tercetak) -->
<div class="d-flex gap-2 no-print" style="justify-content:center; margin-bottom:18px;">
  <button onclick="window.print()" class="btn-app">
    <i class="fas fa-print"></i> Cetak KRS
  </button>
  <a href="krs.php" class="btn-app outline">
    <i class="fas fa-arrow-left"></i> Kembali
  </a>
</div>

<!-- ============ DOKUMEN KRS ============ -->
<div class="krs-doc" id="krs-area">

  <!-- KOP -->
  <div class="krs-kop">
    <div class="kop-logo"><i class="fas fa-graduation-cap"></i></div>
    <div class="kop-text">
      <div class="kop-title">UNIVERSITAS TEKNOLOGI MATARAM</div>
      <div class="kop-sub">Jalan Raya Panca Usaha No. 88, Mataram, NTB<br>Telp. (0370) 123456, Email: info@utm.ac.id</div>
    </div>
    <div style="width:62px;"></div>
  </div>

  <div class="doc-title">KARTU RENCANA STUDI (KRS) MAHASISWA</div>

  <!-- INFO MAHASISWA -->
  <table class="info-table">
    <tr>
      <td style="width:15%">NIM</td><td style="width:35%">: <?= htmlspecialchars($data['nim']) ?></td>
      <td style="width:15%">TA - SMT</td><td>: <?= htmlspecialchars($data['tahun_ajaran']) ?> - <?= htmlspecialchars($data['semester']) ?></td>
    </tr>
    <tr>
      <td>NAMA</td><td>: <?= htmlspecialchars($data['nama_mhs']) ?></td>
      <td>Fakultas</td><td>: TEKNIK INFORMATIKA</td>
    </tr>
    <tr>
      <td>Dosen Wali/PA</td><td>: <?= htmlspecialchars($data['nama_dosen'] ?? '-') ?></td>
      <td>Program Studi</td><td>: <?= htmlspecialchars($data['prodi'] ?? '-') ?></td>
    </tr>
    <tr>
      <td>Jenjang</td><td>: S1</td>
      <td>Kelas</td><td>: <?= htmlspecialchars($data['nama_kelas'] ?? '-') ?></td>
    </tr>
  </table>

  <!-- TABEL MATA KULIAH -->
  <table class="mk-table">
    <thead>
      <tr>
        <th style="width:32px">NO</th>
        <th style="width:70px">KODE</th>
        <th>MATAKULIAH</th>
        <th style="width:60px">SEMESTER</th>
        <th style="width:50px">SKS</th>
        <th style="width:60px">TTD</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($matkul) > 0): $no = 1; ?>
        <?php foreach ($matkul as $mk): ?>
          <tr>
            <td class="c"><?= $no++ ?></td>
            <td class="c"><?= htmlspecialchars($mk['kode_mk']) ?></td>
            <td class="l"><?= htmlspecialchars($mk['nama_mk']) ?></td>
            <td class="c"><?= htmlspecialchars($mk['semester']) ?></td>
            <td class="c"><?= htmlspecialchars($mk['sks']) ?></td>
            <td class="c"></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="c">Belum ada mata kuliah di KRS ini.</td></tr>
      <?php endif; ?>
    </tbody>
    <?php if (count($matkul) > 0): ?>
    <tfoot>
      <tr>
        <th colspan="4" style="text-align:right;">TOTAL SKS</th>
        <th class="c"><?= $total_sks ?></th>
        <th></th>
      </tr>
    </tfoot>
    <?php endif; ?>
  </table>

  <!-- TANDA TANGAN -->
  <table class="sig-table">
    <tr>
      <td style="width:55%; font-size:11px;">
        Keterangan:<br>
        1 lembar untuk Mahasiswa &nbsp;&nbsp; 1 lembar untuk Fakultas<br>
        1 lembar untuk Program Studi &nbsp;&nbsp; 1 lembar untuk Dosen Wali/PA
      </td>
      <td style="text-align:center;">
        Mataram, <?= date('d F Y') ?><br>
        Dosen Wali,
        <div class="sig-space"></div>
        <strong><u><?= htmlspecialchars($data['nama_dosen'] ?? 'Nama Dosen') ?></u></strong><br>
        NIDN. <?= htmlspecialchars($data['nidn'] ?? '-') ?>
      </td>
    </tr>
  </table>
</div>

<p class="no-print" style="text-align:center; font-size:12.5px; color:var(--muted); margin-top:12px;">
  <i class="fas fa-info-circle"></i>
  Tips: pada dialog cetak, buka <strong>More settings</strong> lalu matikan <strong>"Headers and footers"</strong> agar URL & tanggal browser tidak ikut tercetak.
</p>

<?php include '../includes/footer.php'; ?>