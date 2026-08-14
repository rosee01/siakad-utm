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
    $id_user    = mysqli_real_escape_string($koneksi, trim($_POST["id_user"]));
    $username   = mysqli_real_escape_string($koneksi, trim($_POST["username"]));
    // ✅ Password di-hash dengan bcrypt (standar industri)
    $password   = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role       = mysqli_real_escape_string($koneksi, $_POST["role"]);
    $ref_id     = mysqli_real_escape_string($koneksi, trim($_POST["ref_id"]));
    $status     = mysqli_real_escape_string($koneksi, $_POST["status"]);
    $last_login = mysqli_real_escape_string($koneksi, $_POST["last_login"] ?: null);

    $query  = "INSERT INTO user (id_user, username, password, role, ref_id, status, last_login)
               VALUES ('$id_user', '$username', '$password', '$role', '$ref_id', '$status', " .
               ($last_login ? "'$last_login'" : "NULL") . ")";

    if (mysqli_query($koneksi, $query)) {
        header("Location: user.php");
        exit;
    } else {
        $pesan      = "Gagal menyimpan data. Pastikan ID User atau Username belum terdaftar.";
        $pesan_type = 'error';
    }
}

$currentPage = 'user';
$page_title  = 'Tambah User';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="user.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data User
  </a>
  <span>›</span>
  <span>Tambah User</span>
</div>

<div class="data-card" style="max-width:780px;">
  <div class="card-head">
    <h3><i class="fas fa-user-plus me-2" style="color:var(--primary)"></i>Form Tambah User</h3>
  </div>

  <?php if ($pesan): ?>
    <div style="padding:12px 16px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; margin-bottom:18px; font-size:14px;">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($pesan) ?>
    </div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">ID User</label>
        <input type="text" name="id_user" class="form-control" required placeholder="Contoh: U001" />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required />
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter" />
        <small class="form-text text-muted" style="font-size:12px;">Akan disimpan ter-enkripsi (bcrypt).</small>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select" required>
          <option value="" disabled selected>-- Pilih Role --</option>
          <option value="admin">Admin</option>
          <option value="dosen">Dosen</option>
          <option value="mahasiswa">Mahasiswa</option>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Ref ID</label>
        <input type="text" name="ref_id" class="form-control" required placeholder="NIM / NIDN / ID referensi" />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
          <option value="aktif" selected>Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>
    </div>
    <div class="d-flex gap-2">
      <button type="submit" name="simpan" class="btn-app"><i class="fas fa-save"></i> Simpan</button>
      <a href="user.php" class="btn-app outline"><i class="fas fa-times"></i> Batal</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>