<?php
// cek_login.php - Cek data ustadz di database
$db = getDbConnection();

// Cek isi tabel ustadz
$result = $db->query("SELECT * FROM ustadz");
echo "<h2>Data Ustadz di Database:</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nama</th><th>Gelar</th><th>Username</th><th>Password</th></tr>";

while($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['nama'] . "</td>";
    echo "<td>" . $row['gelar'] . "</td>";
    echo "<td>" . $row['username'] . "</td>";
    echo "<td>" . $row['password'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Jika tidak ada data, tambahkan
$count = $db->querySingle("SELECT COUNT(*) FROM ustadz");
if ($count == 0) {
    $db->exec("INSERT INTO ustadz (nama, gelar, username, password) VALUES ('Lina', 'Ustadzah', 'ustadz', '123')");
    $db->exec("INSERT INTO ustadz (nama, gelar, username, password) VALUES ('Ahmad', 'Ustadz', 'ustadz2', '123')");
    echo "<p style='color:green'>✅ Data ustadz berhasil ditambahkan!</p>";
    echo "<meta http-equiv='refresh' content='2'>";
}
?>
