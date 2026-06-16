<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/google_trends_sync.php';

$result = gt_sync_google_trends($pdo);

if (PHP_SAPI === 'cli') {
    echo '[' . date('Y-m-d H:i:s') . '] ' . ($result['ok'] ? 'OK' : 'GAGAL') . PHP_EOL;
    echo 'Pesan   : ' . $result['message'] . PHP_EOL;
    echo 'Sumber  : ' . $result['source_url'] . PHP_EOL;
    echo 'Ambil   : ' . $result['fetched'] . PHP_EOL;
    echo 'Baru    : ' . $result['inserted'] . PHP_EOL;
    echo 'Update  : ' . $result['updated'] . PHP_EOL;
    exit($result['ok'] ? 0 : 1);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
