<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$page_title = 'Refund Policy';
$page_meta  = 'Lichi Lover refund and return policy.';
$page_canonical = url('refund-policy.php');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="page-hero">
    <h1>Refund Policy</h1>
    <p>Last updated: <?= date('F Y') ?></p>
</div>

<section class="section">
    <div class="container">
        <div class="prose">
            <h2>1. Perishable Nature</h2>
            <p>Lichi is a fresh, perishable product. For this reason, we cannot accept returns of fresh fruit once delivered, unless the product arrives damaged or spoiled.</p>

            <h2>2. Damaged or Spoiled Delivery</h2>
            <p>If your lichi arrives damaged or spoiled, please contact us within 24 hours of delivery with your order number and a photo of the product. We will review and, where confirmed, provide a replacement or refund.</p>

            <h2>3. Order Cancellation</h2>
            <p>Orders may be cancelled free of charge before dispatch. Once an order has been shipped, cancellation is not possible. To cancel, contact us as soon as possible after placing your order.</p>

            <h2>4. Refund Method</h2>
            <p>Approved refunds for online payments are processed back to the original payment method (bKash or Nagad) within 5–7 business days. Cash on Delivery orders may be refunded by bank transfer or mobile banking.</p>

            <h2>5. Refund Timelines</h2>
            <p>Refund processing begins after we confirm the issue. Depending on your bank or payment provider, funds may take additional time to appear in your account.</p>

            <h2>6. Contact</h2>
            <p>To request a refund or report an issue, please <a href="<?= url('contact.php') ?>">contact us</a>.</p>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>