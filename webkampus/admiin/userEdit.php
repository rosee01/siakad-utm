<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pesan      = '';
$pesan_type = '';

$id_param = $_GET['id_user'] ?? '';
$id_get = is_string($id_param) ? trim($id_param) : '';
if ($id_get === '') { header("Location: user.php"); exit; }

$stmt = $koneksi->prepare("SELECT * FROM user WHERE id_user = ?");
$stmt->bind_param("s", $id_get);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { header("Location: user.php"); exit; }
$dataedit = $res->fetch_assoc();

if (isset($_POST["simpan"])) {
    foreach (["username", "password", "role", "ref_id", "status", "last_login"] as $field) {
        if (!isset($_POST[$field]) || !is_string($_POST[$field])) {
            http_response_code(400);
            exit("Data user tidak valid.");
        }
    }
    if (
        !in_array($_POST["role"], ["admin", "dosen", "mahasiswa"], true)
        || !in_array($_POST["status"], ["aktif", "nonaktif"], true)
        || $_POST["username"] === ""
        || ($_POST["password"] !== "" && (strlen($_POST["password"]) < 8 || strlen($_POST["password"]) > 4096))
        || strlen($_POST["username"]) > 100
        || strlen($_POST["ref_id"]) > 30
    ) {
        http_response_code(400);
        exit("Data user tidak valid.");
    }

    $username   = $_POST["username"];
    $password   = $_POST["password"];
    $role       = $_POST["role"];
    $ref_id     = $_POST["ref_id"];
    $status     = $_POST["status"];
    $last_login_input = $_POST["last_login"];
    $last_login = null;
    if ($last_login_input !== "") {
        $parsed_last_login = DateTime::createFromFormat('Y-m-d\TH:i', $last_login_input);
        if (!$parsed_last_login || $parsed_last_login->format('Y-m-d\TH:i') !== $last_login_input) {
            http_response_code(400);
            exit("Format waktu login terakhir tidak valid.");
        }
        $last_login = $parsed_last_login->format('Y-m-d H:i:s');
    }

    if ($role === "dosen" || $role === "mahasiswa") {
        $table = $role === "dosen" ? "tbldosen" : "tblmhs2";
        $column = $role === "dosen" ? "nidn" : "nim";
        $stmt_ref = $koneksi->prepare("SELECT 1 FROM {$table} WHERE {$column} = ? LIMIT 1");
        $stmt_ref->bind_param("s", $ref_id);
        $stmt_ref->execute();
        if (!$stmt_ref->get_result()->fetch_row()) {
            http_response_code(400);
            exit("ID referensi tidak sesuai dengan role yang dipilih.");
        }
    }

    if (!empty($password)) {
        // ✅ Hash bcrypt untuk password baru
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $q = "UPDATE user SET username=?, password=?, role=?, ref_id=?, status=?, last_login=? WHERE id_user=?";
        $s = $koneksi->prepare($q);
        $s->bind_param("sssssss", $username, $password_hash, $role, $ref_id, $status, $last_login, $id_get);
    } else {
        $q = "UPDATE user SET username=?, role=?, ref_id=?, status=?, last_login=? WHERE id_user=?";
        $s = $koneksi->prepare($q);
        $s->bind_param("ssssss", $username, $role, $ref_id, $status, $last_login, $id_get);
    }

    if ($s->execute()) {
        $pesan = "Data user berhasil diperbarui.";
        $pesan_type = 'success';
        $s2 = $koneksi->prepare("SELECT * FROM user WHERE id_user=?");
        $s2->bind_param("s", $id_get);
        $s2->execute();
        $dataedit = $s2->get_result()->fetch_assoc();
    } else {
        $pesan = "Data gagal diperbarui.";
        $pesan_type = 'error';
    }
}

$currentPage = 'user';
$page_title  = 'Edit User';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div style="margin-bottom:16px; display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--muted);">
  <a href="user.php" style="color:var(--primary); text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Kembali ke Data User
  </a>
  <span>›</span>
  <span>Edit User</span>
</div>

<div class="data-card" style="max-width:780px;">
  <div class="card-head">
    <h3><i class="fas fa-user-edit me-2" style="color:var(--primary)"></i>Form Edit User</h3>
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
    <?= csrf_field() ?>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">ID User</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($dataedit['id_user']) ?>" readonly
               style="background:#f3f4f6; color:var(--muted);" />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($dataedit['username']) ?>" required />
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Password <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select" required>
          <option value="admin"     <?= $dataedit['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
          <option value="dosen"     <?= $dataedit['role'] === 'dosen' ? 'selected' : '' ?>>Dosen</option>
          <option value="mahasiswa" <?= $dataedit['role'] === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Ref ID</label>
        <input type="text" name="ref_id" class="form-control" value="<?= htmlspecialchars($dataedit['ref_id']) ?>" required />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
          <option value="aktif"    <?= $dataedit['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
          <option value="nonaktif" <?= $dataedit['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
      </div>
    </div>
    <div class="mb-4">
      <label class="form-label">Last Login</label>
      <input type="datetime-local" name="last_login" class="form-control"
             value="<?= $dataedit['last_login'] ? date('Y-m-d\TH:i', strtotime($dataedit['last_login'])) : '' ?>" />
    </div>
    <div class="d-flex gap-2">
      <button type="submit" name="simpan" class="btn-app"><i class="fas fa-save"></i> Simpan</button>
      <a href="user.php" class="btn-app outline"><i class="fas fa-times"></i> Batal</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>