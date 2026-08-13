<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Toggle / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = input('action');
    $id = (int) input('coupon_id');

    if ($action === 'toggle') {
        $c = fetch_one('SELECT is_active FROM coupons WHERE id = ?', [$id]);
        if ($c) {
            query('UPDATE coupons SET is_active = ? WHERE id = ?', [(int) $c['is_active'] === 1 ? 0 : 1, $id]);
            flash('success', 'Coupon status updated.');
        }
    }
    if ($action === 'delete') {
        query('DELETE FROM coupons WHERE id = ?', [$id]);
        flash('success', 'Coupon deleted.');
    }
    redirect('coupons.php');
}

$coupons = fetch_all('SELECT c.*, (SELECT COUNT(*) FROM coupon_usages cu WHERE cu.coupon_id = c.id) AS used FROM coupons c ORDER BY c.id DESC');

$adminTitle = 'Coupons';
require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="card-head">
        <h3>Coupons</h3>
        <a href="<?= url('admin/coupon-add.php') ?>" class="btn btn-primary"><i data-lucide="plus"></i> Add Coupon</a>
    </div>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min Order</th>
                    <th>Max Discount</th>
                    <th>Expires</th>
                    <th>Usage</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $c): ?>
                <tr>
                    <td><strong><?= e($c['code']) ?></strong></td>
                    <td><?= e($c['discount_type']) ?></td>
                    <td><?= $c['discount_type'] === 'percent' ? e((string) $c['discount_value']) . '%' : money((float) $c['discount_value']) ?></td>
                    <td><?= money((float) $c['min_order']) ?></td>
                    <td><?= $c['max_discount'] ? money((float) $c['max_discount']) : '—' ?></td>
                    <td><?= $c['expires_at'] ? e($c['expires_at']) : 'Never' ?></td>
                    <td><?= (int) $c['used'] ?> / <?= (int) $c['usage_limit'] > 0 ? (int) $c['usage_limit'] : '∞' ?></td>
                    <td><span class="badge <?= (int) $c['is_active'] === 1 ? 'badge-active' : 'badge-inactive' ?>"><?= (int) $c['is_active'] === 1 ? 'Active' : 'Inactive' ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <a href="<?= url('admin/coupon-edit.php?id=' . (int) $c['id']) ?>" class="btn btn-ghost btn-sm">Edit</a>
                            <form method="post" action="<?= url('admin/coupons.php') ?>" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="coupon_id" value="<?= (int) $c['id'] ?>">
                                <button type="submit" class="btn btn-ghost btn-sm"><?= (int) $c['is_active'] === 1 ? 'Deactivate' : 'Activate' ?></button>
                            </form>
                            <form method="post" action="<?= url('admin/coupons.php') ?>" style="display:inline" data-confirm="Delete this coupon?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="coupon_id" value="<?= (int) $c['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$coupons): ?>
                <tr><td colspan="9" class="empty-state">No coupons yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>