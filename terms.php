<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$page_title = 'Terms of Service';
$page_meta  = 'Terms and conditions for using the Lichi Lover website and service.';
$page_canonical = url('terms.php');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="page-hero">
    <h1>Terms of Service</h1>
    <p>Last updated: <?= date('F Y') ?></p>
</div>

<section class="section">
    <div class="container">
        <div class="prose">
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing or using the Lichi Lover website, you agree to be bound by these Terms of Service. If you do not agree, please do not use our website.</p>

            <h2>2. Orders &amp; Pricing</h2>
            <p>All prices are listed in Bangladeshi Taka (৳) and may change without notice. We reserve the right to refuse or cancel any order, including orders with incorrect pricing or insufficient stock.</p>

            <h2>3. Delivery</h2>
            <p>Delivery charges and availability vary by location. Estimated delivery times are provided for guidance. We are not responsible for delays caused by factors outside our control, including weather and courier availability.</p>

            <h2>4. Payment</h2>
            <p>We accept bKash, Nagad and Cash on Delivery. By placing an order you authorise us to charge the listed amount through your chosen method.</p>

            <h2>5. Product Quality</h2>
            <p>Lichi is a natural product. We guarantee the quality at the time of dispatch and delivery. Due to the perishable nature of fresh fruit, we cannot guarantee condition beyond the recommended storage time.</p>

            <h2>6. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, Lichi Lover shall not be liable for any indirect, incidental or consequential damages arising from your use of the website or our service.</p>

            <h2>7. Changes to Terms</h2>
            <p>We may update these terms from time to time. Continued use of the website after changes constitutes acceptance of the updated terms.</p>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>