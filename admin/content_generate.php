<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/content_script_generator.php';
require_admin();

if (!is_post()) {
    redirect('admin/content_briefs.php');
}

csrf_verify();

$briefId = (int) ($_POST['brief_id'] ?? 0);
$type = trim((string) ($_POST['content_type'] ?? ''));

if (!in_array($type, cg_allowed_types(), true)) {
    flash_set('danger', 'Jenis konten tidak valid.');
    redirect('admin/content_briefs.php');
}

$stmt = $pdo->prepare("SELECT cb.*, t.trend_name, t.search_volume, t.volume_text, t.started_text, t.status, t.category, t.related_keywords, t.seo_score, t.source
    FROM content_briefs cb
    JOIN trends t ON t.id = cb.trend_id
    WHERE cb.id = :id
    LIMIT 1");
$stmt->execute([':id' => $briefId]);
$brief = $stmt->fetch();

if (!$brief) {
    flash_set('danger', 'SEO content brief tidak ditemukan.');
    redirect('admin/content_briefs.php');
}

try {
    $contentId = cg_save_generated_content($pdo, $brief, $type);
    flash_set('success', cg_type_label($type) . ' berhasil dibuat.');
    redirect('admin/generated_content_detail.php?id=' . $contentId);
} catch (Throwable $e) {
    flash_set('danger', 'Gagal membuat konten: ' . $e->getMessage());
    redirect('admin/content_brief_detail.php?id=' . $briefId);
}
