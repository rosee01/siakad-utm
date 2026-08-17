<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$query  = "SELECT * FROM user ORDER BY id_user ASC";
$result = mysqli_query($koneksi, $query);

$currentPage = 'user';
$page_title  = 'Data User';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-user-cog me-2" style="color:var(--primary)"></i>Data User</h3>
    <a href="userAdd.php" class="btn-app">
      <i class="fas fa-user-plus"></i> Tambah User
    </a>
  </div>

  <div class="table-responsive">
    <table class="table-app">
      <thead>
        <tr>
          <th style="width:50px">No</th>
          <th>ID User</th>
          <th>Username</th>
          <th>Role</th>
          <th>Ref ID</th>
          <th style="width:100px">Status</th>
          <th>Last Login</th>
          <th style="width:170px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="8" style="text-align:center; padding:24px; color:var(--muted);">Tidak ada data user.</td></tr>
        <?php endif; ?>
        <?php $no = 1; while ($u = mysqli_fetch_array($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><strong><?= htmlspecialchars($u['id_user']) ?></strong></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td>
              <?php
                $roleClass = [
                    'admin'     => 'background:#dbeafe; color:#1e40af;',
                    'dosen'     => 'background:#fef3c7; color:#b45309;',
                    'mahasiswa' => 'background:#d1fae5; color:#047857;'
                ];
                $style = $roleClass[$u['role']] ?? 'background:#f3f4f6; color:#374151;';
              ?>
              <span style="display:inline-block; padding:4px 10px; border-radius:999px; font-size:11.5px; font-weight:600; <?= $style ?>">
                <?= htmlspecialchars(ucfirst($u['role'])) ?>
              </span>
            </td>
            <td><?= htmlspecialchars($u['ref_id']) ?></td>
            <td>
              <?php if ($u['status'] === 'aktif'): ?>
                <span class="badge-app green">Aktif</span>
              <?php else: ?>
                <span class="badge-app red">Nonaktif</span>
              <?php endif; ?>
            </td>
            <td style="font-size:13px; color:var(--muted);">
              <?= $u['last_login'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($u['last_login']))) : '<em>Belum login</em>' ?>
            </td>
            <td>
              <a href="userEdit.php?id_user=<?= urlencode($u['id_user']) ?>" class="btn-app btn-sm-app outline">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a href="userdelete.php?id_user=<?= urlencode($u['id_user']) ?>" class="btn-app btn-sm-app danger"
                 onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                <i class="fas fa-trash-alt"></i> Hapus
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>