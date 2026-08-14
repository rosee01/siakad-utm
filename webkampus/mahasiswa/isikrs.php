<?php
session_start();
include "koneksi.php";

// Cek login mahasiswa
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$nim = $_SESSION['nim'] ?? '';
if (empty($nim)) {
    die("NIM tidak ditemukan. Silakan login ulang.");
}

// Ambil data mahasiswa
$query_mhs = "SELECT nim, nama_mhs, semester FROM tblmhs2 WHERE nim = ?";
$stmt = mysqli_prepare($koneksi, $query_mhs);
mysqli_stmt_bind_param($stmt, 's', $nim);
mysqli_stmt_execute($stmt);
$result_mhs = mysqli_stmt_get_result($stmt);
$data_mhs = mysqli_fetch_assoc($result_mhs);

if (!$data_mhs) {
    die("Data mahasiswa tidak ditemukan.");
}

// Ambil data dosen dan kelas
$result_dosen = mysqli_query($koneksi, "SELECT nidn, nama_dosen FROM tbldosen ORDER BY nama_dosen");
$result_kelas = mysqli_query($koneksi, "SELECT kode_kelas, nama_kelas FROM tbl_kelas ORDER BY nama_kelas");
$result_matkul = mysqli_query($koneksi, "SELECT * FROM tblmatkul ORDER BY semester, nama_mk");

// Proses Buat KRS
if (isset($_POST['buatkrs'])) {
    $semester = $_POST['semester'];
    $tahun_ajaran = $_POST['tahun_ajaran'];
    $nidn = $_POST['nidn'];
    $selected_mk = $_POST['kode_mk'] ?? [];
    $id_jadwal = $_POST['id_jadwal'] ?? null;

    if (empty($selected_mk)) {
        $error = "Pilih minimal satu mata kuliah.";
    } else {
        // Cek KRS sudah ada?
        $query_check = "SELECT * FROM tblkrs WHERE nim = ? AND semester = ? AND tahun_ajaran = ?";
        $stmt_check = mysqli_prepare($koneksi, $query_check);
        mysqli_stmt_bind_param($stmt_check, 'sss', $nim, $semester, $tahun_ajaran);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) == 0) {
            $query_insert_krs = "INSERT INTO tblkrs (nim, semester, tahun_ajaran, nidn, id_jadwal) 
                                 VALUES (?, ?, ?, ?, ?)";
            $stmt_krs = mysqli_prepare($koneksi, $query_insert_krs);
            mysqli_stmt_bind_param($stmt_krs, 'sssss', $nim, $semester, $tahun_ajaran, $nidn, $id_jadwal);
            mysqli_stmt_execute($stmt_krs);
        }

        // Simpan ke detail
        $success = 0;
        foreach ($selected_mk as $kode_mk) {
            $query_dup = "SELECT * FROM tblkrsdetail WHERE nim = ? AND kode_mk = ?";
            $stmt_dup = mysqli_prepare($koneksi, $query_dup);
            mysqli_stmt_bind_param($stmt_dup, 'ss', $nim, $kode_mk);
            mysqli_stmt_execute($stmt_dup);
            $result_dup = mysqli_stmt_get_result($stmt_dup);
            if (mysqli_num_rows($result_dup) > 0) continue;

            $query_detail = "INSERT INTO tblkrsdetail (nim, kode_mk) VALUES (?, ?)";
            $stmt_detail = mysqli_prepare($koneksi, $query_detail);
            mysqli_stmt_bind_param($stmt_detail, 'ss', $nim, $kode_mk);
            if (mysqli_stmt_execute($stmt_detail)) {
                $success++;
            }
        }

        if ($success > 0) {
            echo "<script>alert('$success mata kuliah berhasil ditambahkan ke KRS!'); window.location='krs.php';</script>";
            exit;
        } else {
            $error = "Semua mata kuliah sudah pernah diambil atau gagal disimpan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Isi KRS Mahasiswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #6a11cb, #2575fc);
      font-family: 'Segoe UI', sans-serif;
      padding: 20px;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .card h3 {
      background: linear-gradient(90deg, #2575fc, #6a11cb);
      color: white;
      padding: 15px;
      border-top-left-radius: 15px;
      border-top-right-radius: 15px;
    }
    .menu-bar a {
      margin: 5px;
      padding: 10px 20px;
      background: #fff;
      border-radius: 10px;
      color: #333;
      text-decoration: none;
      font-weight: bold;
    }
    .menu-bar a.active {
      background: #2575fc;
      color: white;
    }
    .btn-success {
      background: linear-gradient(90deg, #43e97b, #38f9d7);
      border: none;
    }
    .selected-list {
      margin-top: 20px;
      border: 2px dashed #fff;
      padding: 15px;
      border-radius: 10px;
      background-color: #f8f9ff;
    }
    .total-sks {
      font-weight: bold;
      color: #6a11cb;
      font-size: 1.2em;
      text-align: right;
    }
  </style>
</head>
<body>


<div class="card">
  <h3 class="text-center">Form Isi KRS Mahasiswa</h3>
  <div class="card-body">
    <form action="" method="POST" id="formKRS">
      <table class="table">
        <tr><td><strong>NIM</strong></td><td><?= htmlspecialchars($data_mhs['nim']); ?></td></tr>
        <tr><td><strong>Nama Mahasiswa</strong></td><td><?= htmlspecialchars($data_mhs['nama_mhs']); ?></td></tr>
        <tr><td><strong>Semester</strong></td><td><input type="text" name="semester" class="form-control" required></td></tr>
        <tr><td><strong>Tahun Ajaran</strong></td><td><input type="text" name="tahun_ajaran" class="form-control" required></td></tr>
        <tr>
          <td><strong>Dosen Wali</strong></td>
          <td>
            <select name="nidn" class="form-control" required>
              <option value="">-- Pilih Dosen --</option>
              <?php while ($d = mysqli_fetch_assoc($result_dosen)): ?>
                <option value="<?= $d['nidn']; ?>"><?= htmlspecialchars($d['nama_dosen']); ?></option>
              <?php endwhile; ?>
            </select>
          </td>
        </tr>
        <tr>
          <td><strong>Kelas</strong></td>
          <td>
            <select name="id_jadwal" class="form-control">
              <option value="">-- Pilih Kelas --</option>
              <?php while ($k = mysqli_fetch_assoc($result_kelas)): ?>
                <option value="<?= $k['kode_kelas']; ?>"><?= htmlspecialchars($k['nama_kelas']); ?></option>
              <?php endwhile; ?>
            </select>
          </td>
        </tr>
      </table>

      <h5 class="mt-4">Pilih Mata Kuliah</h5>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th><input type="checkbox" id="checkAll"> Pilih</th>
            <th>Kode MK</th>
            <th>Nama MK</th>
            <th>SKS</th>
          </tr>
        </thead>
        <tbody>
          <?php mysqli_data_seek($result_matkul, 0); while ($mk = mysqli_fetch_assoc($result_matkul)): ?>
          <tr>
            <td><input type="checkbox" class="checkMK" value='<?= json_encode(["kode_mk" => $mk["kode_mk"], "nama_mk" => $mk["nama_mk"], "sks" => $mk["sks"]]) ?>'></td>
            <td><?= htmlspecialchars($mk['kode_mk']); ?></td>
            <td><?= htmlspecialchars($mk['nama_mk']); ?></td>
            <td><?= htmlspecialchars($mk['sks']); ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <div class="selected-list" id="daftarPilihan">
        <h6>Mata Kuliah yang Dipilih:</h6>
        <table class="table table-sm" id="tabelPilihan">
          <thead><tr><th>Kode MK</th><th>Nama MK</th><th>SKS</th><th>Aksi</th></tr></thead>
          <tbody></tbody>
        </table>
        <div class="total-sks">Total SKS: <span id="totalSKS">0</span></div>
      </div>

      <!-- Hidden input untuk kode_mk[] -->
      <div id="kodeMKInputs"></div>

      <button type="submit" name="buatkrs" class="btn btn-success mt-3">
        <i class="fas fa-save"></i> Simpan KRS
      </button>

      <?php if (isset($error)): ?>
        <div class="alert alert-danger mt-3"><?= $error ?></div>
      <?php endif; ?>
    </form>
  </div>
</div>

<script>
  let selectedMK = [];
  let totalSKS = 0;

  function updateDaftar() {
    const tbody = document.querySelector('#tabelPilihan tbody');
    const kodeMKInputs = document.getElementById('kodeMKInputs');
    tbody.innerHTML = '';
    kodeMKInputs.innerHTML = '';
    totalSKS = 0;

    selectedMK.forEach(mk => {
      totalSKS += parseInt(mk.sks);
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${mk.kode_mk}</td><td>${mk.nama_mk}</td><td>${mk.sks}</td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusMK('${mk.kode_mk}')">Hapus</button></td>`;
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
    checkbox.addEventListener('change', function() {
      const data = JSON.parse(this.value);
      if (this.checked) {
        if (!selectedMK.some(m => m.kode_mk === data.kode_mk)) {
          selectedMK.push(data);
        }
      } else {
        selectedMK = selectedMK.filter(m => m.kode_mk !== data.kode_mk);
      }
      updateDaftar();
    });
  });

  document.getElementById('checkAll').onclick = function() {
    const checkboxes = document.querySelectorAll('.checkMK');
    checkboxes.forEach(cb => {
      cb.checked = this.checked;
      const data = JSON.parse(cb.value);
      if (this.checked) {
        if (!selectedMK.some(m => m.kode_mk === data.kode_mk)) {
          selectedMK.push(data);
        }
      } else {
        selectedMK = [];
      }
    });
    updateDaftar();
  };
</script>

</body>
</html>
