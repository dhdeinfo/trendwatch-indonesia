<?php

require_once __DIR__ . '/config/database.php';

$checks = [];
$checks[] = ['PDO SQLite aktif', extension_loaded('pdo_sqlite') ? 'OK' : 'Gagal'];
$checks[] = ['SQLite3 aktif', extension_loaded('sqlite3') ? 'OK' : 'Gagal'];
$checks[] = ['Folder database', is_dir(APP_ROOT . '/database') ? 'OK' : 'Gagal'];
$checks[] = ['File database', file_exists(APP_ROOT . '/database/trendwatch.sqlite') ? 'OK' : 'Gagal'];

$tables = ['admins', 'trends', 'trend_histories', 'content_briefs', 'sync_logs', 'app_settings', 'ai_generation_logs'];
foreach ($tables as $table) {
    try {
        $count = (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        $checks[] = ["Tabel {$table}", "OK ({$count} data)"];
    } catch (PDOException $e) {
        $checks[] = ["Tabel {$table}", 'Gagal'];
    }
}

$sampleTrends = [];
try {
    $sampleTrends = $pdo->query('SELECT trend_name, volume_text, category, status, source FROM trends ORDER BY search_volume DESC LIMIT 5')->fetchAll();
} catch (PDOException $e) {
    $sampleTrends = [];
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cek Koneksi - <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
</head>
<body class="setup-body">
    <main class="setup-card wide">
        <h1>Cek Koneksi SQLite</h1>
        <table class="simple-table">
            <thead>
                <tr><th>Item</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($checks as $check): ?>
                    <tr>
                        <td><?= e($check[0]) ?></td>
                        <td><span class="badge <?= strpos($check[1], 'OK') === 0 ? 'success' : 'danger' ?>"><?= e($check[1]) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Contoh Data Tren</h2>
        <table class="simple-table">
            <thead>
                <tr><th>Tren</th><th>Volume</th><th>Kategori</th><th>Status</th><th>Sumber</th></tr>
            </thead>
            <tbody>
                <?php foreach ($sampleTrends as $trend): ?>
                    <tr>
                        <td><?= e($trend['trend_name']) ?></td>
                        <td><?= e($trend['volume_text']) ?></td>
                        <td><?= e($trend['category']) ?></td>
                        <td><?= e($trend['status']) ?></td>
                        <td><?= e($trend['source'] ?? 'manual') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="mt-16">
            <a class="btn btn-primary" href="<?= e(url('auth/login.php')) ?>">Masuk ke Login</a>
            <a class="btn btn-secondary" href="<?= e(url('setup.php')) ?>">Jalankan Setup</a>
        </div>
    </main>
</body>
</html>
