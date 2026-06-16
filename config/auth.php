<?php

require_once __DIR__ . '/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function admin_logged_in(): bool
{
    return isset($_SESSION['admin_id']);
}

function current_admin_name(): string
{
    return $_SESSION['admin_name'] ?? 'Admin';
}

function require_admin(): void
{
    if (!admin_logged_in()) {
        redirect('auth/login.php');
    }
}
