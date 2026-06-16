<?php
require_once __DIR__ . '/../config/auth.php';
require_admin();
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2563eb">
    <title><?= e($pageTitle) ?> - <?= e(APP_NAME) ?></title>
    <script>
        (function () {
            var savedTheme = localStorage.getItem('trendwatch-theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">
<button type="button" class="mobile-toggle" id="sidebarToggle" aria-label="Buka menu">☰</button>
<button type="button" class="theme-toggle" id="themeToggle" aria-label="Ganti tema">Mode</button>
<div class="mobile-backdrop" id="mobileBackdrop"></div>
<div class="admin-layout">
