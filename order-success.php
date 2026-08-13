<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$orderNumber = trim((string) ($_GET['order'] ?? ''));
$order = $orderNumber !== '' ? fetch_one('SELECT * FROM orders WHERE order_number = ?', [$orderNumber]) : null;

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
        <p>We could not find this order. Please check your order number.</p>
        <a href="<?= url('index.php') ?>" class="btn btn-primary">Back Home</a>
    </div>
    <?php
    require_once INCLUDES_PATH . '/footer.php';
    exit;
}

$items = fetch_all('SELECT * FROM order_items WHERE order_id = ?', [$order['id']]);
$payment = fetch_one('SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1', [$order['id']]);
$isDemo = false;
if ($payment && $payment['gateway_response']) {
    $resp = json_decode($payment['gateway_response'], true);
    $isDemo = is_array($resp) && !empty($resp['demo']);
}

$page_title = 'Order Confirmed — ' . $orderNumber;
$page_meta  = 'Your lichi order has been placed.';
$page_noindex = true;
$page_canonical = url('order-success.php?order=' . urlencode($orderNumber));

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$flash = get_flash();
?>

<div class="crumbs">
    <div class="container">
        <ul>
            <li><a href="<?= url('index.php') ?>">Home</a></li>
            <li>Order Confirmation</li>
        </ul>
    </div>
</div>

<section class="section" style="padding-top:8px">
    <div class="container success-wrap">
        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" style="text-align:left;max-width:560px;margin:0 auto 20px">
            <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle-2' : 'alert-circle' ?>"></i>
            <span><?= e($flash['message']) ?></span>
        </div>
        <?php endif; ?>

        <div class="success-icon"><i data-lucide="check"></i></div>
        <h1>Thank you for your order!</h1>
        <p>Your order has been placed successfully. We have received your details and will contact you for confirmation.</p>

        <span class="order-no">Order Number: <?= e($order['order_number']) ?></span>

        <?php if ($isDemo): ?>
        <div class="demo-banner" style="text-align:left;max-width:560px;margin:20px auto 0">
            <i data-lucide="flask-conical"></i>
            <span><strong>Demo Payment</strong> — this order used a simulated payment. No real money was charged.</span>
        </div>
        <?php endif; ?>

        <div class="checkout-form-card" style="max-width:600px;margin:28px auto 0;text-align:left">
            <h3 style="margin-bottom:14px">Order Summary</h3>
            <?php foreach ($items as $item): ?>
            <div class="os-item" style="margin-bottom:10px">
                <span class="os-name"><?= e($item['product_name']) ?> <span class="os-qty">×<?= (int) $item['quantity'] ?></span></span>
                <span class="os-price"><?= money((float) $item['line_total']) ?></span>
            </div>
            <?php endforeach; ?>
            <div class="summary-row"><span>Subtotal</span><span><?= money((float) $order['subtotal']) ?></span></div>
            <div class="summary-row"><span>Delivery</span><span><?= money((float) $order['delivery_fee']) ?></span></div>
            <?php if ((float) $order['discount'] > 0): ?>
            <div class="summary-row discount"><span>Discount</span><span>-<?= money((float) $order['discount']) ?></span></div>
            <?php endif; ?>
            <div class="summary-row total"><span>Total</span><span><?= money((float) $order['total']) ?></span></div>

            <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--line);font-size:14px;color:var(--ink-soft)">
                <p><strong>Deliver to:</strong> <?= e($order['full_name']) ?>, <?= e($order['phone']) ?></p>
                <p><?= e($order['address']) ?>, <?= e($order['upazila']) ?>, <?= e($order['district']) ?>, <?= e($order['division']) ?></p>
            </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:28px">
            <a href="<?= url('track-order.php') ?>" class="btn btn-primary">Track Order</a>
            <a href="<?= url('shop.php') ?>" class="btn btn-ghost">Continue Shopping</a>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>