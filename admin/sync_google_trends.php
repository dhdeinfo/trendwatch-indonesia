<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/google_trends_sync.php';
require_admin();

$pageTitle = 'Sync Google Trends';
$activeMenu = 'sync_google_trends';

$result = null;
$customUrl = trim($_POST['feed_url'] ?? '');

if (is_post()) {
    csrf_verify();
    $result = gt_sync_google_trends($pdo, $customUrl !== '' ? $customUrl : null);
}

$lastLogs = $pdo->query('SELECT * FROM sync_logs ORDER BY id DESC LIMIT 10')->fetchAll();
$googleTrendsCount = (int) $pdo->query("SELECT COUNT(*) FROM trends WHERE source = 'google_trends'")->fetchColumn();
$lastSync = $pdo->query('SELECT * FROM sync_logs ORDER BY id DESC LIMIT 1')->fetch();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar">
        <div>
            <h1>Sync Google Trends</h1>
            <p>Ambil data tren Indonesia dari RSS Google Trends, lalu simpan ke SQLite.</p>
        </div>
        <div class="admin-profile">
            <span><?= e(current_admin_name()) ?></span>
            <a href="<?= e(url('auth/logout.php')) ?>" class="btn btn-secondary">Logout</a>
        </div>
    </header>

    <?php if ($result): ?>
        <div class="alert <?= $result['ok'] ? 'alert-success' : 'alert-danger' ?>">
            <?= e($result['message']) ?>
        </div>
    <?php endif; ?>

    <section class="stats-grid">
        <article class="stat-card">
            <span>Data Google Trends</span>
            <strong><?= number_format($googleTrendsCount, 0, ',', '.') ?></strong>
            <p>Tren dari sinkronisasi RSS</p>
        </article>
        <article class="stat-card">
            <span>Feed Default</span>
            <strong>ID</strong>
            <p>Wilayah Indonesia</p>
        </article>
        <article class="stat-card">
            <span>Sinkron Terakhir</span>
            <strong><?= $lastSync ? e(date('H:i', strtotime($lastSync['created_at']))) : '-' ?></strong>
            <p><?= $lastSync ? e(date('d/m/Y', strtotime($lastSync['created_at']))) : 'Belum ada log' ?></p>
        </article>
        <article class="stat-card">
            <span>Status Terakhir</span>
            <strong><?= $lastSync ? e($lastSync['status']) : '-' ?></strong>
            <p><?= $lastSync ? e($lastSync['source_name']) : 'Belum disinkronkan' ?></p>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="panel large">
            <div class="panel-header">
                <h2>Ambil Data Real-time</h2>
                <span>Google Trends RSS</span>
            </div>

            <form method="post" class="data-form">
                <?= csrf_field() ?>
                <label for="feed_url">Feed URL opsional</label>
                <input type="url" id="feed_url" name="feed_url" placeholder="Kosongkan untuk memakai feed default Indonesia" value="<?= e($customUrl) ?>">
                <p class="muted">Default yang dipakai: https://trends.google.com/trending/rss?geo=ID</p>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Sync Sekarang</button>
                    <a href="<?= e(url('admin/trends.php')) ?>" class="btn btn-secondary">Lihat Data Tren</a>
                    <a href="<?= e(url('admin/charts.php')) ?>" class="btn btn-secondary">Lihat Grafik</a>
                </div>
            </form>
        </article>

        <article class="panel">
            <div class="panel-header">
                <h2>Hasil Sync</h2>
                <span>Ringkasan</span>
            </div>
            <?php if ($result): ?>
                <div class="sync-summary">
                    <div><span>Terambil</span><strong><?= (int) $result['fetched'] ?></strong></div>
                    <div><span>Baru</span><strong><?= (int) $result['inserted'] ?></strong></div>
                    <div><span>Update</span><strong><?= (int) $result['updated'] ?></strong></div>
                </div>
                <div class="source-box">
                    <strong>Sumber</strong>
                    <p><?= e($result['source_url']) ?></p>
                </div>
            <?php else: ?>
                <p class="muted">Klik tombol <strong>Sync Sekarang</strong> untuk mengambil data tren terbaru.</p>
            <?php endif; ?>
        </article>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Riwayat Sinkronisasi</h2>
            <span>10 log terakhir</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Terambil</th>
                        <th>Baru</th>
                        <th>Update</th>
                        <th>Pesan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$lastLogs): ?>
                        <tr><td colspan="6" class="empty-cell">Belum ada riwayat sinkronisasi.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($lastLogs as $log): ?>
                        <tr>
                            <td><?= e(date('d/m/Y H:i', strtotime($log['created_at']))) ?></td>
                            <td><span class="badge <?= $log['status'] === 'Berhasil' ? 'success' : 'danger' ?>"><?= e($log['status']) ?></span></td>
                            <td><?= number_format((int) $log['total_fetched'], 0, ',', '.') ?></td>
                            <td><?= number_format((int) $log['total_inserted'], 0, ',', '.') ?></td>
                            <td><?= number_format((int) $log['total_updated'], 0, ',', '.') ?></td>
                            <td>
                                <?= e($log['message']) ?>
                                <small><?= e($log['source_url']) ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
