<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$checkout = cart_checkout();
$flash = get_flash();

$page_title = 'Your Cart';
$page_meta  = 'Review your cart and checkout for fresh lichi delivery.';
$page_noindex = true;
$page_canonical = url('cart.php');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="crumbs">
    <div class="container">
        <ul>
            <li><a href="<?= url('index.php') ?>">Home</a></li>
            <li>Cart</li>
        </ul>
    </div>
</div>

<section class="section" style="padding-top:8px">
    <div class="container">
        <div class="section-head" style="text-align:left;margin-bottom:28px">
            <span class="section-eyebrow">Your Selection</span>
            <h2>Shopping Cart</h2>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle-2' : ($flash['type'] === 'error' ? 'alert-circle' : 'info') ?>"></i>
            <span><?= e($flash['message']) ?></span>
        </div>
        <?php endif; ?>

        <?php if (empty($checkout['items'])): ?>
        <div class="cart-empty">
            <i data-lucide="shopping-basket"></i>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added any fresh lichi yet. Let's fix that!</p>
            <a href="<?= url('shop.php') ?>" class="btn btn-primary btn-lg">Shop Fresh Lichi</a>
        </div>
        <?php else: ?>
        <div class="cart-layout">
            <div>
                <?php foreach ($checkout['items'] as $item): ?>
                <div class="cart-item" id="cartItem_<?= (int) $item['variant_id'] ?>">
                    <div class="cart-item-img">
                        <img src="<?= e($item['image'] ?: image_url('lychee_live')) ?>" alt="<?= e($item['product_name']) ?>">
                    </div>
                    <div class="cart-item-info">
                        <div class="name"><a href="<?= url('product.php?id=' . (int) $item['product_id']) ?>"><?= e($item['product_name']) ?></a></div>
                        <div class="variant"><?= e($item['variant_name']) ?><?= $item['weight'] ? ' · ' . rtrim(rtrim(number_format($item['weight'], 2), '0'), '.') . ' KG' : '' ?></div>
                        <div class="line"><?= money($item['unit_price']) ?> each</div>
                    </div>
                    <div class="cart-item-right">
                        <div class="qty-control">
                            <button type="button" class="qty-minus" data-cart-qty-btn="<?= (int) $item['variant_id'] ?>" data-step="-1" aria-label="Decrease">−</button>
                            <input type="number" id="cartQty_<?= (int) $item['variant_id'] ?>" data-cart-qty="<?= (int) $item['variant_id'] ?>" value="<?= (int) $item['qty'] ?>" min="1" max="50" inputmode="numeric" aria-label="Quantity for <?= e($item['product_name']) ?>">
                            <button type="button" class="qty-plus" data-cart-qty-btn="<?= (int) $item['variant_id'] ?>" data-step="1" aria-label="Increase">+</button>
                        </div>
                        <div class="cart-item-total" id="lineTotal_<?= (int) $item['variant_id'] ?>"><?= money($item['line_total']) ?></div>
                        <button class="cart-remove-btn" data-cart-remove="<?= (int) $item['variant_id'] ?>" aria-label="Remove <?= e($item['product_name']) ?>">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>

                <p style="margin-top:16px;font-size:14px;color:var(--muted)">
                    <i data-lucide="info" style="width:15px;height:15px;vertical-align:-2px"></i>
                    Prices are recalculated from the freshest data at checkout.
                </p>
            </div>

            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row"><span>Subtotal</span><span><?= money($checkout['subtotal']) ?></span></div>
                <div class="summary-row"><span>Delivery</span><span>Calculated at checkout</span></div>
                <div class="summary-row total"><span>Total</span><span><?= money($checkout['total']) ?></span></div>
                <a href="<?= url('checkout.php') ?>" class="btn btn-primary btn-lg btn-block" style="margin-top:18px">Proceed to Checkout</a>
                <a href="<?= url('shop.php') ?>" class="btn btn-ghost btn-block" style="margin-top:10px">Continue Shopping</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Wire qty +/- buttons on cart page
document.querySelectorAll('[data-cart-qty-btn]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var vid = btn.getAttribute('data-cart-qty-btn');
    var input = document.getElementById('cartQty_' + vid);
    if (!input) return;
    var step = parseInt(btn.getAttribute('data-step'), 10);
    var v = parseInt(input.value, 10) || 1;
    input.value = Math.max(1, v + step);
    input.dispatchEvent(new Event('change', { bubbles: true }));
  });
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>