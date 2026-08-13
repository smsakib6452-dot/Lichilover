<?php
declare(strict_types=1);

/**
 * Payment gateway callback / IPN endpoint.
 * Gateway redirects and webhooks land here. Payments are NEVER marked "paid"
 * from browser-side data — this endpoint re-verifies with the gateway server-side.
 */

require_once __DIR__ . '/includes/functions.php';
require_once INCLUDES_PATH . '/payments/bkash.php';
require_once INCLUDES_PATH . '/payments/nagad.php';
require_once INCLUDES_PATH . '/payments/cod.php';

$method = strtolower(trim((string) ($_GET['method'] ?? '')));
$orderNumber = trim((string) ($_GET['order'] ?? ''));

if (!in_array($method, ['bkash', 'nagad', 'cod'], true)) {
    http_response_code(400);
    exit('Invalid payment method.');
}

if ($orderNumber === '') {
    http_response_code(400);
    exit('Missing order reference.');
}

$order = fetch_one('SELECT * FROM orders WHERE order_number = ?', [$orderNumber]);
if (!$order) {
    http_response_code(404);
    exit('Order not found.');
}

$payment = order_payment((int) $order['id'], $method);
if (!$payment) {
    http_response_code(404);
    exit('Payment record not found.');
}

$gateway = payment_gateway($method);
$verification = $gateway->verify($payment, array_merge($_GET, $_POST));
$status = $verification['status'] === 'paid' ? 'paid' : 'failed';

update_payment((int) $payment['id'], [
    'status' => $status,
    'transaction_id' => $verification['transaction_id'] ?? null,
    'gateway_response' => json_encode(array_merge($verification, ['method' => $method, 'order' => $orderNumber])),
]);

query('UPDATE orders SET payment_status = ? WHERE id = ?', [$status, $order['id']]);
if ($status === 'paid') {
    query('UPDATE orders SET status = "confirmed" WHERE id = ?', [$order['id']]);
}

log_app("Payment callback: order=$orderNumber method=$method status=$status");

// Redirect the customer to their order result page.
if (!empty($_GET['X-Requested-With']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $status === 'paid', 'order' => $orderNumber]);
    exit;
}

flash($status === 'paid' ? 'success' : 'error', $status === 'paid' ? 'Payment successful! Your order is confirmed.' : 'Payment was not completed.');
redirect('order-success.php?order=' . urlencode($orderNumber));
