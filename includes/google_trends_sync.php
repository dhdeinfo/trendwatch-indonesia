<?php

function gt_feed_urls(): array
{
    return [
        'https://trends.google.com/trending/rss?geo=ID',
        'https://trends.google.co.id/trending/rss?geo=ID',
    ];
}

function gt_http_get(string $url, int $timeout = 20): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'TrendWatch Indonesia/1.0 (+PHP Native SQLite)',
            CURLOPT_HTTPHEADER => [
                'Accept: application/rss+xml, application/xml, text/xml;q=0.9, */*;q=0.8',
            ],
        ]);

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($body !== false && $status >= 200 && $status < 400) {
            return ['ok' => true, 'body' => $body, 'status' => $status, 'error' => ''];
        }

        return ['ok' => false, 'body' => (string) $body, 'status' => $status, 'error' => $error ?: 'HTTP status ' . $status];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => $timeout,
            'header' => "User-Agent: TrendWatch Indonesia/1.0\r\nAccept: application/rss+xml, application/xml, text/xml\r\n",
        ],
    ]);

    $body = @file_get_contents($url, false, $context);

    if ($body !== false) {
        return ['ok' => true, 'body' => $body, 'status' => 200, 'error' => ''];
    }

    return ['ok' => false, 'body' => '', 'status' => 0, 'error' => 'Gagal mengambil feed. Aktifkan cURL atau allow_url_fopen.'];
}

function gt_parse_volume_text(string $volumeText): int
{
    $text = strtolower(trim($volumeText));
    $text = str_replace(['+', ',', ' '], ['', '', ''], $text);
    $text = str_replace(['rb', 'ribu'], 'k', $text);
    $text = str_replace(['jt', 'juta'], 'm', $text);

    if (!preg_match('/([0-9]+(?:\.[0-9]+)?)([km]?)/', $text, $match)) {
        return 0;
    }

    $number = (float) $match[1];
    $suffix = $match[2] ?? '';

    if ($suffix === 'm') {
        $number *= 1000000;
    } elseif ($suffix === 'k') {
        $number *= 1000;
    }

    return (int) round($number);
}

function gt_seo_score(int $volume, string $category): int
{
    $score = 50;

    if ($volume >= 1000000) {
        $score = 98;
    } elseif ($volume >= 500000) {
        $score = 94;
    } elseif ($volume >= 200000) {
        $score = 90;
    } elseif ($volume >= 100000) {
        $score = 84;
    } elseif ($volume >= 50000) {
        $score = 78;
    } elseif ($volume >= 20000) {
        $score = 72;
    } elseif ($volume >= 10000) {
        $score = 66;
    } elseif ($volume > 0) {
        $score = 60;
    }

    $category = strtolower($category);
    if (in_array($category, ['berita', 'olahraga', 'hiburan', 'religi'], true)) {
        $score += 3;
    }

    return max(35, min(99, $score));
}

function gt_classify_category(string $text): string
{
    $lower = strtolower($text);

    $rules = [
        'Olahraga' => '/\b(vs|liga|bola|sepak bola|match|cup|fc|united|city|arsenal|liverpool|chelsea|persib|persija|piala|motogp|f1|basket|nba|badminton|tennis|fifa|world cup)\b/u',
        'Religi' => '/\b(doa|islam|hijriah|muharram|ramadan|ramadhan|puasa|zakat|haji|umrah|shalat|salat)\b/u',
        'Hiburan' => '/\b(film|drama|anime|lagu|musik|konser|artis|aktor|aktris|netflix|idol|kpop|youtube|tiktok)\b/u',
        'Teknologi' => '/\b(ai|iphone|android|google|openai|chatgpt|laptop|aplikasi|game|gadget|software|startup|crypto|bitcoin)\b/u',
        'Ekonomi' => '/\b(saham|rupiah|dolar|bank|pajak|harga|emas|inflasi|bursa|bisnis|umkm|ekonomi)\b/u',
        'Pendidikan' => '/\b(sekolah|kampus|universitas|beasiswa|ujian|ppdb|snpmB|snbt|skripsi|mahasiswa)\b/u',
        'Berita' => '/\b(kasus|kecelakaan|gempa|banjir|kebakaran|korupsi|politik|presiden|menteri|dpr|viral|terbaru)\b/u',
    ];

    foreach ($rules as $category => $pattern) {
        if (preg_match($pattern, $lower)) {
            return $category;
        }
    }

    return 'Umum';
}

function gt_content_angle(string $trendName, string $category): string
{
    $category = strtolower($category);

    if ($category === 'olahraga') {
        return 'Cocok untuk live score, prediksi, susunan pemain, dan hasil pertandingan.';
    }

    if ($category === 'religi') {
        return 'Cocok untuk artikel panduan, doa lengkap, makna, dan amalan terkait.';
    }

    if ($category === 'hiburan') {
        return 'Cocok untuk profil, jadwal tayang, sinopsis, dan reaksi publik.';
    }

    if ($category === 'teknologi') {
        return 'Cocok untuk ulasan cepat, perbandingan fitur, dan tutorial pemakaian.';
    }

    if ($category === 'ekonomi') {
        return 'Cocok untuk update harga, analisis singkat, dan dampaknya bagi publik.';
    }

    if ($category === 'pendidikan') {
        return 'Cocok untuk panduan, jadwal, syarat, dan tips praktis.';
    }

    if ($category === 'berita') {
        return 'Cocok untuk kronologi, fakta terbaru, profil, dan dampak peristiwa.';
    }

    return 'Cocok untuk artikel ringkas yang menjawab alasan topik ini sedang dicari.';
}

function gt_started_text(?string $pubDate): string
{
    if (!$pubDate) {
        return 'Baru disinkronkan';
    }

    $timestamp = strtotime($pubDate);
    if (!$timestamp) {
        return $pubDate;
    }

    $diff = max(0, time() - $timestamp);
    $hours = (int) floor($diff / 3600);
    $minutes = (int) floor(($diff % 3600) / 60);

    if ($hours >= 24) {
        $days = (int) floor($hours / 24);
        return $days . ' hari yang lalu';
    }

    if ($hours > 0) {
        return $hours . ' jam yang lalu';
    }

    if ($minutes > 0) {
        return $minutes . ' menit yang lalu';
    }

    return 'Baru saja';
}

function gt_clean_text(string $text): string
{
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text ?? '');
}

function gt_xpath_first(SimpleXMLElement $element, string $query): string
{
    $nodes = $element->xpath($query);
    if (!$nodes || !isset($nodes[0])) {
        return '';
    }

    return gt_clean_text((string) $nodes[0]);
}

function gt_parse_feed(string $xmlBody): array
{
    if (!extension_loaded('simplexml')) {
        throw new RuntimeException('Ekstensi SimpleXML belum aktif di PHP. Aktifkan extension=simplexml di php.ini.');
    }

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xmlBody, 'SimpleXMLElement', LIBXML_NOCDATA);

    if (!$xml) {
        $errors = libxml_get_errors();
        libxml_clear_errors();
        $message = $errors ? trim($errors[0]->message) : 'Format XML tidak valid.';
        throw new RuntimeException('Feed tidak bisa dibaca: ' . $message);
    }

    $items = [];

    if (isset($xml->channel->item)) {
        foreach ($xml->channel->item as $item) {
            $items[] = gt_extract_item($item);
        }
    } elseif (isset($xml->entry)) {
        foreach ($xml->entry as $entry) {
            $items[] = gt_extract_item($entry);
        }
    }

    return array_values(array_filter($items, static function ($item) {
        return !empty($item['trend_name']);
    }));
}

function gt_extract_item(SimpleXMLElement $item): array
{
    $title = gt_clean_text((string) ($item->title ?? ''));
    $link = gt_clean_text((string) ($item->link ?? ''));

    if (!$link && isset($item->link['href'])) {
        $link = gt_clean_text((string) $item->link['href']);
    }

    $pubDate = gt_clean_text((string) ($item->pubDate ?? $item->updated ?? $item->published ?? ''));
    $description = gt_clean_text((string) ($item->description ?? $item->summary ?? ''));

    $approxTraffic = gt_xpath_first($item, './*[local-name()="approx_traffic"]');
    $newsTitles = [];
    $newsNodes = $item->xpath('./*[local-name()="news_item"]/*[local-name()="news_item_title"]');
    if ($newsNodes) {
        foreach ($newsNodes as $node) {
            $text = gt_clean_text((string) $node);
            if ($text !== '') {
                $newsTitles[] = $text;
            }
        }
    }

    $related = trim(implode(', ', array_unique(array_slice($newsTitles, 0, 5))));
    if (!$related && $description) {
        $related = substr($description, 0, 250);
    }

    $volume = gt_parse_volume_text($approxTraffic);
    $volumeText = $approxTraffic ?: ($volume > 0 ? format_volume_text($volume) : '0+');
    $category = gt_classify_category($title . ' ' . $related . ' ' . $description);

    return [
        'trend_name' => $title,
        'search_volume' => $volume,
        'volume_text' => $volumeText,
        'started_text' => gt_started_text($pubDate),
        'status' => 'Aktif',
        'category' => $category,
        'related_keywords' => $related,
        'seo_score' => gt_seo_score($volume, $category),
        'content_angle' => gt_content_angle($title, $category),
        'source_url' => $link,
        'external_id' => sha1(strtolower($title . '|' . $link)),
    ];
}

function gt_sync_google_trends(PDO $pdo, ?string $manualUrl = null): array
{
    $urls = $manualUrl ? [$manualUrl] : gt_feed_urls();
    $lastError = '';
    $sourceUrl = '';
    $items = [];

    foreach ($urls as $url) {
        $sourceUrl = $url;
        $response = gt_http_get($url);

        if (!$response['ok']) {
            $lastError = $response['error'];
            continue;
        }

        try {
            $items = gt_parse_feed($response['body']);
        } catch (Throwable $e) {
            $lastError = $e->getMessage();
            continue;
        }

        if ($items) {
            break;
        }

        $lastError = 'Feed berhasil dibuka, tetapi tidak ada item tren yang terbaca.';
    }

    if (!$items) {
        gt_save_sync_log($pdo, $sourceUrl, 'Gagal', 0, 0, 0, $lastError ?: 'Tidak ada data yang berhasil diambil.');
        return [
            'ok' => false,
            'message' => $lastError ?: 'Tidak ada data yang berhasil diambil.',
            'source_url' => $sourceUrl,
            'fetched' => 0,
            'inserted' => 0,
            'updated' => 0,
        ];
    }

    $inserted = 0;
    $updated = 0;
    $now = date('Y-m-d H:i:s');
    $pdo->beginTransaction();

    try {
        foreach ($items as $item) {
            $stmt = $pdo->prepare('SELECT id, search_volume FROM trends WHERE lower(trend_name) = lower(:trend_name) LIMIT 1');
            $stmt->execute([':trend_name' => $item['trend_name']]);
            $existing = $stmt->fetch();

            if ($existing) {
                $trendId = (int) $existing['id'];
                $update = $pdo->prepare('UPDATE trends SET
                    search_volume = :search_volume,
                    volume_text = :volume_text,
                    started_text = :started_text,
                    status = :status,
                    category = :category,
                    related_keywords = :related_keywords,
                    seo_score = :seo_score,
                    content_angle = :content_angle,
                    source = :source,
                    source_url = :source_url,
                    external_id = :external_id,
                    last_synced_at = :last_synced_at
                    WHERE id = :id');
                $update->execute([
                    ':search_volume' => $item['search_volume'],
                    ':volume_text' => $item['volume_text'],
                    ':started_text' => $item['started_text'],
                    ':status' => $item['status'],
                    ':category' => $item['category'],
                    ':related_keywords' => $item['related_keywords'],
                    ':seo_score' => $item['seo_score'],
                    ':content_angle' => $item['content_angle'],
                    ':source' => 'google_trends',
                    ':source_url' => $item['source_url'],
                    ':external_id' => $item['external_id'],
                    ':last_synced_at' => $now,
                    ':id' => $trendId,
                ]);
                $updated++;
            } else {
                $insert = $pdo->prepare('INSERT INTO trends
                    (trend_name, search_volume, volume_text, started_text, status, category, related_keywords, seo_score, content_angle, source, source_url, external_id, last_synced_at)
                    VALUES
                    (:trend_name, :search_volume, :volume_text, :started_text, :status, :category, :related_keywords, :seo_score, :content_angle, :source, :source_url, :external_id, :last_synced_at)');
                $insert->execute([
                    ':trend_name' => $item['trend_name'],
                    ':search_volume' => $item['search_volume'],
                    ':volume_text' => $item['volume_text'],
                    ':started_text' => $item['started_text'],
                    ':status' => $item['status'],
                    ':category' => $item['category'],
                    ':related_keywords' => $item['related_keywords'],
                    ':seo_score' => $item['seo_score'],
                    ':content_angle' => $item['content_angle'],
                    ':source' => 'google_trends',
                    ':source_url' => $item['source_url'],
                    ':external_id' => $item['external_id'],
                    ':last_synced_at' => $now,
                ]);
                $trendId = (int) $pdo->lastInsertId();
                $inserted++;
            }

            $history = $pdo->prepare('INSERT INTO trend_histories (trend_id, hour_label, volume) VALUES (:trend_id, :hour_label, :volume)');
            $history->execute([
                ':trend_id' => $trendId,
                ':hour_label' => date('H:i'),
                ':volume' => $item['search_volume'],
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        gt_save_sync_log($pdo, $sourceUrl, 'Gagal', count($items), $inserted, $updated, $e->getMessage());
        return [
            'ok' => false,
            'message' => 'Sinkronisasi gagal: ' . $e->getMessage(),
            'source_url' => $sourceUrl,
            'fetched' => count($items),
            'inserted' => $inserted,
            'updated' => $updated,
        ];
    }

    $message = 'Sinkronisasi berhasil. Data tren Google Trends berhasil diperbarui.';
    gt_save_sync_log($pdo, $sourceUrl, 'Berhasil', count($items), $inserted, $updated, $message);

    return [
        'ok' => true,
        'message' => $message,
        'source_url' => $sourceUrl,
        'fetched' => count($items),
        'inserted' => $inserted,
        'updated' => $updated,
    ];
}

function gt_save_sync_log(PDO $pdo, string $sourceUrl, string $status, int $fetched, int $inserted, int $updated, string $message): void
{
    try {
        $stmt = $pdo->prepare('INSERT INTO sync_logs
            (source_name, source_url, status, total_fetched, total_inserted, total_updated, message)
            VALUES (:source_name, :source_url, :status, :total_fetched, :total_inserted, :total_updated, :message)');
        $stmt->execute([
            ':source_name' => 'Google Trends RSS Indonesia',
            ':source_url' => $sourceUrl,
            ':status' => $status,
            ':total_fetched' => $fetched,
            ':total_inserted' => $inserted,
            ':total_updated' => $updated,
            ':message' => $message,
        ]);
    } catch (Throwable $e) {
        // Log tidak boleh menghentikan aplikasi.
    }
}
