<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$pageTitle = 'Edit Tren';
$activeMenu = 'trends';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    flash_set('danger', 'Data tren tidak ditemukan.');
    redirect('admin/trends.php');
}

$stmt = $pdo->prepare('SELECT * FROM trends WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$data = $stmt->fetch();

if (!$data) {
    flash_set('danger', 'Data tren tidak ditemukan.');
    redirect('admin/trends.php');
}

$errors = [];
$statuses = ['Aktif', 'Menurun', 'Selesai', 'Pantauan'];

if (is_post()) {
    csrf_verify();

    $data['trend_name'] = trim($_POST['trend_name'] ?? '');
    $data['search_volume'] = (int) ($_POST['search_volume'] ?? 0);
    $data['volume_text'] = trim($_POST['volume_text'] ?? '');
    $data['started_text'] = trim($_POST['started_text'] ?? '');
    $data['status'] = trim($_POST['status'] ?? 'Aktif');
    $data['category'] = trim($_POST['category'] ?? 'Umum');
    $data['related_keywords'] = trim($_POST['related_keywords'] ?? '');
    $data['seo_score'] = (int) ($_POST['seo_score'] ?? 0);
    $data['content_angle'] = trim($_POST['content_angle'] ?? '');

    if ($data['trend_name'] === '') {
        $errors[] = 'Nama tren wajib diisi.';
    }

    if ((int) $data['search_volume'] < 0) {
        $errors[] = 'Volume pencarian tidak boleh negatif.';
    }

    if ((int) $data['seo_score'] < 0 || (int) $data['seo_score'] > 100) {
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
            $stmt = $pdo->prepare("UPDATE trends SET
                trend_name = :trend_name,
                search_volume = :search_volume,
                volume_text = :volume_text,
                started_text = :started_text,
                status = :status,
                category = :category,
                related_keywords = :related_keywords,
                seo_score = :seo_score,
                content_angle = :content_angle
                WHERE id = :id");

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
                ':id' => $id,
            ]);

            $pdo->prepare('DELETE FROM trend_histories WHERE trend_id = :id')->execute([':id' => $id]);

            $historyStmt = $pdo->prepare('INSERT INTO trend_histories (trend_id, hour_label, volume) VALUES (:trend_id, :hour_label, :volume)');
            $points = [8, 12, 18, 25, 40, 70, 100];
            foreach ($points as $index => $multiplier) {
                $historyStmt->execute([
                    ':trend_id' => $id,
                    ':hour_label' => ($index * 4) . 'j',
                    ':volume' => (int) round(((int) $data['search_volume'] * $multiplier) / 100),
                ]);
            }

            $pdo->commit();
            flash_set('success', 'Data tren berhasil diperbarui.');
            redirect('admin/trends.php');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Gagal memperbarui data: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar">
        <div>
            <h1>Edit Tren</h1>
            <p>Perbarui data tren pencarian yang sudah tersimpan.</p>
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
                    <input type="text" name="trend_name" value="<?= e($data['trend_name']) ?>" required>
                </div>
                <div>
                    <label>Kategori <span class="required">*</span></label>
                    <input type="text" name="category" value="<?= e($data['category']) ?>" required>
                </div>
                <div>
                    <label>Volume Pencarian</label>
                    <input type="number" name="search_volume" value="<?= e((string) $data['search_volume']) ?>" min="0">
                </div>
                <div>
                    <label>Teks Volume</label>
                    <input type="text" name="volume_text" value="<?= e($data['volume_text']) ?>" placeholder="Kosongkan untuk otomatis">
                </div>
                <div>
                    <label>Dimulai</label>
                    <input type="text" name="started_text" value="<?= e($data['started_text']) ?>">
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
                    <input type="number" name="seo_score" value="<?= e((string) $data['seo_score']) ?>" min="0" max="100">
                </div>
            </div>

            <label>Keyword Terkait</label>
            <textarea name="related_keywords" rows="4"><?= e($data['related_keywords']) ?></textarea>

            <label>Angle Konten</label>
            <textarea name="content_angle" rows="4"><?= e($data['content_angle']) ?></textarea>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="<?= e(url('admin/trends.php')) ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
