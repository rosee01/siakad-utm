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
$result_kelas  = mysqli_query($koneksi, "SELECT kode_kelas, nama_kelas FROM tbl_kelas ORDER BY nama_kelas");
$result_matkul = mysqli_query($koneksi, "SELECT * FROM tblmatkul ORDER BY semester, nama_mk");

$error = '';
if (isset($_POST['buatkrs'])) {
    $semester     = $_POST['semester'];
    $tahun_ajaran = $_POST['tahun_ajaran'];
    $nidn         = $_POST['nidn'];
    $selected_mk  = $_POST['kode_mk'] ?? [];
    $id_jadwal    = $_POST['id_jadwal'] ?? null;

    if (empty($selected_mk)) {
        $error = "Pilih minimal satu mata kuliah.";
    } else {
        // Cek KRS sudah ada?
        $stmt_check = mysqli_prepare($koneksi, "SELECT * FROM tblkrs WHERE nim = ? AND semester = ? AND tahun_ajaran = ?");
        mysqli_stmt_bind_param($stmt_check, 'sss', $nim, $semester, $tahun_ajaran);
        mysqli_stmt_execute($stmt_check);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_check)) == 0) {
            $stmt_krs = mysqli_prepare($koneksi, "INSERT INTO tblkrs (nim, semester, tahun_ajaran, nidn, id_jadwal) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_krs, 'sssss', $nim, $semester, $tahun_ajaran, $nidn, $id_jadwal);
            mysqli_stmt_execute($stmt_krs);
        }

        $success = 0;
        foreach ($selected_mk as $kode_mk) {
            $stmt_dup = mysqli_prepare($koneksi, "SELECT * FROM tblkrsdetail WHERE nim = ? AND kode_mk = ?");
            mysqli_stmt_bind_param($stmt_dup, 'ss', $nim, $kode_mk);
            mysqli_stmt_execute($stmt_dup);
            if (mysqli_num_rows(mysqli_stmt_get_result($stmt_dup)) > 0) continue;

            $stmt_detail = mysqli_prepare($koneksi, "INSERT INTO tblkrsdetail (nim, kode_mk) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt_detail, 'ss', $nim, $kode_mk);
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
        <label class="form-label">Semester</label>
        <input type="number" name="semester" class="form-control" min="1" max="14" required placeholder="Contoh: 4" />
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
        <select name="id_jadwal" class="form-select">
          <option value="">-- Pilih Kelas --</option>
          <?php while ($k = mysqli_fetch_assoc($result_kelas)): ?>
            <option value="<?= htmlspecialchars($k['kode_kelas']) ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <h5 style="font-weight:700; color:var(--navy); margin:20px 0 12px;">
      <i class="fas fa-book-open" style="color:var(--primary);"></i> Pilih Mata Kuliah
    </h5>

    <div class="table-responsive">
      <table class="table-app">
        <thead>
          <tr>
            <th style="width:60px"><input type="checkbox" id="checkAll"></th>
            <th style="width:110px">Kode MK</th>
            <th>Nama Mata Kuliah</th>
            <th style="width:80px">Semester</th>
            <th style="width:80px">SKS</th>
          </tr>
        </thead>
        <tbody>
          <?php mysqli_data_seek($result_matkul, 0); while ($mk = mysqli_fetch_assoc($result_matkul)): ?>
          <tr>
            <td>
              <input type="checkbox" class="checkMK"
                     value='<?= json_encode(["kode_mk" => $mk["kode_mk"], "nama_mk" => $mk["nama_mk"], "sks" => $mk["sks"]]) ?>'>
            </td>
            <td><span class="badge-app green"><?= htmlspecialchars($mk['kode_mk']) ?></span></td>
            <td><strong><?= htmlspecialchars($mk['nama_mk']) ?></strong></td>
            <td style="text-align:center"><?= htmlspecialchars($mk['semester']) ?></td>
            <td style="text-align:center"><strong><?= htmlspecialchars($mk['sks']) ?></strong></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
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

<script>
  let selectedMK = [];

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

  function hapusMK(kode_mk) {
    selectedMK = selectedMK.filter(mk => mk.kode_mk !== kode_mk);
    const checkbox = document.querySelector(`.checkMK[value*='"${kode_mk}"']`);
    if (checkbox) checkbox.checked = false;
    updateDaftar();
  }

  document.querySelectorAll('.checkMK').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
      const data = JSON.parse(this.value);
      if (this.checked) {
        if (!selectedMK.some(m => m.kode_mk === data.kode_mk)) selectedMK.push(data);
      } else {
        selectedMK = selectedMK.filter(m => m.kode_mk !== data.kode_mk);
      }
      updateDaftar();
    });
  });

  document.getElementById('checkAll').onclick = function () {
    const checkboxes = document.querySelectorAll('.checkMK');
    checkboxes.forEach(cb => {
      cb.checked = this.checked;
      const data = JSON.parse(cb.value);
      if (this.checked) {
        if (!selectedMK.some(m => m.kode_mk === data.kode_mk)) selectedMK.push(data);
      } else {
        selectedMK = [];
      }
    });
    updateDaftar();
  };
</script>

<?php include '../includes/footer.php'; ?>