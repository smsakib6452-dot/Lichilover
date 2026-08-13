<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$id = (int) ($_GET['id'] ?? 0);

$order = fetch_one('SELECT * FROM orders WHERE id = ? AND user_id = ?', [$id, $user['id']]);
if (!$order) {
    http_response_code(404);
    $page_title = 'Order not found';
    $page_noindex = true;
    require_once INCLUDES_PATH . '/header.php';
    require_once INCLUDES_PATH . '/navbar.php';
    ?>
    <div class="not-found">
        <h1>404</h1>
        <h2>Order not found</h2>
        <p>We could not find this order in your account.</p>
        <a href="<?= url('orders.php') ?>" class="btn btn-primary">Back to Orders</a>
    </div>
    <?php
    require_once INCLUDES_PATH . '/footer.php';
    exit;
}

$items = fetch_all('SELECT * FROM order_items WHERE order_id = ?', [$order['id']]);
$payment = fetch_one('SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1', [$order['id']]);

$page_title = 'Order ' . $order['order_number'];
$page_meta  = 'Order details for ' . $order['order_number'] . '.';
$page_noindex = true;
$page_canonical = url('order-detail.php?id=' . $order['id']);

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="crumbs">
    <div class="container">
        <ul>
            <li><a href="<?= url('orders.php') ?>">My Orders</a></li>
            <li><?= e($order['order_number']) ?></li>
        </ul>
    </div>
</div>

<section class="section" style="padding-top:8px">
    <div class="container" style="max-width:760px">
        <div class="checkout-form-card" style="margin-bottom:20px">
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px">
                <div>
                    <h3 style="color:var(--green-900)"><?= e($order['order_number']) ?></h3>
                    <p style="color:var(--muted);font-size:14px">Placed <?= e(date('M j, Y h:i A', strtotime($order['created_at']))) ?></p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <span class="badge badge-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span>
                    <?php if ($order['payment_status'] === 'paid'): ?>
                        <span class="badge badge-paid">Paid</span>
                    <?php elseif ($order['payment_method'] === 'cod'): ?>
                        <span class="badge badge-pending">Pay on Delivery</span>
                    <?php else: ?>
                        <span class="badge badge-<?= e($order['payment_status']) ?>"><?= e(ucfirst($order['payment_status'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="margin-top:16px">
                <a href="<?= url('track-order.php') ?>" class="btn btn-primary btn-sm">Track This Order</a>
            </div>
        </div>

        <div class="checkout-form-card" style="margin-bottom:20px">
            <h3 style="margin-bottom:16px">Items</h3>
            <?php foreach ($items as $item): ?>
            <div class="os-item" style="margin-bottom:12px">
                <span class="os-name"><?= e($item['product_name']) ?> <span class="os-qty">×<?= (int) $item['quantity'] ?></span></span>
                <span class="os-price"><?= money((float) $item['line_total']) ?></span>
            </div>
            <?php endforeach; ?>
            <div class="summary-row"><span>Subtotal</span><span><?= money((float) $order['subtotal']) ?></span></div>
            <div class="summary-row"><span>Delivery</span><span><?= money((float) $order['delivery_fee']) ?></span></div>
            <?php if ((float) $order['discount'] > 0): ?>
            <div class="summary-row discount"><span>Discount (<?= e($order['coupon_code'] ?: '') ?>)</span><span>-<?= money((float) $order['discount']) ?></span></div>
            <?php endif; ?>
            <div class="summary-row total"><span>Total</span><span><?= money((float) $order['total']) ?></span></div>
        </div>

        <div class="checkout-form-card">
            <h3 style="margin-bottom:16px">Delivery Details</h3>
            <p><strong><?= e($order['full_name']) ?></strong> — <?= e($order['phone']) ?><?= $order['email'] ? ' (' . e($order['email']) . ')' : '' ?></p>
            <p style="margin-top:8px"><?= e($order['address']) ?>, <?= e($order['upazila']) ?>, <?= e($order['district']) ?>, <?= e($order['division']) ?></p>
            <?php if ($order['delivery_note']): ?>
            <p style="margin-top:8px;color:var(--muted)"><em>Note: <?= e($order['delivery_note']) ?></em></p>
            <?php endif; ?>
            <?php if ($payment && $payment['gateway_response']): $g = json_decode($payment['gateway_response'], true); if (is_array($g) && !empty($g['demo'])): ?>
            <p style="margin-top:12px;font-size:13px;color:var(--muted)">This payment was a demo simulation — no real money charged.</p>
            <?php endif; endif; ?>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>