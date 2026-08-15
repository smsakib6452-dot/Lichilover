<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

if (is_admin_logged_in()) {
    redirect('admin/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $email = trim((string) input('email'));
    $password = (string) input('password');

    if ($email === '') $errors['email'] = 'Please enter your email.';
    if ($password === '') $errors['password'] = 'Please enter your password.';

    if (!$errors) {
        $admin = fetch_one('SELECT * FROM admins WHERE email = ? AND is_active = 1 LIMIT 1', [$email]);
        if ($admin && password_verify($password, $admin['password'])) {
            login_admin($admin);
            flash('success', 'Welcome back, ' . $admin['name'] . '!');
            redirect('admin/index.php');
        }
        $errors['general'] = 'Invalid admin credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — <?= e(APP_NAME) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin-login-body">
    <div class="admin-login">
        <div class="admin-login-card">
            <div style="text-align:center;margin-bottom:20px">
                <img src="<?= asset('images/logo.svg') ?>" alt="Logo" width="64" height="64">
                <h1><?= e(APP_NAME) ?> Admin</h1>
                <p>Sign in to manage your store.</p>
            </div>

            <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error"><i data-lucide="alert-circle"></i><span><?= e($errors['general']) ?></span></div>
            <?php endif; ?>

            <?php if (PAYMENT_MODE === 'demo'): ?>
            <div class="alert alert-info" style="margin-bottom:16px">
                <span>Demo admin (DEVELOPMENT ONLY): <strong>admin@lichilover.com</strong> / <strong>admin123</strong></span>
            </div>
            <?php endif; ?>

            <form method="post" action="<?= url('admin/login.php') ?>">
                <?= csrf_field() ?>
                <div class="form-field" style="margin-bottom:14px">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= e(input('email')) ?>" required autofocus>
                    <?php if (isset($errors['email'])): ?><span class="form-error"><?= e($errors['email']) ?></span><?php endif; ?>
                </div>
                <div class="form-field" style="margin-bottom:18px">
                    <label>Password</label>
                    <input type="password" name="password" required>
                    <?php if (isset($errors['password'])): ?><span class="form-error"><?= e($errors['password']) ?></span><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Login</button>
            </form>

            <p class="admin-login-back"><a href="<?= url('index.php') ?>">&larr; Back to website</a></p>
        </div>
    </div>
</body>
</html>
