<?php
declare(strict_types=1);

/**
 * Payment page. Handles the payment flow for an order:
 *  - COD: confirm immediately (paid on delivery).
 *  - bKash / Nagad: demo simulation or real gateway redirect.
 */

require_once __DIR__ . '/includes/functions.php';
require_once INCLUDES_PATH . '/payments/bkash.php';
require_once INCLUDES_PATH . '/payments/nagad.php';
require_once INCLUDES_PATH . '/payments/cod.php';

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
$method = $order['payment_method'];
$gateway = payment_gateway($method);
$payment = order_payment((int) $order['id'], $method);
$flash = get_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    if ($method === 'cod') {
        // Confirm COD order.
        $paymentId = create_payment($order, 'cod', 'COD-' . $order['order_number']);
        update_payment($paymentId, [
            'status' => 'processing',
            'gateway_response' => json_encode(['cod' => true, 'note' => 'Cash on delivery — payment to be collected at the door.']),
        ]);
        query('UPDATE orders SET status = "confirmed", payment_status = "pending" WHERE id = ?', [$order['id']]);
        unset($_SESSION['paying_order']);
        redirect('order-success.php?order=' . urlencode($order['order_number']));
    }

    // bKash / Nagad
    if (!isset($_SESSION['payment_started'][$orderNumber])) {
        // First visit: create the payment record and simulate/redirect.
        $paymentId = create_payment($order, $method, $gateway->isDemo() ? 'DEMO-' . strtoupper($method) . '-' . $order['order_number'] : '');
        $payment = fetch_one('SELECT * FROM payments WHERE id = ?', [$paymentId]);

        $result = $gateway->initiate($order, $_SERVER['REMOTE_ADDR'] ?? '');

        if (isset($result['redirect'])) {
            $_SESSION['payment_started'][$orderNumber] = true;
            update_payment($paymentId, ['status' => 'processing']);
            redirect($result['redirect']);
        }

        // Demo mode (or failed initiation) — handle here.
        update_payment($paymentId, ['status' => 'processing']);

        if (($result['status'] ?? '') === 'failed') {
            update_payment($paymentId, ['status' => 'failed', 'gateway_response' => json_encode($result)]);
            query('UPDATE orders SET payment_status = "failed" WHERE id = ?', [$order['id']]);
            flash('error', $result['message'] ?? 'Payment could not be processed.');
            redirect('pay.php?order=' . urlencode($order['order_number']));
        }

        // Demo simulation confirmed server-side.
        $verification = $gateway->verify($payment, $_POST);
        $txId = $verification['transaction_id'] ?? ('DEMO-' . strtoupper($method) . '-' . strtoupper(bin2hex(random_bytes(4))));
        $status = $verification['status'] === 'paid' ? 'paid' : 'failed';

        update_payment($paymentId, [
            'status' => $status,
            'transaction_id' => $txId,
            'gateway_response' => json_encode(array_merge($verification, ['demo' => $gateway->isDemo(), 'method' => $method, 'order' => $orderNumber])),
        ]);
        query('UPDATE orders SET payment_status = ?, status = "confirmed" WHERE id = ?', [$status === 'paid' ? 'paid' : 'failed', $order['id']]);
        unset($_SESSION['payment_started'][$orderNumber], $_SESSION['paying_order']);

        if ($status === 'paid') {
            flash('success', 'Payment ' . ($gateway->isDemo() ? 'simulated successfully (demo).' : 'successful.') . ' Your order is confirmed.');
        } else {
            flash('error', 'Payment could not be completed.');
        }
        redirect('order-success.php?order=' . urlencode($order['order_number']));
    }

    flash('info', 'Please complete the payment using the secure page that opened.');
    redirect('pay.php?order=' . urlencode($order['order_number']));
}

// If payment already settled, go straight to success.
if ($order['payment_status'] === 'paid') {
    redirect('order-success.php?order=' . urlencode($order['order_number']));
}
if ($order['status'] === 'cancelled') {
    flash('error', 'This order was cancelled.');
    redirect('track-order.php');
}

$page_title = 'Payment — ' . $orderNumber;
$page_meta  = 'Complete your payment for order ' . $orderNumber . '.';
$page_noindex = true;
$page_canonical = url('pay.php?order=' . urlencode($orderNumber));

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$labels = ['cod' => 'Cash on Delivery', 'bkash' => 'bKash', 'nagad' => 'Nagad'];
?>

<div class="crumbs">
    <div class="container">
        <ul>
            <li><a href="<?= url('cart.php') ?>">Cart</a></li>
            <li>Payment</li>
        </ul>
    </div>
</div>

<section class="section" style="padding-top:8px">
    <div class="container" style="max-width:640px">
        <div class="section-head">
            <span class="section-eyebrow">Secure Checkout</span>
            <h2>Complete Your Payment</h2>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle-2' : ($flash['type'] === 'error' ? 'alert-circle' : 'info') ?>"></i>
            <span><?= e($flash['message']) ?></span>
        </div>
        <?php endif; ?>

        <div class="checkout-form-card" style="max-width:640px;margin:0 auto">
            <div style="text-align:center;margin-bottom:20px">
                <div class="pay-icon" style="width:64px;height:64px;border-radius:16px;font-size:18px;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;color:#fff" class="<?= e($method) ?>">
                    <?= e(strtoupper($method === 'cod' ? 'COD' : $method)) ?>
                </div>
                <h3 style="color:var(--green-900)"><?= e($labels[$method] ?? ucfirst($method)) ?></h3>
                <p class="order-no" style="margin-top:8px"><?= e($order['order_number']) ?></p>
            </div>

            <div class="order-summary-list">
                <?php foreach ($items as $item): ?>
                <div class="os-item">
                    <span class="os-name"><?= e($item['product_name']) ?> <span class="os-qty">×<?= (int) $item['quantity'] ?></span></span>
                    <span class="os-price"><?= money((float) $item['line_total']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-row"><span>Subtotal</span><span><?= money((float) $order['subtotal']) ?></span></div>
            <div class="summary-row"><span>Delivery</span><span><?= money((float) $order['delivery_fee']) ?></span></div>
            <?php if ((float) $order['discount'] > 0): ?>
            <div class="summary-row discount"><span>Discount</span><span>-<?= money((float) $order['discount']) ?></span></div>
            <?php endif; ?>
            <div class="summary-row total"><span>Total Payable</span><span><?= money((float) $order['total']) ?></span></div>

            <?php if (PAYMENT_MODE === 'demo' && $method !== 'cod'): ?>
            <div class="demo-banner" style="margin-top:16px">
                <i data-lucide="flask-conical"></i>
                <span><strong>Demo Payment Mode</strong> — no real money will be charged. The transaction below is simulated for development.</span>
            </div>
            <?php endif; ?>

            <form method="post" action="<?= url('pay.php?order=' . urlencode($order['order_number'])) ?>" data-loading style="margin-top:18px">
                <?= csrf_field() ?>

                <?php if ($method === 'cod'): ?>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Confirm Order — Pay on Delivery</button>
                    <p style="font-size:13px;color:var(--muted);text-align:center;margin-top:12px">Pay <strong><?= money((float) $order['total']) ?></strong> in cash when your lichi arrives.</p>
                <?php elseif ($gateway->isDemo()): ?>
                    <button type="submit" class="btn btn-<?= e($method === 'bkash' ? 'lichi' : 'primary') ?> btn-lg btn-block">
                        <i data-lucide="wallet"></i>
                        Pay <?= money((float) $order['total']) ?> via <?= e($labels[$method]) ?> (Demo)
                    </button>
                    <p style="font-size:13px;color:var(--muted);text-align:center;margin-top:12px">This simulates the <?= e($labels[$method]) ?> payment flow. No real transaction occurs.</p>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i data-lucide="wallet"></i>
                        Pay <?= money((float) $order['total']) ?> via <?= e($labels[$method]) ?>
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>