<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/content_script_generator.php';
require_admin();

$pageTitle = 'Konten Siap Pakai';
$activeMenu = 'generated_contents';
$flash = flash_get();

$keyword = trim($_GET['keyword'] ?? '');
$type = trim($_GET['type'] ?? '');

$sql = "SELECT gc.*, cb.selected_title, cb.main_keyword, t.trend_name, t.category, t.volume_text
    FROM generated_contents gc
    JOIN content_briefs cb ON cb.id = gc.brief_id
    JOIN trends t ON t.id = gc.trend_id
    WHERE 1=1";
$params = [];

if ($keyword !== '') {
    $sql .= ' AND (gc.title LIKE :keyword OR gc.body LIKE :keyword OR cb.main_keyword LIKE :keyword OR t.trend_name LIKE :keyword)';
    $params[':keyword'] = '%' . $keyword . '%';
}

if ($type !== '' && in_array($type, cg_allowed_types(), true)) {
    $sql .= ' AND gc.content_type = :type';
    $params[':type'] = $type;
}

$sql .= ' ORDER BY gc.updated_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$contents = $stmt->fetchAll();

$totalContents = (int) $pdo->query('SELECT COUNT(*) FROM generated_contents')->fetchColumn();
$totalArticles = (int) $pdo->query("SELECT COUNT(*) FROM generated_contents WHERE content_type = 'article'")->fetchColumn();
$totalSocial = max(0, $totalContents - $totalArticles);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar">
        <div>
            <h1>Konten Siap Pakai</h1>
            <p>Kumpulan artikel, carousel, thread, dan script video pendek yang dibuat dari SEO Content Brief.</p>
        </div>
        <div class="admin-profile">
            <a href="<?= e(url('admin/content_briefs.php')) ?>" class="btn btn-primary">Buat dari SEO Brief</a>
            <a href="<?= e(url('auth/logout.php')) ?>" class="btn btn-secondary">Logout</a>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="stats-grid">
        <div class="stat-card">
            <span>Total Konten</span>
            <strong><?= number_format($totalContents, 0, ',', '.') ?></strong>
            <p>Semua konten yang sudah digenerate.</p>
        </div>
        <div class="stat-card">
            <span>Artikel SEO</span>
            <strong><?= number_format($totalArticles, 0, ',', '.') ?></strong>
            <p>Draft artikel siap dikembangkan.</p>
        </div>
        <div class="stat-card">
            <span>Konten Sosial</span>
            <strong><?= number_format($totalSocial, 0, ',', '.') ?></strong>
            <p>Carousel, video pendek, dan thread.</p>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header with-actions">
            <div>
                <h2>Filter Konten</h2>
                <span>Cari berdasarkan judul, keyword, tren, atau jenis konten.</span>
            </div>
        </div>
        <form class="filter-form wide" method="get" action="">
            <input type="text" name="keyword" value="<?= e($keyword) ?>" placeholder="Cari konten, keyword, atau tren">
            <select name="type">
                <option value="">Semua Jenis</option>
                <?php foreach (cg_allowed_types() as $item): ?>
                    <option value="<?= e($item) ?>" <?= $type === $item ? 'selected' : '' ?>><?= e(cg_type_label($item)) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="<?= e(url('admin/generated_contents.php')) ?>" class="btn btn-secondary">Reset</a>
        </form>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Daftar Konten</h2>
            <span><?= count($contents) ?> data</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Konten</th>
                        <th>Jenis</th>
                        <th>Tren</th>
                        <th>Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$contents): ?>
                        <tr><td colspan="6" class="empty-cell">Belum ada konten. Buka SEO Brief lalu klik tombol generator.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($contents as $index => $content): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <strong><?= e($content['title']) ?></strong>
                                <small><?= e(cg_limit_text($content['body'], 160)) ?></small>
                            </td>
                            <td><span class="badge info"><?= e(cg_type_label($content['content_type'])) ?></span></td>
                            <td>
                                <strong><?= e($content['trend_name']) ?></strong>
                                <small><?= e($content['category']) ?> · <?= e($content['volume_text']) ?></small>
                            </td>
                            <td><?= e($content['updated_at']) ?></td>
                            <td><a href="<?= e(url('admin/generated_content_detail.php?id=' . $content['id'])) ?>" class="btn btn-secondary small">Detail</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
