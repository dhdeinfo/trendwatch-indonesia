<?php


if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'TrendWatch Indonesia');
}

if (!defined('APP_ROOT')) {
    define('APP_ROOT', realpath(__DIR__ . '/..'));
}

function app_base_url(): string
{
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $appRoot = APP_ROOT ? realpath(APP_ROOT) : false;

    if ($docRoot && $appRoot && substr($appRoot, 0, strlen($docRoot)) === $docRoot) {
        $relative = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
        $relative = '/' . trim($relative, '/');
        return $relative === '/' ? '' : $relative;
    }

    return '';
}

function url(string $path = ''): string
{
    $base = app_base_url();
    $path = trim($path, '/');
    return $path === '' ? ($base ?: '/') : $base . '/' . $path;
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function flash_set(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function flash_get(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
        die('Akses ditolak. Token keamanan tidak valid.');
    }
}

function format_volume_text(int $volume): string
{
    if ($volume >= 1000000) {
        return number_format($volume / 1000000, 1, ',', '.') . ' jt+';
    }

    if ($volume >= 1000) {
        return number_format($volume / 1000, 0, ',', '.') . ' rb+';
    }

    return number_format($volume, 0, ',', '.') . '+';
}

function badge_class(string $status): string
{
    $status = strtolower($status);

    if ($status === 'aktif') {
        return 'success';
    }

    if ($status === 'menurun') {
        return 'warning';
    }

    if ($status === 'selesai') {
        return 'neutral';
    }

    return 'info';
}
