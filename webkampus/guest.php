<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "koneksi.php";

// Kalau ternyata sudah login beneran, gak perlu mode guest — arahkan ke dashboard sesuai role
if (isset($_SESSION['username'])) {
    switch ($_SESSION['role']) {
        case 'admin':  header("Location: admiin/dashboard.php"); break;
        case 'dosen':  header("Location: dosen/dashboard.php"); break;
        default:       header("Location: mahasiswa/profil_mahasiswa.php"); break;
    }
    exit;
}

// ⚠️ Halaman ini SENGAJA tanpa session/login — hanya menampilkan info publik.
// Jangan tambahkan data pribadi (NIM, nilai, KRS, dll) di halaman ini.

// Jadwal kuliah — dikelompokkan per hari, sama seperti tampilan mahasiswa
$query  = "SELECT * FROM tbljadwalkuliah ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), jam_mulai";
$result = mysqli_query($koneksi, $query);
$jadwal_per_hari = [];
if ($result) {
    while ($data = mysqli_fetch_assoc($result)) {
        $jadwal_per_hari[$data['hari']][] = $data;
    }
}

// Daftar dosen — hanya nama & email, nomor telepon disembunyikan karena bukan info publik
$dosenResult = mysqli_query($koneksi, "SELECT nidn, nama_dosen, email FROM tbldosen ORDER BY nama_dosen ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Mode Tamu — SIAKAD</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/app.css">
  <link rel="stylesheet" href="../css/mahasiswa.css">
  <style>
    body { background: var(--bg); }
    .guest-topbar {
      background: #fff; border-bottom: 1px solid var(--border);
      padding: 14px 32px; display: flex; align-items: center; justify-content: space-between;
    }
    .guest-brand { display: flex; align-items: center; gap: 10px; font-weight: 800; color: var(--navy); }
    .guest-brand .mark {
      width: 38px; height: 38px; border-radius: 10px; background: var(--primary);
      color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px;
    }
    .guest-badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: #fef3c7; color: #92400e; font-weight: 700; font-size: 12px;
      padding: 5px 12px; border-radius: 999px; margin-left: 10px;
    }
    .guest-wrap { max-width: 1100px; margin: 0 auto; padding: 28px 20px 60px; }
    .guest-notice {
      background: #eef4ff; border: 1px solid #d6e4ff; color: var(--navy);
      border-radius: 12px; padding: 14px 18px; margin-bottom: 22px; font-size: 14px;
      display: flex; align-items: flex-start; gap: 10px;
    }
    .guest-tabs { display: flex; gap: 8px; margin-bottom: 20px; }
    .guest-tab {
      border: 1px solid var(--border); background: #fff; color: var(--muted);
      font-weight: 600; font-size: 14px; padding: 9px 18px; border-radius: 10px;
      cursor: pointer;
    }
    .guest-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .guest-section { display: none; }
    .guest-section.active { display: block; }
  </style>
</head>
<body>

  <div class="guest-topbar">
    <div class="guest-brand">
      <div class="mark"><i class="fas fa-graduation-cap"></i></div>
      <div>
        SIAKAD
        <span class="guest-badge"><i class="fas fa-user-clock"></i> Mode Tamu</span>
      </div>
    </div>
    <a href="login.php" class="btn-app">
      <i class="fas fa-right-to-bracket"></i> Login
    </a>
  </div>

  <div class="guest-wrap">

    <div class="guest-notice">
      <i class="fas fa-circle-info" style="margin-top:2px;"></i>
      <div>
        Kamu sedang melihat SIAKAD sebagai <strong>tamu</strong>. Hanya info publik (jadwal kuliah &amp; daftar dosen)
        yang bisa diakses di sini — untuk melihat KRS, nilai, atau data pribadi lainnya, silakan
        <a href="login.php">login</a> dengan akun kamu.
      </div>
    </div>

    <div class="guest-tabs">
      <button type="button" class="guest-tab active" data-target="tab-jadwal">
        <i class="fas fa-calendar-alt"></i> Jadwal Kuliah
      </button>
      <button type="button" class="guest-tab" data-target="tab-dosen">
        <i class="fas fa-chalkboard-teacher"></i> Daftar Dosen
      </button>
    </div>

    <!-- Tab: Jadwal Kuliah -->
    <div id="tab-jadwal" class="guest-section active">
      <?php if (empty($jadwal_per_hari)): ?>
        <div class="data-card" style="text-align:center; padding:40px; color:var(--muted);">
          <i class="fas fa-calendar-times" style="font-size:48px; color:var(--primary); opacity:.3; margin-bottom:14px;"></i>
          <p style="margin:0;">Belum ada jadwal kuliah tersedia.</p>
        </div>
      <?php else: ?>
        <?php foreach ($jadwal_per_hari as $hari => $list): ?>
          <div class="data-card" style="margin-bottom:20px;">
            <div class="card-head">
              <h3><i class="far fa-calendar me-2" style="color:var(--primary)"></i><?= htmlspecialchars($hari) ?></h3>
              <span class="badge-app green"><?= count($list) ?> Kelas</span>
            </div>
            <div class="jadwal-grid">
              <?php foreach ($list as $j): ?>
                <div class="jadwal-card">
                  <span class="day-badge"><?= htmlspecialchars($hari) ?></span>
                  <h4><?= htmlspecialchars($j['matakuliah']) ?></h4>
                  <div class="meta">
                    <div><i class="far fa-clock"></i> <?= htmlspecialchars($j['jam_mulai']) ?> — <?= htmlspecialchars($j['jam_selesai']) ?></div>
                    <div><i class="fas fa-chalkboard-teacher"></i> <?= htmlspecialchars($j['dosen']) ?></div>
                    <div><i class="fas fa-door-open"></i> Ruang <?= htmlspecialchars($j['ruang']) ?></div>
                    <div><i class="fas fa-users"></i> Kelas <?= htmlspecialchars($j['kelas']) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Tab: Daftar Dosen -->
    <div id="tab-dosen" class="guest-section">
      <div class="data-card">
        <div class="card-head">
          <h3><i class="fas fa-chalkboard-teacher me-2" style="color:var(--primary)"></i>Daftar Dosen</h3>
        </div>
        <div class="table-responsive">
          <table class="table-app">
            <thead>
              <tr>
                <th style="width:50px">No</th>
                <th>Nama Dosen</th>
                <th>Email</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$dosenResult || mysqli_num_rows($dosenResult) === 0): ?>
                <tr><td colspan="3" style="text-align:center; padding:24px; color:var(--muted);">Belum ada data dosen.</td></tr>
              <?php else: ?>
                <?php $no = 1; while ($d = mysqli_fetch_assoc($dosenResult)): ?>
                  <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($d['nama_dosen']); ?></td>
                    <td><?= htmlspecialchars($d['email']); ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  <footer class="site-footer" style="text-align:center; padding:20px; color:var(--muted); font-size:13px;">
    © <?= date('Y') ?> SIAKAD. Proyek demonstrasi independen, bukan layanan resmi universitas.
  </footer>

  <script>
    document.querySelectorAll('.guest-tab').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.querySelectorAll('.guest-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.guest-section').forEach(s => s.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.target).classList.add('active');
      });
    });
  </script>
</body>
</html>
