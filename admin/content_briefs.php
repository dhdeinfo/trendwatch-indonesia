<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/seo_brief_generator.php';
require_admin();

$pageTitle = 'SEO Content Brief';
$activeMenu = 'content_briefs';
$flash = flash_get();

if (is_post()) {
    csrf_verify();
    $trendId = (int) ($_POST['trend_id'] ?? 0);

    $stmt = $pdo->prepare('SELECT * FROM trends WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $trendId]);
    $trend = $stmt->fetch();

    if (!$trend) {
        flash_set('danger', 'Data tren tidak ditemukan.');
        redirect('admin/content_briefs.php');
    }

    $brief = seo_generate_brief($trend);
    $briefId = seo_save_brief($pdo, $trendId, $brief);

    flash_set('success', 'SEO content brief berhasil dibuat.');
    redirect('admin/content_brief_detail.php?id=' . $briefId);
}

$keyword = trim($_GET['keyword'] ?? '');
$category = trim($_GET['category'] ?? '');

$sql = "SELECT t.*, cb.id AS brief_id, cb.selected_title, cb.priority_score, cb.updated_at AS brief_updated_at
        FROM trends t
        LEFT JOIN content_briefs cb ON cb.trend_id = t.id
        WHERE 1=1";
$params = [];

if ($keyword !== '') {
    $sql .= ' AND (t.trend_name LIKE :keyword OR t.related_keywords LIKE :keyword OR cb.selected_title LIKE :keyword)';
    $params[':keyword'] = '%' . $keyword . '%';
}

if ($category !== '') {
    $sql .= ' AND t.category = :category';
    $params[':category'] = $category;
}

$sql .= ' ORDER BY t.search_volume DESC, t.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$trends = $stmt->fetchAll();

$briefs = $pdo->query("SELECT cb.*, t.trend_name, t.category, t.search_volume, t.volume_text
    FROM content_briefs cb
    JOIN trends t ON t.id = cb.trend_id
    ORDER BY cb.updated_at DESC
    LIMIT 12")->fetchAll();

$categories = $pdo->query('SELECT DISTINCT category FROM trends ORDER BY category ASC')->fetchAll();
$totalBriefs = (int) $pdo->query('SELECT COUNT(*) FROM content_briefs')->fetchColumn();
$avgPriority = (int) round((float) ($pdo->query('SELECT AVG(priority_score) FROM content_briefs')->fetchColumn() ?: 0));
$topBrief = $pdo->query("SELECT cb.selected_title, cb.priority_score, t.trend_name
    FROM content_briefs cb
    JOIN trends t ON t.id = cb.trend_id
    ORDER BY cb.priority_score DESC, cb.updated_at DESC
    LIMIT 1")->fetch();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar">
        <div>
            <h1>SEO Content Brief Generator</h1>
            <p>Buat brief artikel SEO otomatis dari data tren. Mode ini gratis dan tidak memakai API AI.</p>
        </div>
        <div class="admin-profile">
            <span><?= e(current_admin_name()) ?></span>
            <a href="<?= e(url('auth/logout.php')) ?>" class="btn btn-secondary">Logout</a>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="stats-grid">
        <div class="stat-card">
            <span>Total Brief</span>
            <strong><?= number_format($totalBriefs, 0, ',', '.') ?></strong>
            <p>Brief yang sudah dibuat.</p>
        </div>
        <div class="stat-card">
            <span>Rata-rata Prioritas</span>
            <strong><?= $avgPriority ?>%</strong>
            <p>Skor peluang dari brief tersimpan.</p>
        </div>
        <div class="stat-card">
            <span>Top Brief</span>
            <strong><?= $topBrief ? (int) $topBrief['priority_score'] . '%' : '0%' ?></strong>
            <p><?= $topBrief ? e($topBrief['trend_name']) : 'Belum ada brief.' ?></p>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header with-actions">
            <div>
                <h2>Generate dari Data Tren</h2>
                <span>Pilih tren, lalu sistem membuat keyword, intent, judul, meta description, outline, FAQ, dan ide platform.</span>
            </div>
            <a href="<?= e(url('admin/sync_google_trends.php')) ?>" class="btn btn-secondary">Sync Google Trends</a>
        </div>

        <form class="filter-form wide" method="get" action="">
            <input type="text" name="keyword" value="<?= e($keyword) ?>" placeholder="Cari tren, keyword, atau judul brief">
            <select name="category">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $row): ?>
                    <option value="<?= e($row['category']) ?>" <?= $category === $row['category'] ? 'selected' : '' ?>><?= e($row['category']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="<?= e(url('admin/content_briefs.php')) ?>" class="btn btn-secondary">Reset</a>
        </form>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Daftar Tren untuk Brief</h2>
            <span><?= count($trends) ?> data</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tren</th>
                        <th>Volume</th>
                        <th>Kategori</th>
                        <th>Status Brief</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$trends): ?>
                        <tr><td colspan="6" class="empty-cell">Data belum tersedia.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($trends as $index => $trend): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <strong><?= e($trend['trend_name']) ?></strong>
                                <small><?= e($trend['related_keywords']) ?></small>
                            </td>
                            <td>
                                <strong><?= e($trend['volume_text']) ?></strong>
                                <small><?= number_format((int) $trend['search_volume'], 0, ',', '.') ?> pencarian</small>
                            </td>
                            <td><?= e($trend['category']) ?></td>
                            <td>
                                <?php if ($trend['brief_id']): ?>
                                    <span class="badge success">Sudah dibuat</span>
                                    <small>Update: <?= e($trend['brief_updated_at']) ?></small>
                                    <small>Prioritas: <?= (int) $trend['priority_score'] ?>%</small>
                                <?php else: ?>
                                    <span class="badge warning">Belum dibuat</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-group">
                                    <form method="post" action="" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="trend_id" value="<?= (int) $trend['id'] ?>">
                                        <button type="submit" class="btn btn-primary small"><?= $trend['brief_id'] ? 'Regenerate' : 'Generate' ?></button>
                                    </form>
                                    <?php if ($trend['brief_id']): ?>
                                        <a href="<?= e(url('admin/content_brief_detail.php?id=' . $trend['brief_id'])) ?>" class="btn btn-secondary small">Detail</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Brief Terbaru</h2>
            <span>12 brief terakhir</span>
        </div>
        <div class="brief-grid">
            <?php if (!$briefs): ?>
                <div class="empty-cell">Belum ada brief. Klik Generate pada salah satu tren.</div>
            <?php endif; ?>
            <?php foreach ($briefs as $brief): ?>
                <article class="brief-card">
                    <div class="brief-card-top">
                        <span class="badge info"><?= e($brief['category']) ?></span>
                        <strong><?= (int) $brief['priority_score'] ?>%</strong>
                    </div>
                    <h3><?= e($brief['selected_title']) ?></h3>
                    <p><?= e($brief['meta_description']) ?></p>
                    <small>Keyword: <?= e($brief['main_keyword']) ?></small>
                    <a href="<?= e(url('admin/content_brief_detail.php?id=' . $brief['id'])) ?>" class="btn btn-secondary small">Lihat Brief</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
