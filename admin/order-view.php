<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$order = fetch_one('SELECT * FROM orders WHERE id = ?', [$id]);
if (!$order) {
    flash('error', 'Order not found.');
    redirect('orders.php');
}

$items = fetch_all('SELECT * FROM order_items WHERE order_id = ?', [$id]);
$payments = fetch_all('SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC', [$id]);
$statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = input('action');

    if ($action === 'update_status') {
        $newStatus = input('status');
        if (in_array($newStatus, $statuses, true)) {
            query('UPDATE orders SET status = ? WHERE id = ?', [$newStatus, $id]);
            if ($newStatus === 'delivered') {
                query('UPDATE orders SET payment_status = "paid" WHERE id = ? AND payment_method = "cod"', [$id]);
            }
            flash('success', 'Order status updated to ' . ucfirst($newStatus) . '.');
        }
    }
    if ($action === 'update_payment') {
        $newPayment = input('payment_status');
        if (in_array($newPayment, ['pending', 'processing', 'paid', 'failed', 'refunded'], true)) {
            query('UPDATE orders SET payment_status = ? WHERE id = ?', [$newPayment, $id]);
            flash('success', 'Payment status updated.');
        }
    }
    redirect('order-view.php?id=' . $id);
}

$adminTitle = 'Order ' . $order['order_number'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="filter-bar">
    <a href="<?= url('admin/orders.php') ?>" class="btn btn-ghost btn-sm">&larr; Back to Orders</a>
    <div class="spacer"></div>
    <a href="<?= url('admin/index.php') ?>" class="btn btn-ghost btn-sm">Back to Dashboard</a>
</div>

<div class="order-detail-grid">
    <div>
        <div class="admin-card">
            <h3><?= e($order['order_number']) ?></h3>
            <p style="color:var(--muted);font-size:14px;margin-bottom:12px">
                Placed <?= e(date('M j, Y h:i A', strtotime($order['created_at']))) ?>
                &middot; Updated <?= e(date('M j, Y h:i A', strtotime($order['updated_at']))) ?>
            </p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
                <span class="badge badge-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span>
                <span class="badge badge-<?= e($order['payment_status']) ?>">Payment: <?= e(ucfirst($order['payment_status'])) ?></span>
                <span class="badge badge-active"><?= e(strtoupper($order['payment_method'])) ?></span>
            </div>

            <h4 style="margin-bottom:8px;color:var(--green-900)">Items</h4>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Variant</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['product_name']) ?></td>
                            <td><?= e($item['variant_name'] ?: '—') ?></td>
                            <td><?= (int) $item['quantity'] ?></td>
                            <td><?= money((float) $item['unit_price']) ?></td>
                            <td><?= money((float) $item['line_total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="max-width:340px;margin-top:14px">
                <div class="summary-row" style="display:flex;justify-content:space-between;padding:4px 0"><span>Subtotal</span><span><?= money((float) $order['subtotal']) ?></span></div>
                <div class="summary-row" style="display:flex;justify-content:space-between;padding:4px 0"><span>Delivery</span><span><?= money((float) $order['delivery_fee']) ?></span></div>
                <?php if ((float) $order['discount'] > 0): ?>
                <div class="summary-row" style="display:flex;justify-content:space-between;padding:4px 0;color:var(--green-600)"><span>Discount (<?= e($order['coupon_code'] ?: '') ?>)</span><span>-<?= money((float) $order['discount']) ?></span></div>
                <?php endif; ?>
                <div class="summary-row" style="display:flex;justify-content:space-between;padding:8px 0;font-weight:800;border-top:2px solid var(--line);margin-top:6px"><span>Total</span><span><?= money((float) $order['total']) ?></span></div>
            </div>
        </div>

        <div class="admin-card">
            <h3>Customer &amp; Delivery</h3>
            <p><strong><?= e($order['full_name']) ?></strong> — <?= e($order['phone']) ?></p>
            <?php if ($order['email']): ?><p>Email: <?= e($order['email']) ?></p><?php endif; ?>
            <p style="margin-top:8px"><?= e($order['address']) ?></p>
            <p><?= e($order['upazila']) ?>, <?= e($order['district']) ?>, <?= e($order['division']) ?></p>
            <?php if ($order['delivery_note']): ?><p style="margin-top:8px;color:var(--muted)"><em>Note: <?= e($order['delivery_note']) ?></em></p><?php endif; ?>
        </div>
    </div>

    <div>
        <div class="admin-card">
            <h3>Update Order</h3>
            <form method="post" action="<?= url('admin/order-view.php?id=' . $id) ?>" data-loading>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_status">
                <div class="form-field" style="margin-bottom:10px">
                    <label>Order Status</label>
                    <select name="status">
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?= e($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Update Status</button>
            </form>
        </div>

        <div class="admin-card">
            <h3>Payment</h3>
            <form method="post" action="<?= url('admin/order-view.php?id=' . $id) ?>" data-loading>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_payment">
                <div class="form-field" style="margin-bottom:10px">
                    <label>Payment Status</label>
                    <select name="payment_status">
                        <?php foreach (['pending', 'processing', 'paid', 'failed', 'refunded'] as $ps): ?>
                        <option value="<?= e($ps) ?>" <?= $order['payment_status'] === $ps ? 'selected' : '' ?>><?= e(ucfirst($ps)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Update Payment</button>
            </form>
            <p style="font-size:12px;color:var(--muted);margin-top:10px">Payment status is normally set automatically by the payment gateway.</p>
        </div>

        <div class="admin-card">
            <h3>Payments Recorded</h3>
            <?php if ($payments): ?>
                <?php foreach ($payments as $p): ?>
                <div style="border:1px solid var(--line);border-radius:10px;padding:12px;margin-bottom:10px">
                    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:6px">
                        <strong><?= e(strtoupper($p['method'])) ?></strong>
                        <span class="badge badge-<?= e($p['status']) ?>"><?= e(ucfirst($p['status'])) ?></span>
                    </div>
                    <p style="font-size:13px;margin-top:6px">Amount: <?= money((float) $p['amount']) ?></p>
                    <p style="font-size:12px;color:var(--muted)">Payment ID: <?= e($p['payment_id']) ?></p>
                    <?php if ($p['transaction_id']): ?><p style="font-size:12px;color:var(--muted)">Transaction: <?= e($p['transaction_id']) ?></p><?php endif; ?>
                    <?php if ($p['gateway_response']): ?>
                    <details style="margin-top:8px">
                        <summary style="font-size:13px;cursor:pointer">Gateway response</summary>
                        <pre class="gateway-json"><?= e($p['gateway_response']) ?></pre>
                    </details>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <p class="empty-state">No payment record yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>