<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/content_script_generator.php';
require_admin();

$pageTitle = 'Detail Konten Siap Pakai';
$activeMenu = 'generated_contents';
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT gc.*, cb.selected_title, cb.main_keyword, cb.meta_description, cb.search_intent, cb.target_audience, t.trend_name, t.category, t.volume_text
    FROM generated_contents gc
    JOIN content_briefs cb ON cb.id = gc.brief_id
    JOIN trends t ON t.id = gc.trend_id
    WHERE gc.id = :id
    LIMIT 1");
$stmt->execute([':id' => $id]);
$content = $stmt->fetch();

if (!$content) {
    flash_set('danger', 'Konten tidak ditemukan.');
    redirect('admin/generated_contents.php');
}

$flash = flash_get();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar no-print">
        <div>
            <h1><?= e(cg_type_label($content['content_type'])) ?></h1>
            <p>Konten siap disalin, diedit, lalu dipakai untuk artikel atau media sosial.</p>
        </div>
        <div class="admin-profile">
            <a href="<?= e(url('admin/generated_contents.php')) ?>" class="btn btn-secondary">Kembali</a>
            <a href="<?= e(url('admin/content_brief_detail.php?id=' . $content['brief_id'])) ?>" class="btn btn-secondary">Lihat Brief</a>
            <button type="button" onclick="window.print()" class="btn btn-secondary">Cetak</button>
            <form method="post" action="<?= e(url('admin/content_generate.php')) ?>" class="inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="brief_id" value="<?= (int) $content['brief_id'] ?>">
                <input type="hidden" name="content_type" value="<?= e($content['content_type']) ?>">
                <button type="submit" class="btn btn-primary">Regenerate</button>
            </form>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="panel">
        <div class="detail-hero">
            <div>
                <span class="badge info"><?= e(cg_type_label($content['content_type'])) ?></span>
                <h2><?= e($content['title']) ?></h2>
                <p><?= e($content['meta_description']) ?></p>
            </div>
        </div>
        <div class="detail-grid">
            <div>
                <span>Tren sumber</span>
                <strong><?= e($content['trend_name']) ?></strong>
            </div>
            <div>
                <span>Keyword utama</span>
                <strong><?= e($content['main_keyword']) ?></strong>
            </div>
            <div>
                <span>Kategori</span>
                <strong><?= e($content['category']) ?></strong>
            </div>
            <div>
                <span>Terakhir diperbarui</span>
                <strong><?= e($content['updated_at']) ?></strong>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Preview Konten</h2>
            <span>Gunakan sebagai draft awal, lalu verifikasi data aktual sebelum publikasi.</span>
        </div>
        <pre class="code-preview content-preview"><?= e($content['body']) ?></pre>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Copy Konten</h2>
            <span>Salin cepat ke editor, caption, atau dokumen kerja.</span>
        </div>
        <textarea readonly class="copy-textarea"><?= e($content['body']) ?></textarea>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
