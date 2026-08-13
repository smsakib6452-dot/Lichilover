<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $code          = strtoupper(trim((string) input('code')));
    $discountType  = input('discount_type');
    $discountValue = (float) input('discount_value');
    $minOrder      = (float) input('min_order', 0);
    $maxDiscount   = input('max_discount') !== '' ? (float) input('max_discount') : null;
    $expiresAt     = input('expires_at') !== '' ? input('expires_at') : null;
    $usageLimit    = (int) input('usage_limit', 0);
    $isActive      = isset($_POST['is_active']) ? 1 : 0;

    if ($code === '' || !preg_match('/^[A-Z0-9_-]{3,30}$/', $code)) $errors['code'] = 'Enter a valid code (3-30 chars, letters/numbers/-/_).';
    if (!in_array($discountType, ['percent', 'fixed'], true)) $errors['discount_type'] = 'Invalid discount type.';
    if ($discountValue <= 0) $errors['discount_value'] = 'Discount value must be greater than 0.';
    if ($discountType === 'percent' && $discountValue > 100) $errors['discount_value'] = 'Percent discount cannot exceed 100%.';

    if (!$errors) {
        $dup = fetch_one('SELECT id FROM coupons WHERE code = ?', [$code]);
        if ($dup) {
            $errors['code'] = 'This coupon code already exists.';
        } else {
            query(
                'INSERT INTO coupons (code, discount_type, discount_value, min_order, max_discount, expires_at, usage_limit, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [$code, $discountType, $discountValue, $minOrder, $maxDiscount, $expiresAt, $usageLimit, $isActive]
            );
            flash('success', 'Coupon created successfully.');
            redirect('coupons.php');
        }
    }
}

$adminTitle = 'Add Coupon';
require_once __DIR__ . '/includes/header.php';
?>

<form method="post" action="<?= url('admin/coupon-add.php') ?>" class="admin-card" style="max-width:640px" data-loading>
    <?= csrf_field() ?>

    <h3 style="margin-bottom:16px">Create Coupon</h3>

    <div class="form-grid">
        <div class="form-field <?= isset($errors['code']) ? 'has-error' : '' ?>">
            <label>Coupon Code</label>
            <input type="text" name="code" value="<?= e(input('code')) ?>" placeholder="e.g. SUMMER10" required>
            <?php if (isset($errors['code'])): ?><span class="form-error"><?= e($errors['code']) ?></span><?php endif; ?>
        </div>
        <div class="form-field <?= isset($errors['discount_type']) ? 'has-error' : '' ?>">
            <label>Discount Type</label>
            <select name="discount_type">
                <option value="percent" <?= input('discount_type') === 'fixed' ? '' : 'selected' ?>>Percentage (%)</option>
                <option value="fixed" <?= input('discount_type') === 'fixed' ? 'selected' : '' ?>>Fixed Amount (৳)</option>
            </select>
            <?php if (isset($errors['discount_type'])): ?><span class="form-error"><?= e($errors['discount_type']) ?></span><?php endif; ?>
        </div>
        <div class="form-field <?= isset($errors['discount_value']) ? 'has-error' : '' ?>">
            <label>Discount Value</label>
            <input type="number" step="0.01" min="0" name="discount_value" value="<?= e(input('discount_value')) ?>" required>
            <?php if (isset($errors['discount_value'])): ?><span class="form-error"><?= e($errors['discount_value']) ?></span><?php endif; ?>
        </div>
        <div class="form-field">
            <label>Minimum Order (৳)</label>
            <input type="number" step="0.01" min="0" name="min_order" value="<?= e(input('min_order', 0)) ?>">
        </div>
        <div class="form-field">
            <label>Maximum Discount (৳)</label>
            <input type="number" step="0.01" min="0" name="max_discount" value="<?= e(input('max_discount')) ?>">
            <span class="form-hint">Leave empty for no cap (usually for percentage).</span>
        </div>
        <div class="form-field">
            <label>Expiry Date</label>
            <input type="date" name="expires_at" value="<?= e(input('expires_at')) ?>">
        </div>
        <div class="form-field">
            <label>Usage Limit</label>
            <input type="number" min="0" name="usage_limit" value="<?= e(input('usage_limit', 0)) ?>">
            <span class="form-hint">0 = unlimited.</span>
        </div>
        <div class="form-field">
            <label>&nbsp;</label>
            <label style="display:flex;gap:6px;align-items:center;font-size:14px"><input type="checkbox" name="is_active" value="1" <?= input('is_active', 0) ? 'checked' : 'checked' ?>> Active</label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary" style="margin-top:16px">Create Coupon</button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>