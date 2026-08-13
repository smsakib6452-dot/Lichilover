<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$admin = current_admin();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = input('action');

    if ($action === 'settings') {
        $fields = [
            'shop_name' => 'Shop Name', 'shop_tagline' => 'Tagline', 'announcement' => 'Announcement',
            'shop_email' => 'Shop Email', 'shop_phone' => 'Shop Phone', 'shop_address' => 'Shop Address',
            'whatsapp_number' => 'WhatsApp Number', 'facebook_url' => 'Facebook URL', 'instagram_url' => 'Instagram URL',
            'hero_headline' => 'Hero Headline', 'hero_subheadline' => 'Hero Subheadline', 'about_text' => 'About Text',
        ];
        foreach ($fields as $key => $label) {
            $value = trim((string) input($key));
            $sql = 'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)';
            query($sql, [$key, $value]);
        }
        flash('success', 'Settings saved.');
        redirect('settings.php');
    }

    if ($action === 'password') {
        $current = (string) input('current_password');
        $new = (string) input('new_password');
        $confirm = (string) input('confirm_password');

        if (!password_verify($current, $admin['password'])) $errors['current_password'] = 'Current password is incorrect.';
        if (strlen($new) < 8) $errors['new_password'] = 'New password must be at least 8 characters.';
        if ($new !== $confirm) $errors['confirm_password'] = 'Passwords do not match.';

        if (!$errors) {
            query('UPDATE admins SET password = ?, must_change_password = 0 WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
            flash('success', 'Password updated. Please keep it safe!');
            redirect('settings.php');
        }
    }
}

$s = settings();

$adminTitle = 'Settings';
require_once __DIR__ . '/includes/header.php';
?>

<div class="two-col">
    <div class="admin-card">
        <h3>Store Settings</h3>
        <form method="post" action="<?= url('admin/settings.php') ?>" data-loading>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="settings">
            <div class="form-grid">
                <div class="form-field">
                    <label>Shop Name</label>
                    <input type="text" name="shop_name" value="<?= e($s['shop_name'] ?? APP_NAME) ?>">
                </div>
                <div class="form-field">
                    <label>Tagline</label>
                    <input type="text" name="shop_tagline" value="<?= e($s['shop_tagline'] ?? APP_TAGLINE) ?>">
                </div>
                <div class="form-field full">
                    <label>Announcement Bar Text</label>
                    <input type="text" name="announcement" value="<?= e($s['announcement'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label>Shop Email</label>
                    <input type="email" name="shop_email" value="<?= e($s['shop_email'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label>Shop Phone</label>
                    <input type="text" name="shop_phone" value="<?= e($s['shop_phone'] ?? '') ?>">
                </div>
                <div class="form-field full">
                    <label>Shop Address</label>
                    <input type="text" name="shop_address" value="<?= e($s['shop_address'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label>WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" value="<?= e($s['whatsapp_number'] ?? WHATSAPP_NUMBER) ?>" placeholder="8801712345678">
                    <span class="form-hint">International format without +. Leave empty to hide the WhatsApp button.</span>
                </div>
                <div class="form-field">
                    <label>Facebook URL</label>
                    <input type="text" name="facebook_url" value="<?= e($s['facebook_url'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label>Instagram URL</label>
                    <input type="text" name="instagram_url" value="<?= e($s['instagram_url'] ?? '') ?>">
                </div>
                <div class="form-field full">
                    <label>Hero Headline</label>
                    <input type="text" name="hero_headline" value="<?= e($s['hero_headline'] ?? '') ?>">
                </div>
                <div class="form-field full">
                    <label>Hero Subheadline</label>
                    <textarea name="hero_subheadline" rows="2"><?= e($s['hero_subheadline'] ?? '') ?></textarea>
                </div>
                <div class="form-field full">
                    <label>About Text</label>
                    <textarea name="about_text" rows="3"><?= e($s['about_text'] ?? '') ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:16px">Save Settings</button>
        </form>
    </div>

    <div>
        <div class="admin-card" id="password">
            <h3>Change Password</h3>
            <form method="post" action="<?= url('admin/settings.php') ?>" data-loading>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="password">
                <div class="form-field" style="margin-bottom:12px">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                    <?php if (isset($errors['current_password'])): ?><span class="form-error"><?= e($errors['current_password']) ?></span><?php endif; ?>
                </div>
                <div class="form-field" style="margin-bottom:12px">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="At least 8 characters" required>
                    <?php if (isset($errors['new_password'])): ?><span class="form-error"><?= e($errors['new_password']) ?></span><?php endif; ?>
                </div>
                <div class="form-field" style="margin-bottom:14px">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                    <?php if (isset($errors['confirm_password'])): ?><span class="form-error"><?= e($errors['confirm_password']) ?></span><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>

        <div class="admin-card">
            <h3>Payment Configuration</h3>
            <p style="font-size:14px;margin-bottom:8px">
                Payment mode: <span class="badge <?= PAYMENT_MODE === 'demo' ? 'badge-pending' : 'badge-active' ?>"><?= e(PAYMENT_MODE) ?></span>
            </p>
            <p style="font-size:13px;color:var(--muted)">
                <?php if (PAYMENT_MODE === 'demo'): ?>
                The site is in <strong>demo mode</strong> — payments are simulated and clearly labelled. No real money is charged.
                <?php else: ?>
                Live mode. Gateway credentials are read from <code>.env</code>. Never commit credentials to Git.
                <?php endif; ?>
            </p>
            <p style="font-size:13px;color:var(--muted);margin-top:8px">
                bKash credentials: <?= BKASH_APP_KEY !== '' ? 'configured' : 'not configured' ?><br>
                Nagad credentials: <?= NAGAD_MERCHANT_ID !== '' ? 'configured' : 'not configured' ?><br>
                WhatsApp number: <?= (settings('whatsapp_number') ?: WHATSAPP_NUMBER) !== '' ? 'configured' : 'not configured' ?>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>