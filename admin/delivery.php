<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$divisions = bd_divisions();

// Update zone
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = input('action');
    $id = (int) input('zone_id');

    if ($action === 'update') {
        $fee = (float) input('delivery_fee');
        $threshold = (float) input('free_delivery_threshold');
        $active = isset($_POST['is_active']) ? 1 : 0;

        $existed = fetch_one('SELECT id FROM delivery_zones WHERE id = ?', [$id]);
        if ($existed && $fee >= 0) {
            query('UPDATE delivery_zones SET delivery_fee = ?, free_delivery_threshold = ?, is_active = ? WHERE id = ?', [$fee, $threshold, $active, $id]);
            flash('success', 'Delivery zone updated.');
        }
    }

    if ($action === 'add') {
        $district = trim((string) input('district'));
        $division = trim((string) input('division'));
        $fee = (float) input('delivery_fee');
        $threshold = (float) input('free_delivery_threshold');
        $active = isset($_POST['is_active']) ? 1 : 0;

        if ($district === '') {
            flash('error', 'District is required.');
        } else {
            try {
                query('INSERT INTO delivery_zones (district, division, delivery_fee, free_delivery_threshold, is_active, sort_order) VALUES (?, ?, ?, ?, ?, 999)',
                    [$district, $division, $fee, $threshold, $active]);
                flash('success', 'Delivery zone added.');
            } catch (PDOException $e) {
                flash('error', 'This district already exists.');
            }
        }
    }

    redirect('delivery.php');
}

$zones = delivery_zones();

$adminTitle = 'Delivery Zones';
require_once __DIR__ . '/includes/header.php';
?>

<div class="two-col">
    <div class="admin-card">
        <h3>Delivery Zones</h3>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>District</th>
                        <th>Division</th>
                        <th>Fee (৳)</th>
                        <th>Free Above (৳)</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($zones as $z): ?>
                    <tr>
                        <td><?= e($z['district']) ?></td>
                        <td><?= e($z['division'] ?: '—') ?></td>
                        <td>
                            <form method="post" action="<?= url('admin/delivery.php') ?>" style="display:flex;gap:6px;align-items:center">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="zone_id" value="<?= (int) $z['id'] ?>">
                                <input type="number" step="0.01" min="0" name="delivery_fee" value="<?= e($z['delivery_fee']) ?>" style="width:80px" required>
                                <input type="number" step="0.01" min="0" name="free_delivery_threshold" value="<?= e($z['free_delivery_threshold']) ?>" style="width:80px" title="Free delivery threshold">
                                <input type="checkbox" name="is_active" value="1" <?= (int) $z['is_active'] === 1 ? 'checked' : '' ?> title="Active">
                                <button type="submit" class="btn btn-ghost btn-sm">Save</button>
                            </form>
                        </td>
                        <td style="font-size:12px;color:var(--muted)">0 = no free delivery</td>
                        <td><span class="badge <?= (int) $z['is_active'] === 1 ? 'badge-active' : 'badge-inactive' ?>"><?= (int) $z['is_active'] === 1 ? 'Active' : 'Inactive' ?></span></td>
                        <td></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="admin-card">
            <h3>Add Zone</h3>
            <form method="post" action="<?= url('admin/delivery.php') ?>" data-loading>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div class="form-field" style="margin-bottom:12px">
                    <label>District</label>
                    <input type="text" name="district" required>
                </div>
                <div class="form-field" style="margin-bottom:12px">
                    <label>Division</label>
                    <select name="division">
                        <option value="">Select division</option>
                        <?php foreach ($divisions as $d): ?>
                        <option value="<?= e($d) ?>"><?= e($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field" style="margin-bottom:12px">
                    <label>Delivery Fee (৳)</label>
                    <input type="number" step="0.01" min="0" name="delivery_fee" required>
                </div>
                <div class="form-field" style="margin-bottom:12px">
                    <label>Free Delivery Threshold (৳)</label>
                    <input type="number" step="0.01" min="0" name="free_delivery_threshold" value="0">
                    <span class="form-hint">Cart totals above this get free delivery. 0 = disabled.</span>
                </div>
                <label style="display:flex;gap:6px;align-items:center;font-size:14px;margin-bottom:14px"><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <button type="submit" class="btn btn-primary btn-block">Add Zone</button>
            </form>
        </div>

        <div class="admin-card">
            <h3>Current Setup</h3>
            <p style="font-size:14px;color:var(--muted);margin-bottom:8px">Demo fees by division:</p>
            <ul style="font-size:14px">
                <li>• Chattogram — ৳60</li>
                <li>• Dhaka — ৳100</li>
                <li>• All other districts — ৳120</li>
                <li>• "Other Districts" fallback — ৳120</li>
            </ul>
            <p style="font-size:12px;color:var(--muted);margin-top:10px">Change any value from the table above. Checkout always reads fees from this table.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>