<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$pageTitle = 'Tambah Tren';
$activeMenu = 'trend_add';

$errors = [];
$data = [
    'trend_name' => '',
    'search_volume' => '',
    'volume_text' => '',
    'started_text' => '',
    'status' => 'Aktif',
    'category' => 'Umum',
    'related_keywords' => '',
    'seo_score' => '70',
    'content_angle' => '',
];

$statuses = ['Aktif', 'Menurun', 'Selesai', 'Pantauan'];

if (is_post()) {
    csrf_verify();

    foreach ($data as $key => $value) {
        $data[$key] = trim($_POST[$key] ?? '');
    }

    $data['search_volume'] = (int) ($data['search_volume'] ?: 0);
    $data['seo_score'] = (int) ($data['seo_score'] ?: 0);

    if ($data['trend_name'] === '') {
        $errors[] = 'Nama tren wajib diisi.';
    }

    if ($data['search_volume'] < 0) {
        $errors[] = 'Volume pencarian tidak boleh negatif.';
    }

    if ($data['seo_score'] < 0 || $data['seo_score'] > 100) {
        $errors[] = 'SEO score harus berada pada angka 0 sampai 100.';
    }

    if (!in_array($data['status'], $statuses, true)) {
        $errors[] = 'Status tren tidak valid.';
    }

    if ($data['category'] === '') {
        $errors[] = 'Kategori wajib diisi.';
    }

    if ($data['volume_text'] === '') {
        $data['volume_text'] = format_volume_text((int) $data['search_volume']);
    }

    if (!$errors) {
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("INSERT INTO trends
                (trend_name, search_volume, volume_text, started_text, status, category, related_keywords, seo_score, content_angle)
                VALUES (:trend_name, :search_volume, :volume_text, :started_text, :status, :category, :related_keywords, :seo_score, :content_angle)");

            $stmt->execute([
                ':trend_name' => $data['trend_name'],
                ':search_volume' => $data['search_volume'],
                ':volume_text' => $data['volume_text'],
                ':started_text' => $data['started_text'],
                ':status' => $data['status'],
                ':category' => $data['category'],
                ':related_keywords' => $data['related_keywords'],
                ':seo_score' => $data['seo_score'],
                ':content_angle' => $data['content_angle'],
            ]);

            $trendId = (int) $pdo->lastInsertId();
            $historyStmt = $pdo->prepare('INSERT INTO trend_histories (trend_id, hour_label, volume) VALUES (:trend_id, :hour_label, :volume)');
            $points = [8, 12, 18, 25, 40, 70, 100];

            foreach ($points as $index => $multiplier) {
                $historyStmt->execute([
                    ':trend_id' => $trendId,
                    ':hour_label' => ($index * 4) . 'j',
                    ':volume' => (int) round(((int) $data['search_volume'] * $multiplier) / 100),
                ]);
            }

            $pdo->commit();
            flash_set('success', 'Data tren berhasil ditambahkan.');
            redirect('admin/trends.php');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar">
        <div>
            <h1>Tambah Tren</h1>
            <p>Masukkan data tren pencarian baru untuk dashboard.</p>
        </div>
        <div class="admin-profile">
            <a href="<?= e(url('admin/trends.php')) ?>" class="btn btn-secondary">Kembali</a>
            <a href="<?= e(url('auth/logout.php')) ?>" class="btn btn-secondary">Logout</a>
        </div>
    </header>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="panel">
        <form method="post" action="" class="data-form">
            <?= csrf_field() ?>
            <div class="form-grid two">
                <div>
                    <label>Nama Tren <span class="required">*</span></label>
                    <input type="text" name="trend_name" value="<?= e($data['trend_name']) ?>" placeholder="Contoh: doa awal tahun" required>
                </div>
                <div>
                    <label>Kategori <span class="required">*</span></label>
                    <input type="text" name="category" value="<?= e($data['category']) ?>" placeholder="Religi, Olahraga, Berita" required>
                </div>
                <div>
                    <label>Volume Pencarian</label>
                    <input type="number" name="search_volume" value="<?= e((string) $data['search_volume']) ?>" min="0" placeholder="200000">
                </div>
                <div>
                    <label>Teks Volume</label>
                    <input type="text" name="volume_text" value="<?= e($data['volume_text']) ?>" placeholder="Kosongkan untuk otomatis, contoh: 200 rb+">
                </div>
                <div>
                    <label>Dimulai</label>
                    <input type="text" name="started_text" value="<?= e($data['started_text']) ?>" placeholder="20 jam yang lalu">
                </div>
                <div>
                    <label>Status</label>
                    <select name="status">
                        <?php foreach ($statuses as $item): ?>
                            <option value="<?= e($item) ?>" <?= $data['status'] === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>SEO Score</label>
                    <input type="number" name="seo_score" value="<?= e((string) $data['seo_score']) ?>" min="0" max="100" placeholder="70">
                </div>
            </div>

            <label>Keyword Terkait</label>
            <textarea name="related_keywords" rows="4" placeholder="Pisahkan dengan koma"><?= e($data['related_keywords']) ?></textarea>

            <label>Angle Konten</label>
            <textarea name="content_angle" rows="4" placeholder="Contoh: Artikel cepat untuk menangkap pencarian musiman"><?= e($data['content_angle']) ?></textarea>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Tren</button>
                <a href="<?= e(url('admin/trends.php')) ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
