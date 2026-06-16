<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$pageTitle = 'Hapus Tren';
$activeMenu = 'trends';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
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

if (is_post()) {
    csrf_verify();

    $stmt = $pdo->prepare('DELETE FROM trends WHERE id = :id');
    $stmt->execute([':id' => $id]);

    flash_set('success', 'Data tren berhasil dihapus.');
    redirect('admin/trends.php');
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar">
        <div>
            <h1>Hapus Tren</h1>
            <p>Konfirmasi penghapusan data tren.</p>
        </div>
        <div class="admin-profile">
            <a href="<?= e(url('admin/trends.php')) ?>" class="btn btn-secondary">Kembali</a>
            <a href="<?= e(url('auth/logout.php')) ?>" class="btn btn-secondary">Logout</a>
        </div>
    </header>

    <section class="panel delete-panel">
        <h2>Yakin ingin menghapus data ini?</h2>
        <p>Data tren dan histori grafik yang terhubung akan ikut terhapus.</p>

        <div class="delete-box">
            <strong><?= e($trend['trend_name']) ?></strong>
            <span><?= e($trend['category']) ?> · <?= e($trend['volume_text']) ?> · <?= e($trend['status']) ?></span>
        </div>

        <form method="post" action="" class="form-actions">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $trend['id'] ?>">
            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
            <a href="<?= e(url('admin/trends.php')) ?>" class="btn btn-secondary">Batal</a>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
