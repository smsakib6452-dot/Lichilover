<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$q = trim((string) ($_GET['q'] ?? ''));
$status = $_GET['status'] ?? 'all';
$perPage = 20;

$where = ['1 = 1'];
$params = [];
if ($q !== '') {
    $where[] = '(o.order_number LIKE ? OR o.full_name LIKE ? OR o.phone LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($status !== 'all') {
    $where[] = 'o.status = ?';
    $params[] = $status;
}

$total = (int) fetch_val('SELECT COUNT(*) FROM orders o WHERE ' . implode(' AND ', $where), $params);
$pager = paginate($total, $perPage);

$orders = fetch_all(
    'SELECT o.* FROM orders o WHERE ' . implode(' AND ', $where) . ' ORDER BY o.id DESC LIMIT ' . $pager['perPage'] . ' OFFSET ' . $pager['offset'],
    $params
);

$statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

$adminTitle = 'Orders';
require_once __DIR__ . '/includes/header.php';
?>

<div class="filter-bar">
    <form method="get" action="<?= url('admin/orders.php') ?>" style="display:flex;gap:8px;flex-wrap:wrap;flex:1">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search order #, name, phone..." style="min-width:220px">
        <select name="status">
            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All statuses</option>
            <?php foreach ($statuses as $s): ?>
            <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
    </form>
</div>

<div class="admin-card">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>District</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><a href="<?= url('admin/order-view.php?id=' . (int) $o['id']) ?>"><?= e($o['order_number']) ?></a></td>
                    <td><?= e($o['full_name']) ?></td>
                    <td><?= e($o['phone']) ?></td>
                    <td><?= e($o['district']) ?></td>
                    <td><?= money((float) $o['total']) ?></td>
                    <td><span class="badge badge-<?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
                    <td>
                        <?php if ($o['payment_status'] === 'paid'): ?>
                            <span class="badge badge-paid">Paid</span>
                        <?php elseif ($o['payment_method'] === 'cod'): ?>
                            <span class="badge badge-pending">COD</span>
                        <?php else: ?>
                            <span class="badge badge-<?= e($o['payment_status']) ?>"><?= e(ucfirst($o['payment_status'])) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                    <td><a href="<?= url('admin/order-view.php?id=' . (int) $o['id']) ?>" class="btn btn-ghost btn-sm">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?>
                <tr><td colspan="9" class="empty-state">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pager['totalPages'] > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pager['totalPages']; $i++): ?>
            <a class="btn <?= $i === $pager['page'] ? 'btn-primary' : 'btn-ghost' ?> btn-sm" href="<?= url('admin/orders.php?' . pagination_query(['page' => $i])) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>