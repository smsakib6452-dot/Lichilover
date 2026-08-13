<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('account.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $name     = trim((string) input('name'));
    $email    = trim((string) input('email'));
    $phone    = normalize_bd_phone(input('phone'));
    $password = (string) input('password');
    $confirm  = (string) input('password_confirm');

    if ($name === '' || mb_strlen($name) < 3) $errors['name'] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
    if (!is_valid_bd_phone($phone)) $errors['phone'] = 'Please enter a valid Bangladeshi phone number (e.g. 01712345678).';
    if (strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors['password_confirm'] = 'Passwords do not match.';

    if (!$errors) {
        $exists = fetch_one('SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1', [$email, $phone]);
        if ($exists) {
            $errors['general'] = 'An account with this email or phone already exists. Please login.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            query('INSERT INTO users (name, email, phone, password, is_active) VALUES (?, ?, ?, ?, 1)', [$name, $email, $phone, $hashed]);
            $user = fetch_one('SELECT * FROM users WHERE email = ?', [$email]);
            login_user($user);
            flash('success', 'Welcome to Lichi Lover! Your account has been created.');
            redirect('account.php');
        }
    }
    with_old(['name' => $name ?? '', 'email' => $email ?? '', 'phone' => $phone ?? '']);
}

$page_title = 'Create Account';
$page_meta  = 'Create a Lichi Lover account.';
$page_noindex = true;
$page_canonical = url('register.php');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$flash = get_flash();
?>

<div class="auth-wrap">
    <div class="auth-card">
        <h1>Create your account</h1>
        <p class="sub">Join Lichi Lover for faster checkout and order tracking.</p>

        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><i data-lucide="info"></i><span><?= e($flash['message']) ?></span></div>
        <?php endif; ?>
        <?php if (isset($errors['general'])): ?>
        <div class="alert alert-error"><i data-lucide="alert-circle"></i><span><?= e($errors['general']) ?></span></div>
        <?php endif; ?>

        <form method="post" action="<?= url('register.php') ?>" data-loading>
            <?= csrf_field() ?>
            <div class="form-field" style="margin-bottom:14px">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= e(old('name')) ?>" placeholder="Your full name" required>
                <?php if (isset($errors['name'])): ?><span class="form-error"><?= e($errors['name']) ?></span><?php endif; ?>
            </div>
            <div class="form-field" style="margin-bottom:14px">
                <label>Email</label>
                <input type="email" name="email" value="<?= e(old('email')) ?>" placeholder="you@example.com" required>
                <?php if (isset($errors['email'])): ?><span class="form-error"><?= e($errors['email']) ?></span><?php endif; ?>
            </div>
            <div class="form-field" style="margin-bottom:14px">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?= e(old('phone')) ?>" placeholder="017XXXXXXXX" required>
                <?php if (isset($errors['phone'])): ?><span class="form-error"><?= e($errors['phone']) ?></span><?php endif; ?>
            </div>
            <div class="form-field" style="margin-bottom:14px">
                <label>Password</label>
                <input type="password" name="password" placeholder="At least 8 characters" required>
                <?php if (isset($errors['password'])): ?><span class="form-error"><?= e($errors['password']) ?></span><?php endif; ?>
            </div>
            <div class="form-field" style="margin-bottom:18px">
                <label>Confirm Password</label>
                <input type="password" name="password_confirm" placeholder="Repeat your password" required>
                <?php if (isset($errors['password_confirm'])): ?><span class="form-error"><?= e($errors['password_confirm']) ?></span><?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary btn-lg btn-block">Create Account</button>
        </form>

        <p class="auth-foot">
            Already have an account? <a href="<?= url('login.php') ?>">Login</a>
        </p>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>