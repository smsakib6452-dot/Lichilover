<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$page_title = 'Privacy Policy';
$page_meta  = 'How Lichi Lover collects, uses and protects your information.';
$page_canonical = url('privacy-policy.php');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="page-hero">
    <h1>Privacy Policy</h1>
    <p>Last updated: <?= date('F Y') ?></p>
</div>

<section class="section">
    <div class="container">
        <div class="prose">
            <h2>1. Information We Collect</h2>
            <p>We collect information you provide when placing an order, creating an account or contacting us: your name, email address, phone number, delivery address and order details. We also collect basic analytics data such as pages visited.</p>

            <h2>2. How We Use Your Information</h2>
            <ul>
                <li>To process and deliver your orders.</li>
                <li>To communicate about order status and updates.</li>
                <li>To respond to your enquiries and support requests.</li>
                <li>To improve our website and services.</li>
            </ul>

            <h2>3. Payment Security</h2>
            <p>Payments are processed through secure payment gateways (bKash, Nagad). We never store your full payment credentials. Cash on Delivery requires no online payment information at all.</p>

            <h2>4. Data Sharing</h2>
            <p>We do not sell your personal information. We only share the information required to fulfil your order (for example, your delivery address with our delivery partner).</p>

            <h2>5. Cookies</h2>
            <p>We use cookies to keep you logged in and remember your shopping cart. You can disable cookies in your browser, though some features may not work correctly.</p>

            <h2>6. Your Rights</h2>
            <p>You may request access to, correction of, or deletion of your personal data at any time by contacting us.</p>

            <h2>7. Contact</h2>
            <p>If you have questions about this policy, please <a href="<?= url('contact.php') ?>">contact us</a>.</p>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>