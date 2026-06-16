<?php
require_once __DIR__ . '/config/auth.php';
$isLoggedIn = admin_logged_in();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="TrendWatch Indonesia adalah aplikasi PHP Native untuk memantau tren Google Trends, membaca peluang SEO, dan mengelola data tren dengan SQLite.">
    <meta name="theme-color" content="#2563eb">
    <title><?= e(APP_NAME) ?> - Monitoring Tren SEO</title>
    <script>
        (function () {
            var savedTheme = localStorage.getItem('trendwatch-theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
</head>
<body class="landing-body">
<button type="button" class="theme-toggle" id="themeToggle" aria-label="Ganti tema">Mode</button>
<main class="landing-shell">
    <nav class="landing-nav">
        <div class="landing-brand">
            <div class="landing-logo">TW</div>
            <div>
                <strong>TrendWatch Indonesia</strong>
                <span>Google Trends SEO Monitor</span>
            </div>
        </div>
        <div class="landing-actions">
            <a class="btn btn-secondary" href="<?= e(url('check_connection.php')) ?>">Cek Koneksi</a>
            <?php if ($isLoggedIn): ?>
                <a class="btn btn-primary" href="<?= e(url('admin/dashboard.php')) ?>">Buka Dashboard</a>
            <?php else: ?>
                <a class="btn btn-primary" href="<?= e(url('auth/login.php')) ?>">Login Admin</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="landing-hero">
        <div>
            <span class="small-label">PHP Native + SQLite</span>
            <h1>Dashboard tren yang rapi, cepat, dan siap dipakai.</h1>
            <p>Kelola data tren, sinkron Google Trends RSS Indonesia, pantau grafik, dan baca peluang konten SEO dari satu aplikasi sederhana.</p>
            <div class="hero-actions">
                <?php if ($isLoggedIn): ?>
                    <a class="btn btn-primary" href="<?= e(url('admin/dashboard.php')) ?>">Masuk Dashboard</a>
                <?php else: ?>
                    <a class="btn btn-primary" href="<?= e(url('auth/login.php')) ?>">Mulai Login</a>
                <?php endif; ?>
                <a class="btn btn-secondary" href="<?= e(url('setup.php')) ?>">Setup Database</a>
            </div>
        </div>

        <div class="hero-card">
            <div class="hero-card-head">
                <div>
                    <strong>Preview Grafik Tren</strong>
                    <p class="muted">Simulasi tampilan dashboard.</p>
                </div>
                <span class="badge success">Aktif</span>
            </div>
            <div class="hero-mini-chart" aria-hidden="true">
                <i style="height: 35%"></i>
                <i style="height: 50%"></i>
                <i style="height: 42%"></i>
                <i style="height: 72%"></i>
                <i style="height: 64%"></i>
                <i style="height: 90%"></i>
            </div>
            <div class="hero-metrics">
                <div><span>SYNC</span><strong>RSS</strong></div>
                <div><span>DB</span><strong>SQLite</strong></div>
                <div><span>UI</span><strong>Final</strong></div>
            </div>
        </div>
    </section>

    <section class="feature-grid">
        <article class="feature-card">
            <b>1</b>
            <h3>Dashboard modern</h3>
            <p>Ringkasan tren, volume, status, dan peluang konten tampil lebih bersih.</p>
        </article>
        <article class="feature-card">
            <b>2</b>
            <h3>Dark mode</h3>
            <p>Pengguna bisa mengganti tampilan terang dan gelap dari tombol cepat.</p>
        </article>
        <article class="feature-card">
            <b>3</b>
            <h3>Responsif</h3>
            <p>Sidebar dan tabel sudah lebih nyaman dibuka dari laptop maupun HP.</p>
        </article>
        <article class="feature-card">
            <b>4</b>
            <h3>Siap lanjut</h3>
            <p>Struktur tetap ringan, cocok untuk ditambah export, admin setting, dan AI generator.</p>
        </article>
    </section>
</main>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body>
</html>
