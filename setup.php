<?php

require_once __DIR__ . '/config/database.php';

$message = '';
$error = '';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS trends (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        trend_name TEXT NOT NULL,
        search_volume INTEGER DEFAULT 0,
        volume_text TEXT,
        started_text TEXT,
        status TEXT DEFAULT 'Aktif',
        category TEXT DEFAULT 'Umum',
        related_keywords TEXT,
        seo_score INTEGER DEFAULT 0,
        content_angle TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS trend_histories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        trend_id INTEGER NOT NULL,
        hour_label TEXT NOT NULL,
        volume INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trend_id) REFERENCES trends(id) ON DELETE CASCADE
    )");



    $pdo->exec("CREATE TABLE IF NOT EXISTS content_briefs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        trend_id INTEGER NOT NULL,
        main_keyword TEXT NOT NULL,
        secondary_keywords TEXT,
        search_intent TEXT,
        target_audience TEXT,
        recommended_format TEXT,
        title_options TEXT,
        selected_title TEXT,
        meta_description TEXT,
        content_angle TEXT,
        outline TEXT,
        intro_hook TEXT,
        faq_items TEXT,
        platform_ideas TEXT,
        word_count INTEGER DEFAULT 1000,
        priority_score INTEGER DEFAULT 0,
        generator_source TEXT DEFAULT 'template',
        ai_provider TEXT,
        ai_status TEXT,
        ai_message TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trend_id) REFERENCES trends(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS app_settings (
        setting_key TEXT PRIMARY KEY,
        setting_value TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ai_generation_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        trend_id INTEGER,
        provider TEXT NOT NULL,
        status TEXT NOT NULL,
        message TEXT,
        prompt_excerpt TEXT,
        response_excerpt TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trend_id) REFERENCES trends(id) ON DELETE SET NULL
    )");

    $checkAdmin = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();

    if ($checkAdmin === 0) {
        $stmt = $pdo->prepare('INSERT INTO admins (name, email, password) VALUES (:name, :email, :password)');
        $stmt->execute([
            ':name' => 'Administrator',
            ':email' => 'admin@gmail.com',
            ':password' => password_hash('admin123', PASSWORD_DEFAULT),
        ]);
    }

    $checkTrends = (int) $pdo->query('SELECT COUNT(*) FROM trends')->fetchColumn();

    if ($checkTrends === 0) {
        $sampleTrends = [
            ['doa awal tahun', 200000, '200 rb+', '20 jam yang lalu', 'Aktif', 'Religi', '1 muharram 2026, doa akhir tahun, tahun baru hijriah', 95, 'Konten evergreen islami dengan kebutuhan cepat'],
            ['spanyol vs cape verde', 100000, '100 rb+', '15 jam yang lalu', 'Aktif', 'Olahraga', 'spain vs tanjung verde, cape verde, jadwal bola', 82, 'Berita pertandingan dan hasil skor'],
            ['belgia vs mesir', 100000, '100 rb+', '15 jam yang lalu', 'Aktif', 'Olahraga', 'belgium vs egypt, mohamed salah, mesir vs belgia', 80, 'Preview dan rekap pertandingan'],
            ['arab saudi vs uruguay', 50000, '50 rb+', '8 jam yang lalu', 'Aktif', 'Olahraga', 'uruguay vs arab saudi, jadwal pertandingan', 76, 'Konten live score dan susunan pemain'],
            ['iran vs selandia baru', 20000, '20 rb+', '7 jam yang lalu', 'Aktif', 'Olahraga', 'iran vs new zealand, chris wood, mehdi taremi', 72, 'Konten hasil pertandingan singkat'],
            ['vozinha', 20000, '20 rb+', '10 jam yang lalu', 'Aktif', 'Tokoh', 'cabo verde, sidny lopes cabral, kiper cape verde', 67, 'Profil tokoh yang sedang dicari'],
            ['spain', 50000, '50 rb+', '11 jam yang lalu', 'Aktif', 'Olahraga', '2026, dailon livramento, spain vs', 75, 'Konten ringkas seputar tim nasional'],
            ['eddy tansil', 10000, '10 rb+', '19 jam yang lalu', 'Aktif', 'Berita', 'kasus eddy tansil, berita terbaru', 70, 'Artikel kronologi dan latar belakang'],
            ['iran vs new zealand', 10000, '10 rb+', '5 jam yang lalu', 'Aktif', 'Olahraga', 'new zealand, ramin rezaeian, piala dunia', 68, 'Update pertandingan dan statistik'],
            ['belgium vs egypt', 20000, '20 rb+', '8 jam yang lalu', 'Aktif', 'Olahraga', 'belgia, emam ashour, charles de ketelaere', 73, 'Artikel prediksi dan hasil pertandingan'],
        ];

        $stmt = $pdo->prepare("INSERT INTO trends
            (trend_name, search_volume, volume_text, started_text, status, category, related_keywords, seo_score, content_angle)
            VALUES (:trend_name, :search_volume, :volume_text, :started_text, :status, :category, :related_keywords, :seo_score, :content_angle)");

        foreach ($sampleTrends as $trend) {
            $stmt->execute([
                ':trend_name' => $trend[0],
                ':search_volume' => $trend[1],
                ':volume_text' => $trend[2],
                ':started_text' => $trend[3],
                ':status' => $trend[4],
                ':category' => $trend[5],
                ':related_keywords' => $trend[6],
                ':seo_score' => $trend[7],
                ':content_angle' => $trend[8],
            ]);

            $trendId = (int) $pdo->lastInsertId();
            $historyStmt = $pdo->prepare('INSERT INTO trend_histories (trend_id, hour_label, volume) VALUES (:trend_id, :hour_label, :volume)');

            $points = [8, 12, 18, 25, 40, 70, 100];
            foreach ($points as $index => $multiplier) {
                $historyStmt->execute([
                    ':trend_id' => $trendId,
                    ':hour_label' => ($index * 4) . 'j',
                    ':volume' => (int) round(($trend[1] * $multiplier) / 100),
                ]);
            }
        }
    }

    $settings = [
        'ai_enabled' => '0',
        'ai_provider' => 'template',
        'ai_api_key' => '',
        'ai_model' => 'gemini-1.5-flash',
        'ai_endpoint' => '',
        'ai_temperature' => '0.7',
    ];
    $settingStmt = $pdo->prepare("INSERT OR IGNORE INTO app_settings (setting_key, setting_value) VALUES (:key, :value)");
    foreach ($settings as $key => $value) {
        $settingStmt->execute([':key' => $key, ':value' => $value]);
    }

    $message = 'Setup database berhasil.';
} catch (PDOException $e) {
    $error = 'Setup gagal: ' . $e->getMessage();
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Database - <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
</head>
<body class="setup-body">
    <main class="setup-card">
        <h1>Setup Database</h1>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
            <p>Database SQLite sudah siap dipakai.</p>
            <div class="credential-box">
                <p><strong>Email:</strong> admin@gmail.com</p>
                <p><strong>Password:</strong> admin123</p>
            </div>
            <a class="btn btn-primary" href="<?= e(url('auth/login.php')) ?>">Masuk ke Login</a>
            <a class="btn btn-secondary" href="<?= e(url('check_connection.php')) ?>">Cek Koneksi</a>
        <?php else: ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
    </main>
</body>
</html>
