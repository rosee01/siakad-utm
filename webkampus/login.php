<?php
session_start();
include "koneksi.php";

// Jika sudah login, langsung arahkan sesuai role
if (isset($_SESSION['username'])) {
    switch ($_SESSION['role']) {
        case 'admin':     header("Location: admiin/dashboard.php"); break;
        case 'dosen':     header("Location: dosen/dashboard.php"); break;
        default:          header("Location: mahasiswa/profil_mahasiswa.php"); break;
    }
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // ✅ Prepared statement — aman dari SQL injection
    $stmt = $koneksi->prepare("SELECT id_user, username, password, role, ref_id, status FROM user WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && $user['status'] === 'aktif') {
        $stored   = $user['password'];
        $login_ok = false;

        // 1) Cara modern: bcrypt
        if (password_verify($password, $stored)) {
            $login_ok = true;
        }
        // 2) Legacy md5 → verifikasi lalu upgrade otomatis
        elseif (preg_match('/^[a-f0-9]{32}$/i', $stored) && md5($password) === $stored) {
            $login_ok = true;
        }
        // 3) Legacy plaintext → verifikasi lalu upgrade otomatis
        elseif ($stored === $password) {
            $login_ok = true;
        }

        if ($login_ok) {
            // ✅ Transparent upgrade: password lama langsung disimpan ulang sebagai bcrypt
            if (!password_verify($password, $stored)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $up = $koneksi->prepare("UPDATE user SET password = ? WHERE id_user = ?");
                $up->bind_param("si", $newHash, $user['id_user']);
                $up->execute();
            }

            // ✅ Cegah session fixation
            session_regenerate_id(true);

            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['ref_id']   = $user['ref_id'];
            if ($user['role'] === 'mahasiswa') $_SESSION['nim'] = $user['ref_id'];

            // Catat waktu login terakhir
            $now = date('Y-m-d H:i:s');
            $up2 = $koneksi->prepare("UPDATE user SET last_login = ? WHERE id_user = ?");
            $up2->bind_param("si", $now, $user['id_user']);
            $up2->execute();

            switch ($user['role']) {
                case 'admin': header("Location: admiin/dashboard.php"); break;
                case 'dosen': header("Location: dosen/dashboard.php"); break;
                default:      header("Location: mahasiswa/profil_mahasiswa.php"); break;
            }
            exit;
        }
    }
    $error = "Username atau password salah, atau akun nonaktif.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIAKAD | Sistem Informasi Akademik</title>
  <link rel="stylesheet" href="../css/siakad-login.css">
</head>
<body>
  <main class="page">
    <section class="hero">
      <div class="brand">
        <div class="brand-mark" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M3 7.5 12 3l9 4.5-9 4.5-9-4.5Z" fill="currentColor"/>
            <path d="M6 10.5V15c0 2.2 2.7 4 6 4s6-1.8 6-4v-4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M21 8v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </div>
        <div>
          <div class="brand-name">SIAKAD</div>
          <div class="brand-sub">Sistem Informasi Akademik</div>
        </div>
      </div>

      <div class="hero-content">
        <div class="eyebrow">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.83 6.1 6.67.77-4.93 4.55 1.32 6.58L12 17.2 6.11 20.5l1.32-6.58L2.5 9.37l6.67-.77L12 2.5Z"/></svg>
          Academic Management System
        </div>
        <h1>Selamat Datang di <span>SIAKAD</span></h1>
        <p class="hero-description">
          Sistem informasi akademik untuk membantu pengelolaan data mahasiswa,
          dosen, jadwal, KRS, nilai, dan administrasi akademik secara terintegrasi.
        </p>
      </div>

      <img class="campus-photo" src="../assets/img/utm.png" alt="Gedung Universitas Teknologi Mataram">
      <div class="photo-fade" aria-hidden="true"></div>

      <div class="features">
        <div class="feature">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <circle cx="9" cy="7.5" r="3.5"/>
              <path d="M2.5 19c.5-3.4 3-5.3 6.5-5.3s6 1.9 6.5 5.3l.05 1H2.5v-1Z"/>
              <circle cx="17.2" cy="8.5" r="2.7"/>
              <path d="M15.4 12.9c.6-.15 1.2-.2 1.8-.2 2.9 0 5 1.6 5.4 4.5l.05.8h-4.2c-.2-2.2-1.3-4-3.05-5.1Z"/>
            </svg>
          </div>
          <div><strong>Terintegrasi</strong><span>Data akademik dalam satu sistem.</span></div>
        </div>

        <div class="feature">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <circle cx="12" cy="12" r="10" fill="currentColor"/>
              <path d="M12 6.5v5.5l3.4 2" fill="none" stroke="#fff" stroke-width="1.9" stroke-linecap="round"/>
            </svg>
          </div>
          <div><strong>Efisien</strong><span>Proses akademik lebih cepat dan mudah.</span></div>
        </div>

        <div class="feature">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 2.5 20 5.4v5.3c0 4.9-3.1 8.6-8 10.8-4.9-2.2-8-5.9-8-10.8V5.4L12 2.5Z" fill="currentColor"/>
              <path d="m8.8 11.6 2.3 2.3 4.3-4.3" fill="none" stroke="#fff" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div><strong>Aman</strong><span>Akses berbasis peran pengguna.</span></div>
        </div>
      </div>
    </section>

    <section class="login-side">
      <div class="login-card">
        <div class="login-heading">
          <div class="mini-logo" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <circle cx="12" cy="7.5" r="4.5"/>
              <path d="M12 14c-5 0-9 2.7-9 6.5V22h18v-1.5c0-3.8-4-6.5-9-6.5Z"/>
            </svg>
          </div>
          <h2>Login User</h2>
          <p>Masuk untuk mengakses layanan akademik</p>
        </div>

        <?php if ($error): ?>
          <div class="error">
            <span>⚠</span>
            <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
          <div class="form-group">
            <label for="username">Username / NIM</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="12" cy="8" r="3.5"/><path d="M5 20c.8-3.3 3.1-5 7-5s6.2 1.7 7 5"/>
              </svg>
              <input type="text" id="username" name="username" placeholder="Masukkan username atau NIM" required>
            </div>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>
              </svg>
              <input type="password" id="password" name="password" placeholder="Masukkan password" required>
              <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan password">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 3l18 18"/>
                  <path d="M10.5 5.2A10 10 0 0 1 12 5c5.2 0 8.8 4.4 9.7 7-.35 1-1.1 2.4-2.2 3.6M6.5 6.5C4.3 8.1 2.9 10.2 2.3 12c.9 2.6 4.5 7 9.7 7 1.5 0 2.8-.4 4-1"/>
                  <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="role">Login Sebagai</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="9" cy="8" r="3"/><path d="M3.5 20c.6-3.1 2.4-4.7 5.5-4.7s4.9 1.6 5.5 4.7"/>
                <path d="M16 5.5a3 3 0 0 1 0 5.8M17 15.5c2.1.5 3.3 1.9 3.7 4"/>
              </svg>
              <select id="role" name="role" required>
                <option value="" disabled selected>Pilih peran pengguna</option>
                <option value="admin">Admin</option>
                <option value="dosen">Dosen</option>
                <option value="mahasiswa">Mahasiswa</option>
              </select>
              <svg class="select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="m6 9 6 6 6-6"/>
              </svg>
            </div>
          </div>

          <button class="login-btn" type="submit">LOGIN</button>
        </form>

        <div class="login-note"><span>atau</span></div>
        <button class="guest-btn" type="button" onclick="alert('Fitur Login sebagai Guest belum diaktifkan.')">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <circle cx="12" cy="7.5" r="4"/><path d="M12 13.5c-4.8 0-8.5 2.6-8.5 6.2V21h17v-1.3c0-3.6-3.7-6.2-8.5-6.2Z"/>
          </svg>
          Login sebagai Guest
        </button>
      </div>
    </section>
  </main>

  <footer class="site-footer">© <?= date('Y') ?> Universitas Teknologi Mataram. All rights reserved.</footer>

  <script src="../js/siakad-login.js"></script>
</body>
</html>