<?php

function cg_lines(?string $text): array
{
    $items = preg_split('/\r\n|\r|\n/', (string) $text);
    $items = array_map(static fn($item) => trim($item), $items ?: []);
    return array_values(array_filter($items, static fn($item) => $item !== ''));
}

function cg_clean_line(string $line): string
{
    $line = preg_replace('/^(H1|H2|H3):\s*/i', '', $line);
    $line = preg_replace('/^\d+\.\s*/', '', $line);
    return trim((string) $line);
}

function cg_limit_text(string $text, int $length): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $length) {
        return rtrim(mb_substr($text, 0, $length - 3, 'UTF-8')) . '...';
    }
    if (strlen($text) > $length) {
        return rtrim(substr($text, 0, $length - 3)) . '...';
    }
    return $text;
}

function cg_type_label(string $type): string
{
    $labels = [
        'article' => 'Artikel SEO',
        'instagram_carousel' => 'Instagram Carousel',
        'reels_tiktok' => 'Reels/TikTok',
        'x_threads' => 'X/Threads',
        'youtube_shorts' => 'YouTube Shorts',
    ];

    return $labels[$type] ?? 'Konten';
}

function cg_allowed_types(): array
{
    return ['article', 'instagram_carousel', 'reels_tiktok', 'x_threads', 'youtube_shorts'];
}

function cg_context(array $brief): array
{
    $keyword = trim((string) ($brief['main_keyword'] ?? $brief['trend_name'] ?? 'topik trending'));
    $title = trim((string) ($brief['selected_title'] ?? 'Konten Tren'));
    $meta = trim((string) ($brief['meta_description'] ?? ''));
    $intent = trim((string) ($brief['search_intent'] ?? 'Informasional umum.'));
    $audience = trim((string) ($brief['target_audience'] ?? 'Pembaca umum.'));
    $angle = trim((string) ($brief['content_angle'] ?? 'Bahas topik secara jelas, ringkas, dan mudah dipahami.'));
    $hook = trim((string) ($brief['intro_hook'] ?? ($keyword . ' sedang ramai dicari.')));
    $outline = cg_lines($brief['outline'] ?? '');
    $faq = cg_lines($brief['faq_items'] ?? '');
    $secondary = array_values(array_filter(array_map('trim', explode(',', (string) ($brief['secondary_keywords'] ?? '')))));
    $category = trim((string) ($brief['category'] ?? 'Umum'));
    $volumeText = trim((string) ($brief['volume_text'] ?? ''));

    if (!$outline) {
        $outline = [
            'H1: ' . $title,
            'H2: Apa yang sedang terjadi?',
            'H2: Fakta utama yang perlu diketahui',
            'H2: Kenapa topik ini ramai dicari?',
            'H2: Hal yang perlu diperhatikan pembaca',
            'H2: Kesimpulan',
        ];
    }

    if (!$faq) {
        $faq = [
            '1. Apa itu ' . $keyword . '?',
            '2. Kenapa ' . $keyword . ' menjadi tren?',
            '3. Apa informasi penting yang perlu diketahui?',
        ];
    }

    return compact('keyword', 'title', 'meta', 'intent', 'audience', 'angle', 'hook', 'outline', 'faq', 'secondary', 'category', 'volumeText');
}

function cg_generate_article(array $brief): array
{
    $c = cg_context($brief);
    $sections = [];
    foreach ($c['outline'] as $line) {
        $heading = cg_clean_line($line);
        if ($heading === '') {
            continue;
        }

        if (stripos($line, 'H1:') === 0) {
            $sections[] = '# ' . $heading;
            continue;
        }

        $sections[] = "\n## " . $heading . "\n" .
            "Bagian ini menjelaskan {$heading} dalam konteks {$c['keyword']}. Gunakan bahasa yang jelas, masukkan keyword secara natural, dan hindari klaim yang belum terverifikasi. Jika topik memuat data terbaru, cek kembali sumber resmi sebelum publikasi.\n";
    }

    $keywords = $c['secondary'] ? implode(', ', array_slice($c['secondary'], 0, 6)) : $c['keyword'];
    $faqText = [];
    foreach ($c['faq'] as $item) {
        $question = cg_clean_line($item);
        $faqText[] = "**{$question}**\nJawaban singkat: jelaskan berdasarkan data terbaru yang tersedia, lalu arahkan pembaca mengecek sumber resmi untuk pembaruan.\n";
    }

    $body = "Judul Artikel:\n{$c['title']}\n\n" .
        "Meta Description:\n{$c['meta']}\n\n" .
        "Keyword Utama:\n{$c['keyword']}\n\n" .
        "Keyword Pendukung:\n{$keywords}\n\n" .
        "Draft Artikel:\n\n" .
        implode("\n", $sections) . "\n\n" .
        "Pembuka:\n{$c['hook']} Topik ini perlu dijelaskan sejak awal agar pembaca langsung memahami konteks, fakta utama, dan hal yang perlu diperhatikan.\n\n" .
        "Catatan Verifikasi:\nUntuk isu aktual, periksa kembali data dari sumber resmi sebelum menulis angka, korban, lokasi detail, jadwal, skor, atau klaim terbaru.\n\n" .
        "FAQ:\n" . implode("\n", $faqText) . "\n" .
        "CTA:\nBagikan artikel ini jika membantu, dan pantau pembaruan dari sumber resmi agar tidak tertinggal informasi penting.";

    return [
        'title' => $c['title'],
        'body' => $body,
    ];
}

function cg_generate_instagram_carousel(array $brief): array
{
    $c = cg_context($brief);
    $title = 'Carousel Instagram: ' . cg_limit_text($c['keyword'], 60);
    $facts = array_slice(array_map('cg_clean_line', $c['outline']), 1, 5);
    while (count($facts) < 5) {
        $facts[] = 'Fakta penting tentang ' . $c['keyword'];
    }

    $body = "Format: Instagram Carousel 7 Slide\n\n" .
        "Slide 1 - Cover\nJudul: {$c['title']}\nTeks kecil: Simpan agar tidak ketinggalan update.\n\n" .
        "Slide 2 - Konteks Cepat\n{$c['hook']}\n\n" .
        "Slide 3 - Fakta Utama\n{$facts[0]}\nJelaskan dalam 1 sampai 2 kalimat yang mudah dipahami.\n\n" .
        "Slide 4 - Kenapa Penting\n{$facts[1]}\nHubungkan dengan kebutuhan pembaca dan alasan topik ini ramai dicari.\n\n" .
        "Slide 5 - Hal yang Perlu Dicek\n{$facts[2]}\nArahkan pembaca mengecek sumber resmi sebelum percaya informasi yang beredar.\n\n" .
        "Slide 6 - Ringkasan\n{$facts[3]}\nTulis 3 poin pendek yang paling aman dan jelas.\n\n" .
        "Slide 7 - CTA\nTeks: Simpan postingan ini, bagikan ke teman, dan cek update terbaru dari sumber resmi.\n\n" .
        "Caption:\n{$c['keyword']} sedang ramai dicari. Berikut ringkasan yang perlu kamu tahu secara cepat dan mudah dipahami.\n\n" .
        "Hashtag:\n#Trending #InfoTerkini #BeritaHariIni #SEOContent #Indonesia";

    return ['title' => $title, 'body' => $body];
}

function cg_generate_reels_tiktok(array $brief): array
{
    $c = cg_context($brief);
    $title = 'Script Reels/TikTok: ' . cg_limit_text($c['keyword'], 60);
    $body = "Format: Reels/TikTok 45 sampai 60 detik\n\n" .
        "0-3 detik | Hook\nOn-screen text: Kenapa {$c['keyword']} ramai dicari?\nVoice over: {$c['hook']}\n\n" .
        "4-15 detik | Konteks\nOn-screen text: Konteks singkat\nVoice over: Topik ini masuk tren karena banyak orang mencari penjelasan cepat, fakta utama, dan update yang mudah dipahami.\n\n" .
        "16-35 detik | Isi Utama\nOn-screen text: 3 hal penting\nVoice over:\n1. Pahami dulu konteksnya.\n2. Cek informasi dari sumber resmi.\n3. Hindari membagikan klaim yang belum jelas.\n\n" .
        "36-50 detik | Penutup\nOn-screen text: Jangan asal share\nVoice over: Untuk topik aktual seperti ini, selalu cek ulang data terbaru sebelum membuat kesimpulan.\n\n" .
        "51-60 detik | CTA\nOn-screen text: Simpan dan follow untuk update tren lain\nVoice over: Simpan video ini dan ikuti update tren berikutnya.\n\n" .
        "Caption:\nRingkasan cepat tentang {$c['keyword']}. Simpan agar tidak lupa cek sumber resmi.\n\n" .
        "Hashtag:\n#FYP #TrendingIndonesia #InfoCepat #KontenSEO #BeritaTerkini";

    return ['title' => $title, 'body' => $body];
}

function cg_generate_x_threads(array $brief): array
{
    $c = cg_context($brief);
    $title = 'Thread X: ' . cg_limit_text($c['keyword'], 60);
    $keywords = $c['secondary'] ? implode(', ', array_slice($c['secondary'], 0, 4)) : $c['keyword'];
    $body = "Format: X/Threads 7 Post\n\n" .
        "1/7\n{$c['keyword']} sedang ramai dicari. Berikut ringkasan singkat yang perlu diketahui agar tidak salah memahami konteks.\n\n" .
        "2/7\nInti topik: {$c['angle']}\n\n" .
        "3/7\nSearch intent: {$c['intent']}\nArtinya, pengguna ingin mendapat penjelasan cepat, bukan opini panjang.\n\n" .
        "4/7\nKeyword terkait: {$keywords}. Keyword ini bisa dipakai untuk memperluas konteks artikel atau konten sosial.\n\n" .
        "5/7\nHal penting: jangan menulis angka, korban, lokasi detail, jadwal, atau klaim terbaru tanpa verifikasi dari sumber resmi.\n\n" .
        "6/7\nBagi content creator, topik ini bisa diolah menjadi artikel SEO, carousel, video pendek, atau update thread singkat.\n\n" .
        "7/7\nSimpan thread ini jika membantu. Bagikan dengan konteks yang benar dan hindari menyebarkan informasi yang belum terkonfirmasi.";

    return ['title' => $title, 'body' => $body];
}

function cg_generate_youtube_shorts(array $brief): array
{
    $c = cg_context($brief);
    $title = 'YouTube Shorts: ' . cg_limit_text($c['keyword'], 60);
    $body = "Format: YouTube Shorts 60 detik\n\n" .
        "Judul Video:\n{$c['title']}\n\n" .
        "Opening 0-5 detik\nVisual: teks besar di layar\nNarasi: {$c['hook']}\n\n" .
        "Bagian 1 6-20 detik\nVisual: highlight keyword dan ikon berita\nNarasi: Topik ini banyak dicari karena pengguna ingin memahami fakta utama dan perkembangan terbaru secara cepat.\n\n" .
        "Bagian 2 21-40 detik\nVisual: tiga poin di layar\nNarasi: Pertama, pahami konteksnya. Kedua, cek sumber resmi. Ketiga, jangan langsung percaya potongan informasi yang belum jelas asalnya.\n\n" .
        "Bagian 3 41-52 detik\nVisual: checklist verifikasi\nNarasi: Sebelum membagikan informasi, pastikan data yang kamu pakai sudah diperbarui dan berasal dari sumber yang bisa dipercaya.\n\n" .
        "Closing 53-60 detik\nVisual: CTA subscribe\nNarasi: Subscribe untuk ringkasan tren terbaru yang lebih jelas dan mudah dipahami.\n\n" .
        "Deskripsi Shorts:\nRingkasan cepat tentang {$c['keyword']}. Gunakan sebagai informasi awal dan tetap cek update dari sumber resmi.\n\n" .
        "Hashtag:\n#Shorts #Trending #InfoTerkini #Indonesia #SEO";

    return ['title' => $title, 'body' => $body];
}

function cg_generate_content(array $brief, string $type): array
{
    if ($type === 'article') {
        return cg_generate_article($brief);
    }
    if ($type === 'instagram_carousel') {
        return cg_generate_instagram_carousel($brief);
    }
    if ($type === 'reels_tiktok') {
        return cg_generate_reels_tiktok($brief);
    }
    if ($type === 'x_threads') {
        return cg_generate_x_threads($brief);
    }
    if ($type === 'youtube_shorts') {
        return cg_generate_youtube_shorts($brief);
    }

    return ['title' => 'Konten', 'body' => 'Jenis konten tidak dikenali.'];
}

function cg_save_generated_content(PDO $pdo, array $brief, string $type): int
{
    $content = cg_generate_content($brief, $type);
    $stmt = $pdo->prepare("INSERT INTO generated_contents
        (brief_id, trend_id, content_type, title, body, created_at, updated_at)
        VALUES (:brief_id, :trend_id, :content_type, :title, :body, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ON CONFLICT(brief_id, content_type) DO UPDATE SET
            title = excluded.title,
            body = excluded.body,
            updated_at = CURRENT_TIMESTAMP");

    $stmt->execute([
        ':brief_id' => (int) $brief['id'],
        ':trend_id' => (int) $brief['trend_id'],
        ':content_type' => $type,
        ':title' => $content['title'],
        ':body' => $content['body'],
    ]);

    $find = $pdo->prepare('SELECT id FROM generated_contents WHERE brief_id = :brief_id AND content_type = :content_type LIMIT 1');
    $find->execute([
        ':brief_id' => (int) $brief['id'],
        ':content_type' => $type,
    ]);

    return (int) $find->fetchColumn();
}
