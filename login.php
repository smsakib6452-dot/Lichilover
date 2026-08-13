<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('account.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $login = trim((string) input('login'));
    $password = (string) input('password');

    if ($login === '') $errors['login'] = 'Please enter your email or phone.';
    if ($password === '') $errors['password'] = 'Please enter your password.';

    if (!$errors) {
        $user = fetch_one('SELECT * FROM users WHERE (email = ? OR phone = ?) AND is_active = 1 LIMIT 1', [$login, $login]);
        if ($user && password_verify($password, $user['password'])) {
            login_user($user);
            $redirectTo = $_SESSION['redirect_after_login'] ?? 'account.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirectTo);
        }
        $errors['general'] = 'Invalid login credentials. Please try again.';
    }
    with_old(['login' => $login]);
}

$page_title = 'Login';
$page_meta  = 'Login to your Lichi Lover account.';
$page_noindex = true;
$page_canonical = url('login.php');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$flash = get_flash();
?>

<div class="auth-wrap">
    <div class="auth-card">
        <h1>Welcome back</h1>
        <p class="sub">Login to your Lichi Lover account.</p>

        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><i data-lucide="info"></i><span><?= e($flash['message']) ?></span></div>
        <?php endif; ?>
        <?php if (isset($errors['general'])): ?>
        <div class="alert alert-error"><i data-lucide="alert-circle"></i><span><?= e($errors['general']) ?></span></div>
        <?php endif; ?>

        <form method="post" action="<?= url('login.php') ?>" data-loading>
            <?= csrf_field() ?>
            <div class="form-field" style="margin-bottom:14px">
                <label>Email or Phone</label>
                <input type="text" name="login" value="<?= e(old('login')) ?>" placeholder="you@example.com or 017XXXXXXXX" required autofocus>
                <?php if (isset($errors['login'])): ?><span class="form-error"><?= e($errors['login']) ?></span><?php endif; ?>
            </div>
            <div class="form-field" style="margin-bottom:18px">
                <label>Password</label>
                <input type="password" name="password" placeholder="Your password" required>
                <?php if (isset($errors['password'])): ?><span class="form-error"><?= e($errors['password']) ?></span><?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary btn-lg btn-block">Login</button>
        </form>

        <p class="auth-foot">
            Don't have an account? <a href="<?= url('register.php') ?>">Register now</a>
        </p>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>