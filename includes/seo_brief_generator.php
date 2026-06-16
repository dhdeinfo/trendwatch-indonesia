<?php

function seo_split_keywords(?string $keywords): array
{
    $items = preg_split('/[,;\n]+/', (string) $keywords);
    $items = array_map(static fn($item) => trim($item), $items ?: []);
    $items = array_values(array_filter($items, static fn($item) => $item !== ''));
    return array_values(array_unique($items));
}

function seo_title_case(string $text): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if ($text === '') {
        return 'Topik Trending';
    }

    if (function_exists('mb_convert_case')) {
        return mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');
    }

    return ucwords(strtolower($text));
}

function seo_detect_search_intent(array $trend): string
{
    $name = strtolower($trend['trend_name'] ?? '');
    $category = strtolower($trend['category'] ?? '');
    $keywords = strtolower($trend['related_keywords'] ?? '');
    $text = $name . ' ' . $category . ' ' . $keywords;

    if (str_contains($text, 'vs') || str_contains($text, 'live') || str_contains($text, 'skor') || str_contains($category, 'olahraga')) {
        return 'Informasional cepat. Pengguna mencari jadwal, hasil, skor, susunan pemain, dan ringkasan pertandingan.';
    }

    if (str_contains($text, 'doa') || str_contains($text, 'hijriah') || str_contains($category, 'religi')) {
        return 'Informasional praktis. Pengguna mencari bacaan, arti, tata cara, waktu membaca, dan penjelasan singkat.';
    }

    if (str_contains($category, 'tokoh') || str_contains($text, 'profil') || str_contains($text, 'siapa')) {
        return 'Informasional profil. Pengguna ingin tahu identitas, latar belakang, fakta penting, dan alasan tokoh menjadi tren.';
    }

    if (str_contains($category, 'berita') || str_contains($text, 'kasus') || str_contains($text, 'terbaru')) {
        return 'Informasional berita. Pengguna mencari kronologi, fakta terbaru, konteks, dan dampak dari isu yang sedang ramai.';
    }

    if (str_contains($category, 'hiburan') || str_contains($text, 'film') || str_contains($text, 'artis')) {
        return 'Informasional hiburan. Pengguna mencari kabar terbaru, sinopsis, profil, respons publik, dan fakta menarik.';
    }

    return 'Informasional umum. Pengguna mencari arti topik, alasan topik menjadi tren, fakta utama, dan perkembangan terbaru.';
}

function seo_target_audience(array $trend): string
{
    $category = strtolower($trend['category'] ?? '');
    $name = strtolower($trend['trend_name'] ?? '');

    if (str_contains($category, 'olahraga') || str_contains($name, 'vs')) {
        return 'Pembaca berita olahraga, penggemar sepak bola, admin media olahraga, dan pengguna yang mencari update cepat.';
    }

    if (str_contains($category, 'religi') || str_contains($name, 'doa')) {
        return 'Pembaca muslim, pengelola media Islami, pelajar, keluarga, dan pengguna yang mencari panduan praktis.';
    }

    if (str_contains($category, 'tokoh')) {
        return 'Pembaca umum, pencari profil tokoh, jurnalis, penulis berita ringan, dan pengguna media sosial.';
    }

    if (str_contains($category, 'berita')) {
        return 'Pembaca berita nasional, penulis artikel aktual, admin portal berita, dan pengguna yang butuh konteks cepat.';
    }

    return 'Pembaca umum, content creator, blogger, tim SEO, dan admin media sosial yang mengikuti tren harian.';
}

function seo_recommended_format(array $trend): string
{
    $volume = (int) ($trend['search_volume'] ?? 0);
    $category = strtolower($trend['category'] ?? '');
    $name = strtolower($trend['trend_name'] ?? '');

    if (str_contains($category, 'olahraga') || str_contains($name, 'vs')) {
        return 'Artikel live update, artikel hasil pertandingan, short video 30 sampai 60 detik, dan carousel statistik.';
    }

    if ($volume >= 100000) {
        return 'Artikel SEO cepat 800 sampai 1.200 kata, posting Instagram carousel, dan video pendek rangkuman.';
    }

    if (str_contains($category, 'religi')) {
        return 'Artikel panduan evergreen 1.000 sampai 1.500 kata, infografis bacaan, dan konten carousel edukatif.';
    }

    return 'Artikel ringkas 700 sampai 1.000 kata, thread media sosial, dan video pendek penjelasan.';
}

function seo_word_count(array $trend): int
{
    $volume = (int) ($trend['search_volume'] ?? 0);
    $category = strtolower($trend['category'] ?? '');

    if ($volume >= 200000) {
        return 1400;
    }

    if ($volume >= 100000) {
        return 1200;
    }

    if (str_contains($category, 'olahraga')) {
        return 900;
    }

    return 1000;
}

function seo_priority_score(array $trend): int
{
    $score = (int) ($trend['seo_score'] ?? 0);
    $volume = (int) ($trend['search_volume'] ?? 0);
    $status = strtolower($trend['status'] ?? '');

    if ($score <= 0) {
        $score = 50;
    }

    if ($volume >= 200000) {
        $score += 10;
    } elseif ($volume >= 100000) {
        $score += 8;
    } elseif ($volume >= 50000) {
        $score += 5;
    }

    if ($status === 'aktif') {
        $score += 5;
    } elseif ($status === 'menurun') {
        $score -= 4;
    }

    return max(0, min(100, $score));
}

function seo_generate_title_options(array $trend): array
{
    $name = trim($trend['trend_name'] ?? 'Topik Trending');
    $titleName = seo_title_case($name);
    $category = strtolower($trend['category'] ?? '');

    if (str_contains($category, 'olahraga') || str_contains(strtolower($name), 'vs')) {
        return [
            $titleName . ': Jadwal, Prediksi, dan Link Update Terbaru',
            'Hasil ' . $titleName . ' Terbaru Lengkap dengan Ringkasan Pertandingan',
            'Prediksi ' . $titleName . ': Susunan Pemain, Statistik, dan Peluang Menang',
        ];
    }

    if (str_contains($category, 'religi') || str_contains(strtolower($name), 'doa')) {
        return [
            $titleName . ' Lengkap dengan Arab, Latin, Arti, dan Waktu Membacanya',
            'Bacaan ' . $titleName . ' dan Maknanya yang Mudah Dipahami',
            'Panduan ' . $titleName . ': Bacaan, Arti, dan Amalan yang Dianjurkan',
        ];
    }

    if (str_contains($category, 'tokoh')) {
        return [
            'Profil ' . $titleName . ', Sosok yang Sedang Ramai Dicari',
            'Siapa ' . $titleName . '? Ini Profil, Fakta, dan Latar Belakangnya',
            'Fakta Menarik ' . $titleName . ' yang Membuatnya Jadi Perbincangan',
        ];
    }

    if (str_contains($category, 'berita')) {
        return [
            $titleName . ': Kronologi, Fakta Terbaru, dan Hal yang Perlu Diketahui',
            'Apa Itu ' . $titleName . '? Ini Penjelasan dan Perkembangan Terbarunya',
            $titleName . ' Jadi Sorotan, Ini Fakta dan Konteksnya',
        ];
    }

    return [
        $titleName . ': Pengertian, Fakta, dan Alasan Menjadi Tren',
        'Kenapa ' . $titleName . ' Ramai Dicari? Ini Penjelasan Lengkapnya',
        $titleName . ' Sedang Trending, Ini Fakta dan Update Terbarunya',
    ];
}

function seo_generate_outline(array $trend): string
{
    $name = seo_title_case($trend['trend_name'] ?? 'Topik Trending');
    $category = strtolower($trend['category'] ?? '');

    if (str_contains($category, 'olahraga') || str_contains(strtolower($name), 'Vs')) {
        return "H1: {$name}\nH2: Ringkasan Pertandingan\nH2: Jadwal dan Waktu Kick Off\nH2: Prediksi Susunan Pemain\nH2: Statistik Kedua Tim\nH2: Pemain Kunci yang Perlu Diperhatikan\nH2: Prediksi Skor\nH2: Update Hasil Terbaru\nH2: Kesimpulan";
    }

    if (str_contains($category, 'religi') || str_contains(strtolower($name), 'Doa')) {
        return "H1: {$name}\nH2: Pengertian Singkat\nH2: Bacaan Lengkap\nH2: Latin dan Artinya\nH2: Waktu yang Tepat untuk Membaca\nH2: Makna dan Hikmah\nH2: Amalan Pendukung\nH2: Pertanyaan yang Sering Ditanyakan\nH2: Kesimpulan";
    }

    if (str_contains($category, 'tokoh')) {
        return "H1: Profil {$name}\nH2: Siapa {$name}?\nH2: Latar Belakang Singkat\nH2: Perjalanan dan Fakta Penting\nH2: Alasan Menjadi Tren\nH2: Respons Publik\nH2: Fakta yang Perlu Diverifikasi\nH2: Kesimpulan";
    }

    return "H1: {$name}\nH2: Apa Itu {$name}?\nH2: Kenapa Topik Ini Menjadi Tren?\nH2: Fakta Utama yang Perlu Diketahui\nH2: Perkembangan Terbaru\nH2: Dampak bagi Pembaca\nH2: Rekomendasi Konten Lanjutan\nH2: Kesimpulan";
}

function seo_generate_faq(array $trend): string
{
    $name = trim($trend['trend_name'] ?? 'topik ini');
    $titleName = seo_title_case($name);

    return "1. Apa itu {$titleName}?\n2. Mengapa {$titleName} menjadi tren?\n3. Apa fakta terbaru tentang {$titleName}?\n4. Siapa saja yang perlu mengikuti update {$titleName}?\n5. Bagaimana cara mendapatkan informasi terbaru tentang {$titleName}?";
}

function seo_generate_platform_ideas(array $trend): string
{
    $name = seo_title_case($trend['trend_name'] ?? 'Topik Trending');
    $category = strtolower($trend['category'] ?? '');

    if (str_contains($category, 'olahraga')) {
        return "Instagram Carousel: 5 fakta penting sebelum {$name}\nReels/TikTok: Prediksi skor cepat dalam 30 detik\nX/Threads: Live update poin penting pertandingan\nYouTube Shorts: Ringkasan hasil dan pemain terbaik";
    }

    if (str_contains($category, 'religi')) {
        return "Instagram Carousel: Bacaan dan arti {$name}\nReels/TikTok: Cara membaca {$name} dengan pelan\nWhatsApp Status: Kutipan singkat dan makna utama\nBlog: Panduan lengkap dengan penjelasan konteks";
    }

    return "Instagram Carousel: 5 fakta tentang {$name}\nReels/TikTok: Kenapa {$name} viral?\nX/Threads: Kronologi singkat dalam beberapa poin\nBlog: Artikel lengkap berbasis SEO";
}

function seo_generate_brief(array $trend): array
{
    $keywordList = seo_split_keywords($trend['related_keywords'] ?? '');
    $mainKeyword = trim($trend['trend_name'] ?? 'topik trending');
    $secondaryKeywords = $keywordList ? implode(', ', array_slice($keywordList, 0, 8)) : $mainKeyword . ', berita terbaru, topik viral';
    $titleOptions = seo_generate_title_options($trend);
    $title = $titleOptions[0] ?? seo_title_case($mainKeyword);
    $metaKeyword = strtolower($mainKeyword);
    $wordCount = seo_word_count($trend);
    $priority = seo_priority_score($trend);

    return [
        'main_keyword' => $mainKeyword,
        'secondary_keywords' => $secondaryKeywords,
        'search_intent' => seo_detect_search_intent($trend),
        'target_audience' => seo_target_audience($trend),
        'recommended_format' => seo_recommended_format($trend),
        'title_options' => implode("\n", $titleOptions),
        'selected_title' => $title,
        'meta_description' => 'Simak informasi terbaru tentang ' . $metaKeyword . ', lengkap dengan fakta utama, konteks, keyword terkait, dan rekomendasi konten yang mudah dipahami.',
        'content_angle' => $trend['content_angle'] ?: 'Bahas topik secara cepat, jelas, dan berbasis kebutuhan pencarian pengguna.',
        'outline' => seo_generate_outline($trend),
        'intro_hook' => seo_title_case($mainKeyword) . ' sedang ramai dicari. Artikel ini perlu menjawab pertanyaan utama pembaca sejak paragraf pertama agar sesuai dengan intent pencarian.',
        'faq_items' => seo_generate_faq($trend),
        'platform_ideas' => seo_generate_platform_ideas($trend),
        'word_count' => $wordCount,
        'priority_score' => $priority,
    ];
}

function seo_save_brief(PDO $pdo, int $trendId, array $brief): int
{
    $brief['generator_source'] = $brief['generator_source'] ?? 'template';
    $brief['ai_provider'] = $brief['ai_provider'] ?? 'template';
    $brief['ai_status'] = $brief['ai_status'] ?? 'fallback';
    $brief['ai_message'] = $brief['ai_message'] ?? '';

    $existing = $pdo->prepare('SELECT id FROM content_briefs WHERE trend_id = :trend_id LIMIT 1');
    $existing->execute([':trend_id' => $trendId]);
    $briefId = (int) ($existing->fetchColumn() ?: 0);

    if ($briefId > 0) {
        $stmt = $pdo->prepare("UPDATE content_briefs SET
            main_keyword = :main_keyword,
            secondary_keywords = :secondary_keywords,
            search_intent = :search_intent,
            target_audience = :target_audience,
            recommended_format = :recommended_format,
            title_options = :title_options,
            selected_title = :selected_title,
            meta_description = :meta_description,
            content_angle = :content_angle,
            outline = :outline,
            intro_hook = :intro_hook,
            faq_items = :faq_items,
            platform_ideas = :platform_ideas,
            word_count = :word_count,
            priority_score = :priority_score,
            generator_source = :generator_source,
            ai_provider = :ai_provider,
            ai_status = :ai_status,
            ai_message = :ai_message,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id");
        $brief['id'] = $briefId;
        $stmt->execute([
            ':main_keyword' => $brief['main_keyword'],
            ':secondary_keywords' => $brief['secondary_keywords'],
            ':search_intent' => $brief['search_intent'],
            ':target_audience' => $brief['target_audience'],
            ':recommended_format' => $brief['recommended_format'],
            ':title_options' => $brief['title_options'],
            ':selected_title' => $brief['selected_title'],
            ':meta_description' => $brief['meta_description'],
            ':content_angle' => $brief['content_angle'],
            ':outline' => $brief['outline'],
            ':intro_hook' => $brief['intro_hook'],
            ':faq_items' => $brief['faq_items'],
            ':platform_ideas' => $brief['platform_ideas'],
            ':word_count' => $brief['word_count'],
            ':priority_score' => $brief['priority_score'],
            ':generator_source' => $brief['generator_source'],
            ':ai_provider' => $brief['ai_provider'],
            ':ai_status' => $brief['ai_status'],
            ':ai_message' => $brief['ai_message'],
            ':id' => $briefId,
        ]);
        return $briefId;
    }

    $stmt = $pdo->prepare("INSERT INTO content_briefs
        (trend_id, main_keyword, secondary_keywords, search_intent, target_audience, recommended_format, title_options, selected_title, meta_description, content_angle, outline, intro_hook, faq_items, platform_ideas, word_count, priority_score, generator_source, ai_provider, ai_status, ai_message)
        VALUES (:trend_id, :main_keyword, :secondary_keywords, :search_intent, :target_audience, :recommended_format, :title_options, :selected_title, :meta_description, :content_angle, :outline, :intro_hook, :faq_items, :platform_ideas, :word_count, :priority_score, :generator_source, :ai_provider, :ai_status, :ai_message)");
    $stmt->execute([
        ':trend_id' => $trendId,
        ':main_keyword' => $brief['main_keyword'],
        ':secondary_keywords' => $brief['secondary_keywords'],
        ':search_intent' => $brief['search_intent'],
        ':target_audience' => $brief['target_audience'],
        ':recommended_format' => $brief['recommended_format'],
        ':title_options' => $brief['title_options'],
        ':selected_title' => $brief['selected_title'],
        ':meta_description' => $brief['meta_description'],
        ':content_angle' => $brief['content_angle'],
        ':outline' => $brief['outline'],
        ':intro_hook' => $brief['intro_hook'],
        ':faq_items' => $brief['faq_items'],
        ':platform_ideas' => $brief['platform_ideas'],
        ':word_count' => $brief['word_count'],
        ':priority_score' => $brief['priority_score'],
        ':generator_source' => $brief['generator_source'],
        ':ai_provider' => $brief['ai_provider'],
        ':ai_status' => $brief['ai_status'],
        ':ai_message' => $brief['ai_message'],
    ]);

    return (int) $pdo->lastInsertId();
}
