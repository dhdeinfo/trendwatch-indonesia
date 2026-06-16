<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';

$totalTrends = (int) $pdo->query('SELECT COUNT(*) FROM trends')->fetchColumn();
$activeTrends = (int) $pdo->query("SELECT COUNT(*) FROM trends WHERE status = 'Aktif'")->fetchColumn();
$totalVolume = (int) $pdo->query('SELECT COALESCE(SUM(search_volume), 0) FROM trends')->fetchColumn();
$avgVolume = $totalTrends > 0 ? round($totalVolume / $totalTrends) : 0;

$topTrends = $pdo->query('SELECT * FROM trends ORDER BY search_volume DESC LIMIT 6')->fetchAll();
$fastTrends = $pdo->query('SELECT * FROM trends ORDER BY seo_score DESC, search_volume DESC LIMIT 5')->fetchAll();
$categoryRows = $pdo->query('SELECT category, COUNT(*) AS total FROM trends GROUP BY category ORDER BY total DESC')->fetchAll();
$lastSync = $pdo->query('SELECT * FROM sync_logs ORDER BY id DESC LIMIT 1')->fetch();
$googleTrendsCount = (int) $pdo->query("SELECT COUNT(*) FROM trends WHERE source = 'google_trends'")->fetchColumn();

$trendLabels = [];
$trendVolumes = [];
foreach ($topTrends as $trend) {
    $trendLabels[] = $trend['trend_name'];
    $trendVolumes[] = (int) $trend['search_volume'];
}

$categoryLabels = [];
$categoryTotals = [];
foreach ($categoryRows as $row) {
    $categoryLabels[] = $row['category'];
    $categoryTotals[] = (int) $row['total'];
}

$firstTrendId = $topTrends[0]['id'] ?? 0;
$historyLabels = [];
$historyVolumes = [];
if ($firstTrendId) {
    $stmt = $pdo->prepare('SELECT hour_label, volume FROM trend_histories WHERE trend_id = :trend_id ORDER BY id ASC');
    $stmt->execute([':trend_id' => $firstTrendId]);
    $histories = $stmt->fetchAll();
    foreach ($histories as $history) {
        $historyLabels[] = $history['hour_label'];
        $historyVolumes[] = (int) $history['volume'];
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar">
        <div>
            <h1>Dashboard Tren</h1>
            <p>Pantau ringkasan tren pencarian dan peluang konten SEO.</p>
        </div>
        <div class="admin-profile">
            <a href="<?= e(url('admin/sync_google_trends.php')) ?>" class="btn btn-primary">Sync Google Trends</a>
            <span><?= e(current_admin_name()) ?></span>
            <a href="<?= e(url('auth/logout.php')) ?>" class="btn btn-secondary">Logout</a>
        </div>
    </header>

    <section class="stats-grid">
        <article class="stat-card">
            <span>Total Tren</span>
            <strong><?= number_format($totalTrends, 0, ',', '.') ?></strong>
            <p>Data tren tersimpan</p>
        </article>
        <article class="stat-card">
            <span>Tren Aktif</span>
            <strong><?= number_format($activeTrends, 0, ',', '.') ?></strong>
            <p>Sedang berlangsung</p>
        </article>
        <article class="stat-card">
            <span>Total Volume</span>
            <strong><?= number_format($totalVolume, 0, ',', '.') ?></strong>
            <p>Akumulasi pencarian</p>
        </article>
        <article class="stat-card">
            <span>Rata-rata Volume</span>
            <strong><?= number_format($avgVolume, 0, ',', '.') ?></strong>
            <p>Per tren</p>
        </article>
        <article class="stat-card">
            <span>Dari Google Trends</span>
            <strong><?= number_format($googleTrendsCount, 0, ',', '.') ?></strong>
            <p><?= $lastSync ? 'Sync terakhir ' . e(date('d/m H:i', strtotime($lastSync['created_at']))) : 'Belum ada sync' ?></p>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="panel large">
            <div class="panel-header">
                <h2>Volume Tren Teratas</h2>
                <span>Top 6</span>
            </div>
            <canvas id="volumeChart" height="130"></canvas>
        </article>

        <article class="panel">
            <div class="panel-header">
                <h2>Kategori Tren</h2>
                <span>Distribusi</span>
            </div>
            <canvas id="categoryChart" height="180"></canvas>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="panel large">
            <div class="panel-header">
                <h2>Pergerakan Top Tren</h2>
                <span>Simulasi 24 jam</span>
            </div>
            <canvas id="historyChart" height="130"></canvas>
        </article>

        <article class="panel">
            <div class="panel-header">
                <h2>Peluang Konten</h2>
                <span>SEO Score</span>
            </div>
            <div class="opportunity-list">
                <?php foreach ($fastTrends as $trend): ?>
                    <div class="opportunity-item">
                        <div>
                            <strong><?= e($trend['trend_name']) ?></strong>
                            <span><?= e($trend['category']) ?> · <?= e($trend['volume_text']) ?></span>
                        </div>
                        <b><?= (int) $trend['seo_score'] ?>%</b>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Daftar Tren Prioritas</h2>
            <a href="<?= e(url('admin/trends.php')) ?>" class="btn btn-primary small">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tren</th>
                        <th>Volume</th>
                        <th>Kategori</th>
                        <th>Mulai</th>
                        <th>Status</th>
                        <th>SEO Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topTrends as $trend): ?>
                        <tr>
                            <td>
                                <strong><?= e($trend['trend_name']) ?></strong>
                                <small><?= e($trend['content_angle']) ?></small>
                            </td>
                            <td><?= e($trend['volume_text']) ?></td>
                            <td><?= e($trend['category']) ?></td>
                            <td><?= e($trend['started_text']) ?></td>
                            <td><span class="badge <?= e(badge_class($trend['status'])) ?>"><?= e($trend['status']) ?></span></td>
                            <td><?= (int) $trend['seo_score'] ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
window.trendwatchCharts = {
    trendLabels: <?= json_encode($trendLabels) ?>,
    trendVolumes: <?= json_encode($trendVolumes) ?>,
    categoryLabels: <?= json_encode($categoryLabels) ?>,
    categoryTotals: <?= json_encode($categoryTotals) ?>,
    historyLabels: <?= json_encode($historyLabels) ?>,
    historyVolumes: <?= json_encode($historyVolumes) ?>
};
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
