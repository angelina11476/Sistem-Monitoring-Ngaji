# Sistem Monitoring Ngaji

Sistem Monitoring Ngaji adalah aplikasi web sederhana untuk membantu ustadz/ustadzah mencatat progres belajar santri secara terorganisir. Aplikasi ini mendukung pencatatan perkembangan ngaji, presensi, riwayat belajar, serta pengelolaan data santri dalam satu dashboard.

## Fitur Utama

- Dashboard ringkas untuk melihat data santri dan aktivitas ngaji
- Input progres ngaji per santri
- Tambah dan kelola data santri
- Presensi harian
- Riwayat belajar santri
- Profil santri dan fitur berbagi progres
- Basis data SQLite yang sederhana dan mudah dipakai lokal

## Teknologi yang Digunakan

- PHP
- SQLite
- HTML, CSS, JavaScript

## Persyaratan

- PHP 8+
- Ekstensi SQLite aktif
- Web server lokal seperti Laragon, XAMPP, atau PHP built-in server

## Cara Menjalankan

1. Clone repository ini:
   ```bash
   git clone https://github.com/angelina11476/Sistem-Monitoring-Ngaji.git
   ```
2. Masuk ke folder project:
   ```bash
   cd Sistem-Monitoring-Ngaji
   ```
3. Jalankan aplikasi melalui web server lokal Anda, misalnya di folder root Laragon/XAMPP.
4. Buka aplikasi di browser.

## Login Default

Saat pertama kali dijalankan, sistem akan membuat akun demo jika belum ada data pengguna:

- Username: `ustadz`
- Password: `123`

## Struktur Folder

- `index.php` – dashboard utama
- `login.php` – halaman login
- `presensi.php` – pencatatan presensi
- `profil_santri.php` – profil santri
- `riwayat.php` – riwayat pembelajaran
- `database/` – file schema dan database SQLite
- `includes/` – koneksi database dan helper

## Catatan

Aplikasi ini dirancang untuk kebutuhan operasional sederhana dan cocok dipakai di lingkungan madrasah atau pengajian skala kecil.

