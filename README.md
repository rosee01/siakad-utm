# 🎓 SIAKAD — Sistem Informasi Akademik

A web-based Academic Information System for universities, built with **native PHP + MySQL**.
Manages students, lecturers, study programs, courses, schedules, KRS/KHS, grades,
and role-based portals for **Admin**, **Lecturer**, and **Student** — including
print-ready official academic documents (KHS & KRS).

---

## 📸 Screenshots

Screenshots will be added after all visible personal and academic records have been replaced with fabricated, anonymized examples.

The login page uses an AI-generated campus illustration, not a documentary photograph of the university.

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
3. Create a local database named `db_kampus` and prepare its schema and synthetic demo records locally.
4. Configure local database credentials in `webkampus/koneksi.php`.
5. Open: `http://localhost/portofolio/siakad-portofolio1/webkampus/login.php`

> A database dump is intentionally not included in this repository because it contained student-like personal and academic records. The application will not run until you create a local database using fabricated test data. Never use production personal data in a public repository.

---

## 🔑 Demo Accounts

No demo accounts or passwords are published. Create local test accounts with fabricated data before signing in; do not reuse production credentials.

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

- CSRF tokens protect POST forms; logout and destructive actions require POST
- Prepared statements on authentication and CRUD mutations
- `htmlspecialchars()` output escaping across all pages
- Students can only view/edit **their own** data (server-side NIM validation); profile edits cannot change program or semester
- KRS selections and administrator grade updates are validated against enrollment and course data on the server
- Lecturer grade entry is restricted to the lecturer's own schedules and enrolled students
- The legacy `page` parameter is allowlisted and HTML-attribute escaped
- bcrypt password hashing with automatic legacy upgrade
- This educational project is not ready for production use with real academic data; authorization rules, input validation, password migration, and database constraints still need a comprehensive production review

---

## 🚧 Future Improvements

- Database normalization (separate schedule data from `tblmatkul`)
- Add database foreign keys and uniqueness constraints for academic records
- Pagination for large tables
- Migration to Laravel (MVC)

---

**Developed by Rosee** — Universitas Teknologi Mataram