<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$page_title = 'About Us';
$page_meta  = 'Learn about Lichi Lover — a fresh fruit e-commerce brand bringing carefully selected lichi to customers with convenient online ordering and doorstep delivery.';
$page_canonical = url('about.php');
$page_og_image = image_url('about_hero');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="page-hero">
    <h1>About Lichi Lover</h1>
    <p>Lichi Lover is a fresh fruit e-commerce brand focused on bringing carefully selected lichi to customers with convenient online ordering and doorstep delivery.</p>
</div>

<section class="section">
    <div class="container">
        <div class="prose">
            <img src="<?= e(image_url('about_hero')) ?>" alt="<?= e(image_alt('about_hero')) ?>">

            <h2>Our Story</h2>
            <p>Lichi season in Bangladesh is short, precious and wonderfully sweet. Lichi Lover was born from a simple idea: everyone should be able to enjoy the very best of the season without hunting through crowded markets or risking bruised fruit.</p>
            <p>We work directly with trusted orchards, harvest at the peak of ripeness, and bring the lichi straight to your door — fresh, naturally sweet and carefully selected.</p>

            <h2>Freshness First</h2>
            <p>Freshness is not a slogan for us — it's our whole reason for existing. Our lichi is picked at the perfect moment and delivered within hours of harvest, so what reaches your table tastes the way nature intended.</p>

            <h2>Quality Selection</h2>
            <p>Every single lichi is checked fruit by fruit. Under-ripe, over-ripe and damaged fruits never make it into your box. Only the best reaches you, every single time.</p>

            <h2>Careful Packaging</h2>
            <p>Lichi is delicate. Our packaging is designed to protect each fruit during transit — cushioning, ventilation and careful stacking keep your order fresh and beautiful when it arrives.</p>

            <h2>Doorstep Delivery</h2>
            <p>From Dhaka to Chattogram to the farthest districts, we deliver across Bangladesh. Order online, track your delivery, and enjoy the taste of the season at home.</p>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <span class="section-eyebrow">Why Choose Us</span>
            <h2>What We Promise</h2>
        </div>
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon"><i data-lucide="sprout"></i></div>
                <h3>Farm Direct</h3>
                <p>Sourced from trusted orchards at peak season.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i data-lucide="star"></i></div>
                <h3>Hand Selected</h3>
                <p>Every fruit quality-checked before packing.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i data-lucide="truck"></i></div>
                <h3>Across Bangladesh</h3>
                <p>Delivery to all 64 districts.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i data-lucide="heart"></i></div>
                <h3>Made with Love</h3>
                <p>We genuinely care about every single order.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container" style="text-align:center">
        <h2 style="font-size:clamp(24px,4vw,34px);color:var(--green-900);margin-bottom:12px">Taste the Season</h2>
        <p style="color:var(--muted);max-width:520px;margin:0 auto 24px">Ready to enjoy the freshest lichi of the season?</p>
        <a href="<?= url('shop.php') ?>" class="btn btn-primary btn-lg">Shop Fresh Lichi <i data-lucide="arrow-right"></i></a>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>