<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$checkout = cart_checkout();
$user = current_user();

// Load all payment gateways so classes are available.
require_once INCLUDES_PATH . '/payments/bkash.php';
require_once INCLUDES_PATH . '/payments/nagad.php';
require_once INCLUDES_PATH . '/payments/cod.php';

$paymentMethods = [
    'cod'   => ['label' => 'Cash on Delivery', 'icon' => 'cod', 'desc' => 'Pay in cash when your lichi arrives'],
    'bkash' => ['label' => 'bKash', 'icon' => 'bkash', 'desc' => 'Pay securely with your bKash account'],
    'nagad' => ['label' => 'Nagad', 'icon' => 'nagad', 'desc' => 'Pay securely with your Nagad account'],
];

if (empty($checkout['items'])) {
    flash('info', 'Your cart is empty. Add some fresh lichi first!');
    redirect('cart.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $fullName = trim((string) input('full_name'));
    $phone    = normalize_bd_phone(input('phone'));
    $email    = trim((string) input('email'));
    $division = trim((string) input('division'));
    $district = trim((string) input('district'));
    $upazila  = trim((string) input('upazila'));
    $address  = trim((string) input('address'));
    $note     = trim((string) input('delivery_note'));
    $method   = trim((string) input('payment_method'));

    if ($fullName === '' || mb_strlen($fullName) < 3) $errors['full_name'] = 'Please enter your full name.';
    if (!is_valid_bd_phone($phone)) $errors['phone'] = 'Please enter a valid Bangladeshi phone number (e.g. 01712345678).';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
    if ($email === '') $errors['email'] = 'Email is required for order updates.';
    if ($division === '') $errors['division'] = 'Please select your division.';
    if ($district === '') $errors['district'] = 'Please select your district.';
    if ($upazila === '') $errors['upazila'] = 'Please enter your upazila / thana.';
    if ($address === '' || mb_strlen($address) < 10) $errors['address'] = 'Please enter your full address (at least 10 characters).';
    if (!in_array($method, ['cod', 'bkash', 'nagad'], true)) $errors['payment_method'] = 'Please choose a payment method.';

    if (!$errors) {
        $db = db();
        try {
            $db->beginTransaction();

            // Re-fetch fresh cart data and validate stock.
            $fresh = cart_checkout();
            if (empty($fresh['items'])) {
                throw new RuntimeException('Your cart is empty.');
            }
            $subtotal = 0.0;
            foreach ($fresh['items'] as $item) {
                if ($item['stock_qty'] > 0 && $item['qty'] > $item['stock_qty']) {
                    throw new RuntimeException('Insufficient stock for ' . $item['product_name'] . '. Please reduce quantity.');
                }
                $subtotal += $item['line_total'];
            }

            // Delivery fee from DB.
            $zone = fetch_one('SELECT * FROM delivery_zones WHERE district = ? AND is_active = 1 LIMIT 1', [$district]);
            if (!$zone) {
                $zone = fetch_one("SELECT * FROM delivery_zones WHERE district = 'Other Districts' AND is_active = 1 LIMIT 1");
            }
            $deliveryFee = $zone ? (float) $zone['delivery_fee'] : 0.0;
            $freeThreshold = $zone ? (float) $zone['free_delivery_threshold'] : 0.0;
            if ($freeThreshold > 0 && $subtotal >= $freeThreshold) {
                $deliveryFee = 0.0;
            }

            // Coupon
            $discount = 0.0;
            $couponCode = null;
            $couponCodeInput = strtoupper(trim((string) input('coupon_code')));
            if ($couponCodeInput !== '') {
                $coupon = fetch_one('SELECT * FROM coupons WHERE code = ? AND is_active = 1 LIMIT 1', [$couponCodeInput]);
                if (!$coupon) {
                    throw new RuntimeException('Invalid coupon code.');
                }
                $now = date('Y-m-d');
                if ($coupon['expires_at'] && $coupon['expires_at'] < $now) {
                    throw new RuntimeException('This coupon has expired.');
                }
                if ($coupon['usage_limit'] > 0 && (int) $coupon['used_count'] >= (int) $coupon['usage_limit']) {
                    throw new RuntimeException('This coupon has reached its usage limit.');
                }
                if ($subtotal < (float) $coupon['min_order']) {
                    throw new RuntimeException('This coupon requires a minimum order of ' . money((float) $coupon['min_order']) . '.');
                }
                if ($coupon['discount_type'] === 'percent') {
                    $discount = $subtotal * ((float) $coupon['discount_value'] / 100);
                    if ($coupon['max_discount'] && $discount > (float) $coupon['max_discount']) {
                        $discount = (float) $coupon['max_discount'];
                    }
                } else {
                    $discount = min((float) $coupon['discount_value'], $subtotal);
                }
                $couponCode = $coupon['code'];
            }

            $total = max(0, $subtotal - $discount) + $deliveryFee;

            // Generate order number LL-YYYY-######
            $year = date('Y');
            $seq = (int) fetch_val("SELECT COUNT(*) FROM orders WHERE order_number LIKE 'LL-$year-%'");
            $orderNumber = '';
            do {
                $seq++;
                $orderNumber = sprintf('LL-%d-%06d', (int) $year, $seq);
            } while (fetch_one('SELECT id FROM orders WHERE order_number = ?', [$orderNumber]));

            $stmt = query(
                'INSERT INTO orders
                 (user_id, order_number, full_name, phone, email, division, district, upazila, address, delivery_note,
                  delivery_zone_id, subtotal, delivery_fee, discount, coupon_code, total, status, payment_method, payment_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending", ?, "pending")',
                [
                    $user ? $user['id'] : null,
                    $orderNumber,
                    $fullName,
                    $phone,
                    $email,
                    $division,
                    $district,
                    $upazila,
                    $address,
                    $note,
                    $zone ? $zone['id'] : null,
                    $subtotal,
                    $deliveryFee,
                    $discount,
                    $couponCode,
                    $total,
                    $method,
                ]
            );
            $orderId = (int) $db->lastInsertId();

            foreach ($fresh['items'] as $item) {
                query(
                    'INSERT INTO order_items (order_id, product_id, variant_id, product_name, variant_name, weight, unit_price, quantity, line_total)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [$orderId, $item['product_id'], $item['variant_id'], $item['product_name'], $item['variant_name'],
                     $item['weight'], $item['unit_price'], $item['qty'], $item['line_total']]
                );
                // Decrement stock
                if ($item['stock_qty'] > 0) {
                    query('UPDATE product_variants SET stock_qty = stock_qty - ? WHERE id = ?', [$item['qty'], $item['variant_id']]);
                    query('UPDATE products SET stock_qty = GREATEST(0, stock_qty - ?), sold_count = sold_count + ? WHERE id = ?', [$item['qty'], $item['qty'], $item['product_id']]);
                }
            }

            // Coupon usage
            if ($couponCode !== null) {
                $coupon = fetch_one('SELECT * FROM coupons WHERE code = ?', [$couponCode]);
                query('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?', [$coupon['id']]);
                query('INSERT INTO coupon_usages (coupon_id, order_id, user_id, discount_amount) VALUES (?, ?, ?, ?)', [$coupon['id'], $orderId, $user ? $user['id'] : null, $discount]);
            }

            // Save address for logged-in users
            if ($user) {
                query(
                    'INSERT INTO addresses (user_id, full_name, phone, division, district, upazila, address, is_default)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 0)',
                    [$user['id'], $fullName, $phone, $division, $district, $upazila, $address]
                );
            }

            $db->commit();
            cart_clear();

            // Redirect to payment page
            $order = fetch_one('SELECT * FROM orders WHERE id = ?', [$orderId]);
            $_SESSION['paying_order'] = $orderNumber;
            redirect('pay.php?order=' . urlencode($orderNumber));
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            log_app('Checkout error: ' . $e->getMessage());
            $errors['general'] = $e->getMessage();
        }
    }

    with_old([
        'full_name' => $fullName ?? '', 'phone' => $phone ?? '', 'email' => $email ?? '',
        'division' => $division ?? '', 'district' => $district ?? '', 'upazila' => $upazila ?? '',
        'address' => $address ?? '', 'delivery_note' => $note ?? '',
    ]);
}

$defaults = [
    'full_name' => $user ? $user['name'] : '',
    'phone'     => $user ? $user['phone'] : '',
    'email'     => $user ? $user['email'] : '',
    'division'  => '',
    'district'  => '',
    'upazila'   => '',
    'address'   => '',
    'delivery_note' => '',
];

$page_title = 'Checkout';
$page_meta  = 'Complete your lichi order — delivery details and payment.';
$page_noindex = true;
$page_canonical = url('checkout.php');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$zones = delivery_zones();
$divisions = bd_divisions();
?>

<div class="crumbs">
    <div class="container">
        <ul>
            <li><a href="<?= url('cart.php') ?>">Cart</a></li>
            <li>Checkout</li>
        </ul>
    </div>
</div>

<section class="section" style="padding-top:8px">
    <div class="container">
        <div class="section-head" style="text-align:left;margin-bottom:24px">
            <span class="section-eyebrow">Almost There</span>
            <h2>Checkout</h2>
        </div>

        <?php if (isset($errors['general'])): ?>
        <div class="alert alert-error">
            <i data-lucide="alert-circle"></i>
            <span><?= e($errors['general']) ?></span>
        </div>
        <?php endif; ?>

        <div class="checkout-layout">
            <form method="post" action="<?= url('checkout.php') ?>" data-loading class="checkout-form">
                <?= csrf_field() ?>

                <div class="checkout-form-card">
                    <h3><span class="step-num">1</span> Contact Information</h3>
                    <div class="form-grid">
                        <div class="form-field <?= isset($errors['full_name']) ? 'has-error' : '' ?>">
                            <label>Full Name <span class="req">*</span></label>
                            <input type="text" name="full_name" value="<?= e(old('full_name', $defaults['full_name'])) ?>" placeholder="e.g. Karim Ahmed" required>
                            <?php if (isset($errors['full_name'])): ?><span class="form-error"><?= e($errors['full_name']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-field <?= isset($errors['phone']) ? 'has-error' : '' ?>">
                            <label>Phone <span class="req">*</span></label>
                            <input type="tel" name="phone" value="<?= e(old('phone', $defaults['phone'])) ?>" placeholder="017XXXXXXXX" required>
                            <?php if (isset($errors['phone'])): ?><span class="form-error"><?= e($errors['phone']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-field full <?= isset($errors['email']) ? 'has-error' : '' ?>">
                            <label>Email <span class="req">*</span></label>
                            <input type="email" name="email" value="<?= e(old('email', $defaults['email'])) ?>" placeholder="you@example.com" required>
                            <?php if (isset($errors['email'])): ?><span class="form-error"><?= e($errors['email']) ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="checkout-form-card">
                    <h3><span class="step-num">2</span> Delivery Address</h3>
                    <div class="form-grid">
                        <div class="form-field <?= isset($errors['division']) ? 'has-error' : '' ?>">
                            <label>Division <span class="req">*</span></label>
                            <select name="division" id="divisionSelect" required>
                                <option value="">Select division</option>
                                <?php foreach ($divisions as $d): ?>
                                <option value="<?= e($d) ?>" <?= old('division') === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['division'])): ?><span class="form-error"><?= e($errors['division']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-field <?= isset($errors['district']) ? 'has-error' : '' ?>">
                            <label>District <span class="req">*</span></label>
                            <select name="district" id="districtSelect" required>
                                <option value="">Select district</option>
                                <?php foreach ($zones as $z): ?>
                                <option value="<?= e($z['district']) ?>" data-division="<?= e($z['division'] ?? '') ?>" data-fee="<?= e((string) $z['delivery_fee']) ?>" data-free="<?= e((string) $z['free_delivery_threshold']) ?>" <?= old('district') === $z['district'] ? 'selected' : '' ?>><?= e($z['district']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['district'])): ?><span class="form-error"><?= e($errors['district']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-field <?= isset($errors['upazila']) ? 'has-error' : '' ?>">
                            <label>Upazila / Thana <span class="req">*</span></label>
                            <input type="text" name="upazila" value="<?= e(old('upazila')) ?>" placeholder="e.g. Shahbag" required>
                            <?php if (isset($errors['upazila'])): ?><span class="form-error"><?= e($errors['upazila']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-field full <?= isset($errors['address']) ? 'has-error' : '' ?>">
                            <label>Full Address <span class="req">*</span></label>
                            <textarea name="address" rows="3" placeholder="House, road, area, landmark..." required><?= e(old('address')) ?></textarea>
                            <?php if (isset($errors['address'])): ?><span class="form-error"><?= e($errors['address']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-field full">
                            <label>Delivery Note (optional)</label>
                            <textarea name="delivery_note" rows="2" placeholder="Anything we should know?"><?= e(old('delivery_note')) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="checkout-form-card">
                    <h3><span class="step-num">3</span> Payment Method</h3>
                    <?php if (PAYMENT_MODE === 'demo'): ?>
                    <div class="demo-banner">
                        <i data-lucide="flask-conical"></i>
                        <span><strong>Demo Payment Mode</strong> — no real money will be charged. Payments are simulated for development.</span>
                    </div>
                    <?php endif; ?>
                    <div class="pay-methods">
                        <?php foreach ($paymentMethods as $key => $pm): ?>
                        <label class="pay-method">
                            <input type="radio" name="payment_method" value="<?= e($key) ?>" <?= old('payment_method', 'cod') === $key ? 'checked' : '' ?>>
                            <span class="pay-icon <?= e($pm['icon']) ?>"><?= e($key === 'cod' ? 'COD' : $pm['label']) ?></span>
                            <span class="pay-info">
                                <b><?= e($pm['label']) ?></b>
                                <span><?= e($pm['desc']) ?></span>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($errors['payment_method'])): ?><span class="form-error" style="margin-top:8px;display:block"><?= e($errors['payment_method']) ?></span><?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" style="max-width:420px">
                    Place Order <i data-lucide="arrow-right"></i>
                </button>
            </form>

            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="order-summary-list">
                    <?php foreach ($checkout['items'] as $item): ?>
                    <div class="os-item">
                        <img src="<?= e($item['image'] ?: image_url('lychee_live')) ?>" alt="<?= e($item['product_name']) ?>">
                        <span class="os-name"><?= e($item['product_name']) ?> <span class="os-qty">×<?= (int) $item['qty'] ?></span></span>
                        <span class="os-price"><?= money($item['line_total']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="summary-row"><span>Subtotal</span><span><?= money($checkout['subtotal']) ?></span></div>
                <div class="summary-row"><span>Delivery</span><span id="deliveryFeeDisplay">Calculated at next step</span></div>
                <div class="summary-row total"><span>Total</span><span id="totalDisplay"><?= money($checkout['subtotal']) ?></span></div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
  var divisionSel = document.getElementById('divisionSelect');
  var districtSel = document.getElementById('districtSelect');

  function filterDistricts() {
    var div = divisionSel.value;
    var opts = districtSel.querySelectorAll('option');
    var firstVisible = null;
    opts.forEach(function (o) {
      if (!o.value) return;
      var show = !div || o.getAttribute('data-division') === div || o.getAttribute('data-division') === '';
      o.style.display = show ? '' : 'none';
      if (show && !firstVisible) firstVisible = o;
    });
    if (districtSel.value && districtSel.selectedOptions[0] && districtSel.selectedOptions[0].style.display !== 'none') return;
    districtSel.value = '';
  }

  if (divisionSel && districtSel) {
    divisionSel.addEventListener('change', filterDistricts);
    filterDistricts();
  }
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>