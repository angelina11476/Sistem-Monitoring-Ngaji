<?php
// includes/db.php - koneksi database dan helper autentikasi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDbConnection() {
    // Cek lokasi database yang mungkin dipakai oleh versi berbeda dari project
    $candidates = [
        __DIR__ . '/../database/ngaji.db',
        __DIR__ . '/../ngaji.db',
    ];

    foreach ($candidates as $path) {
        if (file_exists($path)) {
            return new SQLite3($path);
        }
    }

    // Jika tidak ditemukan, buat file di root project
    $fallback = __DIR__ . '/../ngaji.db';
    return new SQLite3($fallback);
}

function requireLogin() {
    if (empty($_SESSION['login'])) {
        header('Location: login.php');
        exit;
    }
}

if (!function_exists('isCurrentUserAdmin')) {
    function isCurrentUserAdmin(): bool {
        return !empty($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

?>
