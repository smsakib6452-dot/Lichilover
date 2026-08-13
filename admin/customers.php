<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$q = trim((string) ($_GET['q'] ?? ''));
$perPage = 20;

$where = ['1 = 1'];
$params = [];
if ($q !== '') {
    $where[] = '(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}

$total = (int) fetch_val('SELECT COUNT(*) FROM users u WHERE ' . implode(' AND ', $where), $params);
$pager = paginate($total, $perPage);

$users = fetch_all(
    'SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count,
            (SELECT COALESCE(SUM(o.total),0) FROM orders o WHERE o.user_id = u.id AND o.status <> "cancelled") AS total_spent
     FROM users u WHERE ' . implode(' AND ', $where) . ' ORDER BY u.id DESC LIMIT ' . $pager['perPage'] . ' OFFSET ' . $pager['offset'],
    $params
);

$adminTitle = 'Customers';
require_once __DIR__ . '/includes/header.php';
?>

<div class="filter-bar">
    <form method="get" action="<?= url('admin/customers.php') ?>" style="display:flex;gap:8px;flex-wrap:wrap;flex:1">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email, phone..." style="min-width:240px">
        <button type="submit" class="btn btn-ghost btn-sm">Search</button>
    </form>
</div>

<div class="admin-card">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int) $u['id'] ?></td>
                    <td>
                        <strong><?= e($u['name']) ?></strong>
                        <div style="font-size:12px;color:var(--muted)"><?= e($u['email']) ?></div>
                    </td>
                    <td><?= e($u['phone']) ?></td>
                    <td><?= (int) $u['order_count'] ?></td>
                    <td><?= money((float) $u['total_spent']) ?></td>
                    <td><?= e(date('M j, Y', strtotime($u['created_at']))) ?></td>
                    <td><span class="badge <?= (int) $u['is_active'] === 1 ? 'badge-active' : 'badge-inactive' ?>"><?= (int) $u['is_active'] === 1 ? 'Active' : 'Inactive' ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$users): ?>
                <tr><td colspan="7" class="empty-state">No customers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pager['totalPages'] > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pager['totalPages']; $i++): ?>
            <a class="btn <?= $i === $pager['page'] ? 'btn-primary' : 'btn-ghost' ?> btn-sm" href="<?= url('admin/customers.php?' . pagination_query(['page' => $i])) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>