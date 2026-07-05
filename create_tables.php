<?php
require_once __DIR__ . '/includes/db.php';

if (!class_exists('SQLite3')) {
    die('Error: SQLite3 tidak tersedia');
}

$db = getDbConnection();

$db->exec("CREATE INDEX IF NOT EXISTS idx_presensi_tanggal ON presensi(tanggal)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_presensi_anak ON presensi(anak_id)");

echo "<h2 style='color: green;'>✅ Skema database berhasil dipastikan!</h2>";
echo "<p>Tabel yang tersedia di database:</p>";
echo "<ul>";

$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
while($table = $tables->fetchArray(SQLITE3_ASSOC)) {
    echo "<li>" . $table['name'] . "</li>";
}
echo "</ul>";
echo "<a href='presensi.php' style='background: #2c7a4d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 Buka Presensi</a>";
echo "&nbsp;&nbsp;";
echo "<a href='index.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Kembali ke Dashboard</a>";
?>