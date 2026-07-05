<?php
require_once __DIR__ . '/includes/db.php';

requireLogin();

$db = getDbConnection();

// Proses tambah santri
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_anak'])) {
    $stmt = $db->prepare("INSERT INTO anak (nama, level) VALUES (:nama, :level)");
    $stmt->bindValue(':nama', $_POST['nama'], SQLITE3_TEXT);
    $stmt->bindValue(':level', $_POST['level'], SQLITE3_TEXT);
    $stmt->execute();
    $anak_id = $db->lastInsertRowID();
    
    $stmt = $db->prepare("INSERT INTO biodata_santri (anak_id, tempat_lahir, tanggal_lahir, nama_ayah, nama_ibu, alamat, no_telp) 
                          VALUES (:anak_id, :tempat_lahir, :tanggal_lahir, :nama_ayah, :nama_ibu, :alamat, :no_telp)");
    $stmt->bindValue(':anak_id', $anak_id, SQLITE3_INTEGER);
    $stmt->bindValue(':tempat_lahir', $_POST['tempat_lahir'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':tanggal_lahir', $_POST['tanggal_lahir'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':nama_ayah', $_POST['nama_ayah'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':nama_ibu', $_POST['nama_ibu'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':alamat', $_POST['alamat'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':no_telp', $_POST['no_telp'] ?? '', SQLITE3_TEXT);
    $stmt->execute();
    $success = "Santri berhasil ditambahkan!";
}

// Proses input ngaji
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan'])) {
    $juz = $_POST['juz'];
    $surah = $_POST['surah'];
    $ayat = $_POST['ayat'] ?? 0;
    $halaman = $_POST['halaman'] ?? 0;
    
    // Jika level Juz Amma, set juz = 0
    if ($_POST['level_santri'] == 'Juz Amma Ma\'al Hijaiyah') {
        $juz = 0;
    }
    
    $stmt = $db->prepare("INSERT INTO progres (anak_id, tanggal, juz, surah, ayat, halaman, kelancaran, catatan, durasi, ustadz_id) 
                          VALUES (:anak_id, :tanggal, :juz, :surah, :ayat, :halaman, :kelancaran, :catatan, :durasi, :ustadz_id)");
    $stmt->bindValue(':anak_id', $_POST['anak_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':tanggal', $_POST['tanggal'], SQLITE3_TEXT);
    $stmt->bindValue(':juz', $juz, SQLITE3_INTEGER);
    $stmt->bindValue(':surah', $surah, SQLITE3_TEXT);
    $stmt->bindValue(':ayat', $ayat, SQLITE3_INTEGER);
    $stmt->bindValue(':halaman', $halaman, SQLITE3_INTEGER);
    $stmt->bindValue(':kelancaran', $_POST['kelancaran'], SQLITE3_INTEGER);
    $stmt->bindValue(':catatan', $_POST['catatan'], SQLITE3_TEXT);
    $stmt->bindValue(':durasi', $_POST['durasi'], SQLITE3_INTEGER);
    $stmt->bindValue(':ustadz_id', $_SESSION['ustadz_id'], SQLITE3_INTEGER);
    $stmt->execute();
    $success = "Data ngaji berhasil disimpan!";
}

$anakList = $db->query("SELECT * FROM anak ORDER BY nama");
$totalAnak = $db->querySingle("SELECT COUNT(*) FROM anak");
$totalNgajiHariIni = $db->querySingle("SELECT COUNT(*) FROM progres WHERE tanggal = date('now')");

// Daftar Surah untuk dropdown
$daftarSurah = array(
    "Al-Fatihah", "Al-Baqarah", "Ali Imran", "An-Nisa'", "Al-Ma'idah",
    "Al-An'am", "Al-A'raf", "Al-Anfal", "At-Taubah", "Yunus",
    "Hud", "Yusuf", "Ar-Ra'd", "Ibrahim", "Al-Hijr",
    "An-Nahl", "Al-Isra'", "Al-Kahf", "Maryam", "Taha",
    "Al-Anbiya'", "Al-Hajj", "Al-Mu'minun", "An-Nur", "Al-Furqan",
    "Asy-Syu'ara'", "An-Naml", "Al-Qasas", "Al-'Ankabut", "Ar-Rum",
    "Luqman", "As-Sajdah", "Al-Ahzab", "Saba'", "Fatir",
    "Yasin", "As-Saffat", "Sad", "Az-Zumar", "Ghafir",
    "Fussilat", "Asy-Syura", "Az-Zukhruf", "Ad-Dukhan", "Al-Jasiyah",
    "Al-Ahqaf", "Muhammad", "Al-Fath", "Al-Hujurat", "Qaf",
    "Az-Zariyat", "At-Tur", "An-Najm", "Al-Qamar", "Ar-Rahman",
    "Al-Waqi'ah", "Al-Hadid", "Al-Mujadilah", "Al-Hasyr", "Al-Mumtahanah",
    "As-Saff", "Al-Jumu'ah", "Al-Munafiqun", "At-Tagabun", "At-Talaq",
    "At-Tahrim", "Al-Mulk", "Al-Qalam", "Al-Haqqah", "Al-Ma'arij",
    "Nuh", "Al-Jinn", "Al-Muzzammil", "Al-Muddassir", "Al-Qiyamah",
    "Al-Insan", "Al-Mursalat", "An-Naba'", "An-Nazi'at", "'Abasa",
    "At-Takwir", "Al-Infitar", "Al-Mutaffifin", "Al-Insyiqaq", "Al-Buruj",
    "Ath-Thariq", "Al-A'la", "Al-Ghasyiyah", "Al-Fajr", "Al-Balad",
    "Asy-Syams", "Al-Lail", "Ad-Dhuha", "Al-Insyirah", "At-Tin",
    "Al-'Alaq", "Al-Qadr", "Al-Bayyinah", "Az-Zalzalah", "Al-'Adiyat",
    "Al-Qari'ah", "At-Takatsur", "Al-'Asr", "Al-Humazah", "Al-Fil",
    "Quraisy", "Al-Ma'un", "Al-Kautsar", "Al-Kafirun", "An-Nasr",
    "Al-Lahab", "Al-Ikhlas", "Al-Falaq", "An-Nas"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Monitoring Ngaji</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body { min-height: 100vh; }
        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background: #1e5a38; color: white; padding: 25px 0; box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 20px; padding: 0 20px; }
        .sidebar nav a {
            display: block; padding: 12px 25px; color: #e0e0e0; text-decoration: none;
            transition: all 0.3s; margin: 5px 0;
        }
        .sidebar nav a:hover { background: #2c7a4d; color: white; border-left: 4px solid #ffc107; }
        .main { margin-left: 260px; padding: 20px 30px; }
        .header {
            background: white; border-radius: 12px; padding: 15px 25px; margin-bottom: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center;
        }
        .header h1 { color: #1e5a38; font-size: 22px; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .logout-btn { background: #dc3545; color: white; padding: 8px 20px; border-radius: 8px; text-decoration: none; }
        .stats {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;
        }
        .stat-card {
            background: white; padding: 20px; border-radius: 12px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .stat-number { font-size: 32px; font-weight: bold; color: #2c7a4d; }
        .stat-label { color: #666; font-size: 14px; margin-top: 5px; }
        .card {
            background: white; border-radius: 12px; padding: 25px; margin-bottom: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .card h3 { color: #1e5a38; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        .santri-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;
        }
        .santri-card {
            background: #f9f9f9; border-radius: 12px; padding: 20px; border-left: 4px solid #2c7a4d;
            transition: transform 0.3s;
        }
        .santri-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .santri-card h4 { color: #1e5a38; font-size: 18px; margin-bottom: 8px; }
        .level-badge {
            display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; margin-bottom: 12px;
        }
        .level-quran { background: #2c7a4d; color: white; }
        .level-hijaiyah { background: #fd7e14; color: white; }
        .btn-link {
            display: inline-block; background: #6c757d; color: white; padding: 5px 12px;
            border-radius: 5px; text-decoration: none; font-size: 12px; margin-top: 10px; margin-right: 5px;
        }
        .btn-profil { background: #17a2b8; }
        .btn-share:hover { background: #128C7E; }
        .info-hijaiyah {
            background: #e3f2fd; padding: 12px; border-radius: 8px; margin-bottom: 20px;
            font-size: 13px; color: #1565c0; border-left: 4px solid #2196f3;
        }
        .info-quran {
            background: #e8f5e9; padding: 12px; border-radius: 8px; margin-bottom: 20px;
            font-size: 13px; color: #2e7d32; border-left: 4px solid #4caf50;
        }
        .tab-content { display: none; }
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar h2, .sidebar nav a span { display: none; }
            .sidebar nav a { text-align: center; padding: 12px; }
            .main { margin-left: 70px; padding: 15px; }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>📖 Ngaji App</h2>
    <nav>
        <a href="#" onclick="showTab('dashboard'); return false;">📊 Dashboard</a>
        <a href="#" onclick="showTab('input'); return false;">✏️ Input Ngaji</a>
        <a href="#" onclick="showTab('tambah'); return false;">👶 Tambah Santri</a>
        <a href="presensi.php">📋 Presensi</a>
        <a href="pengaturan.php">⚙️ Pengaturan</a>
    </nav>
</div>
<div class="main">
    <div class="hero-panel">
        <h1>📖 Monitoring Ngaji Ba'da Maghrib</h1>
        <p>Kelola santri, catat progres, dan lihat riwayat ngaji dengan tampilan yang lebih rapi dan cepat.</p>
        <div class="action-row">
            <button type="button" class="btn btn-secondary" data-open-modal="quickActions">⚡ Aksi Cepat</button>
            <span class="pill">✅ Sistem siap dipakai</span>
        </div>
    </div>
    <div class="header">
        <h1>📋 Ringkasan Dashboard</h1>
        <div class="user-info">
            <span>👤 <?= htmlspecialchars($_SESSION['gelar'] ?? 'Ustadz') ?> <?= htmlspecialchars($_SESSION['nama'] ?? '') ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    <?php if(isset($success)): ?>
    <div class="success">✅ <?= $success ?></div>
    <?php endif; ?>
    <div class="stats">
        <div class="stat-card"><div class="stat-number"><?= $totalAnak ?></div><div class="stat-label">Total Santri</div></div>
        <div class="stat-card"><div class="stat-number"><?= $totalNgajiHariIni ?></div><div class="stat-label">Ngaji Hari Ini</div></div>
    </div>
    
    <!-- Dashboard Tab -->
    <div id="dashboard" class="tab-content">
        <div class="card">
            <h3>📋 Daftar Santri</h3>
            <div class="santri-grid">
                <?php while($anak = $anakList->fetchArray(SQLITE3_ASSOC)): ?>
                <div class="santri-card">
                    <h4><?= htmlspecialchars($anak['nama']) ?></h4>
                    <span class="level-badge <?= ($anak['level'] == 'Al-Qur\'an (Juz 1-30)') ? 'level-quran' : 'level-hijaiyah' ?>">
                        <?= ($anak['level'] == 'Al-Qur\'an (Juz 1-30)') ? '📖 Al-Qur\'an' : '🔤 Juz Amma + Hijaiyah' ?>
                    </span>
                    <div>
                        <a href="profil_santri.php?id=<?= $anak['id'] ?>" class="btn-link btn-profil">👤 Profil</a>
                        <a href="riwayat.php?id=<?= $anak['id'] ?>" class="btn-link">📜 Riwayat</a>
                        <a href="share_wa.php?id=<?= $anak['id'] ?>&type=progres" class="btn-link btn-share">📱 Share</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <!-- Input Ngaji Tab -->
    <div id="input" class="tab-content">
        <div class="card">
            <h3>✏️ Input Progres Ngaji</h3>
            <form method="POST" id="formNgaji">
                <div class="form-group">
                    <label>Pilih Santri</label>
                    <select name="anak_id" id="anak_id" required>
                        <option value="">-- Pilih Santri --</option>
                        <?php 
                        $anakList2 = $db->query("SELECT * FROM anak ORDER BY nama");
                        while($anak = $anakList2->fetchArray(SQLITE3_ASSOC)): 
                        ?>
                        <option value="<?= $anak['id'] ?>" data-level="<?= htmlspecialchars($anak['level']) ?>">
                            <?= htmlspecialchars($anak['nama']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>📅 Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>📖 Juz</label>
                        <select name="juz" id="juz_input" required>
                            <option value="">-- Pilih Juz --</option>
                            <?php for($j=1; $j<=30; $j++): ?>
                            <option value="<?= $j ?>">Juz <?= $j ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>📜 Surah</label>
                        <select name="surah" id="surah_input" required>
                            <option value="">-- Pilih Surah --</option>
                            <?php foreach($daftarSurah as $surah): ?>
                            <option value="<?= htmlspecialchars($surah) ?>"><?= htmlspecialchars($surah) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>🔢 Ayat</label>
                        <input type="number" name="ayat" id="ayat_input" placeholder="Nomor ayat">
                    </div>
                </div>
                <div id="halamanField" style="display:none;">
                    <div class="form-group">
                        <label>📄 Halaman (khusus Juz Amma)</label>
                        <input type="number" name="halaman" id="halaman_input" placeholder="Nomor halaman">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>⭐ Kelancaran (1-5)</label>
                        <select name="kelancaran">
                            <option value="1">1 - Tersendat</option><option value="2">2 - Terbata-bata</option>
                            <option value="3">3 - Lancar tapi pelan</option><option value="4">4 - Lancar</option>
                            <option value="5">5 - Sangat Lancar</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>⏱️ Durasi (menit)</label>
                        <input type="number" name="durasi" placeholder="15">
                    </div>
                </div>
                <div class="form-group">
                    <label>📝 Catatan Guru</label>
                    <textarea name="catatan" rows="3" placeholder="Catatan untuk santri..."></textarea>
                </div>
                <div id="infoHijaiyah" class="info-hijaiyah" style="display:none;">ℹ️ Santri ini berada di level <strong>Juz Amma Ma'al Hijaiyah</strong>. Juz akan otomatis tersimpan sebagai <strong>0</strong>.</div>
                <div id="infoQuran" class="info-quran" style="display:none;">ℹ️ Santri ini berada di level <strong>Al-Qur'an (Juz 1-30)</strong>. Silakan pilih Juz dan Surah yang dibaca.</div>
                <input type="hidden" name="level_santri" id="level_santri">
                <button type="submit" name="simpan">💾 Simpan Progres</button>
            </form>
        </div>
    </div>
    
    <!-- Tambah Santri Tab -->
    <div id="tambah" class="tab-content">
        <div class="card">
            <h3>👶 Tambah Santri Baru</h3>
            <form method="POST">
                <div class="grid-2">
                    <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" required placeholder="Contoh: Ahmad Bin Abdullah"></div>
                    <div class="form-group"><label>Level</label><select name="level" required><option value="Al-Qur'an (Juz 1-30)">📖 Al-Qur'an (Juz 1-30)</option><option value="Juz Amma Ma'al Hijaiyah">🔤 Juz Amma + Hijaiyah (Juz 0)</option></select></div>
                    <div class="form-group"><label>Tempat Lahir</label><input type="text" name="tempat_lahir" placeholder="Contoh: Jakarta"></div>
                    <div class="form-group"><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir"></div>
                    <div class="form-group"><label>Nama Ayah</label><input type="text" name="nama_ayah" placeholder="Nama ayah kandung"></div>
                    <div class="form-group"><label>Nama Ibu</label><input type="text" name="nama_ibu" placeholder="Nama ibu kandung"></div>
                    <div class="form-group"><label>Alamat</label><textarea name="alamat" rows="2" placeholder="Alamat lengkap"></textarea></div>
                    <div class="form-group"><label>No. Telepon</label><input type="text" name="no_telp" placeholder="Contoh: 0812xxxxxx"></div>
                </div>
                <button type="submit" name="tambah_anak">➕ Tambah Santri</button>
            </form>
        </div>
    </div>
</div>
<script>
    const anakSelect = document.getElementById('anak_id');
    const juzInput = document.getElementById('juz_input');
    const halamanField = document.getElementById('halamanField');
    const infoHijaiyah = document.getElementById('infoHijaiyah');
    const infoQuran = document.getElementById('infoQuran');
    const levelSantri = document.getElementById('level_santri');
    if(anakSelect){
        anakSelect.addEventListener('change', function(){
            var opt = anakSelect.options[anakSelect.selectedIndex];
            var level = opt.getAttribute('data-level');
            levelSantri.value = level;
            if(level === 'Juz Amma Ma\'al Hijaiyah'){
                juzInput.disabled = true;
                juzInput.value = 0;
                halamanField.style.display = 'block';
                infoHijaiyah.style.display = 'block';
                infoQuran.style.display = 'none';
            } else {
                juzInput.disabled = false;
                juzInput.value = '';
                halamanField.style.display = 'none';
                infoHijaiyah.style.display = 'none';
                infoQuran.style.display = 'block';
            }
        });
    }
    function showTab(tabName){
        document.getElementById('dashboard').style.display = 'none';
        document.getElementById('input').style.display = 'none';
        document.getElementById('tambah').style.display = 'none';
        document.getElementById(tabName).style.display = 'block';
    }
    showTab('dashboard');
</script>
<div class="modal-backdrop" id="quickActions" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="quickTitle">
        <h3 id="quickTitle">Aksi cepat</h3>
        <p>Pilih fitur yang ingin Anda buka dengan cepat.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" data-close-modal="quickActions">Tutup</button>
            <button type="button" class="btn btn-primary" onclick="showTab('input'); closeModal('quickActions');">✏️ Input Ngaji</button>
            <button type="button" class="btn btn-primary" onclick="showTab('tambah'); closeModal('quickActions');">👶 Tambah Santri</button>
        </div>
    </div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>