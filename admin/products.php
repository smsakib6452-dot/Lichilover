<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$q = trim((string) ($_GET['q'] ?? ''));
$status = $_GET['status'] ?? 'all';
$perPage = 20;

// Toggle active status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    require_csrf();
    $id = (int) input('product_id');
    $current = fetch_one('SELECT is_active FROM products WHERE id = ?', [$id]);
    if ($current) {
        query('UPDATE products SET is_active = ? WHERE id = ?', [(int) $current['is_active'] === 1 ? 0 : 1, $id]);
        flash('success', 'Product status updated.');
    }
    redirect('products.php');
}

$where = ['1 = 1'];
$params = [];
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.slug LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($status === 'active') $where[] = 'p.is_active = 1';
if ($status === 'inactive') $where[] = 'p.is_active = 0';
if ($status === 'out') $where[] = 'p.stock_qty <= 0';

$total = (int) fetch_val('SELECT COUNT(*) FROM products p WHERE ' . implode(' AND ', $where), $params);
$pager = paginate($total, $perPage);

$products = fetch_all(
    "SELECT p.*, c.name AS category_name,
            (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.id) AS min_price,
            (SELECT COUNT(*) FROM product_variants v WHERE v.product_id = p.id) AS variant_count
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE " . implode(' AND ', $where) . "
     ORDER BY p.id DESC
     LIMIT {$pager['perPage']} OFFSET {$pager['offset']}",
    $params
);

$adminTitle = 'Products';
require_once __DIR__ . '/includes/header.php';
?>

<div class="filter-bar">
    <form method="get" action="<?= url('admin/products.php') ?>" style="display:flex;gap:8px;flex-wrap:wrap;flex:1">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search products..." style="min-width:200px">
        <select name="status">
            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All statuses</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            <option value="out" <?= $status === 'out' ? 'selected' : '' ?>>Out of stock</option>
        </select>
        <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
    </form>
    <div class="spacer"></div>
    <a href="<?= url('admin/product-add.php') ?>" class="btn btn-primary"><i data-lucide="plus"></i> Add Product</a>
</div>

<div class="admin-card">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= (int) $p['id'] ?></td>
                    <td>
                        <div style="display:flex;gap:10px;align-items:center">
                            <img src="<?= e($p['image'] ?: image_url('lychee_live')) ?>" alt="" style="width:44px;height:44px;border-radius:8px;object-fit:cover">
                            <div>
                                <strong><?= e($p['name']) ?></strong>
                                <div style="font-size:12px;color:var(--muted)"><?= (int) $p['variant_count'] ?> variants</div>
                            </div>
                        </div>
                    </td>
                    <td><?= e($p['category_name'] ?? '—') ?></td>
                    <td><?= $p['min_price'] ? money((float) $p['min_price']) : '—' ?></td>
                    <td>
                        <span class="badge <?= (int) $p['stock_qty'] <= 0 ? 'badge-failed' : ((int) $p['stock_qty'] <= 10 ? 'badge-pending' : 'badge-active') ?>">
                            <?= (int) $p['stock_qty'] ?>
                        </span>
                    </td>
                    <td><?= (int) $p['is_featured'] === 1 ? '<span class="badge badge-active">Yes</span>' : '—' ?></td>
                    <td><span class="badge <?= (int) $p['is_active'] === 1 ? 'badge-active' : 'badge-inactive' ?>"><?= (int) $p['is_active'] === 1 ? 'Active' : 'Inactive' ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <a href="<?= url('admin/product-edit.php?id=' . (int) $p['id']) ?>" class="btn btn-ghost btn-sm">Edit</a>
                            <a href="<?= url('product.php?id=' . (int) $p['id']) ?>" target="_blank" class="btn btn-ghost btn-sm" title="View">👁</a>
                            <form method="post" action="<?= url('admin/products.php') ?>" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="toggle_active" value="1">
                                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                <button type="submit" class="btn btn-ghost btn-sm"><?= (int) $p['is_active'] === 1 ? 'Deactivate' : 'Activate' ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$products): ?>
                <tr><td colspan="8" class="empty-state">No products found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pager['totalPages'] > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pager['totalPages']; $i++): ?>
            <a class="btn <?= $i === $pager['page'] ? 'btn-primary' : 'btn-ghost' ?> btn-sm" href="<?= url('admin/products.php?' . pagination_query(['page' => $i])) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>