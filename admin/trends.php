<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$pageTitle = 'Data Tren';
$activeMenu = 'trends';

$keyword = trim($_GET['keyword'] ?? '');
$category = trim($_GET['category'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = 'SELECT * FROM trends WHERE 1=1';
$params = [];

if ($keyword !== '') {
    $sql .= ' AND (trend_name LIKE :keyword OR related_keywords LIKE :keyword OR content_angle LIKE :keyword)';
    $params[':keyword'] = '%' . $keyword . '%';
}

if ($category !== '') {
    $sql .= ' AND category = :category';
    $params[':category'] = $category;
}

if ($status !== '') {
    $sql .= ' AND status = :status';
    $params[':status'] = $status;
}

$sql .= ' ORDER BY search_volume DESC, created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$trends = $stmt->fetchAll();

$categories = $pdo->query('SELECT DISTINCT category FROM trends ORDER BY category ASC')->fetchAll();
$statuses = ['Aktif', 'Menurun', 'Selesai', 'Pantauan'];
$flash = flash_get();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar">
        <div>
            <h1>Data Tren</h1>
            <p>Tambah, edit, lihat, dan hapus data tren pencarian.</p>
        </div>
        <div class="admin-profile">
            <span><?= e(current_admin_name()) ?></span>
            <a href="<?= e(url('auth/logout.php')) ?>" class="btn btn-secondary">Logout</a>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="panel">
        <div class="panel-header with-actions">
            <div>
                <h2>Filter Tren</h2>
                <span>Temukan data berdasarkan kata kunci, kategori, atau status.</span>
            </div>
            <div class="action-group"><a href="<?= e(url('admin/sync_google_trends.php')) ?>" class="btn btn-secondary">Sync Google Trends</a><a href="<?= e(url('admin/trend_add.php')) ?>" class="btn btn-primary">+ Tambah Tren</a></div>
        </div>

        <form class="filter-form wide" method="get" action="">
            <input type="text" name="keyword" value="<?= e($keyword) ?>" placeholder="Cari nama tren atau keyword terkait">
            <select name="category">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $row): ?>
                    <option value="<?= e($row['category']) ?>" <?= $category === $row['category'] ? 'selected' : '' ?>><?= e($row['category']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status">
                <option value="">Semua Status</option>
                <?php foreach ($statuses as $item): ?>
                    <option value="<?= e($item) ?>" <?= $status === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="<?= e(url('admin/trends.php')) ?>" class="btn btn-secondary">Reset</a>
        </form>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Daftar Tren</h2>
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
                        <th>Mulai</th>
                        <th>Status</th>
                        <th>SEO</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$trends): ?>
                        <tr><td colspan="8" class="empty-cell">Data belum tersedia.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($trends as $index => $trend): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <strong><?= e($trend['trend_name']) ?></strong>
                                <small><?= e($trend['related_keywords']) ?></small>
                                <small>Sumber: <?= e($trend['source'] ?? 'manual') ?></small>
                            </td>
                            <td>
                                <strong><?= e($trend['volume_text']) ?></strong>
                                <small><?= number_format((int) $trend['search_volume'], 0, ',', '.') ?> pencarian</small>
                            </td>
                            <td><?= e($trend['category']) ?></td>
                            <td><?= e($trend['started_text']) ?></td>
                            <td><span class="badge <?= e(badge_class($trend['status'])) ?>"><?= e($trend['status']) ?></span></td>
                            <td><?= (int) $trend['seo_score'] ?>%</td>
                            <td>
                                <div class="action-group">
                                    <a href="<?= e(url('admin/trend_detail.php?id=' . $trend['id'])) ?>" class="btn btn-secondary small">Detail</a>
                                    <a href="<?= e(url('admin/trend_edit.php?id=' . $trend['id'])) ?>" class="btn btn-primary small">Edit</a>
                                    <a href="<?= e(url('admin/trend_delete.php?id=' . $trend['id'])) ?>" class="btn btn-danger small">Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
