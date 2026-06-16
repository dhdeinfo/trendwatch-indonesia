<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$pageTitle = 'Grafik Tren';
$activeMenu = 'charts';

$categoryFilter = trim($_GET['category'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$selectedTrendId = (int) ($_GET['trend_id'] ?? 0);

$categories = $pdo->query('SELECT DISTINCT category FROM trends WHERE category IS NOT NULL AND category != "" ORDER BY category ASC')->fetchAll(PDO::FETCH_COLUMN);
$statuses = $pdo->query('SELECT DISTINCT status FROM trends WHERE status IS NOT NULL AND status != "" ORDER BY status ASC')->fetchAll(PDO::FETCH_COLUMN);
$allTrends = $pdo->query('SELECT id, trend_name FROM trends ORDER BY search_volume DESC, trend_name ASC')->fetchAll();

$where = [];
$params = [];

if ($categoryFilter !== '') {
    $where[] = 'category = :category';
    $params[':category'] = $categoryFilter;
}

if ($statusFilter !== '') {
    $where[] = 'status = :status';
    $params[':status'] = $statusFilter;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$trendStmt = $pdo->prepare("SELECT * FROM trends $whereSql ORDER BY search_volume DESC, seo_score DESC LIMIT 10");
$trendStmt->execute($params);
$filteredTrends = $trendStmt->fetchAll();

if ($selectedTrendId <= 0 && !empty($filteredTrends)) {
    $selectedTrendId = (int) $filteredTrends[0]['id'];
}

$selectedTrend = null;
if ($selectedTrendId > 0) {
    $selectedStmt = $pdo->prepare('SELECT * FROM trends WHERE id = :id');
    $selectedStmt->execute([':id' => $selectedTrendId]);
    $selectedTrend = $selectedStmt->fetch();
}

$volumeLabels = [];
$volumeData = [];
$seoLabels = [];
$seoData = [];
foreach ($filteredTrends as $trend) {
    $volumeLabels[] = $trend['trend_name'];
    $volumeData[] = (int) $trend['search_volume'];
    $seoLabels[] = $trend['trend_name'];
    $seoData[] = (int) $trend['seo_score'];
}

$categoryStmt = $pdo->prepare("SELECT category, COUNT(*) AS total FROM trends $whereSql GROUP BY category ORDER BY total DESC");
$categoryStmt->execute($params);
$categoryRows = $categoryStmt->fetchAll();
$categoryLabels = array_column($categoryRows, 'category');
$categoryTotals = array_map('intval', array_column($categoryRows, 'total'));

$statusStmt = $pdo->prepare("SELECT status, COUNT(*) AS total FROM trends $whereSql GROUP BY status ORDER BY total DESC");
$statusStmt->execute($params);
$statusRows = $statusStmt->fetchAll();
$statusLabels = array_column($statusRows, 'status');
$statusTotals = array_map('intval', array_column($statusRows, 'total'));

$historyLabels = [];
$historyVolumes = [];
if ($selectedTrendId > 0) {
    $historyStmt = $pdo->prepare('SELECT hour_label, volume FROM trend_histories WHERE trend_id = :trend_id ORDER BY id ASC');
    $historyStmt->execute([':trend_id' => $selectedTrendId]);
    $historyRows = $historyStmt->fetchAll();
    foreach ($historyRows as $row) {
        $historyLabels[] = $row['hour_label'];
        $historyVolumes[] = (int) $row['volume'];
    }
}

$compareTrendIds = array_map(function ($trend) { return (int) $trend['id']; }, array_slice($filteredTrends, 0, 4));
$comparisonLabels = [];
$comparisonDatasets = [];
foreach ($compareTrendIds as $trendId) {
    $nameStmt = $pdo->prepare('SELECT trend_name FROM trends WHERE id = :id');
    $nameStmt->execute([':id' => $trendId]);
    $trendName = $nameStmt->fetchColumn();

    $historyStmt = $pdo->prepare('SELECT hour_label, volume FROM trend_histories WHERE trend_id = :trend_id ORDER BY id ASC');
    $historyStmt->execute([':trend_id' => $trendId]);
    $rows = $historyStmt->fetchAll();

    $labels = [];
    $data = [];
    foreach ($rows as $row) {
        $labels[] = $row['hour_label'];
        $data[] = (int) $row['volume'];
    }

    if (empty($comparisonLabels) && !empty($labels)) {
        $comparisonLabels = $labels;
    }

    $comparisonDatasets[] = [
        'label' => $trendName,
        'data' => $data,
        'tension' => 0.35,
        'fill' => false,
    ];
}

$growthStmt = $pdo->prepare("SELECT id, trend_name, category, volume_text, search_volume, seo_score FROM trends $whereSql ORDER BY search_volume DESC LIMIT 10");
$growthStmt->execute($params);
$growthRows = $growthStmt->fetchAll();

foreach ($growthRows as &$row) {
    $historyStmt = $pdo->prepare('SELECT volume FROM trend_histories WHERE trend_id = :trend_id ORDER BY id ASC');
    $historyStmt->execute([':trend_id' => (int) $row['id']]);
    $volumes = array_map('intval', $historyStmt->fetchAll(PDO::FETCH_COLUMN));
    $first = $volumes[0] ?? 0;
    $last = !empty($volumes) ? $volumes[count($volumes) - 1] : 0;
    $row['first_volume'] = $first;
    $row['last_volume'] = $last;
    $row['growth'] = $last - $first;
    $row['growth_percent'] = $first > 0 ? round((($last - $first) / $first) * 100, 1) : 0;
}
unset($row);

usort($growthRows, function ($a, $b) {
    return $b['growth_percent'] <=> $a['growth_percent'];
});

$totalFiltered = count($filteredTrends);
$highestVolume = $filteredTrends[0]['search_volume'] ?? 0;
$highestVolumeText = $filteredTrends[0]['volume_text'] ?? '0+';
$avgSeo = $totalFiltered > 0 ? round(array_sum($seoData) / $totalFiltered) : 0;
$topGrowth = $growthRows[0]['growth_percent'] ?? 0;

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar">
        <div>
            <h1>Grafik Tren</h1>
            <p>Analisis visual untuk membandingkan volume, kategori, status, SEO score, dan pergerakan tren.</p>
        </div>
        <div class="admin-profile">
            <span><?= e(current_admin_name()) ?></span>
            <a href="<?= e(url('auth/logout.php')) ?>" class="btn btn-secondary">Logout</a>
        </div>
    </header>

    <section class="panel">
        <div class="panel-header with-actions">
            <div>
                <h2>Filter Grafik</h2>
                <span>Pilih data yang ingin dianalisis.</span>
            </div>
        </div>
        <form class="filter-form charts-filter" method="get">
            <select name="category">
                <option value="">Semua kategori</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category) ?>" <?= $categoryFilter === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status">
                <option value="">Semua status</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="trend_id">
                <option value="">Top tren otomatis</option>
                <?php foreach ($allTrends as $trend): ?>
                    <option value="<?= (int) $trend['id'] ?>" <?= $selectedTrendId === (int) $trend['id'] ? 'selected' : '' ?>><?= e($trend['trend_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">Terapkan</button>
            <a class="btn btn-secondary" href="<?= e(url('admin/charts.php')) ?>">Reset</a>
        </form>
    </section>

    <section class="stats-grid mt-16">
        <article class="stat-card">
            <span>Tren Terbaca</span>
            <strong><?= number_format($totalFiltered, 0, ',', '.') ?></strong>
            <p>Berdasarkan filter aktif</p>
        </article>
        <article class="stat-card">
            <span>Volume Tertinggi</span>
            <strong><?= e($highestVolumeText) ?></strong>
            <p><?= number_format((int) $highestVolume, 0, ',', '.') ?> pencarian</p>
        </article>
        <article class="stat-card">
            <span>Rata-rata SEO Score</span>
            <strong><?= (int) $avgSeo ?>%</strong>
            <p>Skor dari data terfilter</p>
        </article>
        <article class="stat-card">
            <span>Kenaikan Tertinggi</span>
            <strong><?= e((string) $topGrowth) ?>%</strong>
            <p>Dari histori tren</p>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="panel large">
            <div class="panel-header">
                <h2>Perbandingan Volume Tren</h2>
                <span>Top 10</span>
            </div>
            <canvas id="analyticsVolumeChart" height="130"></canvas>
        </article>
        <article class="panel">
            <div class="panel-header">
                <h2>Distribusi Kategori</h2>
                <span>Jumlah tren</span>
            </div>
            <canvas id="analyticsCategoryChart" height="180"></canvas>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="panel large">
            <div class="panel-header">
                <h2>Pergerakan Tren Pilihan</h2>
                <span><?= $selectedTrend ? e($selectedTrend['trend_name']) : 'Belum ada data' ?></span>
            </div>
            <canvas id="analyticsHistoryChart" height="130"></canvas>
        </article>
        <article class="panel">
            <div class="panel-header">
                <h2>Status Tren</h2>
                <span>Distribusi</span>
            </div>
            <canvas id="analyticsStatusChart" height="180"></canvas>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="panel large">
            <div class="panel-header">
                <h2>Komparasi Histori Top Tren</h2>
                <span>Top 4</span>
            </div>
            <canvas id="analyticsCompareChart" height="130"></canvas>
        </article>
        <article class="panel">
            <div class="panel-header">
                <h2>SEO Score Tren</h2>
                <span>Peluang konten</span>
            </div>
            <canvas id="analyticsSeoChart" height="180"></canvas>
        </article>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Ranking Kenaikan Tren</h2>
            <span>Berdasarkan histori volume</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tren</th>
                        <th>Kategori</th>
                        <th>Volume Awal</th>
                        <th>Volume Akhir</th>
                        <th>Kenaikan</th>
                        <th>SEO Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($growthRows)): ?>
                        <tr>
                            <td class="empty-cell" colspan="6">Belum ada data tren.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($growthRows as $trend): ?>
                        <tr>
                            <td>
                                <strong><?= e($trend['trend_name']) ?></strong>
                                <small><?= e($trend['volume_text']) ?></small>
                            </td>
                            <td><?= e($trend['category']) ?></td>
                            <td><?= number_format((int) $trend['first_volume'], 0, ',', '.') ?></td>
                            <td><?= number_format((int) $trend['last_volume'], 0, ',', '.') ?></td>
                            <td><strong><?= e((string) $trend['growth_percent']) ?>%</strong></td>
                            <td><?= (int) $trend['seo_score'] ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
window.trendwatchAnalytics = {
    volumeLabels: <?= json_encode($volumeLabels) ?>,
    volumeData: <?= json_encode($volumeData) ?>,
    seoLabels: <?= json_encode($seoLabels) ?>,
    seoData: <?= json_encode($seoData) ?>,
    categoryLabels: <?= json_encode($categoryLabels) ?>,
    categoryTotals: <?= json_encode($categoryTotals) ?>,
    statusLabels: <?= json_encode($statusLabels) ?>,
    statusTotals: <?= json_encode($statusTotals) ?>,
    historyLabels: <?= json_encode($historyLabels) ?>,
    historyVolumes: <?= json_encode($historyVolumes) ?>,
    comparisonLabels: <?= json_encode($comparisonLabels) ?>,
    comparisonDatasets: <?= json_encode($comparisonDatasets) ?>
};
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
