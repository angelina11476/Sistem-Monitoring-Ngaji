<?php
require_once __DIR__ . '/includes/db.php';

requireLogin();

$db = getDbConnection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!isCurrentUserAdmin()) {
    $stmt = $db->prepare("SELECT anak_id FROM progres WHERE id = :id LIMIT 1");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $ownedRow = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$ownedRow || (int) $ownedRow['anak_id'] !== (int) ($_SESSION['ustadz_id'] ?? 0)) {
        http_response_code(403);
        echo 'Akses ditolak.';
        exit;
    }
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $stmt = $db->prepare("UPDATE progres SET 
        tanggal = :tanggal,
        juz = :juz,
        surah = :surah,
        ayat = :ayat,
        halaman = :halaman,
        kelancaran = :kelancaran,
        catatan = :catatan,
        durasi = :durasi
    WHERE id = :id");
    
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':tanggal', $_POST['tanggal'], SQLITE3_TEXT);
    $stmt->bindValue(':juz', $_POST['juz'], SQLITE3_INTEGER);
    $stmt->bindValue(':surah', $_POST['surah'], SQLITE3_TEXT);
    $stmt->bindValue(':ayat', $_POST['ayat'], SQLITE3_INTEGER);
    $stmt->bindValue(':halaman', $_POST['halaman'], SQLITE3_INTEGER);
    $stmt->bindValue(':kelancaran', $_POST['kelancaran'], SQLITE3_INTEGER);
    $stmt->bindValue(':catatan', $_POST['catatan'], SQLITE3_TEXT);
    $stmt->bindValue(':durasi', $_POST['durasi'], SQLITE3_INTEGER);
    $stmt->execute();
    
    header('Location: riwayat.php?id=' . $_POST['anak_id']);
    exit;
}

// Ambil data
$stmt = $db->prepare("SELECT p.*, a.nama, a.level FROM progres p 
                      JOIN anak a ON p.anak_id = a.id WHERE p.id = :id");
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$data = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$data) {
    header('Location: index.php');
    exit;
}

$isQuran = ($data['level'] == 'Al-Qur\'an (Juz 1-30)');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data - <?= htmlspecialchars($data['nama']) ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body { padding: 30px; }
        .container { max-width: 600px; margin: 0 auto; }
        button { margin-right: 10px; }
        .btn-delete { background: #dc3545; }
        .btn-delete:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="container card">
        <h2 class="panel-title">✏️ Edit Data Ngaji</h2>
        <div class="subtitle panel-subtitle">
            Santri: <strong><?= htmlspecialchars($data['nama']) ?></strong><br>
            Level: <?= $isQuran ? '📖 Al-Qur\'an' : '🔤 Juz Amma + Hijaiyah' ?>
        </div>
        
        <div class="warning">⚠️ Edit data dengan hati-hati. Perubahan akan langsung tersimpan.</div>
        
        <form method="POST">
            <input type="hidden" name="anak_id" value="<?= $data['anak_id'] ?>">
            
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" value="<?= $data['tanggal'] ?>" required>
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label>Juz</label>
                    <input type="number" name="juz" min="1" max="30" value="<?= $data['juz'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Surah</label>
                    <input type="text" name="surah" value="<?= htmlspecialchars($data['surah']) ?>" required>
                </div>
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label><?= $isQuran ? 'Ayat' : 'Halaman' ?></label>
                    <input type="number" name="<?= $isQuran ? 'ayat' : 'halaman' ?>" value="<?= $isQuran ? $data['ayat'] : $data['halaman'] ?>">
                </div>
                <div class="form-group">
                    <label>Kelancaran (1-5)</label>
                    <select name="kelancaran">
                        <option value="1" <?= $data['kelancaran'] == 1 ? 'selected' : '' ?>>1 - Tersendat</option>
                        <option value="2" <?= $data['kelancaran'] == 2 ? 'selected' : '' ?>>2 - Terbata-bata</option>
                        <option value="3" <?= $data['kelancaran'] == 3 ? 'selected' : '' ?>>3 - Lancar tapi pelan</option>
                        <option value="4" <?= $data['kelancaran'] == 4 ? 'selected' : '' ?>>4 - Lancar</option>
                        <option value="5" <?= $data['kelancaran'] == 5 ? 'selected' : '' ?>>5 - Sangat Lancar</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Catatan</label>
                <textarea name="catatan" rows="3"><?= htmlspecialchars($data['catatan']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Durasi (menit)</label>
                <input type="number" name="durasi" value="<?= $data['durasi'] ?>">
            </div>
            
            <button type="submit" name="update">💾 Simpan Perubahan</button>
            <a href="riwayat.php?id=<?= $data['anak_id'] ?>" class="btn-back btn-delete" style="padding: 12px 25px;">❌ Batal</a>
        </form>
    </div>
<script src="assets/js/app.js"></script>
</body>
</html>