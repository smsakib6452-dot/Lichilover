<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Stats
$totalSales = (float) fetch_val("SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'cancelled'");
$totalOrders = (int) fetch_val('SELECT COUNT(*) FROM orders');
$pendingOrders = (int) fetch_val("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$deliveredOrders = (int) fetch_val("SELECT COUNT(*) FROM orders WHERE status = 'delivered'");
$totalCustomers = (int) fetch_val('SELECT COUNT(*) FROM users');
$totalProducts = (int) fetch_val('SELECT COUNT(*) FROM products');
$lowStock = (int) fetch_val('SELECT COUNT(*) FROM products WHERE stock_qty > 0 AND stock_qty <= 10');
$outOfStock = (int) fetch_val('SELECT COUNT(*) FROM products WHERE stock_qty <= 0');

// Sales last 7 days
$salesData = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $sum = (float) fetch_val("SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'cancelled' AND DATE(created_at) = ?", [$day]);
    $salesData[] = ['day' => date('D', strtotime($day)), 'total' => $sum];
}

// Status distribution
$statusCounts = [
    'pending'    => (int) fetch_val("SELECT COUNT(*) FROM orders WHERE status = 'pending'"),
    'confirmed'  => (int) fetch_val("SELECT COUNT(*) FROM orders WHERE status = 'confirmed'"),
    'processing' => (int) fetch_val("SELECT COUNT(*) FROM orders WHERE status = 'processing'"),
    'shipped'    => (int) fetch_val("SELECT COUNT(*) FROM orders WHERE status = 'shipped'"),
    'delivered'  => (int) fetch_val("SELECT COUNT(*) FROM orders WHERE status = 'delivered'"),
    'cancelled'  => (int) fetch_val("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'"),
];

$recentOrders = fetch_all('SELECT * FROM orders ORDER BY id DESC LIMIT 8');

$adminTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="banknote"></i></div>
        <span class="stat-label">Total Sales</span>
        <span class="stat-value"><?= money($totalSales) ?></span>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="shopping-cart"></i></div>
        <span class="stat-label">Total Orders</span>
        <span class="stat-value"><?= $totalOrders ?></span>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="hourglass"></i></div>
        <span class="stat-label">Pending Orders</span>
        <span class="stat-value"><?= $pendingOrders ?></span>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="package-check"></i></div>
        <span class="stat-label">Delivered</span>
        <span class="stat-value"><?= $deliveredOrders ?></span>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="users"></i></div>
        <span class="stat-label">Customers</span>
        <span class="stat-value"><?= $totalCustomers ?></span>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="package"></i></div>
        <span class="stat-label">Products</span>
        <span class="stat-value"><?= $totalProducts ?></span>
    </div>
    <div class="stat-card <?= $lowStock > 0 ? 'stat-warn' : '' ?>">
        <div class="stat-icon"><i data-lucide="alert-triangle"></i></div>
        <span class="stat-label">Low Stock</span>
        <span class="stat-value"><?= $lowStock ?></span>
    </div>
    <div class="stat-card <?= $outOfStock > 0 ? 'stat-danger' : '' ?>">
        <div class="stat-icon"><i data-lucide="x-circle"></i></div>
        <span class="stat-label">Out of Stock</span>
        <span class="stat-value"><?= $outOfStock ?></span>
    </div>
</div>

<div class="admin-grid-2">
    <div class="admin-card">
        <h3>Sales — Last 7 Days</h3>
        <canvas id="salesChart" height="220"></canvas>
    </div>
    <div class="admin-card">
        <h3>Orders by Status</h3>
        <canvas id="statusChart" height="220"></canvas>
    </div>
</div>

<div class="admin-card">
    <div class="card-head">
        <h3>Recent Orders</h3>
        <a href="<?= url('admin/orders.php') ?>" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td><a href="<?= url('admin/order-view.php?id=' . (int) $o['id']) ?>"><?= e($o['order_number']) ?></a></td>
                    <td><?= e($o['full_name']) ?></td>
                    <td><?= e($o['phone']) ?></td>
                    <td><?= money((float) $o['total']) ?></td>
                    <td><span class="badge badge-<?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
                    <td>
                        <?php if ($o['payment_status'] === 'paid'): ?>
                            <span class="badge badge-paid">Paid</span>
                        <?php else: ?>
                            <span class="badge badge-<?= e($o['payment_status']) ?>"><?= e(ucfirst($o['payment_status'])) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                    <td><a href="<?= url('admin/order-view.php?id=' . (int) $o['id']) ?>" class="btn btn-ghost btn-sm">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$recentOrders): ?>
                <tr><td colspan="8" class="empty-state">No orders yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
window.SALES_DATA = <?= json_encode($salesData) ?>;
window.STATUS_DATA = <?= json_encode($statusCounts) ?>;
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>