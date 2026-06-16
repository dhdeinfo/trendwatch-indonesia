<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-icon">TW</div>
        <div>
            <strong>TrendWatch</strong>
            <span>SEO Dashboard</span>
        </div>
    </div>

    <div class="sidebar-user">
        <span>Login sebagai</span>
        <strong><?= e(current_admin_name()) ?></strong>
    </div>

    <nav class="nav-menu">
        <a data-icon="D" href="<?= e(url('admin/dashboard.php')) ?>" class="<?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a data-icon="T" href="<?= e(url('admin/trends.php')) ?>" class="<?= ($activeMenu ?? '') === 'trends' ? 'active' : '' ?>">Data Tren</a>
        <a data-icon="G" href="<?= e(url('admin/charts.php')) ?>" class="<?= ($activeMenu ?? '') === 'charts' ? 'active' : '' ?>">Grafik Tren</a>
        <a data-icon="B" href="<?= e(url('admin/content_briefs.php')) ?>" class="<?= ($activeMenu ?? '') === 'content_briefs' ? 'active' : '' ?>">SEO Brief</a>
        <a data-icon="AI" href="<?= e(url('admin/ai_settings.php')) ?>" class="<?= ($activeMenu ?? '') === 'ai_settings' ? 'active' : '' ?>">AI Optional</a>
        <a data-icon="S" href="<?= e(url('admin/sync_google_trends.php')) ?>" class="<?= ($activeMenu ?? '') === 'sync_google_trends' ? 'active' : '' ?>">Sync Google Trends</a>
        <a data-icon="+" href="<?= e(url('admin/trend_add.php')) ?>" class="<?= ($activeMenu ?? '') === 'trend_add' ? 'active' : '' ?>">Tambah Tren</a>
        <div class="nav-divider"></div>
        <a data-icon="C" href="<?= e(url('check_connection.php')) ?>">Cek Koneksi</a>
        <a data-icon="DB" href="<?= e(url('setup.php')) ?>">Setup Database</a>
        <a data-icon="L" href="<?= e(url('auth/logout.php')) ?>">Logout</a>
    </nav>
</aside>
