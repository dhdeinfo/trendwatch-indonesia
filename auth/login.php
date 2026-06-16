<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

if (admin_logged_in()) {
    redirect('admin/dashboard.php');
}

$error = '';
$email = '';

if (is_post()) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            redirect('admin/dashboard.php');
        }

        $error = 'Email atau password salah.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
</head>
<body class="login-body">
    <main class="login-wrapper">
        <section class="login-info">
            <span class="small-label">Aplikasi Monitoring Tren SEO</span>
            <h1>TrendWatch Indonesia</h1>
            <p>Pantau tren pencarian, lihat peluang konten, dan siapkan ide artikel lebih cepat.</p>
        </section>

        <section class="login-card">
            <h2>Login Admin</h2>
            <p class="muted">Masuk untuk mengelola dashboard tren.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($email) ?>" placeholder="admin@gmail.com" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="admin123" required>

                <button type="submit" class="btn btn-primary full">Masuk</button>
            </form>

            <div class="login-help">
                <p>Email: <strong>admin@gmail.com</strong></p>
                <p>Password: <strong>admin123</strong></p>
            </div>
        </section>
    </main>
</body>
</html>
