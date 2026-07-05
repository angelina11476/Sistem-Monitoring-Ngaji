<?php
// update_database.php - Jalankan SEKALI untuk menambah kolom ustadz_id
$db = new SQLite3('database/ngaji.db');

// Tambah kolom ustadz_id jika belum ada
$db->exec("ALTER TABLE progres ADD COLUMN ustadz_id INTEGER DEFAULT 1");

// Update data lama dengan ustadz_id = 1
$db->exec("UPDATE progres SET ustadz_id = 1 WHERE ustadz_id IS NULL");

echo "✅ Database berhasil diupdate! Kolom ustadz_id telah ditambahkan.";
echo "<br><a href='index.php'>Kembali ke Dashboard</a>";
?>