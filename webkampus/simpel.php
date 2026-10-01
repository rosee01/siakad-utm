<?php
session_start();
require_once __DIR__ . '/includes/security.php';

// Redirect jika belum login
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];
$page = $_GET['page'] ?? 'home';
if (!is_string($page) || !in_array($page, ['home', 'mahasiswa', 'dosen', 'jadwal', 'prodi', 'matakuliah', 'krs', 'kelas', 'ruangan'], true)) {
    $page = 'home';
}
$search = $_GET['search'] ?? '';
if (!is_string($search)) {
    $search = '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Akademik</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #eef2f5;
            color: #333;
        }
        .navbar {
            background-color: #3498db;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 6px;
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        .search-form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 auto;
            justify-content: center;
        }
        .search-form input[type="text"] {
            padding: 7px 12px;
            border: none;
            border-radius: 6px;
            width: 220px;
            font-size: 14px;
        }
        .search-form input:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(52,152,219,0.7);
        }
        .search-form button {
            padding: 7px 12px;
            border: none;
            border-radius: 6px;
            background: white;
            color: #3498db;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .search-form button:hover {
            background: #ecf0f1;
        }
        .content {
            padding: 100px 20px 60px;
            background: white;
            max-width: 1000px;
            margin: 80px auto;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
            min-height: calc(100vh - 160px);
        }
        .content h3 {
            text-align: center;
            color: #e74c3c;
            font-size: 18px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: 40px;
            background-color: #3498db;
            z-index: 999;
            overflow: hidden;
        }
        .footer p {
            white-space: nowrap;
            color: black;
            font-weight: 600;
            padding-left: 100%;
            animation: gerakFooter 20s linear infinite;
            font-size: 14px;
            line-height: 40px;
            margin: 0;
        }
        @keyframes gerakFooter {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }
        @media screen and (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .search-form {
                width: 100%;
                justify-content: flex-start;
            }
            .nav-links {
                flex-wrap: wrap;
                gap: 10px;
            }
            .content {
                padding: 100px 15px 60px;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-links">
        <?php if ($role === 'mahasiswa'): ?>
            <a href="?page=mahasiswa"><i class="fa-solid fa-user"></i> Biodata Mahasiswa</a>
            <a href="?page=jadwal"><i class="fa-solid fa-calendar-days"></i> Jadwal</a>
            <a href="?page=krs"><i class="fa-solid fa-clipboard-list"></i> KRS</a>
            <a href="?page=khs"><i class="fa-solid fa-graduation-cap"></i> KHS</a>
        <?php elseif ($role === 'admin' || $role === 'dosen'): ?>
            <a href="?page=home"><i class="fa-solid fa-house"></i> Home</a>
            <a href="?page=mahasiswa"><i class="fa-solid fa-user-graduate"></i> Mahasiswa</a>
            <a href="?page=dosen"><i class="fa-solid fa-chalkboard-teacher"></i> Dosen</a>
            <a href="?page=jadwal"><i class="fa-solid fa-calendar-days"></i> Jadwal</a>
            <a href="?page=prodi"><i class="fa-solid fa-building-columns"></i> Prodi</a>
            <a href="?page=matakuliah"><i class="fa-solid fa-book"></i> Mata Kuliah</a>
            <a href="?page=krs"><i class="fa-solid fa-clipboard-list"></i> KRS</a>
            <a href="?page=kelas"><i class="fa-solid fa-users"></i> Kelas</a>
            <a href="?page=ruangan"><i class="fa-solid fa-door-open"></i> Ruangan</a>
        <?php endif; ?>
        <form method="post" action="logout.php" style="display:inline;">
            <?= csrf_field() ?>
            <button type="submit" style="color:white;background:none;border:0;font-weight:600;padding:8px 14px;cursor:pointer;">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </button>
        </form>
    </div>
    <form class="search-form" method="GET" action="" style="margin: 0 auto; display: flex; justify-content: center; align-items: center; gap: 10px;">
        <input type="hidden" name="page" value="<?= htmlspecialchars($page, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input type="text" name="search" placeholder="Cari..." value="<?= htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" style="width: 220px;">
        <button type="submit"><i class="fa fa-search"></i></button>
    </form>
</div>

<div class="content">
    <?php
    switch ($page) {
        case 'mahasiswa': include "mahasiswa.php"; break;
        case 'dosen': include "dosen.php"; break;
        case 'matakuliah': include "matakul.php"; break;
        case 'jadwal': include "jadwalkuliah.php"; break;
        case 'prodi': include "prodi.php"; break;
        case 'krs': include "krs.php"; break;
        case 'kelas': include "kelas.php"; break;
        case 'ruangan': include "ruangan.php"; break;
        default: include "beranda.php"; break;
    }
    ?>
</div>

<div class="footer"></div>
    <p><i class="fa-solid fa-code"></i> Dibuat oleh rosee &     copy; 2025 - Sistem Akademik</p>
</div>

</body>
</html>
