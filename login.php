<?php
require_once __DIR__ . '/includes/db.php';

$db = getDbConnection();

$count = $db->querySingle("SELECT COUNT(*) FROM ustadz");
if ($count == 0) {
    $defaultPassword = password_hash('123', PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO ustadz (nama, gelar, username, password, role) VALUES (:nama, :gelar, :username, :password, :role)");
    $stmt->bindValue(':nama', 'Lina', SQLITE3_TEXT);
    $stmt->bindValue(':gelar', 'Ustadzah', SQLITE3_TEXT);
    $stmt->bindValue(':username', 'ustadz', SQLITE3_TEXT);
    $stmt->bindValue(':password', $defaultPassword, SQLITE3_TEXT);
    $stmt->bindValue(':role', 'admin', SQLITE3_TEXT);
    $stmt->execute();

    $stmt = $db->prepare("INSERT INTO ustadz (nama, gelar, username, password, role) VALUES (:nama, :gelar, :username, :password, :role)");
    $stmt->bindValue(':nama', 'Ahmad', SQLITE3_TEXT);
    $stmt->bindValue(':gelar', 'Ustadz', SQLITE3_TEXT);
    $stmt->bindValue(':username', 'ustadz2', SQLITE3_TEXT);
    $stmt->bindValue(':password', $defaultPassword, SQLITE3_TEXT);
    $stmt->bindValue(':role', 'ustadz', SQLITE3_TEXT);
    $stmt->execute();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $stmt = $db->prepare("SELECT * FROM ustadz WHERE username = :username LIMIT 1");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user && (password_verify($password, $user['password']) || $user['password'] === $password)) {
        if ($user['password'] === $password) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $db->prepare("UPDATE ustadz SET password = :password WHERE id = :id");
            $updateStmt->bindValue(':password', $newHash, SQLITE3_TEXT);
            $updateStmt->bindValue(':id', $user['id'], SQLITE3_INTEGER);
            $updateStmt->execute();
        }

        session_regenerate_id(true);
        $_SESSION['login'] = true;
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['gelar'] = $user['gelar'];
        $_SESSION['ustadz_id'] = $user['id'];
        $_SESSION['role'] = $user['role'] ?? 'ustadz';
        header('Location: index.php');
        exit;
    }

    $error = "Username atau password salah!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Monitoring Ngaji</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body { font-family: Arial; background: #2c7a4d; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 30px; border-radius: 10px; width: 320px; text-align: center; }
        h2 { color: #2c7a4d; margin-bottom: 20px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { background: #2c7a4d; color: white; padding: 12px; width: 100%; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #1e5a38; }
        .error { color: red; margin-bottom: 10px; padding: 10px; background: #fee; border-radius: 5px; }
        .info { margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2 class="panel-title" style="text-align:center;">📖 Monitoring Ngaji</h2>
        <p class="panel-subtitle" style="text-align:center;">Masuk untuk mengelola progres santri</p>
        <?php if($error): ?>
        <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <div class="info">
            <p>Username: <strong>ustadz</strong> | Password: <strong>123</strong></p>
            <p>Username: <strong>ustadz2</strong> | Password: <strong>123</strong></p>
        </div>
    </div>
<script src="assets/js/app.js"></script>
</body>
</html>