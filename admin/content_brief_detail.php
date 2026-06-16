<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/ai_content_generator.php';
require_once __DIR__ . '/../includes/content_script_generator.php';
require_admin();

$pageTitle = 'Detail SEO Content Brief';
$activeMenu = 'content_briefs';
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT cb.*, t.trend_name, t.search_volume, t.volume_text, t.started_text, t.status, t.category, t.related_keywords, t.seo_score, t.source
    FROM content_briefs cb
    JOIN trends t ON t.id = cb.trend_id
    WHERE cb.id = :id
    LIMIT 1");
$stmt->execute([':id' => $id]);
$brief = $stmt->fetch();

if (!$brief) {
    flash_set('danger', 'SEO content brief tidak ditemukan.');
    redirect('admin/content_briefs.php');
}

if (is_post()) {
    csrf_verify();
    $trendStmt = $pdo->prepare('SELECT * FROM trends WHERE id = :id LIMIT 1');
    $trendStmt->execute([':id' => (int) $brief['trend_id']]);
    $trend = $trendStmt->fetch();

    if ($trend) {
        $newBrief = ai_generate_seo_brief($pdo, $trend);
        $briefId = seo_save_brief($pdo, (int) $brief['trend_id'], $newBrief);
        flash_set('success', 'SEO content brief berhasil diperbarui.');
        redirect('admin/content_brief_detail.php?id=' . $briefId);
    }

    flash_set('danger', 'Data tren sumber tidak ditemukan.');
    redirect('admin/content_briefs.php');
}

function brief_lines(?string $text): array
{
    $items = preg_split('/\r\n|\r|\n/', (string) $text);
    $items = array_map(static fn($item) => trim($item), $items ?: []);
    return array_values(array_filter($items, static fn($item) => $item !== ''));
}

$flash = flash_get();
$titleOptions = brief_lines($brief['title_options']);
$outlineLines = brief_lines($brief['outline']);
$faqLines = brief_lines($brief['faq_items']);
$platformIdeas = brief_lines($brief['platform_ideas']);
$secondaryKeywords = seo_split_keywords($brief['secondary_keywords']);
$generatedStmt = $pdo->prepare('SELECT * FROM generated_contents WHERE brief_id = :brief_id ORDER BY updated_at DESC');
$generatedStmt->execute([':brief_id' => (int) $brief['id']]);
$generatedContents = $generatedStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar no-print">
        <div>
            <h1>Detail SEO Content Brief</h1>
            <p>Brief siap dipakai sebagai bahan artikel, carousel, video pendek, dan tugas konten.</p>
        </div>
        <div class="admin-profile">
            <a href="<?= e(url('admin/content_briefs.php')) ?>" class="btn btn-secondary">Kembali</a>
            <button type="button" onclick="window.print()" class="btn btn-secondary">Cetak</button>
            <form method="post" action="" class="inline-form">
                <?= csrf_field() ?>
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
                <span class="badge info"><?= e($brief['category']) ?></span>
                <h2><?= e($brief['selected_title']) ?></h2>
                <p><?= e($brief['meta_description']) ?></p>
            </div>
            <div class="score-circle">
                <div>
                    <strong><?= (int) $brief['priority_score'] ?>%</strong>
                    <span>Prioritas</span>
                </div>
            </div>
        </div>

        <div class="detail-grid">
            <div>
                <span>Tren sumber</span>
                <strong><?= e($brief['trend_name']) ?></strong>
            </div>
            <div>
                <span>Volume pencarian</span>
                <strong><?= e($brief['volume_text']) ?> atau <?= number_format((int) $brief['search_volume'], 0, ',', '.') ?></strong>
            </div>
            <div>
                <span>Keyword utama</span>
                <strong><?= e($brief['main_keyword']) ?></strong>
            </div>
            <div>
                <span>Target panjang artikel</span>
                <strong><?= number_format((int) $brief['word_count'], 0, ',', '.') ?> kata</strong>
            </div>
            <div>
                <span>Format rekomendasi</span>
                <strong><?= e($brief['recommended_format']) ?></strong>
            </div>
            <div>
                <span>Terakhir diperbarui</span>
                <strong><?= e($brief['updated_at']) ?></strong>
            </div>
            <div>
                <span>Generator</span>
                <strong><?= e(($brief['generator_source'] ?? 'template') === 'ai' ? 'AI Optional' : 'Template Gratis') ?></strong>
            </div>
            <div>
                <span>Status AI</span>
                <strong><?= e($brief['ai_message'] ?? 'Tidak ada catatan AI.') ?></strong>
            </div>

        </div>
    </section>

    <section class="dashboard-grid">
        <div class="panel">
            <div class="panel-header">
                <h2>Brief Utama</h2>
                <span>Bagian paling penting untuk penulis</span>
            </div>
            <div class="insight-box">
                <strong>Search Intent</strong>
                <p><?= e($brief['search_intent']) ?></p>
            </div>
            <div class="insight-box">
                <strong>Target Audiens</strong>
                <p><?= e($brief['target_audience']) ?></p>
            </div>
            <div class="insight-box">
                <strong>Angle Konten</strong>
                <p><?= e($brief['content_angle']) ?></p>
            </div>
            <div class="insight-box">
                <strong>Opening Hook</strong>
                <p><?= e($brief['intro_hook']) ?></p>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>Keyword Turunan</h2>
                <span>Masukkan natural di artikel</span>
            </div>
            <div class="keyword-chip-wrap">
                <?php if (!$secondaryKeywords): ?>
                    <span class="keyword-chip"><?= e($brief['main_keyword']) ?></span>
                <?php endif; ?>
                <?php foreach ($secondaryKeywords as $keyword): ?>
                    <span class="keyword-chip"><?= e($keyword) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Opsi Judul Artikel</h2>
            <span>Pilih yang paling sesuai dengan kanal publikasi</span>
        </div>
        <ol class="clean-list">
            <?php foreach ($titleOptions as $title): ?>
                <li><?= e($title) ?></li>
            <?php endforeach; ?>
        </ol>
    </section>

    <section class="dashboard-grid">
        <div class="panel">
            <div class="panel-header">
                <h2>Outline SEO</h2>
                <span>Struktur H1 sampai H2</span>
            </div>
            <ol class="clean-list">
                <?php foreach ($outlineLines as $line): ?>
                    <li><?= e($line) ?></li>
                <?php endforeach; ?>
            </ol>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>FAQ</h2>
                <span>Untuk People Also Ask dan bagian akhir artikel</span>
            </div>
            <ol class="clean-list">
                <?php foreach ($faqLines as $line): ?>
                    <li><?= e(preg_replace('/^\d+\.\s*/', '', $line)) ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Ide Distribusi Konten</h2>
            <span>Turunan untuk media sosial</span>
        </div>
        <div class="brief-grid">
            <?php foreach ($platformIdeas as $idea): ?>
                <div class="insight-box">
                    <strong><?= e(strtok($idea, ':') ?: 'Ide') ?></strong>
                    <p><?= e(trim(str_contains($idea, ':') ? substr($idea, strpos($idea, ':') + 1) : $idea)) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel no-print">
        <div class="panel-header with-actions">
            <div>
                <h2>Generator Artikel dan Konten Short</h2>
                <span>Klik tombol sesuai format. Sistem akan membuat draft artikel atau script media sosial dari brief ini.</span>
            </div>
            <a href="<?= e(url('admin/generated_contents.php')) ?>" class="btn btn-secondary">Lihat Semua Konten</a>
        </div>
        <div class="content-generator-grid">
            <?php foreach (cg_allowed_types() as $type): ?>
                <form method="post" action="<?= e(url('admin/content_generate.php')) ?>" class="generator-card">
                    <?= csrf_field() ?>
                    <input type="hidden" name="brief_id" value="<?= (int) $brief['id'] ?>">
                    <input type="hidden" name="content_type" value="<?= e($type) ?>">
                    <strong><?= e(cg_type_label($type)) ?></strong>
                    <p><?= e($type === 'article' ? 'Buat draft artikel SEO dari outline brief.' : 'Buat script siap pakai untuk konten short.') ?></p>
                    <button type="submit" class="btn btn-primary small">Generate</button>
                </form>
            <?php endforeach; ?>
        </div>

        <?php if ($generatedContents): ?>
            <div class="panel-header mt-16">
                <h2>Konten yang Sudah Dibuat</h2>
                <span><?= count($generatedContents) ?> format tersedia</span>
            </div>
            <div class="brief-grid">
                <?php foreach ($generatedContents as $content): ?>
                    <article class="brief-card">
                        <div class="brief-card-top">
                            <span class="badge info"><?= e(cg_type_label($content['content_type'])) ?></span>
                            <small><?= e($content['updated_at']) ?></small>
                        </div>
                        <h3><?= e($content['title']) ?></h3>
                        <p><?= e(cg_limit_text($content['body'], 140)) ?></p>
                        <a href="<?= e(url('admin/generated_content_detail.php?id=' . $content['id'])) ?>" class="btn btn-secondary small">Lihat Konten</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Copy Brief</h2>
            <span>Salin cepat ke dokumen kerja</span>
        </div>
        <textarea readonly class="copy-textarea"><?= e("Judul: " . $brief['selected_title'] . "\n\nMeta Description:\n" . $brief['meta_description'] . "\n\nKeyword Utama:\n" . $brief['main_keyword'] . "\n\nKeyword Turunan:\n" . $brief['secondary_keywords'] . "\n\nSearch Intent:\n" . $brief['search_intent'] . "\n\nTarget Audiens:\n" . $brief['target_audience'] . "\n\nOutline:\n" . $brief['outline'] . "\n\nFAQ:\n" . $brief['faq_items'] . "\n\nIde Platform:\n" . $brief['platform_ideas']) ?></textarea>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
