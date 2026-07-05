<?php
// export_excel.php - Export data ke Excel

if (!class_exists('SQLite3')) {
    die('Error: SQLite3 tidak tersedia');
}

$db = new SQLite3('database/ngaji.db');

// Ambil parameter filter
$anak_id = $_GET['id'] ?? 0;
$bulan = $_GET['bulan'] ?? date('Y-m');
$tahun = substr($bulan, 0, 4);
$bulan_angka = substr($bulan, 5, 2);

// Ambil data anak
$anak = null;
if ($anak_id) {
    $stmt = $db->prepare("SELECT * FROM anak WHERE id = :id");
    $stmt->bindValue(':id', $anak_id, SQLITE3_INTEGER);
    $anak = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
}

// Nama bulan dalam Bahasa Indonesia
$namaBulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Header untuk file Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="laporan_ngaji_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

// Query data
if ($anak_id) {
    $stmt = $db->prepare("SELECT * FROM progres WHERE anak_id = :id AND strftime('%Y-%m', tanggal) = :bulan ORDER BY tanggal DESC");
    $stmt->bindValue(':id', $anak_id, SQLITE3_INTEGER);
    $stmt->bindValue(':bulan', $bulan, SQLITE3_TEXT);
    $data = $stmt->execute();
    $judul = "Laporan Ngaji - " . $anak['nama'] . " - " . $namaBulan[$bulan_angka] . " $tahun";
} else {
    $stmt = $db->prepare("SELECT p.*, a.nama as nama_anak, a.level FROM progres p 
                          JOIN anak a ON p.anak_id = a.id 
                          WHERE strftime('%Y-%m', p.tanggal) = :bulan 
                          ORDER BY p.tanggal DESC, a.nama");
    $stmt->bindValue(':bulan', $bulan, SQLITE3_TEXT);
    $data = $stmt->execute();
    $judul = "Laporan Ngaji Semua Santri - " . $namaBulan[$bulan_angka] . " $tahun";
}

// Tampilkan HTML untuk Excel
echo "<html>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>$judul</title>";
echo "<style>";
echo "th { background: #2c7a4d; color: white; }";
echo "td, th { border: 1px solid #ddd; padding: 8px; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<h2>$judul</h2>";
echo "<p>Tanggal Export: " . date('d/m/Y H:i:s') . "</p>";

echo "<table border='1'>";
echo "<thead>";
echo "<tr>";
if (!$anak_id) echo "<th>Nama Santri</th>";
echo "<th>Tanggal</th>";
echo "<th>Juz</th>";
echo "<th>Surah</th>";
echo "<th>Ayat/Halaman</th>";
echo "<th>Kelancaran</th>";
echo "<th>Catatan</th>";
echo "<th>Durasi</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

$totalDurasi = 0;
$totalPertemuan = 0;
$totalKelancaran = 0;

while($row = $data->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>";
    if (!$anak_id) echo "<td>" . htmlspecialchars($row['nama_anak']) . "</td>";
    echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
    echo "<td>" . $row['juz'] . "</td>";
    echo "<td>" . htmlspecialchars($row['surah']) . "</td>";
    
    // Tampilkan ayat atau halaman
    if ($row['halaman']) {
        echo "<td>Halaman " . $row['halaman'] . "</td>";
    } else {
        echo "<td>Ayat " . ($row['ayat'] ?: '-') . "</td>";
    }
    
    echo "<td>" . str_repeat('⭐', $row['kelancaran']) . " ({$row['kelancaran']}/5)</td>";
    echo "<td>" . nl2br(htmlspecialchars($row['catatan'] ?? '-')) . "</td>";
    echo "<td>" . ($row['durasi'] ? $row['durasi'] . ' menit' : '-') . "</td>";
    echo "</tr>";
    
    $totalDurasi += $row['durasi'] ?? 0;
    $totalPertemuan++;
    $totalKelancaran += $row['kelancaran'] ?? 0;
}

echo "</tbody>";
echo "</table>";

echo "<br>";
echo "<h3>📊 Ringkasan:</h3>";
echo "<table border='1'>";
echo "<tr><td><strong>Total Pertemuan</strong></td><td>$totalPertemuan kali</td></tr>";
echo "<tr><td><strong>Total Durasi</strong></td><td>$totalDurasi menit (" . round($totalDurasi/60, 1) . " jam)</td></tr>";
echo "<tr><td><strong>Rata-rata Durasi</strong></td><td>" . ($totalPertemuan > 0 ? round($totalDurasi/$totalPertemuan, 1) : 0) . " menit</td></tr>";
echo "<tr><td><strong>Rata-rata Kelancaran</strong></td><td>" . ($totalPertemuan > 0 ? round($totalKelancaran/$totalPertemuan, 1) : 0) . " / 5</td></tr>";
echo "</table>";

echo "</body>";
echo "</html>";
?>