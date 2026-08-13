<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$orders = fetch_all('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC', [$user['id']]);

$page_title = 'My Orders';
$page_meta  = 'View your Lichi Lover orders.';
$page_noindex = true;
$page_canonical = url('orders.php');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$flash = get_flash();
?>

<div class="crumbs">
    <div class="container">
        <ul>
            <li><a href="<?= url('account.php') ?>">My Account</a></li>
            <li>My Orders</li>
        </ul>
    </div>
</div>

<section class="section" style="padding-top:8px">
    <div class="container">
        <div class="section-head" style="text-align:left;margin-bottom:24px">
            <span class="section-eyebrow">History</span>
            <h2>My Orders</h2>
        </div>

        <div class="account-nav" style="margin-bottom:28px">
            <a href="<?= url('account.php') ?>">Profile</a>
            <a href="<?= url('orders.php') ?>" class="active">My Orders</a>
            <a href="<?= url('logout.php') ?>">Logout</a>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><i data-lucide="info"></i><span><?= e($flash['message']) ?></span></div>
        <?php endif; ?>

        <?php if (!$orders): ?>
        <div class="empty-state">
            <i data-lucide="package-open" style="width:48px;height:48px;color:var(--green-300);margin-bottom:12px"></i>
            <h3 style="margin-bottom:6px">No orders yet</h3>
            <p>When you place an order, it will appear here.</p>
            <a href="<?= url('shop.php') ?>" class="btn btn-primary" style="margin-top:14px">Shop Fresh Lichi</a>
        </div>
        <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o):
                        $itemCount = (int) fetch_val('SELECT COUNT(*) FROM order_items WHERE order_id = ?', [$o['id']]);
                    ?>
                    <tr>
                        <td><a href="<?= url('order-detail.php?id=' . (int) $o['id']) ?>"><?= e($o['order_number']) ?></a></td>
                        <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                        <td><?= $itemCount ?> item<?= $itemCount === 1 ? '' : 's' ?></td>
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
                        <td>
                            <div style="display:flex;gap:8px">
                                <a href="<?= url('order-detail.php?id=' . (int) $o['id']) ?>" class="btn btn-ghost btn-sm">Details</a>
                                <a href="<?= url('track-order.php') ?>" class="btn btn-ghost btn-sm">Track</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>