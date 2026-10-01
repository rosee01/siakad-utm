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
    $selected_mk  = $_POST['kode_mk'] ?? [];
    $semester     = $_POST['semester'] ?? '';
    $tahun_ajaran = $_POST['tahun_ajaran'] ?? '';
    $nidn         = $_POST['nidn'] ?? '';
    $id_jadwal    = $_POST['id_jadwal'] ?? '';

    if (
        !is_string($semester)
        || !ctype_digit($semester)
        || (int) $semester < 1
        || (int) $semester > 8
        || !is_string($tahun_ajaran)
        || !preg_match('/^\\d{4}\\/\\d{4}$/', $tahun_ajaran)
        || (int) substr($tahun_ajaran, 5, 4) !== (int) substr($tahun_ajaran, 0, 4) + 1
        || !is_string($nidn)
        || !is_string($id_jadwal)
        || !ctype_digit($id_jadwal)
        || (int) $id_jadwal < 1
        || !is_array($selected_mk)
        || count($selected_mk) < 1
        || count($selected_mk) > 30
        || count(array_filter(
            $selected_mk,
            static fn ($kode_mk) => !is_string($kode_mk) || $kode_mk === ''
        )) > 0
    ) {
        $error = "Data KRS tidak valid. Periksa semester, tahun ajaran, dosen, jadwal, dan mata kuliah.";
    } elseif (!$nidn) {
        $error = "Pilih dosen wali.";
    } elseif (!$id_jadwal) {
        $error = "Pilih kelas terlebih dahulu.";
    } else {
        $selected_mk = array_values(array_unique($selected_mk));
        $stmt = $koneksi->prepare("SELECT nidn FROM tbldosen WHERE nidn = ? LIMIT 1");
        $stmt->bind_param('s', $nidn);
        $stmt->execute();
        $dosen_valid = $stmt->get_result()->fetch_assoc();

        $stmt = $koneksi->prepare("SELECT id_jadwal, kelas FROM tbljadwalkuliah WHERE id_jadwal = ? LIMIT 1");
        $id_jadwal_int = (int) $id_jadwal;
        $stmt->bind_param('i', $id_jadwal_int);
        $stmt->execute();
        $jadwal = $stmt->get_result()->fetch_assoc();

        if (!$dosen_valid || !$jadwal) {
            $error = "Dosen wali atau jadwal yang dipilih tidak terdaftar.";
        } else {
            $course_valid = true;
            $course_exists = $koneksi->prepare(
                "SELECT kode_mk FROM tblmatkul WHERE kode_mk = ? AND semester = ? LIMIT 1"
            );
            $offering_exists = $koneksi->prepare(
                "SELECT j.id_jadwal
                 FROM tbljadwalkuliah j
                 JOIN tblmatkul m ON m.nama_mk = j.matakuliah
                 WHERE m.kode_mk = ? AND j.kelas = ?
                 LIMIT 1"
            );
            foreach ($selected_mk as $kode_mk) {
                if (!is_string($kode_mk) || $kode_mk === '') {
                    $course_valid = false;
                    break;
                }

                $course_exists->bind_param('ss', $kode_mk, $semester);
                $course_exists->execute();
                if (!$course_exists->get_result()->fetch_assoc()) {
                    $course_valid = false;
                    break;
                }

                $offering_exists->bind_param('ss', $kode_mk, $jadwal['kelas']);
                $offering_exists->execute();
                if (!$offering_exists->get_result()->fetch_assoc()) {
                    $course_valid = false;
                    break;
                }
            }

            if (!$course_valid) {
                $error = "Ada mata kuliah yang tidak tersedia pada kelas yang dipilih.";
            } else {
                try {
                    $koneksi->begin_transaction();
                    $stmt_check = $koneksi->prepare(
                        "SELECT id_krs FROM tblkrs WHERE nim = ? AND semester = ? AND tahun_ajaran = ? LIMIT 1"
                    );
                    $stmt_check->bind_param('sss', $nim, $semester, $tahun_ajaran);
                    $stmt_check->execute();
                    $existing = $stmt_check->get_result()->fetch_assoc();

                    if ($existing) {
                        $id_krs = (int) $existing['id_krs'];
                        $stmt_update = $koneksi->prepare(
                            "UPDATE tblkrs SET nidn = ?, id_jadwal = ? WHERE id_krs = ? AND nim = ?"
                        );
                        $stmt_update->bind_param('siis', $nidn, $id_jadwal_int, $id_krs, $nim);
                        $stmt_update->execute();
                    } else {
                        $stmt_krs = $koneksi->prepare(
                            "INSERT INTO tblkrs (nim, semester, tahun_ajaran, nidn, id_jadwal) VALUES (?, ?, ?, ?, ?)"
                        );
                        $stmt_krs->bind_param('ssssi', $nim, $semester, $tahun_ajaran, $nidn, $id_jadwal_int);
                        $stmt_krs->execute();
                        $id_krs = (int) $koneksi->insert_id;
                    }

                    $stmt_dup = $koneksi->prepare(
                        "SELECT id_krsdetail FROM tblkrsdetail WHERE id_krs = ? AND kode_mk = ? LIMIT 1"
                    );
                    $stmt_detail = $koneksi->prepare(
                        "INSERT INTO tblkrsdetail (id_krs, nim, kode_mk) VALUES (?, ?, ?)"
                    );
                    $success = 0;
                    foreach ($selected_mk as $kode_mk) {
                        $stmt_dup->bind_param('is', $id_krs, $kode_mk);
                        $stmt_dup->execute();
                        if ($stmt_dup->get_result()->fetch_assoc()) {
                            continue;
                        }

                        $stmt_detail->bind_param('iss', $id_krs, $nim, $kode_mk);
                        $stmt_detail->execute();
                        $success++;
                    }
                    $koneksi->commit();

                    if ($success > 0) {
                        header('Location: krs.php?saved=1', true, 303);
                        exit;
                    }
                    $error = "Semua mata kuliah tersebut sudah ada di KRS ini.";
                } catch (mysqli_sql_exception $exception) {
                    $koneksi->rollback();
                    error_log('Gagal menyimpan KRS mahasiswa: ' . $exception->getMessage());
                    $error = "KRS gagal disimpan. Silakan coba lagi.";
                }
            }
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
    <?= csrf_field() ?>
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
  const MATKUL_PER_SEMESTER = <?= json_encode(
      $matkul_per_semester,
      JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
  ) ?>;
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
      const top = document.createElement('div');
      top.className = 'krs-mk-card-top';
      const code = document.createElement('span');
      code.className = 'krs-mk-code';
      code.textContent = mk.kode_mk;
      const credits = document.createElement('span');
      credits.className = 'krs-mk-sks';
      credits.textContent = `${mk.sks} SKS`;
      top.append(code, credits);

      const name = document.createElement('div');
      name.className = 'krs-mk-name';
      name.textContent = mk.nama_mk;

      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'krs-mk-btn';
      button.textContent = isSelected ? 'Terdaftar di KRS' : 'Tambahkan';
      card.append(top, name, button);
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
      const codeCell = document.createElement('td');
      const codeBadge = document.createElement('span');
      codeBadge.className = 'badge-app green';
      codeBadge.textContent = mk.kode_mk;
      codeCell.appendChild(codeBadge);

      const nameCell = document.createElement('td');
      nameCell.textContent = mk.nama_mk;
      const semesterCell = document.createElement('td');
      semesterCell.style.textAlign = 'center';
      semesterCell.textContent = mk.semester;
      const creditsCell = document.createElement('td');
      creditsCell.style.textAlign = 'center';
      const credits = document.createElement('strong');
      credits.textContent = mk.sks;
      creditsCell.appendChild(credits);

      const actionCell = document.createElement('td');
      const removeButton = document.createElement('button');
      removeButton.type = 'button';
      removeButton.className = 'btn-app btn-sm-app danger';
      removeButton.textContent = 'Hapus';
      removeButton.addEventListener('click', () => hapusMK(mk.kode_mk));
      actionCell.appendChild(removeButton);
      tr.append(codeCell, nameCell, semesterCell, creditsCell, actionCell);
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