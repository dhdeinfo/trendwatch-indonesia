<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$pageTitle = 'Detail Tren';
$activeMenu = 'trends';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    flash_set('danger', 'Data tren tidak ditemukan.');
    redirect('admin/trends.php');
}

$stmt = $pdo->prepare('SELECT * FROM trends WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$trend = $stmt->fetch();

if (!$trend) {
    flash_set('danger', 'Data tren tidak ditemukan.');
    redirect('admin/trends.php');
}

$stmt = $pdo->prepare('SELECT hour_label, volume FROM trend_histories WHERE trend_id = :id ORDER BY id ASC');
$stmt->execute([':id' => $id]);
$histories = $stmt->fetchAll();

$historyLabels = [];
$historyVolumes = [];
foreach ($histories as $history) {
    $historyLabels[] = $history['hour_label'];
    $historyVolumes[] = (int) $history['volume'];
}

$keywordItems = preg_split('/[,;\n]+/', (string) $trend['related_keywords']);
$keywordItems = array_values(array_filter(array_map('trim', $keywordItems)));

$maxVolume = $historyVolumes ? max($historyVolumes) : (int) $trend['search_volume'];
$minVolume = $historyVolumes ? min($historyVolumes) : 0;
$lastVolume = $historyVolumes ? end($historyVolumes) : (int) $trend['search_volume'];
$firstVolume = $historyVolumes ? reset($historyVolumes) : (int) $trend['search_volume'];
$growth = $firstVolume > 0 ? round((($lastVolume - $firstVolume) / $firstVolume) * 100, 1) : 0;

$seoScore = (int) $trend['seo_score'];
$searchVolume = (int) $trend['search_volume'];
$status = strtolower((string) $trend['status']);
$category = strtolower((string) $trend['category']);

if ($status === 'aktif' && $seoScore >= 85 && $searchVolume >= 50000) {
    $priorityLabel = 'Prioritas tinggi';
    $priorityClass = 'success';
    $priorityNote = 'Tren masih aktif, volume besar, dan skor SEO kuat. Buat konten secepatnya.';
} elseif ($seoScore >= 70 || $searchVolume >= 20000) {
    $priorityLabel = 'Prioritas sedang';
    $priorityClass = 'warning';
    $priorityNote = 'Tren masih layak dikerjakan. Pilih angle yang spesifik agar konten tidak terlalu umum.';
} else {
    $priorityLabel = 'Pantauan';
    $priorityClass = 'info';
    $priorityNote = 'Tren dapat dipantau dulu. Konten pendek lebih aman daripada artikel panjang.';
}

$intentLabel = 'Informasional';
$intentNote = 'Pengguna kemungkinan mencari penjelasan, konteks, arti, atau ringkasan terbaru.';
if (strpos($category, 'olahraga') !== false) {
    $intentLabel = 'Berita dan hasil pertandingan';
    $intentNote = 'Pengguna kemungkinan mencari jadwal, hasil, skor, susunan pemain, atau rekap cepat.';
} elseif (strpos($category, 'religi') !== false) {
    $intentLabel = 'Panduan dan referensi';
    $intentNote = 'Pengguna kemungkinan mencari teks, arti, tata cara, atau penjelasan yang mudah diikuti.';
} elseif (strpos($category, 'tokoh') !== false) {
    $intentLabel = 'Profil tokoh';
    $intentNote = 'Pengguna kemungkinan mencari biodata, latar belakang, alasan viral, dan fakta singkat.';
}

$trendName = (string) $trend['trend_name'];
$articleIdeas = [
    'Apa Itu ' . $trendName . '? Penjelasan Lengkap dan Fakta Terbaru',
    $trendName . ': Informasi Penting, Keyword Terkait, dan Alasan Banyak Dicari',
    'Kenapa ' . $trendName . ' Sedang Trending? Ini Ringkasan Lengkapnya',
];

if (strpos($category, 'olahraga') !== false) {
    $articleIdeas = [
        $trendName . ': Jadwal, Prediksi, dan Informasi Pertandingan',
        'Hasil ' . $trendName . ' Terbaru, Skor, dan Jalannya Pertandingan',
        'Link Live Score ' . $trendName . ' dan Update Terbaru',
    ];
} elseif (strpos($category, 'religi') !== false) {
    $articleIdeas = [
        ucwords($trendName) . ' Lengkap dengan Arti dan Penjelasannya',
        'Panduan ' . $trendName . ' yang Mudah Dipahami',
        'Kumpulan ' . $trendName . ' dan Amalan yang Berkaitan',
    ];
} elseif (strpos($category, 'tokoh') !== false) {
    $articleIdeas = [
        'Profil ' . ucwords($trendName) . ', Biodata, dan Fakta Singkat',
        'Siapa ' . ucwords($trendName) . '? Ini Alasan Namanya Banyak Dicari',
        'Fakta Menarik ' . ucwords($trendName) . ' yang Sedang Trending',
    ];
}

$socialIdeas = [
    'Carousel ringkas: 5 fakta tentang ' . $trendName,
    'Reels 30 detik: kenapa ' . $trendName . ' ramai dicari',
    'Story interaktif: polling opini audiens tentang ' . $trendName,
];

$recommendedActions = [
    'Gunakan keyword utama pada judul, slug, paragraf awal, dan meta description.',
    'Tambahkan 3 sampai 5 keyword terkait sebagai subjudul atau FAQ.',
    'Terbitkan konten pendek lebih dulu, lalu update setelah data tren bertambah.',
];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar no-print">
        <div>
            <h1>Detail Tren</h1>
            <p>Analisis satu tren untuk membaca peluang konten dan kebutuhan SEO.</p>
        </div>
        <div class="admin-profile">
            <button type="button" class="btn btn-secondary" onclick="window.print()">Cetak</button>
            <a href="<?= e(url('admin/trends.php')) ?>" class="btn btn-secondary">Kembali</a>
            <a href="<?= e(url('admin/trend_edit.php?id=' . $trend['id'])) ?>" class="btn btn-primary">Edit</a>
            <a href="<?= e(url('auth/logout.php')) ?>" class="btn btn-secondary">Logout</a>
        </div>
    </header>

    <section class="detail-hero panel">
        <div>
            <div class="badge-row">
                <span class="badge <?= e(badge_class($trend['status'])) ?>"><?= e($trend['status']) ?></span>
                <span class="badge <?= e($priorityClass) ?>"><?= e($priorityLabel) ?></span>
                <span class="badge info"><?= e($trend['category']) ?></span>
            </div>
            <h2><?= e($trend['trend_name']) ?></h2>
            <p><?= e($trend['content_angle'] ?: 'Belum ada angle konten. Tambahkan angle agar analisis lebih kuat.') ?></p>
        </div>
        <div class="score-circle">
            <strong><?= (int) $trend['seo_score'] ?>%</strong>
            <span>SEO Score</span>
        </div>
    </section>

    <section class="stats-grid detail-stats">
        <article class="stat-card">
            <span>Volume Pencarian</span>
            <strong><?= e($trend['volume_text']) ?></strong>
            <p><?= number_format($searchVolume, 0, ',', '.') ?> pencarian estimasi.</p>
        </article>
        <article class="stat-card">
            <span>Puncak Histori</span>
            <strong><?= number_format($maxVolume, 0, ',', '.') ?></strong>
            <p>Volume tertinggi pada data histori.</p>
        </article>
        <article class="stat-card">
            <span>Perubahan Histori</span>
            <strong><?= e((string) $growth) ?>%</strong>
            <p>Dari titik awal sampai titik terakhir.</p>
        </article>
        <article class="stat-card">
            <span>Intent Pencarian</span>
            <strong><?= e($intentLabel) ?></strong>
            <p><?= e($intentNote) ?></p>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="panel">
            <div class="panel-header">
                <h2>Informasi Tren</h2>
                <span>Data utama</span>
            </div>
            <div class="detail-grid">
                <div>
                    <span>Nama Tren</span>
                    <strong><?= e($trend['trend_name']) ?></strong>
                </div>
                <div>
                    <span>Kategori</span>
                    <strong><?= e($trend['category']) ?></strong>
                </div>
                <div>
                    <span>Status</span>
                    <strong><?= e($trend['status']) ?></strong>
                </div>
                <div>
                    <span>Dimulai</span>
                    <strong><?= e($trend['started_text']) ?></strong>
                </div>
                <div>
                    <span>Dibuat</span>
                    <strong><?= e($trend['created_at']) ?></strong>
                </div>
                <div>
                    <span>Prioritas Konten</span>
                    <strong><?= e($priorityLabel) ?></strong>
                </div>
            </div>
        </article>

        <article class="panel">
            <div class="panel-header">
                <h2>Ringkasan Keputusan</h2>
                <span>Rekomendasi</span>
            </div>
            <div class="insight-box">
                <strong><?= e($priorityLabel) ?></strong>
                <p><?= e($priorityNote) ?></p>
            </div>
            <ul class="clean-list">
                <?php foreach ($recommendedActions as $action): ?>
                    <li><?= e($action) ?></li>
                <?php endforeach; ?>
            </ul>
        </article>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Keyword Terkait</h2>
            <span>Turunan kata kunci</span>
        </div>
        <?php if ($keywordItems): ?>
            <div class="keyword-chip-wrap">
                <?php foreach ($keywordItems as $keyword): ?>
                    <span class="keyword-chip"><?= e($keyword) ?></span>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="muted">Keyword terkait belum tersedia.</p>
        <?php endif; ?>
    </section>

    <section class="dashboard-grid">
        <article class="panel">
            <div class="panel-header">
                <h2>Ide Judul Artikel</h2>
                <span>SEO</span>
            </div>
            <ol class="idea-list">
                <?php foreach ($articleIdeas as $idea): ?>
                    <li><?= e($idea) ?></li>
                <?php endforeach; ?>
            </ol>
        </article>

        <article class="panel">
            <div class="panel-header">
                <h2>Ide Konten Sosial</h2>
                <span>Instagram atau TikTok</span>
            </div>
            <ol class="idea-list">
                <?php foreach ($socialIdeas as $idea): ?>
                    <li><?= e($idea) ?></li>
                <?php endforeach; ?>
            </ol>
        </article>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Grafik Tren</h2>
            <span>Simulasi histori volume</span>
        </div>
        <?php if ($histories): ?>
            <canvas id="detailHistoryChart" height="100"></canvas>
        <?php else: ?>
            <p class="muted">Histori grafik belum tersedia.</p>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Riwayat Volume</h2>
            <span>Data per jam</span>
        </div>
        <?php if ($histories): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Jam</th>
                            <th>Volume</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($histories as $history): ?>
                            <?php
                            $volume = (int) $history['volume'];
                            $volumeStatus = $volume >= $maxVolume ? 'Puncak' : ($volume <= $minVolume ? 'Rendah' : 'Normal');
                            ?>
                            <tr>
                                <td><?= e($history['hour_label']) ?></td>
                                <td><?= number_format($volume, 0, ',', '.') ?></td>
                                <td><?= e($volumeStatus) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="muted">Belum ada data riwayat volume.</p>
        <?php endif; ?>
    </section>
</main>
<script>
window.trendwatchDetailChart = {
    labels: <?= json_encode($historyLabels) ?>,
    volumes: <?= json_encode($historyVolumes) ?>
};
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
