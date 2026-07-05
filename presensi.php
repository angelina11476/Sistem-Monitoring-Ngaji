<?php
require_once __DIR__ . '/includes/db.php';

requireLogin();

$db = getDbConnection();

// Proses simpan presensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_presensi'])) {
    $tanggal = $_POST['tanggal'];
    
    foreach ($_POST['presensi'] as $anak_id => $status) {
        // Cek apakah sudah ada presensi untuk tanggal ini
        $check = $db->prepare("SELECT id FROM presensi WHERE anak_id = :anak_id AND tanggal = :tanggal");
        $check->bindValue(':anak_id', $anak_id, SQLITE3_INTEGER);
        $check->bindValue(':tanggal', $tanggal, SQLITE3_TEXT);
        $exists = $check->execute()->fetchArray();
        
        $keterangan = $_POST['keterangan'][$anak_id] ?? '';
        
        if ($exists) {
            // Update
            $stmt = $db->prepare("UPDATE presensi SET status = :status, keterangan = :keterangan WHERE anak_id = :anak_id AND tanggal = :tanggal");
        } else {
            // Insert
            $stmt = $db->prepare("INSERT INTO presensi (anak_id, tanggal, status, keterangan) VALUES (:anak_id, :tanggal, :status, :keterangan)");
        }
        $stmt->bindValue(':anak_id', $anak_id, SQLITE3_INTEGER);
        $stmt->bindValue(':tanggal', $tanggal, SQLITE3_TEXT);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':keterangan', $keterangan, SQLITE3_TEXT);
        $stmt->execute();
    }
    $success = "Presensi berhasil disimpan!";
}

// Ambil parameter tanggal (default hari ini)
$tanggal_display = $_GET['tanggal'] ?? date('Y-m-d');
$tanggal_sekarang = date('Y-m-d');

// Ambil daftar anak
$anakList = $db->query("SELECT * FROM anak ORDER BY nama");

// Ambil presensi untuk tanggal yang dipilih
$presensiData = $db->prepare("SELECT anak_id, status, keterangan FROM presensi WHERE tanggal = :tanggal");
$presensiData->bindValue(':tanggal', $tanggal_display, SQLITE3_TEXT);
$result = $presensiData->execute();
$presensi_hari_ini = [];
while($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $presensi_hari_ini[$row['anak_id']] = [
        'status' => $row['status'],
        'keterangan' => $row['keterangan']
    ];
}

// Ambil rekap presensi bulan ini
$bulan_ini = date('Y-m', strtotime($tanggal_display));
$rekapStmt = $db->prepare("
    SELECT 
        anak_id,
        SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN status = 'alpa' THEN 1 ELSE 0 END) as alpa,
        COUNT(*) as total
    FROM presensi 
    WHERE strftime('%Y-%m', tanggal) = :bulan
    GROUP BY anak_id
");
$rekapStmt->bindValue(':bulan', $bulan_ini, SQLITE3_TEXT);
$rekapResult = $rekapStmt->execute();
$rekapData = [];
while($row = $rekapResult->fetchArray(SQLITE3_ASSOC)) {
    $rekapData[$row['anak_id']] = [
        'hadir' => $row['hadir'],
        'izin' => $row['izin'],
        'alpa' => $row['alpa'],
        'total' => $row['total']
    ];
}

// Hitung statistik hari ini
$statHadir = 0;
$statIzin = 0;
$statAlpa = 0;
$anakList->reset();
while($anak = $anakList->fetchArray(SQLITE3_ASSOC)) {
    $status = $presensi_hari_ini[$anak['id']]['status'] ?? 'belum';
    if ($status == 'hadir') $statHadir++;
    elseif ($status == 'izin') $statIzin++;
    elseif ($status == 'alpa') $statAlpa++;
}
$totalSantri = $db->querySingle("SELECT COUNT(*) FROM anak");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Santri - Monitoring Ngaji</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body { padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        /* Header */
        .header {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .header h1 {
            color: #1e5a38;
            font-size: 24px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
        }
        
        /* Stats Cards */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .hadir { color: #28a745; }
        .izin { color: #ffc107; }
        .alpa { color: #dc3545; }
        .belum { color: #6c757d; }
        
        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .card h3 {
            color: #1e5a38;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        /* Date Navigation */
        .date-nav {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .date-nav input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .btn {
            background: #2c7a4d;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .btn:hover {
            background: #1e5a38;
        }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            border: 1px solid #e0e0e0;
            padding: 12px;
            text-align: left;
        }
        
        th {
            background: #2c7a4d;
            color: white;
            font-weight: 600;
        }
        
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-hadir {
            background: #28a745;
            color: white;
        }
        
        .status-izin {
            background: #ffc107;
            color: #333;
        }
        
        .status-alpa {
            background: #dc3545;
            color: white;
        }
        
        select {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        
        input[type="text"] {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            width: 100%;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .rekap-table {
            font-size: 14px;
        }
        
        .progress-bar {
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            width: 80px;
            display: inline-block;
        }
        
        .progress-fill {
            background: #28a745;
            height: 8px;
            border-radius: 10px;
        }
        
        @media (max-width: 768px) {
            th, td { font-size: 12px; padding: 8px; }
            .stats { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>📋 Presensi Harian Santri</h1>
                <p>👤 <?= $_SESSION['gelar'] ?> <?= $_SESSION['nama'] ?> | 📅 <?= date('d/m/Y', strtotime($tanggal_display)) ?></p>
            </div>
            <div>
                <a href="index.php" class="btn-back">← Dashboard</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <?php if(isset($success)): ?>
        <div class="success">✅ <?= $success ?></div>
        <?php endif; ?>
        
        <!-- Statistik Hari Ini -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number hadir"><?= $statHadir ?></div>
                <div class="stat-label">✅ Hadir</div>
            </div>
            <div class="stat-card">
                <div class="stat-number izin"><?= $statIzin ?></div>
                <div class="stat-label">📝 Izin</div>
            </div>
            <div class="stat-card">
                <div class="stat-number alpa"><?= $statAlpa ?></div>
                <div class="stat-label">❌ Alpa</div>
            </div>
            <div class="stat-card">
                <div class="stat-number belum"><?= $totalSantri - ($statHadir + $statIzin + $statAlpa) ?></div>
                <div class="stat-label">⏳ Belum Diisi</div>
            </div>
        </div>
        
        <!-- Pilih Tanggal -->
        <div class="card">
            <div class="toolbar">
                <div class="panel-title" style="margin-bottom:0;">📅 Pilih Tanggal</div>
                <div class="date-nav">
                    <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                        <input type="date" name="tanggal" value="<?= $tanggal_display ?>">
                        <button type="submit" class="btn btn-primary">📅 Tampilkan</button>
                    </form>
                    <?php if($tanggal_display != $tanggal_sekarang): ?>
                    <a href="presensi.php" class="btn" style="background: #6c757d;">📆 Hari Ini</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Form Presensi -->
        <div class="card">
            <h3>📝 Form Presensi Santri</h3>
            <form method="POST">
                <input type="hidden" name="tanggal" value="<?= $tanggal_display ?>">
                
                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Santri</th>
                                <th>Level</th>
                                <th>Status</th>
                                <th>Keterangan (jika izin)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $anakList->reset();
                            while($anak = $anakList->fetchArray(SQLITE3_ASSOC)): 
                                $status_skrg = $presensi_hari_ini[$anak['id']]['status'] ?? 'hadir';
                                $keterangan_skrg = $presensi_hari_ini[$anak['id']]['keterangan'] ?? '';
                                $isQuran = ($anak['level'] == 'Al-Qur\'an (Juz 1-30)');
                                $levelText = $isQuran ? '📖 Al-Qur\'an' : '🔤 Juz Amma + Hijaiyah';
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($anak['nama']) ?></strong></td>
                                <td><?= $levelText ?></td>
                                <td>
                                    <select name="presensi[<?= $anak['id'] ?>]" class="status-select">
                                        <option value="hadir" <?= $status_skrg == 'hadir' ? 'selected' : '' ?>>✅ Hadir</option>
                                        <option value="izin" <?= $status_skrg == 'izin' ? 'selected' : '' ?>>📝 Izin</option>
                                        <option value="alpa" <?= $status_skrg == 'alpa' ? 'selected' : '' ?>>❌ Alpa</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="keterangan[<?= $anak['id'] ?>]" value="<?= htmlspecialchars($keterangan_skrg) ?>" placeholder="Alasan izin (jika izin)">
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <button type="submit" name="simpan_presensi" class="btn btn-primary" style="margin-top: 20px;">💾 Simpan Presensi</button>
            </form>
        </div>
        
        <!-- Rekap Presensi Bulan Ini -->
        <div class="card">
            <h3>📊 Rekap Presensi Bulan <?= date('F Y', strtotime($tanggal_display)) ?></h3>
            <div class="table-shell">
                <table class="rekap-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Santri</th>
                            <th>✅ Hadir</th>
                            <th>📝 Izin</th>
                            <th>❌ Alpa</th>
                            <th>Total</th>
                            <th>Presentase Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $anakList->reset();
                        while($anak = $anakList->fetchArray(SQLITE3_ASSOC)): 
                            $hadir = $rekapData[$anak['id']]['hadir'] ?? 0;
                            $izin = $rekapData[$anak['id']]['izin'] ?? 0;
                            $alpa = $rekapData[$anak['id']]['alpa'] ?? 0;
                            $total = $hadir + $izin + $alpa;
                            $persen = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($anak['nama']) ?></strong></td>
                            <td style="color: #28a745; font-weight: bold;"><?= $hadir ?></td>
                            <td style="color: #ffc107;"><?= $izin ?></td>
                            <td style="color: #dc3545;"><?= $alpa ?></td>
                            <td><?= $total ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $persen ?>%;"></div>
                                    </div>
                                    <span><?= $persen ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Tombol Export Excel -->
        <div style="text-align: right;">
            <a href="export_presensi.php?bulan=<?= $bulan_ini ?>" class="btn" style="background: #28a745;">📊 Export ke Excel</a>
        </div>
    </div>
</body>
</html>