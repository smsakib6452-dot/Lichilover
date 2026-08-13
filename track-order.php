<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$order = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $orderNumber = strtoupper(trim((string) input('order_number')));
    $phone = normalize_bd_phone(input('phone'));

    if ($orderNumber === '') $errors['order_number'] = 'Please enter your order number.';
    if (!is_valid_bd_phone($phone)) $errors['phone'] = 'Please enter the phone number used for this order.';

    if (!$errors) {
        $order = fetch_one('SELECT * FROM orders WHERE order_number = ? AND phone = ?', [$orderNumber, $phone]);
        if (!$order) {
            $errors['general'] = 'No order found with that order number and phone. Please check and try again.';
        }
    }
}

$steps = [
    'pending'     => ['label' => 'Order Placed', 'desc' => 'We received your order'],
    'confirmed'   => ['label' => 'Confirmed', 'desc' => 'Order confirmed by our team'],
    'processing'  => ['label' => 'Processing', 'desc' => 'Fresh lichi being packed'],
    'shipped'     => ['label' => 'Shipped', 'desc' => 'On the way to your address'],
    'delivered'   => ['label' => 'Delivered', 'desc' => 'Delivered to your doorstep'],
];
$statusOrder = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
$currentIndex = $order ? array_search($order['status'], $statusOrder, true) : -1;

$page_title = 'Track Your Order';
$page_meta  = 'Track your Lichi Lover order status in real time.';
$page_canonical = url('track-order.php');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$flash = get_flash();
?>

<div class="page-hero">
    <h1>Track Your Order</h1>
    <p>Enter your order number and phone number to see live status.</p>
</div>

<section class="section">
    <div class="container">
        <div style="max-width:520px;margin:0 auto">
            <form method="post" action="<?= url('track-order.php') ?>" class="checkout-form-card" data-loading>
                <?= csrf_field() ?>
                <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><i data-lucide="info"></i><span><?= e($flash['message']) ?></span></div>
                <?php endif; ?>
                <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error"><i data-lucide="alert-circle"></i><span><?= e($errors['general']) ?></span></div>
                <?php endif; ?>

                <div class="form-field" style="margin-bottom:14px">
                    <label>Order Number</label>
                    <input type="text" name="order_number" value="<?= e(input('order_number', $order ? $order['order_number'] : '')) ?>" placeholder="e.g. LL-2026-000001" required>
                    <?php if (isset($errors['order_number'])): ?><span class="form-error"><?= e($errors['order_number']) ?></span><?php endif; ?>
                </div>
                <div class="form-field" style="margin-bottom:18px">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?= e(input('phone')) ?>" placeholder="017XXXXXXXX" required>
                    <?php if (isset($errors['phone'])): ?><span class="form-error"><?= e($errors['phone']) ?></span><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Track My Order</button>
            </form>
        </div>

        <?php if ($order): ?>
        <div style="max-width:640px;margin:48px auto 0">
            <div class="checkout-form-card" style="text-align:center;margin-bottom:24px">
                <p class="order-no"><?= e($order['order_number']) ?></p>
                <p style="color:var(--muted);font-size:14px">Placed on <?= e(date('M j, Y h:i A', strtotime($order['created_at']))) ?></p>
                <p style="margin-top:8px">
                    <?php if ($order['status'] === 'cancelled'): ?>
                        <span class="badge badge-cancelled">Cancelled</span>
                    <?php else: ?>
                        <span class="badge badge-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span>
                        &middot;
                        <?php if ($order['payment_status'] === 'paid'): ?>
                            <span class="badge badge-paid">Paid</span>
                        <?php elseif ($order['payment_method'] === 'cod'): ?>
                            <span class="badge badge-pending">Pay on Delivery</span>
                        <?php else: ?>
                            <span class="badge badge-<?= e($order['payment_status']) ?>"><?= e(ucfirst($order['payment_status'])) ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </p>
            </div>

            <div class="timeline">
                <?php if ($order['status'] === 'cancelled'): ?>
                    <div class="alert alert-error"><i data-lucide="alert-circle"></i><span>This order was cancelled. Please contact support for assistance.</span></div>
                <?php else: ?>
                    <?php foreach ($steps as $key => $step):
                        $idx = array_search($key, $statusOrder, true);
                        $state = $idx < $currentIndex ? 'done' : ($idx === $currentIndex ? 'active' : '');
                    ?>
                    <div class="timeline-step <?= e($state) ?>">
                        <div class="tl-dot"></div>
                        <div class="tl-body">
                            <b><?= e($step['label']) ?></b>
                            <span><?= e($step['desc']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>