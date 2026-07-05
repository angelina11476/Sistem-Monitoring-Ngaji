<?php
require_once __DIR__ . '/includes/db.php';

requireLogin();

$db = getDbConnection();
$anak_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = $_GET['type'] ?? 'progres';

if (!isCurrentUserAdmin()) {
    $stmt = $db->prepare("SELECT id FROM progres WHERE anak_id = :anak_id LIMIT 1");
    $stmt->bindValue(':anak_id', $anak_id, SQLITE3_INTEGER);
    $ownedRow = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$ownedRow) {
        http_response_code(403);
        echo 'Akses ditolak.';
        exit;
    }
}

// Ambil data anak
$stmt = $db->prepare("SELECT * FROM anak WHERE id = :id");
$stmt->bindValue(':id', $anak_id, SQLITE3_INTEGER);
$anak = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$anak) {
    header('Location: index.php');
    exit;
}

// Fungsi untuk membuat garis pemisah
function garis() {
    return "────────────────────────────\n";
}

// Fungsi untuk cetak label dan nilai
function label($label, $value) {
    return "▸ *{$label}:* {$value}\n";
}

if ($type == 'progres') {
    // Ambil progres terakhir
    $stmt = $db->prepare("
        SELECT p.*, u.nama as ustadz_nama, u.gelar as ustadz_gelar 
        FROM progres p 
        LEFT JOIN ustadz u ON p.ustadz_id = u.id 
        WHERE p.anak_id = :id 
        ORDER BY p.tanggal DESC 
        LIMIT 1
    ");
    $stmt->bindValue(':id', $anak_id, SQLITE3_INTEGER);
    $last = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$last) {
        $message = "❌ Belum ada data ngaji untuk ananda " . $anak['nama'];
    } else {
        // Header
        $message = "📘 *LAPORAN PERKEMBANGAN NGAJI* 📘\n";
        $message .= garis();
        $message .= "🏷️ *IDENTITAS SANTRI*\n";
        $message .= label("Nama", $anak['nama']);
        $message .= label("Level", ($anak['level'] == 'Al-Qur\'an (Juz 1-30)') ? 'Al-Qur\'an (Juz 1-30)' : 'Juz Amma + Hijaiyah');
        $message .= "\n";
        $message .= "📅 *LAPORAN TERAKHIR*\n";
        $message .= label("Tanggal", date('d/m/Y', strtotime($last['tanggal'])));
        
        if ($last['juz'] == 0) {
            $message .= label("Bacaan", "Juz Amma - " . $last['surah']);
            $message .= label("Halaman", $last['halaman'] ?: '-');
        } else {
            $message .= label("Juz", $last['juz']);
            $message .= label("Surah", $last['surah']);
            $message .= label("Ayat", $last['ayat'] ?: '-');
        }
        
        $message .= label("Kelancaran", str_repeat('⭐', $last['kelancaran']) . " (" . $last['kelancaran'] . "/5)");
        $message .= label("Durasi", ($last['durasi'] ?: '-') . " menit");
        
        if ($last['catatan']) {
            $message .= "\n📝 *CATATAN GURU:*\n";
            $message .= "   " . $last['catatan'] . "\n";
        }
        
        $message .= "\n👨‍🏫 *PENGAJAR:* " . ($last['ustadz_gelar'] ?? 'Ustadz') . " " . ($last['ustadz_nama'] ?? '-') . "\n";
        $message .= garis();
        $message .= "📱 *Dikirim via:* Sistem Monitoring Ngaji Ba'da Maghrib\n";
        $message .= "📅 *Waktu kirim:* " . date('d/m/Y H:i:s');
    }
    
} elseif ($type == 'riwayat') {
    // Ambil 10 riwayat terakhir
    $stmt = $db->prepare("
        SELECT p.*, u.nama as ustadz_nama, u.gelar as ustadz_gelar 
        FROM progres p 
        LEFT JOIN ustadz u ON p.ustadz_id = u.id 
        WHERE p.anak_id = :id 
        ORDER BY p.tanggal DESC 
        LIMIT 10
    ");
    $stmt->bindValue(':id', $anak_id, SQLITE3_INTEGER);
    $riwayat = $stmt->execute();
    
    $message = "📚 *LAPORAN RIWAYAT NGAJI* 📚\n";
    $message .= garis();
    $message .= "🏷️ *IDENTITAS SANTRI*\n";
    $message .= label("Nama", $anak['nama']);
    $message .= label("Level", ($anak['level'] == 'Al-Qur\'an (Juz 1-30)') ? 'Al-Qur\'an (Juz 1-30)' : 'Juz Amma + Hijaiyah');
    $message .= "\n📊 *RIWAYAT (10 TERAKHIR)*\n";
    $message .= garis();
    
    $no = 1;
    while($row = $riwayat->fetchArray(SQLITE3_ASSOC)) {
        $message .= "*" . $no . ".* 📅 " . date('d/m/Y', strtotime($row['tanggal'])) . "\n";
        if ($row['juz'] == 0) {
            $message .= "   📖 " . $row['surah'] . " (Juz Amma)\n";
            $message .= "   📄 Halaman: " . ($row['halaman'] ?: '-') . "\n";
        } else {
            $message .= "   📖 Juz " . $row['juz'] . " - " . $row['surah'] . "\n";
            $message .= "   🔢 Ayat: " . ($row['ayat'] ?: '-') . "\n";
        }
        $message .= "   ⭐ Kelancaran: " . str_repeat('⭐', $row['kelancaran']) . " (" . $row['kelancaran'] . "/5)\n";
        $message .= "   ⏱️ Durasi: " . ($row['durasi'] ?: '-') . " menit\n";
        if ($row['catatan']) {
            $message .= "   📝 Catatan: " . $row['catatan'] . "\n";
        }
        $message .= "   👨‍🏫 Pengajar: " . ($row['ustadz_gelar'] ?? 'Ustadz') . " " . ($row['ustadz_nama'] ?? '-') . "\n";
        $message .= "\n";
        $no++;
    }
    
    $message .= garis();
    $message .= "📱 *Dikirim via:* Sistem Monitoring Ngaji Ba'da Maghrib\n";
    $message .= "📅 *Waktu kirim:* " . date('d/m/Y H:i:s');
}

// Encode message untuk URL
$encodedMessage = urlencode($message);
header("Location: https://wa.me/?text=$encodedMessage");
exit;
?>