<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/ai_content_generator.php';
require_admin();

$pageTitle = 'AI Optional';
$activeMenu = 'ai_settings';
$flash = flash_get();
$settings = ai_get_settings($pdo);
$testResult = null;

if (is_post()) {
    csrf_verify();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'save') {
        $provider = $_POST['ai_provider'] ?? 'template';
        $allowedProviders = ['template', 'gemini', 'openai', 'openrouter', 'ollama', 'custom'];
        if (!in_array($provider, $allowedProviders, true)) {
            $provider = 'template';
        }

        $currentKey = $settings['ai_api_key'] ?? '';
        $newKey = trim($_POST['ai_api_key'] ?? '');
        $keepKey = isset($_POST['keep_existing_key']);

        ai_save_settings($pdo, [
            'ai_enabled' => isset($_POST['ai_enabled']) ? '1' : '0',
            'ai_provider' => $provider,
            'ai_api_key' => $keepKey && $newKey === '' ? $currentKey : $newKey,
            'ai_model' => trim($_POST['ai_model'] ?? ''),
            'ai_endpoint' => trim($_POST['ai_endpoint'] ?? ''),
            'ai_temperature' => (string) max(0, min(2, (float) ($_POST['ai_temperature'] ?? 0.7))),
        ]);

        flash_set('success', 'Pengaturan AI berhasil disimpan. Generator tetap memakai template gratis jika AI dimatikan atau gagal.');
        redirect('admin/ai_settings.php');
    }

    if ($action === 'test') {
        $settings = ai_get_settings($pdo);
        if (!ai_is_enabled($settings)) {
            $testResult = ['type' => 'warning', 'message' => 'AI belum aktif. Aktifkan AI dan pilih provider terlebih dahulu.'];
        } else {
            $demoTrend = [
                'id' => 0,
                'trend_name' => 'contoh tren indonesia',
                'search_volume' => 10000,
                'volume_text' => '10 rb+',
                'status' => 'Aktif',
                'category' => 'Berita',
                'related_keywords' => 'contoh berita, topik viral, tren hari ini',
                'content_angle' => 'Tes koneksi AI untuk generator SEO brief',
            ];
            $prompt = ai_build_prompt($demoTrend);
            [$ok, $response] = ai_call_provider($settings, $prompt);
            $testResult = $ok
                ? ['type' => 'success', 'message' => 'Tes AI berhasil. Respons diterima dari ' . ai_provider_label($settings['ai_provider']) . '.', 'response' => (function_exists('mb_substr') ? mb_substr($response, 0, 1200) : substr($response, 0, 1200))]
                : ['type' => 'danger', 'message' => 'Tes AI gagal: ' . $response];
        }
    }
}

$settings = ai_get_settings($pdo);
$logs = [];
try {
    $logs = $pdo->query('SELECT * FROM ai_generation_logs ORDER BY created_at DESC LIMIT 10')->fetchAll();
} catch (PDOException $e) {
    $logs = [];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <header class="topbar">
        <div>
            <h1>AI Optional</h1>
            <p>Aktifkan AI untuk membuat SEO brief yang lebih natural. Jika AI gagal, sistem otomatis kembali ke template gratis.</p>
        </div>
        <div class="admin-profile">
            <span><?= e(ai_provider_label($settings['ai_provider'] ?? 'template')) ?></span>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <?php if ($testResult): ?>
        <div class="alert alert-<?= e($testResult['type']) ?>"><?= e($testResult['message']) ?></div>
        <?php if (!empty($testResult['response'])): ?>
            <section class="panel">
                <div class="panel-header">
                    <h2>Cuplikan Respons AI</h2>
                    <span>Hanya 1.200 karakter pertama</span>
                </div>
                <pre class="code-preview"><?= e($testResult['response']) ?></pre>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <section class="panel">
        <div class="panel-header">
            <h2>Pengaturan Generator AI</h2>
            <span>Mode default tetap gratis tanpa API</span>
        </div>

        <form method="post" action="" class="form-grid compact-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">

            <label class="toggle-row full-row">
                <input type="checkbox" name="ai_enabled" value="1" <?= ($settings['ai_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                <span>Aktifkan AI Optional</span>
            </label>

            <label>
                Provider
                <select name="ai_provider">
                    <?php foreach (['template' => 'Template Gratis', 'gemini' => 'Gemini API', 'openai' => 'OpenAI API', 'openrouter' => 'OpenRouter', 'ollama' => 'Ollama Lokal', 'custom' => 'Custom OpenAI-Compatible'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($settings['ai_provider'] ?? 'template') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Model
                <input type="text" name="ai_model" value="<?= e($settings['ai_model'] ?? '') ?>" placeholder="Contoh: gemini-1.5-flash, gpt-4o-mini, llama3.1">
            </label>

            <label>
                API Key
                <input type="password" name="ai_api_key" value="" placeholder="Kosongkan jika ingin mempertahankan key lama">
                <small>Key saat ini: <?= e(ai_mask_key($settings['ai_api_key'] ?? '')) ?></small>
            </label>

            <label class="checkbox-label">
                <input type="checkbox" name="keep_existing_key" value="1" checked>
                Pertahankan API key lama jika kolom API key kosong
            </label>

            <label>
                Endpoint Custom atau Ollama
                <input type="text" name="ai_endpoint" value="<?= e($settings['ai_endpoint'] ?? '') ?>" placeholder="Contoh: http://localhost:11434/api/generate">
            </label>

            <label>
                Temperature
                <input type="number" name="ai_temperature" value="<?= e($settings['ai_temperature'] ?? '0.7') ?>" min="0" max="2" step="0.1">
            </label>

            <div class="full-row action-group">
                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            </div>
        </form>
    </section>

    <section class="dashboard-grid">
        <div class="panel">
            <div class="panel-header">
                <h2>Status AI</h2>
                <span>Ringkasan konfigurasi aktif</span>
            </div>
            <div class="detail-grid single-column">
                <div><span>Status</span><strong><?= ($settings['ai_enabled'] ?? '0') === '1' ? 'Aktif' : 'Nonaktif' ?></strong></div>
                <div><span>Provider</span><strong><?= e(ai_provider_label($settings['ai_provider'] ?? 'template')) ?></strong></div>
                <div><span>Model</span><strong><?= e($settings['ai_model'] ?: '-') ?></strong></div>
                <div><span>Fallback</span><strong>Template gratis otomatis</strong></div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>Tes Koneksi AI</h2>
                <span>Gunakan setelah menyimpan pengaturan</span>
            </div>
            <p class="muted-text">Tes ini mengirim prompt pendek ke provider yang dipilih. Pastikan internet aktif untuk API cloud, atau Ollama sudah berjalan untuk mode lokal.</p>
            <form method="post" action="" class="mt-16">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="test">
                <button type="submit" class="btn btn-secondary">Tes AI Sekarang</button>
            </form>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Log AI Terbaru</h2>
            <span>10 aktivitas terakhir</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Pesan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$logs): ?>
                        <tr><td colspan="4" class="empty-cell">Belum ada log AI.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= e($log['created_at']) ?></td>
                            <td><?= e(ai_provider_label($log['provider'])) ?></td>
                            <td><span class="badge <?= $log['status'] === 'success' ? 'success' : 'warning' ?>"><?= e($log['status']) ?></span></td>
                            <td><?= e($log['message']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel warning-panel">
        <div class="panel-header">
            <h2>Catatan Keamanan</h2>
            <span>Penting untuk production</span>
        </div>
        <p>API key disimpan di SQLite lokal agar mudah dipakai saat pengembangan. Untuk aplikasi production, pindahkan API key ke file konfigurasi di luar folder public atau gunakan environment variable.</p>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
