<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$nim = $_SESSION['nim'] ?? '';
if (empty($nim)) {
    die("NIM tidak ditemukan.");
}

// Data mahasiswa
$stmt = mysqli_prepare($koneksi, "SELECT nim, nama_mhs, semester FROM tblmhs2 WHERE nim = ?");
mysqli_stmt_bind_param($stmt, 's', $nim);
mysqli_stmt_execute($stmt);
$data_mhs = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$data_mhs) die("Data mahasiswa tidak ditemukan.");

// Dropdown data
$result_dosen  = mysqli_query($koneksi, "SELECT nidn, nama_dosen FROM tbldosen ORDER BY nama_dosen");
$result_kelas  = mysqli_query($koneksi, "SELECT kelas, MIN(id_jadwal) AS id_jadwal FROM tbljadwalkuliah GROUP BY kelas ORDER BY kelas");
$result_matkul = mysqli_query($koneksi, "SELECT * FROM tblmatkul ORDER BY semester, nama_mk");

// Susun mata kuliah per semester untuk ditampilkan sebagai katalog (dipilih via JS, tanpa reload)
$matkul_per_semester = [];
while ($mk = mysqli_fetch_assoc($result_matkul)) {
    $sem = (int) $mk['semester'];
    $matkul_per_semester[$sem][] = [
        'kode_mk' => $mk['kode_mk'],
        'nama_mk' => $mk['nama_mk'],
        'sks'     => (int) $mk['sks'],
    ];
}
ksort($matkul_per_semester);
$semester_mahasiswa = (int) ($data_mhs['semester'] ?? 1);
if ($semester_mahasiswa < 1 || $semester_mahasiswa > 8) $semester_mahasiswa = 1;

$error = '';
if (isset($_POST['buatkrs'])) {
    $semester     = $_POST['semester'];
    $tahun_ajaran = $_POST['tahun_ajaran'];
    $nidn         = $_POST['nidn'];
    $selected_mk  = $_POST['kode_mk'] ?? [];
    $id_jadwal    = $_POST['id_jadwal'] ?? '';

    if (empty($selected_mk)) {
        $error = "Pilih minimal satu mata kuliah.";
    } elseif (empty($id_jadwal)) {
        $error = "Pilih kelas terlebih dahulu.";
    } else {
        // Cek KRS sudah ada? Kalau sudah ada, pakai id_krs yang sama (bukan bikin baru)
        $stmt_check = mysqli_prepare($koneksi, "SELECT id_krs FROM tblkrs WHERE nim = ? AND semester = ? AND tahun_ajaran = ?");
        mysqli_stmt_bind_param($stmt_check, 'sss', $nim, $semester, $tahun_ajaran);
        mysqli_stmt_execute($stmt_check);
        $existing = mysqli_stmt_get_result($stmt_check)->fetch_assoc();

        if ($existing) {
            $id_krs = $existing['id_krs'];
        } else {
            $stmt_krs = mysqli_prepare($koneksi, "INSERT INTO tblkrs (nim, semester, tahun_ajaran, nidn, id_jadwal) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_krs, 'sssss', $nim, $semester, $tahun_ajaran, $nidn, $id_jadwal);
            mysqli_stmt_execute($stmt_krs);
            $id_krs = mysqli_insert_id($koneksi);
        }

        $success = 0;
        foreach ($selected_mk as $kode_mk) {
            // Cek duplikat dalam KRS yang sama (bukan lintas semester)
            $stmt_dup = mysqli_prepare($koneksi, "SELECT * FROM tblkrsdetail WHERE id_krs = ? AND kode_mk = ?");
            mysqli_stmt_bind_param($stmt_dup, 'is', $id_krs, $kode_mk);
            mysqli_stmt_execute($stmt_dup);
            if (mysqli_num_rows(mysqli_stmt_get_result($stmt_dup)) > 0) continue;

            $stmt_detail = mysqli_prepare($koneksi, "INSERT INTO tblkrsdetail (id_krs, nim, kode_mk) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt_detail, 'iss', $id_krs, $nim, $kode_mk);
            if (mysqli_stmt_execute($stmt_detail)) $success++;
        }

        if ($success > 0) {
            echo "<script>alert('$success mata kuliah berhasil ditambahkan ke KRS!'); window.location='krs.php';</script>";
            exit;
        } else {
            $error = "Semua mata kuliah sudah pernah diambil atau gagal disimpan.";
        }
    }
}

$currentPage = 'krs';
$page_title  = 'Isi KRS';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="data-card">
  <div class="card-head">
    <h3><i class="fas fa-file-signature me-2" style="color:var(--primary)"></i>Isi Kartu Rencana Studi</h3>
  </div>

  <!-- Info Mahasiswa -->
  <div class="mhs-info-grid">
    <div class="mhs-info-item">
      <div class="label">NIM</div>
      <div class="value"><?= htmlspecialchars($data_mhs['nim']) ?></div>
    </div>
    <div class="mhs-info-item">
      <div class="label">Nama Mahasiswa</div>
      <div class="value"><?= htmlspecialchars($data_mhs['nama_mhs']) ?></div>
    </div>
  </div>

  <form method="POST" id="formKRS">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Semester KRS</label>
        <select name="semester" id="semesterKRS" class="form-select" required>
          <option value="" disabled <?= $semester_mahasiswa ? '' : 'selected' ?>>-- Pilih Semester --</option>
          <?php for ($s = 1; $s <= 8; $s++): ?>
            <option value="<?= $s ?>" <?= $s === $semester_mahasiswa ? 'selected' : '' ?>>
              Semester <?= $s ?><?= $s === $semester_mahasiswa ? ' (semester kamu saat ini)' : '' ?>
            </option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Tahun Ajaran</label>
        <input type="text" name="tahun_ajaran" class="form-control" required placeholder="Contoh: 2025/2026" />
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Dosen Wali</label>
        <select name="nidn" class="form-select" required>
          <option value="">-- Pilih Dosen --</option>
          <?php while ($d = mysqli_fetch_assoc($result_dosen)): ?>
            <option value="<?= htmlspecialchars($d['nidn']) ?>"><?= htmlspecialchars($d['nama_dosen']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Kelas</label>
        <select name="id_jadwal" class="form-select" required>
          <option value="" disabled selected>-- Pilih Kelas --</option>
          <?php while ($k = mysqli_fetch_assoc($result_kelas)): ?>
            <option value="<?= htmlspecialchars($k['id_jadwal']) ?>"><?= htmlspecialchars($k['kelas']) ?></option>
          <?php endwhile; ?>
        </select>
        <small style="color:#6B7280;">Pilih kelompok kelas kamu (mis. TI A). Mata kuliah yang diambil dipilih terpisah di katalog bawah.</small>
      </div>
    </div>

    <div class="krs-catalog-box">
      <div class="krs-catalog-head">
        <div>
          <h5 style="font-weight:700; color:var(--navy); margin:0;">
            <i class="fas fa-book-open" style="color:var(--primary);"></i> Katalog Mata Kuliah
          </h5>
          <p style="color:#6B7280; font-size:13px; margin:4px 0 0;">
            Menampilkan mata kuliah semester terpilih. Untuk mengulang atau mengambil mata kuliah susulan, ganti semester di bawah ini — pilihan sebelumnya tidak akan hilang.
          </p>
        </div>
        <div class="krs-catalog-filter">
          <label class="form-label" style="margin-bottom:4px;">Tampilkan mata kuliah semester</label>
          <select id="filterSemesterMK" class="form-select">
            <?php for ($s = 1; $s <= 8; $s++): ?>
              <option value="<?= $s ?>" <?= $s === $semester_mahasiswa ? 'selected' : '' ?>>
                Semester <?= $s ?><?= $s === $semester_mahasiswa ? ' (semester kamu)' : '' ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>
      </div>

      <div id="katalogGrid" class="krs-catalog-grid"></div>
      <div id="katalogKosong" class="krs-catalog-empty" style="display:none;">
        <i class="fas fa-inbox"></i> Belum ada mata kuliah terdaftar untuk semester ini.
      </div>
    </div>

    <!-- Daftar Pilihan -->
    <div class="krs-selected-box" id="daftarPilihan">
      <h6><i class="fas fa-list-check"></i> Mata Kuliah yang Dipilih</h6>
      <div class="table-responsive">
        <table class="table-app" id="tabelPilihan">
          <thead>
            <tr>
              <th>Kode MK</th>
              <th>Nama MK</th>
              <th style="width:90px">Semester</th>
              <th style="width:80px">SKS</th>
              <th style="width:90px">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="krs-total-sks">
        <span><i class="fas fa-calculator"></i> Total SKS yang Diambil</span>
        <span class="value" id="totalSKS">0</span>
      </div>
    </div>

    <div id="kodeMKInputs"></div>

    <?php if ($error): ?>
      <div style="padding:12px 16px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; margin:16px 0; font-size:14px;">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div class="d-flex gap-2 mt-3">
      <button type="submit" name="buatkrs" class="btn-app">
        <i class="fas fa-save"></i> Simpan KRS
      </button>
      <a href="krs.php" class="btn-app outline">
        <i class="fas fa-arrow-left"></i> Batal
      </a>
    </div>
  </form>
</div>

<style>
  .krs-catalog-box {
    background: #fff;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    padding: 0;
    margin: 22px 0;
    overflow: hidden;
  }
  .krs-catalog-head {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin: 0;
    padding: 14px 20px;
    background: var(--navy, #1E293B);
    border-bottom: 1px solid #D1D5DB;
  }
  .krs-catalog-head h5 { color: #fff !important; }
  .krs-catalog-head h5 i { color: #93C5FD !important; }
  .krs-catalog-head p { color: #CBD5E1 !important; }
  .krs-catalog-filter { min-width: 240px; }
  .krs-catalog-filter .form-label { color: #E2E8F0; font-size: 12.5px; font-weight: 600; }
  .krs-catalog-filter .form-select {
    border-radius: 4px;
    border: 1px solid #CBD5E1;
  }
  .krs-catalog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 0;
    border-top: 1px solid #E5E7EB;
  }
  .krs-mk-card {
    background: #fff;
    border: none;
    border-right: 1px solid #E5E7EB;
    border-bottom: 1px solid #E5E7EB;
    padding: 16px 18px;
    transition: background .12s;
    cursor: pointer;
  }
  .krs-mk-card:hover {
    background: #F8FAFC;
  }
  .krs-mk-card.selected {
    background: #EFF6FF;
    box-shadow: inset 3px 0 0 var(--navy, #1E293B);
  }
  .krs-mk-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #D1D5DB;
  }
  .krs-mk-code {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.3px;
    color: var(--navy, #1E293B);
    font-family: 'Courier New', monospace;
  }
  .krs-mk-sks {
    font-size: 11.5px;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  .krs-mk-name {
    font-weight: 600;
    color: #1E293B;
    font-size: 14.5px;
    line-height: 1.45;
    margin-bottom: 14px;
    min-height: 40px;
  }
  .krs-mk-btn {
    width: 100%;
    padding: 7px;
    border-radius: 4px;
    border: 1px solid #94A3B8;
    background: #fff;
    color: #334155;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.2px;
    transition: background .12s, color .12s, border-color .12s;
  }
  .krs-mk-card.selected .krs-mk-btn {
    background: var(--navy, #1E293B);
    color: #fff;
    border-color: var(--navy, #1E293B);
  }
  .krs-catalog-empty {
    text-align: center;
    color: #9CA3AF;
    padding: 40px 0;
    font-size: 14px;
  }
</style>

<script>
  const MATKUL_PER_SEMESTER = <?= json_encode($matkul_per_semester, JSON_PRETTY_PRINT) ?>;
  let selectedMK = []; // {kode_mk, nama_mk, sks, semester}

  function renderKatalog(semester) {
    const grid = document.getElementById('katalogGrid');
    const kosong = document.getElementById('katalogKosong');
    const daftar = MATKUL_PER_SEMESTER[semester] || [];
    grid.innerHTML = '';

    if (daftar.length === 0) {
      grid.style.display = 'none';
      kosong.style.display = 'block';
      return;
    }
    grid.style.display = 'grid';
    kosong.style.display = 'none';

    daftar.forEach(mk => {
      const isSelected = selectedMK.some(m => m.kode_mk === mk.kode_mk);
      const card = document.createElement('div');
      card.className = 'krs-mk-card' + (isSelected ? ' selected' : '');
      card.innerHTML = `
        <div class="krs-mk-card-top">
          <span class="krs-mk-code">${mk.kode_mk}</span>
          <span class="krs-mk-sks">${mk.sks} SKS</span>
        </div>
        <div class="krs-mk-name">${mk.nama_mk}</div>
        <button type="button" class="krs-mk-btn">
          <i class="fas fa-${isSelected ? 'check-circle' : 'plus'}"></i> ${isSelected ? 'Terdaftar di KRS' : 'Tambahkan'}
        </button>`;
      card.addEventListener('click', () => toggleMK({ ...mk, semester }));
      grid.appendChild(card);
    });
  }

  function toggleMK(mk) {
    const idx = selectedMK.findIndex(m => m.kode_mk === mk.kode_mk);
    if (idx >= 0) {
      selectedMK.splice(idx, 1);
    } else {
      selectedMK.push(mk);
    }
    updateDaftar();
    renderKatalog(document.getElementById('filterSemesterMK').value);
  }

  function hapusMK(kode_mk) {
    selectedMK = selectedMK.filter(mk => mk.kode_mk !== kode_mk);
    updateDaftar();
    renderKatalog(document.getElementById('filterSemesterMK').value);
  }

  function updateDaftar() {
    const tbody = document.querySelector('#tabelPilihan tbody');
    const kodeMKInputs = document.getElementById('kodeMKInputs');
    tbody.innerHTML = '';
    kodeMKInputs.innerHTML = '';
    let totalSKS = 0;

    selectedMK.forEach(mk => {
      totalSKS += parseInt(mk.sks);
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><span class="badge-app green">${mk.kode_mk}</span></td>
        <td>${mk.nama_mk}</td>
        <td style="text-align:center">${mk.semester}</td>
        <td style="text-align:center"><strong>${mk.sks}</strong></td>
        <td>
          <button type="button" class="btn-app btn-sm-app danger" onclick="hapusMK('${mk.kode_mk}')">
            <i class="fas fa-times"></i> Hapus
          </button>
        </td>`;
      tbody.appendChild(tr);

      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'kode_mk[]';
      input.value = mk.kode_mk;
      kodeMKInputs.appendChild(input);
    });

    document.getElementById('totalSKS').textContent = totalSKS;
  }

  document.getElementById('filterSemesterMK').addEventListener('change', function () {
    renderKatalog(this.value);
  });

  // Render awal: sesuai semester mahasiswa
  renderKatalog(document.getElementById('filterSemesterMK').value);
</script>

<?php include '../includes/footer.php'; ?>