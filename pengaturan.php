<?php
require_once __DIR__ . '/includes/db.php';

if (!function_exists('isCurrentUserAdmin')) {
    function isCurrentUserAdmin(): bool {
        return !empty($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

requireLogin();

$db = getDbConnection();
$success = '';
$isAdmin = isCurrentUserAdmin();

// Update profil sendiri
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $stmt = $db->prepare("UPDATE ustadz SET nama = :nama, gelar = :gelar WHERE id = :id");
    $stmt->bindValue(':id', $_SESSION['ustadz_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':nama', $_POST['nama'], SQLITE3_TEXT);
    $stmt->bindValue(':gelar', $_POST['gelar'], SQLITE3_TEXT);
    $stmt->execute();
    
    $_SESSION['nama'] = $_POST['nama'];
    $_SESSION['gelar'] = $_POST['gelar'];
    $success = "Profil berhasil diupdate!";
}

// Tambah ustadz baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_ustadz'])) {
    if (!$isAdmin) {
        $success = "Hanya admin yang dapat menambah akun ustadz.";
    } else {
        $hashedPassword = password_hash((string)($_POST['password'] ?? ''), PASSWORD_DEFAULT);
        $role = in_array($_POST['role'] ?? 'ustadz', ['admin', 'ustadz'], true) ? $_POST['role'] : 'ustadz';

        $stmt = $db->prepare("INSERT INTO ustadz (nama, gelar, username, password, role) VALUES (:nama, :gelar, :username, :password, :role)");
        $stmt->bindValue(':nama', $_POST['nama'], SQLITE3_TEXT);
        $stmt->bindValue(':gelar', $_POST['gelar'], SQLITE3_TEXT);
        $stmt->bindValue(':username', $_POST['username'], SQLITE3_TEXT);
        $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
        $stmt->bindValue(':role', $role, SQLITE3_TEXT);
        $stmt->execute();
        $success = "Ustadz baru berhasil ditambahkan!";
    }
}

// Ambil data ustadz yang boleh dilihat
if ($isAdmin) {
    $ustadzList = $db->query("SELECT * FROM ustadz ORDER BY id");
} else {
    $ustadzList = $db->prepare("SELECT * FROM ustadz WHERE id = :id ORDER BY id");
    $ustadzList->bindValue(':id', $_SESSION['ustadz_id'], SQLITE3_INTEGER);
    $ustadzList = $ustadzList->execute();
}
$currentStmt = $db->prepare("SELECT * FROM ustadz WHERE id = :id");
$currentStmt->bindValue(':id', $_SESSION['ustadz_id'], SQLITE3_INTEGER);
$currentUser = $currentStmt->execute()->fetchArray(SQLITE3_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Monitoring Ngaji</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body { padding: 30px; }
        .container { max-width: 800px; margin: 0 auto; }
        .btn-back { margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>⚙️ Pengaturan</h2>
        <div class="subtitle">Kelola profil dan akun ustadz/ustadzah</div>
        
        <?php if($success): ?>
        <div class="success">✅ <?= $success ?></div>
        <?php endif; ?>
        
        <!-- Edit Profil Sendiri -->
        <div style="margin-bottom: 30px;">
            <h3>✏️ Edit Profil Saya</h3>
            <form method="POST">
                <div class="grid-2">
                    <div class="form-group">
                        <label>Gelar</label>
                        <select name="gelar">
                            <option value="Ustadz" <?= ($currentUser['gelar'] == 'Ustadz') ? 'selected' : '' ?>>Ustadz (Laki-laki)</option>
                            <option value="Ustadzah" <?= ($currentUser['gelar'] == 'Ustadzah') ? 'selected' : '' ?>>Ustadzah (Perempuan)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($currentUser['nama']) ?>" required>
                    </div>
                </div>
                <button type="submit" name="update_profil">💾 Simpan Profil</button>
            </form>
        </div>
        
        <!-- Daftar Ustadz -->
        <div style="margin-bottom: 30px;">
            <h3>👥 Daftar Ustadz/Ustadzah</h3>
            <table>
                <thead>
                    <tr><th>ID</th><th>Gelar</th><th>Nama</th><th>Username</th><th>Peran</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if ($isAdmin): while($row = $ustadzList->fetchArray(SQLITE3_ASSOC)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['gelar']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['role'] ?? 'ustadz') ?></td>
                        <td><?= ($row['id'] == $_SESSION['ustadz_id']) ? '✅ Aktif' : '' ?></td>
                    </tr>
                    <?php endwhile; else: while($row = $ustadzList->fetchArray(SQLITE3_ASSOC)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['gelar']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['role'] ?? 'ustadz') ?></td>
                        <td>✅ Aktif</td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Tambah Ustadz Baru -->
        <div style="margin-bottom: 30px;">
            <h3>➕ Tambah Ustadz/Ustadzah Baru</h3>
            <?php if ($isAdmin): ?>
            <form method="POST">
                <div class="grid-2">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="nama" placeholder="Contoh: Ahmad" required>
                    </div>
                    <div class="form-group">
                        <label>Gelar</label>
                        <select name="gelar">
                            <option value="Ustadz">Ustadz (Laki-laki)</option>
                            <option value="Ustadzah">Ustadzah (Perempuan)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="Username untuk login" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="text" name="password" placeholder="Password" value="123" required>
                    </div>
                    <div class="form-group">
                        <label>Peran</label>
                        <select name="role">
                            <option value="ustadz">Ustadz</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="tambah_ustadz">➕ Tambah Ustadz</button>
            </form>
            <?php else: ?>
            <div class="info">Hanya admin yang dapat menambah akun ustadz.</div>
            <?php endif; ?>
        </div>
        
        <a href="index.php" class="btn-back" style="display: inline-block; padding: 10px 20px;">← Kembali ke Dashboard</a>
    </div>
<script src="assets/js/app.js"></script>
</body>
</html>