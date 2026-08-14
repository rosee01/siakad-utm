# 🎓 SIAKAD — Sistem Informasi Akademik

A web-based Academic Information System for universities, built with **native PHP + MySQL**.
Manages students, lecturers, study programs, courses, schedules, KRS/KHS, grades,
and role-based portals for **Admin**, **Lecturer**, and **Student** — including
print-ready official academic documents (KHS & KRS).

---

## 📸 Screenshots

| Login | Admin Dashboard |
|:---:|:---:|
| ![Login](screenshots/login.png) | ![Admin](screenshots/admin-dashboard.png) |

| Dosen Dashboard | Cetak KHS (1 halaman) |
|:---:|:---:|
| ![Dosen](screenshots/dosen-dashboard.png) | ![KHS](screenshots/cetak-khs.png) |

> *(Tambahkan folder `screenshots/` lalu isi dengan gambar sesuai nama di atas)*

---

## ✨ Features

### 🔐 Authentication & Authorization
- Multi-role login: **Admin, Lecturer (Dosen), Student (Mahasiswa)**
- Passwords secured with **bcrypt** (`password_hash` / `password_verify`)
- **Transparent legacy upgrade** — old plaintext/md5 passwords are automatically re-hashed on first login
- Session regeneration on login (anti session-fixation)
- Role-based access control enforced on every page

### 🛠 Admin
- Full CRUD: Mahasiswa, Dosen, Prodi, Mata Kuliah, Ruangan, Kelas, Jadwal, KRS, KRS Detail, User
- KHS management with grade conversion (A–E) and automatic GPA (IP) calculation
- Real-time dashboard statistics from the database

### 👨🏫 Lecturer (Dosen)
- Profile & teaching schedule
- Grade entry with **live grade-letter preview**
- Print student KHS as an official 1-page A4 document

### 👩‍🎓 Student (Mahasiswa)
- Profile, KRS planning (with live total-credit counter), KRS detail
- Print-ready official KRS document

### 🖨 Document Printing
- KHS & KRS render as official university documents (letterhead, signature block)
- Print CSS guarantees **exactly 1 A4 page**

---

## 🛠 Tech Stack

- **Backend:** PHP 8 (procedural, native)
- **Database:** MySQL (XAMPP)
- **Frontend:** Bootstrap 5, Font Awesome 6, custom CSS design system (`app.css`, `dosen.css`, `mahasiswa.css`)
- **JS:** Vanilla JavaScript

---

## 📦 Installation

1. Install [XAMPP](https://www.apachefriends.org/) and start Apache + MySQL.
2. Copy this project into `htdocs/portofolio/siakad-portofolio1/`.
3. Import `db_kampus.sql` via phpMyAdmin.
4. Check database credentials in `webkampus/koneksi.php` if needed.
5. Open: `http://localhost/portofolio/siakad-portofolio1/webkampus/login.php`

---

## 🔑 Demo Accounts

| Role      | Username | Password |
|-----------|----------|----------|
| Admin     | `admin`     | *(isi sendiri)* |
| Dosen     | *(isi)*     | *(isi)* |
| Mahasiswa | `23TI061`   | *(isi)* |

---

## 🗄 Database Schema

| Table | Description |
|---|---|
| `user` | Authentication & roles (admin/dosen/mahasiswa) |
| `tblmhs2` | Students |
| `tbldosen` | Lecturers |
| `tblprodi` | Study programs |
| `tblmatkul` | Courses |
| `tbljadwalkuliah` | Teaching schedules |
| `tbl_kelas` / `tbl_ruangan` | Classes & rooms |
| `tblkrs` → `tblkrsdetail` | Study plan (header → chosen courses) |
| `tblnilai` | Grades (linked to schedule) |

---

## 📁 Project Structure

```
webkampus/
├── admiin/        # Admin portal (CRUD modules)
├── dosen/         # Lecturer portal
├── mahasiswa/     # Student portal
├── includes/      # Shared components (header, sidebar, topbar, footer)
├── koneksi.php    # DB connection
└── login.php      # Authentication
css/
├── app.css        # Global design system
├── dosen.css      # Lecturer components
└── mahasiswa.css  # Student components
```

---

## 🔐 Security Notes

- Prepared statements on authentication & critical flows
- `htmlspecialchars()` output escaping across all pages
- Students can only view/edit **their own** data (server-side NIM validation)
- bcrypt password hashing with automatic legacy upgrade

---

## 🚧 Future Improvements

- CSRF token protection on all forms
- Full prepared-statement coverage on every module
- Database normalization (separate schedule data from `tblmatkul`)
- Pagination for large tables
- Migration to Laravel (MVC)

---

**Developed by Rosee** — Universitas Teknologi Mataram