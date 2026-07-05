<?php
require_once __DIR__ . '/includes/db.php';

requireLogin();

$db = getDbConnection();
$anak_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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

// Proses update profil santri
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    // Update data anak
    $stmt = $db->prepare("UPDATE anak SET nama = :nama, level = :level WHERE id = :id");
    $stmt->bindValue(':nama', $_POST['nama'], SQLITE3_TEXT);
    $stmt->bindValue(':level', $_POST['level'], SQLITE3_TEXT);
    $stmt->bindValue(':id', $anak_id, SQLITE3_INTEGER);
    $stmt->execute();
    
    // Update biodata
    $stmt = $db->prepare("UPDATE biodata_santri SET 
        tempat_lahir = :tempat_lahir,
        tanggal_lahir = :tanggal_lahir,
        nama_ayah = :nama_ayah,
        nama_ibu = :nama_ibu,
        alamat = :alamat,
        no_telp = :no_telp
        WHERE anak_id = :anak_id");
    $stmt->bindValue(':tempat_lahir', $_POST['tempat_lahir'], SQLITE3_TEXT);
    $stmt->bindValue(':tanggal_lahir', $_POST['tanggal_lahir'], SQLITE3_TEXT);
    $stmt->bindValue(':nama_ayah', $_POST['nama_ayah'], SQLITE3_TEXT);
    $stmt->bindValue(':nama_ibu', $_POST['nama_ibu'], SQLITE3_TEXT);
    $stmt->bindValue(':alamat', $_POST['alamat'], SQLITE3_TEXT);
    $stmt->bindValue(':no_telp', $_POST['no_telp'], SQLITE3_TEXT);
    $stmt->bindValue(':anak_id', $anak_id, SQLITE3_INTEGER);
    $stmt->execute();
    
    $success = "Profil santri berhasil diupdate!";
    
    // Refresh data setelah update
    header('Location: profil_santri.php?id=' . $anak_id . '&success=1');
    exit;
}

// Ambil data anak
$stmt = $db->prepare("SELECT * FROM anak WHERE id = :id");
$stmt->bindValue(':id', $anak_id, SQLITE3_INTEGER);
$anak = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$anak) {
    header('Location: index.php');
    exit;
}

// Ambil biodata
$stmt = $db->prepare("SELECT * FROM biodata_santri WHERE anak_id = :id");
$stmt->bindValue(':id', $anak_id, SQLITE3_INTEGER);
$biodata = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

$success_message = $_GET['success'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?= htmlspecialchars($anak['nama']) ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body { padding: 30px; }
        .container { max-width: 700px; margin: 0 auto; }
        
        .profile-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .avatar {
            font-size: 60px;
            background: #2c7a4d;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
        }
        
        h2 {
            color: #1e5a38;
        }
        
        .level-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .level-quran {
            background: #2c7a4d;
            color: white;
        }
        
        .level-hijaiyah {
            background: #fd7e14;
            color: white;
        }
        
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-label {
            width: 130px;
            font-weight: 600;
            color: #555;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        .btn-back, .btn-edit {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
            margin-right: 10px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #333;
        }
        
        .btn-edit:hover {
            background: #e0a800;
        }
        
        .btn-save {
            background: #2c7a4d;
            color: white;
        }
        
        .btn-save:hover {
            background: #1e5a38;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #2c7a4d;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .edit-mode {
            display: none;
        }
        
        .view-mode {
            display: block;
        }
        
        @media (max-width: 600px) {
            .info-label { width: 100px; font-size: 13px; }
            .grid-2 { grid-template-columns: 1fr; }
            .container { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if($success_message == '1'): ?>
        <div class="success">✅ Profil santri berhasil diupdate!</div>
        <?php endif; ?>
        
        <!-- Mode Tampilan (View Mode) -->
        <div id="viewMode" class="view-mode">
            <div class="profile-header">
                <div class="avatar">👤</div>
                <h2><?= htmlspecialchars($anak['nama']) ?></h2>
                <span class="level-badge <?= ($anak['level'] == 'Al-Qur\'an (Juz 1-30)') ? 'level-quran' : 'level-hijaiyah' ?>">
                    <?= ($anak['level'] == 'Al-Qur\'an (Juz 1-30)') ? '📖 Al-Qur\'an (Juz 1-30)' : '🔤 Juz Amma + Hijaiyah' ?>
                </span>
            </div>
            
            <div class="info-row">
                <div class="info-label">Tempat Lahir</div>
                <div class="info-value"><?= $biodata ? htmlspecialchars($biodata['tempat_lahir']) : '-' ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Lahir</div>
                <div class="info-value"><?= $biodata && $biodata['tanggal_lahir'] ? date('d/m/Y', strtotime($biodata['tanggal_lahir'])) : '-' ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Nama Ayah</div>
                <div class="info-value"><?= $biodata ? htmlspecialchars($biodata['nama_ayah']) : '-' ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Nama Ibu</div>
                <div class="info-value"><?= $biodata ? htmlspecialchars($biodata['nama_ibu']) : '-' ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Alamat</div>
                <div class="info-value"><?= $biodata ? nl2br(htmlspecialchars($biodata['alamat'])) : '-' ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">No. Telepon</div>
                <div class="info-value"><?= $biodata ? htmlspecialchars($biodata['no_telp']) : '-' ?></div>
            </div>
            
            <div>
                <button class="btn-edit" onclick="toggleEditMode()">✏️ Edit Profil</button>
                <a href="index.php" class="btn-back">← Kembali ke Dashboard</a>
            </div>
        </div>
        
        <!-- Mode Edit (Edit Mode) -->
        <div id="editMode" class="edit-mode">
            <div class="profile-header">
                <div class="avatar">✏️</div>
                <h2>Edit Profil Santri</h2>
            </div>
            
            <form method="POST">
                <div class="grid-2">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($anak['nama']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Level</label>
                        <select name="level" required>
                            <option value="Al-Qur'an (Juz 1-30)" <?= ($anak['level'] == 'Al-Qur\'an (Juz 1-30)') ? 'selected' : '' ?>>
                                📖 Al-Qur'an (Juz 1-30)
                            </option>
                            <option value="Juz Amma Ma'al Hijaiyah" <?= ($anak['level'] == 'Juz Amma Ma\'al Hijaiyah') ? 'selected' : '' ?>>
                                🔤 Juz Amma + Hijaiyah (Juz 0)
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="<?= $biodata ? htmlspecialchars($biodata['tempat_lahir']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="<?= $biodata ? $biodata['tanggal_lahir'] : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Nama Ayah</label>
                        <input type="text" name="nama_ayah" value="<?= $biodata ? htmlspecialchars($biodata['nama_ayah']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Nama Ibu</label>
                        <input type="text" name="nama_ibu" value="<?= $biodata ? htmlspecialchars($biodata['nama_ibu']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" rows="2"><?= $biodata ? htmlspecialchars($biodata['alamat']) : '' ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" name="no_telp" value="<?= $biodata ? htmlspecialchars($biodata['no_telp']) : '' ?>">
                    </div>
                </div>
                
                <div>
                    <button type="submit" name="update_profil" class="btn-save">💾 Simpan Perubahan</button>
                    <button type="button" class="btn-back" onclick="toggleEditMode()">❌ Batal</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function toggleEditMode() {
            var viewMode = document.getElementById('viewMode');
            var editMode = document.getElementById('editMode');
            
            if (viewMode.style.display === 'none') {
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
            } else {
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
            }
        }
    </script>
<script src="assets/js/app.js"></script>
</body>
</html>