<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = input('action');

    if ($action === 'update_profile') {
        $name  = trim((string) input('name'));
        $email = trim((string) input('email'));
        $phone = normalize_bd_phone(input('phone'));

        if ($name === '' || mb_strlen($name) < 3) $errors['name'] = 'Please enter your full name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email.';
        if (!is_valid_bd_phone($phone)) $errors['phone'] = 'Please enter a valid Bangladeshi phone number.';

        if (!$errors) {
            $dup = fetch_one('SELECT id FROM users WHERE (email = ? OR phone = ?) AND id <> ? LIMIT 1', [$email, $phone, $user['id']]);
            if ($dup) {
                $errors['general'] = 'Another account already uses this email or phone.';
            } else {
                query('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?', [$name, $email, $phone, $user['id']]);
                flash('success', 'Profile updated successfully.');
                redirect('account.php');
            }
        }
        with_old(['name' => $name, 'email' => $email, 'phone' => $phone]);
    }

    if ($action === 'change_password') {
        $current   = (string) input('current_password');
        $newPass   = (string) input('new_password');
        $confirm   = (string) input('confirm_password');

        if (!password_verify($current, $user['password'])) $errors['current_password'] = 'Your current password is incorrect.';
        if (strlen($newPass) < 8) $errors['new_password'] = 'New password must be at least 8 characters.';
        if ($newPass !== $confirm) $errors['confirm_password'] = 'Passwords do not match.';

        if (!$errors) {
            query('UPDATE users SET password = ? WHERE id = ?', [password_hash($newPass, PASSWORD_DEFAULT), $user['id']]);
            flash('success', 'Password changed successfully.');
            redirect('account.php');
        }
    }
}

$orders = fetch_all('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5', [$user['id']]);
$addresses = fetch_all('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC LIMIT 5', [$user['id']]);

$page_title = 'My Account';
$page_meta  = 'Manage your Lichi Lover account.';
$page_noindex = true;
$page_canonical = url('account.php');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$flash = get_flash();
?>

<div class="crumbs">
    <div class="container">
        <ul>
            <li><a href="<?= url('index.php') ?>">Home</a></li>
            <li>My Account</li>
        </ul>
    </div>
</div>

<section class="section" style="padding-top:8px">
    <div class="container">
        <div class="section-head" style="text-align:left;margin-bottom:24px">
            <span class="section-eyebrow">Dashboard</span>
            <h2>Hello, <?= e($user['name']) ?> 👋</h2>
        </div>

        <div class="account-nav" style="margin-bottom:28px">
            <a href="<?= url('account.php') ?>" class="active">Profile</a>
            <a href="<?= url('orders.php') ?>">My Orders</a>
            <a href="<?= url('shop.php') ?>">Shop</a>
            <a href="<?= url('logout.php') ?>">Logout</a>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><i data-lucide="info"></i><span><?= e($flash['message']) ?></span></div>
        <?php endif; ?>
        <?php if (isset($errors['general'])): ?>
        <div class="alert alert-error"><i data-lucide="alert-circle"></i><span><?= e($errors['general']) ?></span></div>
        <?php endif; ?>

        <div class="account-layout">
            <div class="checkout-form-card">
                <h3 style="margin-bottom:16px">Profile Information</h3>
                <form method="post" action="<?= url('account.php') ?>" data-loading>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-grid">
                        <div class="form-field <?= isset($errors['name']) ? 'has-error' : '' ?>">
                            <label>Full Name</label>
                            <input type="text" name="name" value="<?= e(old('name', $user['name'])) ?>" required>
                            <?php if (isset($errors['name'])): ?><span class="form-error"><?= e($errors['name']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-field <?= isset($errors['email']) ? 'has-error' : '' ?>">
                            <label>Email</label>
                            <input type="email" name="email" value="<?= e(old('email', $user['email'])) ?>" required>
                            <?php if (isset($errors['email'])): ?><span class="form-error"><?= e($errors['email']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-field <?= isset($errors['phone']) ? 'has-error' : '' ?>">
                            <label>Phone</label>
                            <input type="tel" name="phone" value="<?= e(old('phone', $user['phone'])) ?>" required>
                            <?php if (isset($errors['phone'])): ?><span class="form-error"><?= e($errors['phone']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-field">
                            <label>Member Since</label>
                            <input type="text" value="<?= e(date('M j, Y', strtotime($user['created_at']))) ?>" disabled>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:16px">Save Changes</button>
                </form>
            </div>

            <div class="checkout-form-card">
                <h3 style="margin-bottom:16px">Change Password</h3>
                <form method="post" action="<?= url('account.php') ?>" data-loading>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-field" style="margin-bottom:14px">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                        <?php if (isset($errors['current_password'])): ?><span class="form-error"><?= e($errors['current_password']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-field" style="margin-bottom:14px">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="At least 8 characters" required>
                        <?php if (isset($errors['new_password'])): ?><span class="form-error"><?= e($errors['new_password']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-field" style="margin-bottom:16px">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                        <?php if (isset($errors['confirm_password'])): ?><span class="form-error"><?= e($errors['confirm_password']) ?></span><?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>

        <?php if ($orders): ?>
        <div style="margin-top:32px">
            <h3 style="margin-bottom:14px;color:var(--green-900)">Recent Orders</h3>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><a href="<?= url('order-detail.php?id=' . (int) $o['id']) ?>"><?= e($o['order_number']) ?></a></td>
                            <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                            <td><?= money((float) $o['total']) ?></td>
                            <td><span class="badge badge-<?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
                            <td><a href="<?= url('order-detail.php?id=' . (int) $o['id']) ?>" class="btn btn-ghost btn-sm">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>