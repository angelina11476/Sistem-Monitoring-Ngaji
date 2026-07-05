<?php
require_once __DIR__ . '/includes/db.php';

requireLogin();

$db = getDbConnection();

$anak_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data anak
$stmt = $db->prepare("SELECT * FROM anak WHERE id = :id");
$stmt->bindValue(':id', $anak_id, SQLITE3_INTEGER);
$anak = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$anak) {
    header('Location: index.php');
    exit;
}

// Ambil riwayat progres dengan join ustadz
$riwayat = $db->prepare("
    SELECT p.*, u.nama as ustadz_nama, u.gelar as ustadz_gelar 
    FROM progres p 
    LEFT JOIN ustadz u ON p.ustadz_id = u.id 
    WHERE p.anak_id = :id 
    ORDER BY p.tanggal DESC
");
$riwayat->bindValue(':id', $anak_id, SQLITE3_INTEGER);
$data = $riwayat->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat - <?= htmlspecialchars($anak['nama']) ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body { padding: 30px; }
        .container { max-width: 1200px; margin: 0 auto; }

        h2 {
            color: #1e5a38;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        /* Tombol Share */
        .btn-group {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .btn-share {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #25D366;
            color: white;
            padding: 10px 20px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-share:hover {
            background: #128C7E;
            transform: translateY(-2px);
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-back:hover {
            background: #5a6268;
        }

        /* Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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
        .btn-edit {
            background: #ffc107;
            color: #333;
            padding: 5px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-edit:hover {
            background: #e0a800;
        }
        .kelancaran {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        .lancar-5 { background: #28a745; color: white; }
        .lancar-4 { background: #20c997; color: white; }
        .lancar-3 { background: #ffc107; }
        .lancar-2 { background: #fd7e14; color: white; }
        .lancar-1 { background: #dc3545; color: white; }
        .juz-badge {
            background: #e9ecef;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .ustadz-badge {
            background: #e3f2fd;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            color: #1565c0;
        }

        @media (max-width: 768px) {
            .container { padding: 15px; }
            th, td { font-size: 12px; padding: 8px; }
            .btn-group { justify-content: center; }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📜 Riwayat Ngaji</h2>
    <div class="subtitle">
        <strong><?= htmlspecialchars($anak['nama']) ?></strong> •
        <?= ($anak['level'] == 'Al-Qur\'an (Juz 1-30)') ? '📖 Al-Qur\'an (Juz 1-30)' : '🔤 Juz Amma + Hijaiyah' ?>
    </div>

    <!-- Tombol Share WhatsApp -->
    <div class="btn-group">
        <a href="share_wa.php?id=<?= $anak_id ?>&type=progres" class="btn-share">
            📱 Share Progres Terakhir via WA
        </a>
        <a href="share_wa.php?id=<?= $anak_id ?>&type=riwayat" class="btn-share">
            📊 Share Riwayat Lengkap via WA
        </a>
        <a href="index.php" class="btn-back">
            ← Kembali ke Dashboard
        </a>
    </div>

    <!-- Tabel Riwayat -->
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Juz</th>
                    <th>Surah</th>
                    <th>Progres</th>
                    <th>Kelancaran</th>
                    <th>Catatan</th>
                    <th>Pengajar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $data->fetchArray(SQLITE3_ASSOC)): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                    <td>
                        <?php if($row['juz'] == 0): ?>
                            <span class="juz-badge">🎯 Juz Amma (0)</span>
                        <?php else: ?>
                            Juz <?= $row['juz'] ?>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['surah']) ?></td>
                    <td><?= $row['halaman'] ? "Halaman {$row['halaman']}" : "Ayat {$row['ayat']}" ?></td>
                    <td>
                        <span class="kelancaran lancar-<?= $row['kelancaran'] ?>">
                            <?= str_repeat('⭐', $row['kelancaran']) ?> (<?= $row['kelancaran'] ?>/5)
                        </span>
                    </td>
                    <td><?= nl2br(htmlspecialchars($row['catatan'] ?? '-')) ?></td>
                    <td>
                        <span class="ustadz-badge">
                            👤 <?= htmlspecialchars($row['ustadz_gelar'] ?? 'Ustadz') ?> <?= htmlspecialchars($row['ustadz_nama'] ?? '-') ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn-edit">✏️ Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>