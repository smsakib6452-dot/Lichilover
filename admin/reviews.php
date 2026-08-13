<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = input('action');
    $id = (int) input('review_id');

    if ($action === 'approve') {
        query('UPDATE reviews SET status = "approved" WHERE id = ?', [$id]);
        flash('success', 'Review approved.');
    }
    if ($action === 'reject') {
        query('UPDATE reviews SET status = "rejected" WHERE id = ?', [$id]);
        flash('success', 'Review rejected.');
    }
    if ($action === 'delete') {
        query('DELETE FROM reviews WHERE id = ?', [$id]);
        flash('success', 'Review deleted.');
    }
    redirect('reviews.php');
}

$status = $_GET['status'] ?? 'pending';
$where = ['1 = 1'];
$params = [];
if ($status !== 'all') {
    $where[] = 'r.status = ?';
    $params[] = $status;
}

$reviews = fetch_all(
    "SELECT r.*, u.name AS user_name, p.name AS product_name, p.id AS product_id
     FROM reviews r
     JOIN users u ON u.id = r.user_id
     LEFT JOIN products p ON p.id = r.product_id
     WHERE " . implode(' AND ', $where) . "
     ORDER BY r.created_at DESC LIMIT 100",
    $params
);

$pendingCount = (int) fetch_val('SELECT COUNT(*) FROM reviews WHERE status = "pending"');

$adminTitle = 'Reviews';
require_once __DIR__ . '/includes/header.php';
?>

<div class="filter-bar">
    <form method="get" action="<?= url('admin/reviews.php') ?>" style="display:flex;gap:8px">
        <select name="status" onchange="this.form.submit()">
            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending (<?= $pendingCount ?>)</option>
            <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
        </select>
    </form>
</div>

<div class="admin-card">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $r): ?>
                <tr>
                    <td><a href="<?= url('product.php?id=' . (int) $r['product_id']) ?>" target="_blank"><?= e($r['product_name'] ?: 'Deleted product') ?></a></td>
                    <td><?= e($r['user_name']) ?></td>
                    <td style="color:var(--gold)"><?= str_repeat('★', (int) $r['rating']) ?></td>
                    <td style="max-width:300px"><?= e(truncate($r['review'] ?: '', 120)) ?></td>
                    <td><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
                    <td>
                        <span class="badge badge-<?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span>
                        <?php if ($r['is_demo']): ?><span class="badge badge-inactive">Sample</span><?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <?php if ($r['status'] !== 'approved'): ?>
                            <form method="post" action="<?= url('admin/reviews.php') ?>" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="review_id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" class="btn btn-ghost btn-sm">Approve</button>
                            </form>
                            <?php endif; ?>
                            <?php if ($r['status'] !== 'rejected'): ?>
                            <form method="post" action="<?= url('admin/reviews.php') ?>" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="review_id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" class="btn btn-ghost btn-sm">Reject</button>
                            </form>
                            <?php endif; ?>
                            <form method="post" action="<?= url('admin/reviews.php') ?>" style="display:inline" data-confirm="Delete this review?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="review_id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$reviews): ?>
                <tr><td colspan="7" class="empty-state">No reviews in this view.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>