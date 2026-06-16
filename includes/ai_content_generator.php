<?php

require_once __DIR__ . '/seo_brief_generator.php';

function ai_default_settings(): array
{
    return [
        'ai_enabled' => '0',
        'ai_provider' => 'template',
        'ai_api_key' => '',
        'ai_model' => 'gemini-1.5-flash',
        'ai_endpoint' => '',
        'ai_temperature' => '0.7',
    ];
}

function ai_get_settings(PDO $pdo): array
{
    $settings = ai_default_settings();

    try {
        $rows = $pdo->query('SELECT setting_key, setting_value FROM app_settings')->fetchAll();
        foreach ($rows as $row) {
            if (array_key_exists($row['setting_key'], $settings)) {
                $settings[$row['setting_key']] = (string) ($row['setting_value'] ?? '');
            }
        }
    } catch (PDOException $e) {
        // App tetap jalan dengan setting default.
    }

    return $settings;
}

function ai_save_settings(PDO $pdo, array $settings): void
{
    $allowed = ai_default_settings();
    $stmt = $pdo->prepare("INSERT INTO app_settings (setting_key, setting_value, updated_at)
        VALUES (:key, :value, CURRENT_TIMESTAMP)
        ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, updated_at = CURRENT_TIMESTAMP");

    foreach ($allowed as $key => $default) {
        if (!array_key_exists($key, $settings)) {
            continue;
        }

        $stmt->execute([
            ':key' => $key,
            ':value' => (string) $settings[$key],
        ]);
    }
}

function ai_mask_key(string $key): string
{
    $key = trim($key);
    if ($key === '') {
        return 'Belum diisi';
    }

    if (strlen($key) <= 8) {
        return str_repeat('*', strlen($key));
    }

    return substr($key, 0, 4) . str_repeat('*', max(4, strlen($key) - 8)) . substr($key, -4);
}

function ai_provider_label(string $provider): string
{
    $labels = [
        'gemini' => 'Gemini API',
        'openai' => 'OpenAI API',
        'openrouter' => 'OpenRouter',
        'ollama' => 'Ollama Lokal',
        'custom' => 'Custom OpenAI-Compatible',
        'template' => 'Template Gratis',
    ];

    return $labels[$provider] ?? 'Template Gratis';
}

function ai_is_enabled(array $settings): bool
{
    return ($settings['ai_enabled'] ?? '0') === '1' && ($settings['ai_provider'] ?? 'template') !== 'template';
}

function ai_build_prompt(array $trend): string
{
    $trendName = trim($trend['trend_name'] ?? 'Topik Trending');
    $category = trim($trend['category'] ?? 'Umum');
    $volume = (int) ($trend['search_volume'] ?? 0);
    $volumeText = trim($trend['volume_text'] ?? format_volume_text($volume));
    $keywords = trim($trend['related_keywords'] ?? '');
    $status = trim($trend['status'] ?? 'Aktif');
    $angle = trim($trend['content_angle'] ?? '');

    return "Anda adalah SEO content strategist berbahasa Indonesia. Buat SEO content brief yang praktis, faktual, dan siap dipakai untuk penulis. Jangan mengarang data spesifik yang tidak tersedia. Gunakan bahasa Indonesia yang jelas.\n\n" .
        "Data tren:\n" .
        "- Tren: {$trendName}\n" .
        "- Kategori: {$category}\n" .
        "- Volume pencarian: {$volumeText} ({$volume})\n" .
        "- Status: {$status}\n" .
        "- Keyword terkait: {$keywords}\n" .
        "- Angle awal: {$angle}\n\n" .
        "Kembalikan hanya JSON valid tanpa markdown. Gunakan struktur ini:\n" .
        "{\n" .
        "  \"main_keyword\": \"...\",\n" .
        "  \"secondary_keywords\": \"keyword 1, keyword 2, keyword 3\",\n" .
        "  \"search_intent\": \"...\",\n" .
        "  \"target_audience\": \"...\",\n" .
        "  \"recommended_format\": \"...\",\n" .
        "  \"title_options\": \"Judul 1\\nJudul 2\\nJudul 3\",\n" .
        "  \"selected_title\": \"...\",\n" .
        "  \"meta_description\": \"maksimal 155 karakter\",\n" .
        "  \"content_angle\": \"...\",\n" .
        "  \"outline\": \"H1: ...\\nH2: ...\\nH2: ...\",\n" .
        "  \"intro_hook\": \"...\",\n" .
        "  \"faq_items\": \"1. ...\\n2. ...\\n3. ...\\n4. ...\\n5. ...\",\n" .
        "  \"platform_ideas\": \"Instagram Carousel: ...\\nReels/TikTok: ...\\nX/Threads: ...\\nBlog: ...\",\n" .
        "  \"word_count\": 1000,\n" .
        "  \"priority_score\": 80\n" .
        "}";
}

function ai_http_post_json(string $url, array $payload, array $headers = [], int $timeout = 45): array
{
    if (!function_exists('curl_init')) {
        return [false, 'Ekstensi cURL belum aktif di PHP.', 0];
    }

    $ch = curl_init($url);
    $baseHeaders = ['Content-Type: application/json'];
    $headers = array_merge($baseHeaders, $headers);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return [false, $error ?: 'Request AI gagal.', $httpCode];
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        return [false, 'HTTP ' . $httpCode . ': ' . substr((string) $response, 0, 600), $httpCode];
    }

    return [true, (string) $response, $httpCode];
}

function ai_call_gemini(array $settings, string $prompt): array
{
    $apiKey = trim($settings['ai_api_key'] ?? '');
    $model = trim($settings['ai_model'] ?? 'gemini-1.5-flash') ?: 'gemini-1.5-flash';

    if ($apiKey === '') {
        return [false, 'API key Gemini belum diisi.'];
    }

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($apiKey);
    $payload = [
        'contents' => [[
            'parts' => [[
                'text' => $prompt,
            ]],
        ]],
        'generationConfig' => [
            'temperature' => (float) ($settings['ai_temperature'] ?? 0.7),
            'responseMimeType' => 'application/json',
        ],
    ];

    [$ok, $response] = ai_http_post_json($url, $payload);
    if (!$ok) {
        return [false, $response];
    }

    $json = json_decode($response, true);
    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
    return $text !== '' ? [true, $text] : [false, 'Respons Gemini kosong atau format tidak dikenali.'];
}

function ai_call_chat_completion(array $settings, string $prompt, string $provider): array
{
    $apiKey = trim($settings['ai_api_key'] ?? '');
    $model = trim($settings['ai_model'] ?? '');
    $endpoint = trim($settings['ai_endpoint'] ?? '');

    if ($provider !== 'ollama' && $apiKey === '') {
        return [false, 'API key belum diisi.'];
    }

    if ($provider === 'openai') {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $model = $model ?: 'gpt-4o-mini';
    } elseif ($provider === 'openrouter') {
        $endpoint = 'https://openrouter.ai/api/v1/chat/completions';
        $model = $model ?: 'openrouter/auto';
    } elseif ($provider === 'custom') {
        if ($endpoint === '') {
            return [false, 'Endpoint custom belum diisi.'];
        }
        $model = $model ?: 'gpt-4o-mini';
    }

    $headers = ['Authorization: Bearer ' . $apiKey];
    if ($provider === 'openrouter') {
        $headers[] = 'HTTP-Referer: http://localhost';
        $headers[] = 'X-Title: TrendWatch Indonesia';
    }

    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'Anda adalah SEO strategist Indonesia. Jawab hanya JSON valid.'],
            ['role' => 'user', 'content' => $prompt],
        ],
        'temperature' => (float) ($settings['ai_temperature'] ?? 0.7),
    ];

    [$ok, $response] = ai_http_post_json($endpoint, $payload, $headers);
    if (!$ok) {
        return [false, $response];
    }

    $json = json_decode($response, true);
    $text = $json['choices'][0]['message']['content'] ?? '';
    return $text !== '' ? [true, $text] : [false, 'Respons chat completion kosong atau format tidak dikenali.'];
}

function ai_call_ollama(array $settings, string $prompt): array
{
    $endpoint = trim($settings['ai_endpoint'] ?? '') ?: 'http://localhost:11434/api/generate';
    $model = trim($settings['ai_model'] ?? '') ?: 'llama3.1';

    $payload = [
        'model' => $model,
        'prompt' => $prompt,
        'stream' => false,
        'format' => 'json',
        'options' => [
            'temperature' => (float) ($settings['ai_temperature'] ?? 0.7),
        ],
    ];

    [$ok, $response] = ai_http_post_json($endpoint, $payload);
    if (!$ok) {
        return [false, $response];
    }

    $json = json_decode($response, true);
    $text = $json['response'] ?? '';
    return $text !== '' ? [true, $text] : [false, 'Respons Ollama kosong atau format tidak dikenali.'];
}

function ai_call_provider(array $settings, string $prompt): array
{
    $provider = $settings['ai_provider'] ?? 'template';

    if ($provider === 'gemini') {
        return ai_call_gemini($settings, $prompt);
    }

    if (in_array($provider, ['openai', 'openrouter', 'custom'], true)) {
        return ai_call_chat_completion($settings, $prompt, $provider);
    }

    if ($provider === 'ollama') {
        return ai_call_ollama($settings, $prompt);
    }

    return [false, 'Mode AI tidak aktif.'];
}

function ai_extract_json(string $text): ?array
{
    $text = trim($text);
    $text = preg_replace('/^```json\s*/i', '', $text);
    $text = preg_replace('/^```\s*/', '', $text);
    $text = preg_replace('/\s*```$/', '', $text);
    $text = trim($text);

    $decoded = json_decode($text, true);
    if (is_array($decoded)) {
        return $decoded;
    }

    $start = strpos($text, '{');
    $end = strrpos($text, '}');
    if ($start !== false && $end !== false && $end > $start) {
        $candidate = substr($text, $start, $end - $start + 1);
        $decoded = json_decode($candidate, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    return null;
}

function ai_normalize_brief(array $trend, array $aiData, array $fallback, string $provider): array
{
    $fields = [
        'main_keyword', 'secondary_keywords', 'search_intent', 'target_audience', 'recommended_format',
        'title_options', 'selected_title', 'meta_description', 'content_angle', 'outline', 'intro_hook',
        'faq_items', 'platform_ideas', 'word_count', 'priority_score',
    ];

    $brief = $fallback;
    foreach ($fields as $field) {
        if (!array_key_exists($field, $aiData)) {
            continue;
        }

        if (in_array($field, ['word_count', 'priority_score'], true)) {
            $brief[$field] = (int) $aiData[$field];
            continue;
        }

        $value = trim((string) $aiData[$field]);
        if ($value !== '') {
            $brief[$field] = $value;
        }
    }

    $brief['word_count'] = max(500, min(2500, (int) ($brief['word_count'] ?? 1000)));
    $brief['priority_score'] = max(0, min(100, (int) ($brief['priority_score'] ?? 70)));
    $brief['generator_source'] = 'ai';
    $brief['ai_provider'] = $provider;
    $brief['ai_status'] = 'success';
    $brief['ai_message'] = 'Brief dibuat dengan ' . ai_provider_label($provider) . '.';

    return $brief;
}

function ai_safe_substr(string $text, int $length): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $length);
    }

    return substr($text, 0, $length);
}

function ai_log_generation(PDO $pdo, ?int $trendId, string $provider, string $status, string $message, string $prompt = '', string $response = ''): void
{
    try {
        $stmt = $pdo->prepare('INSERT INTO ai_generation_logs (trend_id, provider, status, message, prompt_excerpt, response_excerpt)
            VALUES (:trend_id, :provider, :status, :message, :prompt_excerpt, :response_excerpt)');
        $stmt->execute([
            ':trend_id' => $trendId,
            ':provider' => $provider,
            ':status' => $status,
            ':message' => ai_safe_substr($message, 500),
            ':prompt_excerpt' => ai_safe_substr($prompt, 1000),
            ':response_excerpt' => ai_safe_substr($response, 1000),
        ]);
    } catch (Throwable $e) {
        // Log gagal tidak boleh menghentikan aplikasi.
    }
}

function ai_generate_seo_brief(PDO $pdo, array $trend): array
{
    $fallback = seo_generate_brief($trend);
    $fallback['generator_source'] = 'template';
    $fallback['ai_provider'] = 'template';
    $fallback['ai_status'] = 'fallback';
    $fallback['ai_message'] = 'Brief dibuat memakai template gratis.';

    $settings = ai_get_settings($pdo);
    $provider = $settings['ai_provider'] ?? 'template';

    if (!ai_is_enabled($settings)) {
        return $fallback;
    }

    $prompt = ai_build_prompt($trend);
    [$ok, $response] = ai_call_provider($settings, $prompt);

    if (!$ok) {
        $fallback['ai_provider'] = $provider;
        $fallback['ai_status'] = 'fallback';
        $fallback['ai_message'] = 'AI gagal. Sistem memakai template gratis. Detail: ' . $response;
        ai_log_generation($pdo, (int) ($trend['id'] ?? 0), $provider, 'failed', $response, $prompt, '');
        return $fallback;
    }

    $json = ai_extract_json($response);
    if (!$json) {
        $fallback['ai_provider'] = $provider;
        $fallback['ai_status'] = 'fallback';
        $fallback['ai_message'] = 'Respons AI bukan JSON valid. Sistem memakai template gratis.';
        ai_log_generation($pdo, (int) ($trend['id'] ?? 0), $provider, 'invalid_json', 'Respons AI bukan JSON valid.', $prompt, $response);
        return $fallback;
    }

    $brief = ai_normalize_brief($trend, $json, $fallback, $provider);
    ai_log_generation($pdo, (int) ($trend['id'] ?? 0), $provider, 'success', 'Brief AI berhasil dibuat.', $prompt, $response);
    return $brief;
}
