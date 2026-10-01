<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../koneksi.php';

/* ---------- DATA TABEL ---------- */
$query  = "SELECT * FROM tblmhs2 ORDER BY nim ASC";
$result = mysqli_query($koneksi, $query);

$currentPage = 'mahasiswa';
$page_title  = 'Data Mahasiswa';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="data-card">
  <?php if (is_array($flash) && isset($flash['message'], $flash['type'])): ?>
    <div style="padding:12px 16px; border-radius:10px; background:<?= $flash['type'] === 'success' ? '#f0fdf4' : '#fef2f2' ?>; border:1px solid <?= $flash['type'] === 'success' ? '#bbf7d0' : '#fecaca' ?>; color:<?= $flash['type'] === 'success' ? '#166534' : '#b91c1c' ?>; margin-bottom:18px;">
      <?= htmlspecialchars($flash['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
  <?php endif; ?>
  <div class="card-head">
    <h3><i class="fas fa-user-graduate me-2" style="color:var(--primary)"></i>Data Mahasiswa</h3>
    <a href="mahasiswaAdd.php" class="btn-app">
      <i class="fas fa-plus-circle"></i> Tambah Mahasiswa
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th>NIM</th>
          <th>Nama</th>
          <th>Prodi</th>
          <th style="width:90px">Semester</th>
          <th style="width:70px">JK</th>
          <th>Alamat</th>
          <th style="width:170px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="8" style="text-align:center; padding:24px; color:var(--muted);">Tidak ada data mahasiswa.</td></tr>
        <?php endif; ?>
        <?php $no = 1; while ($m = mysqli_fetch_array($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><strong><?= htmlspecialchars($m['nim']); ?></strong></td>
            <td><?= htmlspecialchars($m['nama_mhs']); ?></td>
            <td><span class="badge-app green"><?= htmlspecialchars($m['prodi']); ?></span></td>
            <td style="text-align:center"><?= htmlspecialchars($m['semester']); ?></td>
            <td><?= htmlspecialchars($m['jns_kelamin']); ?></td>
            <td><?= htmlspecialchars($m['alamat']); ?></td>
            <td>
              <a href="mahasiswaEdit.php?nim=<?= urlencode($m['nim']); ?>" class="btn-app btn-sm-app outline">
                <i class="fas fa-edit"></i> Edit
              </a>
              <form method="post" action="mahasiswadelete.php" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                <input type="hidden" name="nim" value="<?= htmlspecialchars($m['nim'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn-app btn-sm-app danger">
                  <i class="fas fa-trash-alt"></i> Hapus
                </button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>