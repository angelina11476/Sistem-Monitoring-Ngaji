# AGENT.md - MANDATORY RULES FOR ALL AI MODELS

> **Setiap AI model yang bekerja pada proyek ini WAJIB mematuhi seluruh aturan di bawah ini.**
> **Tidak ada pengecualian. Tidak ada shortcut.**

---

## 1. ATURAN UTAMA: DEVELOPMENT WORKFLOW (WAJIB)

```
┌─────────────────────────────────────────────────────────────────┐
│                    DEVELOPMENT WORKFLOW CYCLE                    │
│                                                                 │
│  STEP 1: Kode → Localhost                                       │
│  STEP 2: Test di Localhost (manual + automated)                 │
│  STEP 3: Jika LOLOS → Commit & Push ke GitHub                   │
│  STEP 4: GitHub Actions CI/CD otomatis jalankan test            │
│  STEP 5: Jika GAGAL → Analisa → Fix → Ulang dari STEP 2        │
│  STEP 6: Jika LOLOS → Merge/Deploy                              │
│  STEP 7: Dokumentasikan hasil di audit log                      │
└─────────────────────────────────────────────────────────────────┘
```

### DETAL TIAP STEP:

#### STEP 1: Update Kode di Localhost
- Semua perubahan kode DILAKUKAN di environment localhost terlebih dahulu
- Gunakan `php artisan serve` / Laragon / XAMPP untuk menjalankan lokal
- **JANGAN PERNAH** langsung edit di production/server

#### STEP 2: Test di Localhost
- Jalankan minimal 3 jenis test:
  - **Manual test**: Buka setiap halaman yang terpengaruh, klik setiap tombol, submit setiap form
  - **Functional test**: Pastikan setiap fitur berfungsi sesuai flow
  - **Security test**: Cek input sanitization, session handling, CSRF protection
- Test semua role user (Ustadz, Admin jika ada)
- Test di mobile view (responsive)
- **JANGAN LANJUT** jika ada 1 test gagal sekalipun

#### STEP 3: Push ke GitHub
- Hanya boleh push jika SEMUA test di STEP 2 lolos
- Gunakan commit message yang jelas:
  ```
  type(scope): deskripsi singkat

  - detail perubahan 1
  - detail perubahan 2
  ```
- Type: `feat`, `fix`, `security`, `refactor`, `docs`, `test`
- **JANGAN** push secrets, credentials, API keys, atau .env files

#### STEP 4: GitHub Actions CI/CD (OTOMATIS)
- `.github/workflows/ci.yml` WAJIB ada dan aktif
- Pipeline WAJIB menjalankan SEMUA ini:
  - ✅ PHP syntax check (`php -l`) — semua file .php
  - ✅ PHP CodeSniffer (psr-12 standard)
  - ✅ Unit tests (PHPUnit / simple test script)
  - ✅ Integration tests (form submission, database operations)
  - ✅ Security scan (basic: cek debug files, cek plaintext password)
  - ✅ Responsive check (jika ada tool otomatis)
- **CI/CD EKSEKUSI OTOMATIS** setiap kali push ke GitHub
- **WAJIB tunggu sampai pipeline selesai** — jangan merge sebelum hasil keluar
- **GAGAL = TIDAK BOLEH MERGE. TIDAK ADA PENGECUALIAN.**
- Notifikasi kegagalan WAJIB dibaca dan ditindaklanjuti sebelum langkah berikutnya

#### STEP 5: Analisa Gagal & Retest Loop (WAJIB DIULANG SAMPAI PASS)

```
┌──────────────────────────────────────────────────────────────┐
│                    RETEST LOOP PROTOCOL                       │
│                                                              │
│  CI/CD FAIL → Baca log error detail-detail                   │
│       ↓                                                      │
│  Identifikasi ROOT CAUSE (bukan symptom)                     │
│       ↓                                                      │
│  Fix root cause di localhost                                  │
│       ↓                                                      │
│  Test ulang di localhost (STEP 2) — ALL TESTS MUST PASS      │
│       ↓                                                      │
│  Commit & Push ulang (STEP 3)                                 │
│       ↓                                                      │
│  CI/CD otomatis jalan lagi (STEP 4) — tunggu hasilnya         │
│       ↓                                                      │
│  Jika PASS → lanjut STEP 6                                   │
│       ↓                                                      │
│  Jika FAIL lagi → ulangi dari awal (baca log, fix, test...)  │
│                                                              │
│  ⚠️ TIDAK ADA BATAS MAKSIMUM RETRY — HARUS PASS            │
│  ⚠️ JANGAN PERNAH FORCE PUSH BYPASS TEST                     │
│  ⚠️ JANGAN PERNAH SKIP TEST LOKAL "karena cuma kecil"        │
└──────────────────────────────────────────────────────────────┘
```

### Detail Retest Protocol:

**a. Baca Log Error dari GitHub Actions DETAIL-DETAIL:**
- Buka tab Actions di repository GitHub
- Klik workflow run yang gagal
- Expand step yang error — baca pesan error SELURUHNYA
- Screenshot/copy log untuk referensi

**b. Identifikasi Root Cause:**
- ❌ BUKAN: "Error di line 50" (ini symptom)
- ✅ BENAR: "Fungsi X tidak didefinisikan karena file include tidak ada"
- ✅ BENAR: "SQL query gagal karena tabel Y belum di-create"
- ✅ BENAR: "Session variable undefined karena login flow berubah"

**c. Fix Root Cause (BUKAN Workaround):**
- ❌ DILARANG: "Tambah @ suppress error"
- ❌ DILARANG: "Wrap pake try-catch tanpa logic"
- ✅ WAJIB: Perbaiki kode yang benar-benar salah
- ✅ WAJIB: Tambah pengecekan yang seharusnya ada

**d. Retest di Localhost (WAJIB):**
- Jalankan ulang SEMUA test dari STEP 2 checklist
- Jangan hanya test bagian yang di-fix — test SEMUA
- Pastikan fix tidak merusak fitur lain

**e. Commit & Push Ulang:**
- Commit message wajib sebutkan root cause:
  ```
  fix(scope): perbaiki [root cause singkat]
  
  - Root cause: [penjelasan detail]
  - Fix: [apa yang diubah]
  - Test: [test apa yang dijalankan]
  ```
- Push ulang dan pantau CI/CD pipeline

**f. Repeat Until PASS:**
- Tidak ada batas maksimal retry
- Setiap iterasi harus LEBIH BAIK dari sebelumnya
- Jika gagal 3x berturut-turut → evaluasi pendekatan, jangan terus paksa
- **JANGAN** force push untuk bypass test — ini aturan TANPA KOMPROMI

#### STEP 6: Merge/Deploy
- Hanya merge ke main branch jika CI/CD pipeline PASS
- Deploy ke production HANYA setelah merge
- Gunakan environment variables untuk production config

#### STEP 7: Dokumentasi
- Catat di commit message apa yang berubah dan kenapa
- Update README jika ada perubahan fitur
- Log di audit trail jika ada perubahan security-related

---

## 2. ATURAN KEAMANAN (SECURITY RULES)

### 2.1 Password Management
```php
// ❌ DILARANG - Plaintext password
$password = $_POST['password'];

// ✅ WAJIB - Hashed password
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
if (password_verify($_POST['password'], $hashed_password)) { ... }
```

### 2.2 SQL Injection Prevention
```php
// ❌ DILARANG - String concatenation
$db->query("SELECT * FROM users WHERE id = " . $_GET['id']);

// ✅ WAJIB - Prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindValue(':id', $_GET['id'], SQLITE3_INTEGER);
$result = $stmt->execute();
```

### 2.3 XSS Prevention
```php
// ❌ DILARANG - Raw output
echo $_GET['nama'];

// ✅ WAJIB - Escaped output
echo htmlspecialchars($_GET['nama'], ENT_QUOTES, 'UTF-8');
```

### 2.4 CSRF Protection
```php
// ❌ DILARANG - Forms tanpa token
<form method="POST">

// ✅ WAJIB - Forms dengan CSRF token
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// ... di form:
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'">';
// ... di handler:
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('CSRF token mismatch');
}
```

### 2.5 Session Security
```php
// ✅ WAJIB setelah login berhasil:
session_regenerate_id(true);

// ✅ WAJIB di setiap page load:
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}
```

### 2.6 File Security
- **HAPUS** `cek_login` (password dump script)
- **HAPUS** `database/info.php` (phpinfo exposure)
- **HAPUS** atau restrict `info.php` (debug script)
- **Tambahkan** `.htaccess` untuk block akses ke `database/*.db`

### 2.7 Credentials
- **JANGAN PERNAH** tampilkan password di halaman manapun
- **JANGAN PERNAH** commit credentials ke repository
- Gunakan environment variables untuk config sensitif

---

## 3. ATURAN DATABASE

### 3.1 Schema Management
- **SATU file saja** untuk schema: `database/schema.sql`
- **TIDAK BOLEK** create table di multiple files (`index.php`, `login.php`, `riwayat.php`)
- Gunakan migration file untuk perubahan schema:
  ```
  database/migrations/001_create_ustadz.sql
  database/migrations/002_create_anak.sql
  database/migrations/003_add_ustadz_id_to_progres.sql
  ```

### 3.2 Foreign Keys
```sql
-- ✅ WAJIB - Semua relasi harus punya FK
CREATE TABLE progres (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    anak_id INTEGER NOT NULL,
    ustadz_id INTEGER NOT NULL,
    FOREIGN KEY (anak_id) REFERENCES anak(id) ON DELETE CASCADE,
    FOREIGN KEY (ustadz_id) REFERENCES ustadz(id)
);
```

### 3.3 Constraints
```sql
-- ✅ WAJIB - UNIQUE constraint untuk username
ALTER TABLE ustadz ADD CONSTRAINT uq_username UNIQUE (username);

-- ✅ WAJIB - Composite UNIQUE untuk presensi
CREATE UNIQUE INDEX idx_presensi_unique 
    ON presensi(anak_id, tanggal);
```

### 3.4 Single Source of Truth
- Schema definition: `database/schema.sql` SAJA
- Default data: `database/seed.sql` SAJA
- Tidak ada `CREATE TABLE IF NOT EXISTS` di PHP files
- Tidak ada schema logic yang tersembunyi di application code

---

## 4. ATURAN CODE QUALITY

### 4.1 File Structure
```
ngaji/
├── AGENT.md                    # File ini
├── AGENT.py                    # AI Agent helper
├── .github/
│   └── workflows/
│       └── ci.yml              # CI/CD pipeline
├── database/
│   ├── ngaji.db                # SQLite database
│   ├── schema.sql              # Single source of truth schema
│   ├── seed.sql                # Default data
│   └── migrations/             # Schema versioning
├── includes/
│   ├── config.php              # Database config, constants
│   ├── auth.php                # Authentication helpers
│   ├── functions.php           # Shared functions
│   ├── header.html             # Shared header
│   └── footer.html             # Shared footer
├── assets/
│   ├── css/
│   │   └── style.css           # SHARED stylesheet (TIDAK inline)
│   └── js/
│       └── app.js              # SHARED JavaScript
├── login.php
├── logout.php
├── index.php                   # Dashboard
├── presensi.php
├── edit.php
├── riwayat.php
├── profil_santri.php
├── pengaturan.php
├── share_wa.php
├── export_excel.php
├── export_presensi.php
└── tests/
    ├── unit/
    └── integration/
```

### 4.2 Shared Code (DRY Principle)
```php
// ❌ DILARANG - CSS inline di setiap file
<style>
    body { font-family: Arial; }
    .sidebar { background: #1e5a38; }
</style>

// ✅ WAJIB - External stylesheet
<link rel="stylesheet" href="assets/css/style.css">
```

```php
// ❌ DILARANG - PHP logic yang diulang di setiap file
$db = new SQLite3('database/ngaji.db');
if (!isset($_SESSION['login'])) { header('Location: login.php'); exit; }

// ✅ WAJIB - Shared include
require_once 'includes/config.php';
require_once 'includes/auth.php';
```

### 4.3 Navigation Consistency
- **SEMUA halaman** harus punya sidebar yang SAMA
- **SEMUA halaman** harus punya header yang SAMA
- **SEMUA halaman** harus punya footer yang SAMA
- Navigation state (active menu) harus konsisten

### 4.4 Responsive Design
- **Satu breakpoint**: `768px` untuk semua halaman
- **Satu container max-width**: `1200px`
- **Satu font stack**: `'Segoe UI', Arial, sans-serif` (jangan pakai Poppins jika tidak di-load)

### 4.5 Form Handling Pattern
```php
// ✅ WAJIB - Standard form handling
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }
    
    // 2. Validate required fields
    $errors = [];
    if (empty($_POST['nama'])) $errors[] = 'Nama wajib diisi';
    if (empty($_POST['email'])) $errors[] = 'Email wajib diisi';
    
    // 3. Sanitize input
    $nama = htmlspecialchars($_POST['nama'], ENT_QUOTES, 'UTF-8');
    
    // 4. Process if no errors
    if (empty($errors)) {
        // ... database operation
        $_SESSION['success'] = 'Data berhasil disimpan!';
    }
}
```

---

## 5. ATURAN USER ROLES & PERMISSIONS

### 5.1 Role Hierarchy (WAJIB diimplementasi)
```
┌─────────────────────────────────────────┐
│           ROLE HIERARCHY                │
├─────────────────────────────────────────┤
│ Admin                                    │
│   - Full access                          │
│   - Kelola Ustadz (CRUD)                 │
│   - Kelola Santri (CRUD)                 │
│   - View all reports                     │
│   - System settings                      │
├─────────────────────────────────────────┤
│ Ustadz/Ustadzah                          │
│   - View Dashboard                       │
│   - Kelola Santri assigned              │
│   - Input/Edit Progres own students     │
│   - Presensi own students               │
│   - Share via WhatsApp                  │
├─────────────────────────────────────────┤
│ Parent (calon fitur)                     │
│   - View own child progress             │
│   - View attendance                      │
│   - Receive WhatsApp notifications     │
└─────────────────────────────────────────┘
```

### 5.2 Permission Matrix
| Fitur | Admin | Ustadz | Parent |
|-------|-------|--------|--------|
| Login | ✅ | ✅ | ✅ |
| Dashboard (full) | ✅ | Partial | ❌ |
| Kelola Ustadz | ✅ CRUD | ❌ | ❌ |
| Tambah Santri | ✅ | ✅ | ❌ |
| Edit Santri Profile | ✅ All | ✅ Own | ❌ |
| Hapus Santri | ✅ | ❌ | ❌ |
| Input Progres | ✅ | ✅ | ❌ |
| Edit Progres | ✅ All | ✅ Own | ❌ |
| Hapus Progres | ✅ | ❌ | ❌ |
| Presensi | ✅ | ✅ | ❌ |
| Export Excel | ✅ | ✅ | ❌ |
| Share WA | ✅ | ✅ | ❌ |
| Pengaturan | ✅ Full | ✅ Own profile | ❌ |
| View Laporan Anak | ✅ | ✅ | ✅ |

### 5.3 Authorization Checks
```php
// ✅ WAJIB - Authorization check di setiap aksi
function checkPermission($action, $resource_owner_id = null) {
    if (!isset($_SESSION['login'])) {
        header('Location: login.php');
        exit;
    }
    
    $role = $_SESSION['role'];
    
    if ($role == 'admin') return true; // Admin boleh semua
    
    if ($role == 'ustadz') {
        // Ustadz hanya boleh akses data sendiri
        if ($resource_owner_id && $resource_owner_id != $_SESSION['ustadz_id']) {
            die('Unauthorized: Anda tidak memiliki akses ke data ini');
        }
        return true;
    }
    
    return false; // Default deny
}
```

---

## 6. ATURAN UI/UX CONSISTENCY

### 6.1 Shared Components (WAJIB)
- **Sidebar**: Sama di semua halaman, dengan menu aktif yang highlighted
- **Header**: Logo, nama aplikasi, info user, tombol logout
- **Footer**: Copyright, versi aplikasi
- **Success/Error messages**: Sama styling di semua halaman
- **Cards**: Sama border-radius, shadow, padding
- **Buttons**: Sama warna, ukuran, hover effect
- **Tables**: Sama styling header, row hover, responsive
- **Forms**: Sama input styling, focus state, error state

### 6.2 Color Palette (WAJIB konsisten)
```css
:root {
    --primary: #1e5a38;      /* Dark green - sidebar, headers */
    --primary-light: #2c7a4d; /* Medium green - buttons, accents */
    --primary-lighter: #f0f7f0; /* Light green - backgrounds */
    --danger: #dc3545;        /* Red - logout, delete, error */
    --warning: #ffc107;       /* Yellow - edit, caution */
    --success: #28a745;       /* Green - success messages */
    --info: #17a2b8;          /* Blue - info, links */
    --text: #333333;          /* Dark text */
    --text-muted: #666666;    /* Muted text */
    --border: #e0e0e0;        /* Border color */
    --bg: #f0f7f0;            /* Page background */
    --white: #ffffff;         /* Card background */
}
```

### 6.3 Typography
```css
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 14px;
    line-height: 1.5;
    color: var(--text);
}

h1 { font-size: 22px; }
h2 { font-size: 20px; }
h3 { font-size: 18px; }
```

### 6.4 Spacing
```css
:root {
    --spacing-xs: 4px;
    --spacing-sm: 8px;
    --spacing-md: 12px;
    --spacing-lg: 16px;
    --spacing-xl: 24px;
    --spacing-xxl: 32px;
}
```

---

## 7. ATURAN TESTING

### 7.1 Test Checklist (WAJIB sebelum push)
```
□ SEMUA halaman bisa diakses tanpa error
□ Login berhasil dengan kredensial valid
□ Login GAGAL dengan kredensial invalid (tidak error)
□ Logout berfungsi dan session destroyed
□ Dashboard menampilkan data yang benar
□ Tambah santri berhasil
□ Edit santri berhasil
□ Input progres berhasil
□ Edit progres berhasil
□ Presensi harian bisa diisi
□ Presensi bisa di-update untuk tanggal yang sama
□ Export Excel berfungsi
□ Share WhatsApp berfungsi
□ Form tambah ustadz berfungsi (jika admin)
□ Semua form punya CSRF token
□ Responsive: menu sidebar collapse di mobile
□ Tidak ada error di browser console
□ Tidak ada warning di PHP error log
```

### 7.2 Security Test Checklist
```
□ Password di-hash di database (bukan plaintext)
□ Prepared statements digunakan di SEMUA query
□ htmlspecialchars() digunakan di SEMUA output user
□ CSRF token ada di SEMUA form
□ Session regenerate setelah login
□ Tidak ada credentials di halaman manapun
□ database/*.db tidak bisa diakses langsung via web
□ File debug (cek_login, info.php, database/info.php) dihapus/restricted
```

---

## 8. ATURAN DOCUMENTATION

### 8.1 Code Documentation
```php
/**
 * Fungsi untuk input progres ngaji santri
 * 
 * @param int $anak_id ID santri
 * @param string $tanggal Tanggal bacaan (YYYY-MM-DD)
 * @param int $juz Nomor juz (0-30)
 * @param string $surah Nama surah
 * @param int $ayat Nomor ayat (0 jika tidak ada)
 * @param int $halaman Nomor halaman (0 jika tidak ada)
 * @param int $kelancaran Skor kelancaran (1-5)
 * @param string $catatan Catatan guru
 * @param int $durasi Durasi dalam menit
 * @return bool True jika berhasil
 */
function simpanProgres($anak_id, $tanggal, $juz, $surah, ...) {
    // ...
}
```

### 8.2 Commit Message Format
```
type(scope): deskripsi singkat dalam Bahasa Indonesia

- detail perubahan 1
- detail perubahan 2

Refs: #issue-number (jika ada)
```

**Type**: feat | fix | security | refactor | docs | test | chore

**Scope**: auth | santri | progres | presensi | export | ui | db | api

**Contoh**:
```
feat(progres): tambah fitur edit progres ngaji

- Tambah form edit di edit.php
- Handle POST update ke database progres
- Redirect ke riwayat.php setelah berhasil
- Tambah CSRF token di form edit
```

---

## 9. ATURAN UNTUK AI MODEL

### 9.1 Sebelum Memulai
1. **Baca file ini** dari awal sampai akhir
2. **Pahami konteks** aplikasi: Monitoring Ngaji untuk pesantren
3. **Identifikasi role** yang sedang dikerjakan
4. **Cari file terkait** yang mungkin terpengaruh
5. **Pahami flow data** dari input sampai output

### 9.2 Saat Bekerja
1. **Ikuti struktur file** yang ada di Section 4.1
2. **Gunakan shared includes** untuk common code
3. **Tambahkan CSRF token** di setiap form baru
4. **Gunakan prepared statements** untuk semua query
5. **Escape output** dengan htmlspecialchars()
6. **Ikuti color palette** dari Section 6.2
7. **Ikuti typography** dari Section 6.3
8. **Tambahkan authorization check** di setiap aksi

### 9.3 Setelah Selesai
1. **Jalankan test checklist** dari Section 7.1
2. **Jalankan security test** dari Section 7.2
3. **Commit dengan format** dari Section 8.2
4. **JANGAN push** jika ada test gagal
5. **Dokumentasikan** perubahan yang dilakukan

### 9.4 Yang DILARANG
- ❌ Menambahkan CSS inline di file PHP (gunakan shared stylesheet)
- ❌ Mengulang kode yang sudah ada (gunakan include/function)
- ❌ Menyimpan password dalam plaintext
- ❌ Menggunakan string concatenation untuk SQL
- ❌ Mengabaikan CSRF protection
- ❌ Push credentials/secrets ke repository
- ❌ Menghapus file test tanpa alasan
- ❌ Force push ke branch protected
- ❌ Merge tanpa CI/CD pass

### 9.5 Yang WAJIB Dilakukan
- ✅ Selalu test sebelum push
- ✅ Gunakan prepared statements untuk semua query
- ✅ Escape semua output dengan htmlspecialchars()
- ✅ Tambahkan CSRF token di semua form
- ✅ Hash password dengan password_hash()
- ✅ Regenerate session ID setelah login
- ✅ Hapus/restrict file debug (cek_login, info.php)
- ✅ Update schema di satu file saja (schema.sql)
- ✅ Ikuti naming convention yang sudah ada
- ✅ Dokumentasikan semua perubahan

---

## 10. AUDIT TRAIL

Setiap perubahan yang signifikan harus didokumentasikan:

```
TANGGAL: 2026-07-04
OLEH: AI Model (nama model)
PERUBAHAN: Deskripsi singkat
FILE YANG BERUBAH:
  - path/to/file1.php (apa yang berubah)
  - path/to/file2.php (apa yang berubah)
TEST YANG DIJALANKAN:
  - Manual test: [PASS/FAIL]
  - Functional test: [PASS/FAIL]
  - Security test: [PASS/FAIL]
CI/CD STATUS: [PASS/FAIL]
KETERANGAN: Catatan tambahan jika ada
```

---

## 11. CURRENT APPLICATION ALIGNMENT STATUS (ANALISIS 2026-07-04)

> **Bagian ini berisi hasil analisis menyeluruh terhadap aplikasi "Monitoring Ngaji Ba'da Maghrib"**
> **Semua AI model WAJIB membaca bagian ini sebelum memulai perubahan apapun.**
> **Gunakan sebagai baseline untuk mengukur progress perbaikan.**

---

### 11.1 BACKEND-FRONTEND ALIGNMENT — STATUS: **❌ TIDAK SELARAS**

| Aspek | Temuan | File Terkait |
|-------|--------|-------------|
| Database connection | Diulang 11x di file berbeda (`$db = new SQLite3(...)`) | Semua file PHP |
| Auth check | 4 baris boilerplate diulang di 7 file | index, presensi, riwayat, edit, profil_santri, share_wa, pengaturan |
| CSS approach | 100% inline `<style>` — 0 CSS file eksternal | Semua UI file (~800 baris duplikasi) |
| JavaScript | 0 JS file — inline `<script>` di 3 dari 7 UI file | index, profil_santri |
| HTML structure | Tidak ada include — setiap file buat header sendiri | Semua file |
| Form handling | Pola tidak konsisten — validasi, sanitasi, CSRF berbeda-beda | Semua file dengan form |

### 11.2 DATABASE-BACKEND ALIGNMENT — STATUS: **❌ TIDAK SELARAS**

| Aspek | Temuan |
|-------|--------|
| Schema source of truth | TIDAK ADA — `CREATE TABLE` tersebar di 5 file (login.php, index.php, presensi.php, riwayat.php, create_tables.php) |
| Foreign keys | Hanya 1 dari 4 relasi punya FK (`presensi.anak_id → anak.id`) |
| UNIQUE constraints | Tidak ada — `ustadz.username` bisa duplikat, `presensi(anak_id, tanggal)` bisa duplikat |
| Password storage | **PLAINTEXT** — kritis! (login.php:37, pengaturan.php:31) |
| Migration system | TIDAK ADA — `update_database.php` adalah one-shot script tanpa versioning |

### 11.3 SYSTEM LOGIC & USER ROLES — STATUS: **❌ TIDAK SELARAS**

| Aspek | Temuan |
|-------|--------|
| RBAC | **TIDAK ADA** — hanya binary check (login/tidak) |
| Role column | Tidak ada di tabel `ustadz` |
| Admin features | Tidak bisa diakses — tidak ada user yang punya role admin |
| Ownership check | Tidak ada — ustadz A bisa edit progres ustadz B |
| Tambah ustadz | SEMUA user bisa tambah ustadz baru — harusnya admin-only (pengaturan.php:26-34) |
| Hapus data | **TIDAK BISA** — fitur delete tidak ada untuk entitas apapun |

### 11.4 UI/UX CONSISTENCY — STATUS: **❌ TIDAK KONSISTEN**

| Aspek | Temuan |
|-------|--------|
| Sidebar | HANYA di index.php — halaman lain tidak ada navigasi |
| Header/Fomater | Tidak konsisten — 3 pola berbeda (sidebar, header-bar, title-only) |
| Footer | **TIDAK ADA** di halaman manapun |
| CSS Variables | **TIDAK ADA** — semua warna hardcoded |
| Color palette | Relatif konsisten (`#1e5a38`, `#2c7a4d`) tapi implementasi detail berbeda |
| Font stack | 3 varian: `Arial` (login.php), `Segoe UI` (6 file), `Poppins` (edit.php & pengaturan.php — TIDAK PERNAH di-load) |
| Breakpoints | 2 nilai berbeda: `768px` (5 file) vs `600px` (profil_santri.php) — 2 file tanpa media query |
| Success messages | Border-left ADA di index.php & profil_santri.php, TIDAK ADA di presensi.php & pengaturan.php |
| Table styling | Border `#ddd` vs `#e0e0e0`, padding `10px` vs `12px`, striping ada/tidak ada |
| Input focus styles | Ada di 2 file, tidak ada di sisanya |
| Viewport meta | ADA di semua UI file **kecuali** login.php |

### 11.5 SECURITY POSTURE — STATUS: **🔴 KRITIS**

| Level | Jumlah | Contoh |
|-------|--------|--------|
| **CRITICAL** | 7 | Plaintext password, credentials di halaman login, phpinfo(), cek_login, database terekspos, info.php debug |
| **HIGH** | 8 | SQL concatenation (pengaturan.php:38), NO CSRF, NO session regenerate, XSS, NO rate limit, broken export link |
| **MEDIUM** | 7 | mkdir 0777, NO FK, NO UNIQUE, NO delete, NO session timeout, error_reporting(E_ALL) |

### 11.6 FEATURES PER ROLE — KESENJANGAN

| Fitur | Admin (target) | Ustadz (target) | Saat Ini (semua user) |
|-------|---------------|-----------------|----------------------|
| Login | ✅ | ✅ | ✅ |
| Dashboard full | ✅ | ❌ (limited) | ✅ (semua lihat full) |
| Kelola Ustadz | ✅ CRUD | ❌ | ❌ (TIDAK ADA) |
| Tambah Santri | ✅ | ✅ | ✅ (semua bisa) |
| Hapus Santri | ✅ | ❌ | ❌ (TIDAK ADA) |
| Input Progres | ✅ | ✅ | ✅ (semua bisa) |
| Edit Progres All | ✅ | ❌ (own only) | ✅ (semua edit semua!) |
| Hapus Progres | ✅ | ❌ | ❌ (TIDAK ADA) |
| Presensi | ✅ | ✅ | ✅ (semua bisa) |
| Export Excel | ✅ | ✅ | ✅ (semua bisa) |
| Pengaturan Full | ✅ | ❌ (profile only) | ✅ (semua full akses!) |

### 11.7 MISSING FILES (WAJIB DIBUAT)

| File | Fungsi | Prioritas |
|------|--------|-----------|
| `database/schema.sql` | Single source of truth schema | 🔴 Tinggi |
| `includes/config.php` | Shared DB config & constants | 🔴 Tinggi |
| `includes/auth.php` | Shared auth helpers | 🔴 Tinggi |
| `includes/header.php` | Shared header + navigation | 🟠 Sedang |
| `includes/footer.php` | Shared footer | 🟠 Sedang |
| `assets/css/style.css` | Shared stylesheet | 🔴 Tinggi |
| `assets/js/app.js` | Shared JavaScript | 🟢 Rendah |
| `export_presensi.php` | Export presensi (broken link) | 🔴 Tinggi |
| `.github/workflows/ci.yml` | CI/CD pipeline | 🔴 Tinggi |
| `.htaccess` | Protect database directory | 🔴 Tinggi |
| `database/seed.sql` | Default data seeding | 🟠 Sedang |
| `database/migrations/` | Schema versioning | 🟠 Sedang |
| `tests/unit/` | Unit tests | 🟠 Sedang |
| `tests/integration/` | Integration tests | 🟠 Sedang |

### 11.8 PRIORITAS PERBAIKAN (ROADMAP)

```
FASE 1 — KEAMANAN (SEKARANG)
  □ Hash semua password (password_hash)
  □ Hapus credentials dari login.php
  □ Hapus/restrict cek_login, info.php, database/info.php
  □ Tambah .htaccess proteksi database/
  □ Session regenerate setelah login
  □ CSRF token di semua form
  □ Escape semua output (htmlspecialchars)

FASE 2 — FOUNDATION (MINGGU INI)
  □ Buat database/schema.sql (single source of truth)
  □ Buat includes/config.php (shared config)
  □ Buat includes/auth.php (shared auth)
  □ Buat assets/css/style.css (shared CSS)
  □ Buat includes/header.php (shared header + sidebar)
  □ Buat includes/footer.php (shared footer)
  □ Buat export_presensi.php (fix broken link)
  □ Buat .github/workflows/ci.yml (CI/CD pipeline)

FASE 3 — RBAC (2 MINGGU)
  □ Tambah role column di tabel ustadz
  □ Implementasi role hierarchy (Admin, Ustadz, Parent)
  □ Tambah authorization check di setiap aksi
  □ Implementasi ownership check (ustadz hanya akses data sendiri)
  □ Admin-only: kelola ustadz, hapus data, pengaturan sistem

FASE 4 — KONSISTENSI (1 BULAN)
  □ Standardisasi sidebar di semua halaman
  □ Standardisasi semua CSS ke style.css
  □ Standardisasi breakpoints ke 768px
  □ Standardisasi font ke 'Segoe UI', Arial, sans-serif
  □ Standardisasi form handling pattern
  □ Standardisasi tabel, button, card, success messages
  □ Tambah foreign keys di semua relasi
  □ Tambah UNIQUE constraints

FASE 5 — ADVANCED (2 BULAN)
  □ Fitur delete untuk semua entitas (admin-only)
  □ Pagination untuk riwayat
  □ Search/filter santri
  □ Session timeout
  □ Rate limiting login
  □ Audit trail untuk perubahan data
  □ Backup/restore database
```

---

## 12. AUTOMATED RETESTING PROTOCOL

> **Setiap AI model WAJIB mengikuti protokol ini saat terjadi kegagalan test.**

### 12.1 Trigger
Protokol ini AKTIF ketika:
- ❌ CI/CD pipeline GAGAL di GitHub Actions
- ❌ Test lokal GAGAL sebelum push
- ❌ Ada error PHP (syntax, runtime) yang muncul

### 12.2 Failure Response Flow

```
[FAILURE DETECTED]
       ↓
1. CAPTURE — Screenshot/copy FULL error log
       ↓
2. ANALYZE — Baca error, identifikasi root cause
       ↓
3. CLASSIFY — Tentukan kategori error:
       ├─ Syntax Error → Fix kode, lint ulang
       ├─ Logic Error → Trace alur data, fix logika
       ├─ Database Error → Cek schema, query, constraints
       ├─ Security Error → Cek sanitasi, auth, CSRF
       └─ UI Error → Cek CSS, responsive, component
       ↓
4. FIX — Perbaiki root cause di localhost
       ↓
5. RETEST LOKAL — Jalankan ALL test (bukan hanya yang gagal)
       ↓
6. COMMIT & PUSH — Dengan pesan yang sebutkan root cause
       ↓
7. MONITOR CI/CD — Pantau pipeline sampai selesai
       ↓
8. VERIFY — Jika PASS → lanjut. Jika FAIL → ulang dari langkah 1
```

### 12.3 Failure Classification & Response

| Error Type | Response | Tools |
|-----------|----------|-------|
| **PHP Parse/Syntax Error** | `php -l <file>` untuk lint, fix syntax | `php -l` |
| **Undefined Variable/Function** | Cek include path, function definition, scope | grep, read |
| **SQL Error (table not found)** | Cek apakah table di-create, cek schema.sql | database/schema.sql |
| **SQL Error (constraint violation)** | Cek data duplikat, null values, FK issues | query database |
| **Security Scan Failed** | Cek plaintext password, XSS, CSRF | AGENT.md Section 2 |
| **CSRF Token Mismatch** | Cek session token generation, form hidden field | Form handler code |
| **CSS/UI Broken** | Cek class names, file paths, responsive breakpoints | Browser DevTools |
| **CI/CD Pipeline Failed** | Baca GitHub Actions log, cek step mana yang error | GitHub Actions tab |

### 12.4 Rules for Retesting

```
┌─────────────────────────────────────────────────────────────────┐
│                    RETESTING RULES                               │
│                                                                 │
│  1. SATU error → fix SATU penyebab — jangan sambil fix lain     │
│  2. Test LOKAL dulu — jangan push langsung ke GitHub            │
│  3. Test SEMUA fitur — bukan hanya yang di-fix                  │
│  4. Jika error yang SAMA muncul 3x → evaluasi pendekatan        │
│  5. Jika error BARU muncul → fix juga sebelum push              │
│  6. JANGAN force push — PUSH BIASA SAJA                         │
│  7. JANGAN skip test — "kecil" tetap harus di-test              │
│  8. Jika ragu → TANYA — jangan asumsi                           │
└─────────────────────────────────────────────────────────────────┘
```

### 12.5 Retest Documentation Template

Setiap kali melakukan retest, WAJIB catat di commit message:

```
fix(scope): perbaiki [error singkat]

ERROR LOG:
  - Step/Job: [nama step CI/CD yang gagal]
  - Error message: [pesan error lengkap]
  - Line: [file:line]

ROOT CAUSE:
  - [penjelasan singkat kenapa error terjadi]

FIX:
  - [apa yang diubah dan di file mana]

TEST LOKAL:
  - PHP lint: PASS
  - Manual test: PASS
  - Functional test: PASS
  - Security test: PASS

CI/CD RESULT: [PASS / FAIL]
```

---

## 13. FINAL NOTES

1. **Tujuan utama**: Aplikasi Monitoring Ngaji yang AMAN, KONSISTEN, dan MUDAH DIKEMBANGKAN
2. **Setiap AI model adalah penjaga kualitas** — jangan pernah kompromi dengan security
3. **Konsistensi lebih penting daripada kecepatan** — lebih baik lambat dan benar
4. **Jika ada aturan yang bertentangan**, prioritaskan: Security > Functionality > Consistency > Performance
5. **Dokumentasi adalah bagian dari kode** — jika mengubah kode, ubah juga dokumentasinya
6. **Setiap error adalah pelajaran** — catat root cause agar tidak terulang

---

**Dibuat: 2026-06-24**
**Terakhir diperbarui: 2026-07-04**
**Versi: 2.0**
