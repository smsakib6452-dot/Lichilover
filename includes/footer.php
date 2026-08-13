<?php
declare(strict_types=1);

$footerLinks = [
    ['label' => 'Home', 'url' => url('index.php')],
    ['label' => 'Shop', 'url' => url('shop.php')],
    ['label' => 'About', 'url' => url('about.php')],
    ['label' => 'Contact', 'url' => url('contact.php')],
    ['label' => 'FAQ', 'url' => url('faq.php')],
    ['label' => 'Track Order', 'url' => url('track-order.php')],
    ['label' => 'Privacy Policy', 'url' => url('privacy-policy.php')],
    ['label' => 'Terms', 'url' => url('terms.php')],
    ['label' => 'Refund Policy', 'url' => url('refund-policy.php')],
];
$shopEmail = settings('shop_email') ?: SHOP_EMAIL;
?>
<footer class="footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="<?= asset('images/logo.svg') ?>" alt="<?= e(APP_NAME) ?> logo" width="40" height="40">
                <span><?= e(APP_NAME) ?></span>
            </div>
            <p><?= e(settings('shop_tagline') ?: APP_TAGLINE) ?></p>
            <div class="payment-badges" aria-label="Accepted payment methods">
                <span class="pay-badge pay-bkash">bKash</span>
                <span class="pay-badge pay-nagad">Nagad</span>
                <span class="pay-badge pay-cod">Cash on Delivery</span>
            </div>
        </div>

        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul>
                <?php foreach (array_slice($footerLinks, 0, 5) as $link): ?>
                    <li><a href="<?= e($link['url']) ?>"><?= e($link['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Policies</h4>
            <ul>
                <?php foreach (array_slice($footerLinks, 5) as $link): ?>
                    <li><a href="<?= e($link['url']) ?>"><?= e($link['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="footer-col footer-contact">
            <h4>Get in Touch</h4>
            <ul>
                <li><i data-lucide="mail"></i><a href="mailto:<?= e($shopEmail) ?>"><?= e($shopEmail) ?></a></li>
                <?php if (settings('shop_phone')): ?>
                    <li><i data-lucide="phone"></i><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', settings('shop_phone'))) ?>"><?= e(settings('shop_phone')) ?></a></li>
                <?php endif; ?>
                <?php if (settings('shop_address')): ?>
                    <li><i data-lucide="map-pin"></i><span><?= e(settings('shop_address')) ?></span></li>
                <?php endif; ?>
            </ul>
            <form class="newsletter-form" action="<?= url('index.php') ?>" method="post" data-newsletter>
                <?= csrf_field() ?>
                <input type="hidden" name="newsletter" value="1">
                <input type="email" name="email" placeholder="Your email for fresh updates" aria-label="Email for newsletter" required>
                <button type="submit" class="btn btn-primary btn-sm">Subscribe</button>
            </form>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>© <?= date('Y') ?> <?= e(APP_NAME) ?>. <?= e(settings('shop_tagline') ?: APP_TAGLINE) ?> All rights reserved.</p>
            <p class="footer-made">Made with <span class="heart">♥</span> in Bangladesh</p>
        </div>
    </div>
</footer>

<?php if (WHATSAPP_NUMBER): ?>
<a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', WHATSAPP_NUMBER)) ?>" class="whatsapp-float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
    <i data-lucide="message-circle"></i>
</a>
<?php endif; ?>

<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
