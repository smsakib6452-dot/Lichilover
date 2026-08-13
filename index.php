<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

// Newsletter subscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter'])) {
    require_csrf();
    $email = filter_var(input('email'), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        if (is_ajax()) json_response(['success' => false, 'message' => 'Please enter a valid email address.']);
        flash('error', 'Please enter a valid email address.');
        redirect('index.php');
    }
    try {
        query('INSERT INTO newsletter_subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE is_active = 1', [$email]);
        if (is_ajax()) json_response(['success' => true, 'message' => 'Thanks for subscribing! Stay tuned for fresh lichi updates.']);
        flash('success', 'Thanks for subscribing! Stay tuned for fresh lichi updates.');
    } catch (Throwable $e) {
        if (is_ajax()) json_response(['success' => false, 'message' => 'Could not subscribe right now.']);
        flash('error', 'Could not subscribe right now.');
    }
    redirect('index.php');
}

$featured = fetch_all(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
            (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1) AS min_price,
            (SELECT MAX(v.price) FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1) AS max_price
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.is_active = 1 AND p.is_featured = 1
     ORDER BY p.sold_count DESC, p.created_at DESC
     LIMIT 8"
);

$categories = fetch_all('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');

$page_title    = 'Fresh Lichi Delivered in Bangladesh';
$page_meta     = settings('hero_subheadline') ?: 'Order fresh, naturally sweet lichi online in Bangladesh. Premium Rajshahi lichi, family packs and gift boxes delivered to your door.';
$page_canonical = url('index.php');
$page_og_image = image_url('hero');

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$flash = get_flash();
?>

<?php if ($flash): ?>
<div class="flash-message">
    <div class="alert alert-<?= e($flash['type']) ?>">
        <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle-2' : ($flash['type'] === 'error' ? 'alert-circle' : 'info') ?>"></i>
        <span><?= e($flash['message']) ?></span>
    </div>
</div>
<?php endif; ?>

<!-- Hero -->
<section class="hero">
    <div class="hero-bg">
        <img src="<?= e(image_url('hero')) ?>" alt="<?= e(image_alt('hero')) ?>" fetchpriority="high">
    </div>
    <div class="hero-inner">
        <span class="hero-tag">
            <i data-lucide="leaf" style="width:16px;height:16px"></i>
            <?= e(settings('shop_tagline') ?: APP_TAGLINE) ?>
        </span>
        <h1><?= e(settings('hero_headline') ?: 'Fresh Lichi, Straight to Your Door') ?></h1>
        <p><?= e(settings('hero_subheadline') ?: 'Enjoy naturally sweet, juicy and freshly selected lichi delivered across Bangladesh.') ?></p>
        <div class="hero-cta">
            <a href="<?= url('shop.php') ?>" class="btn btn-lichi btn-lg">Shop Fresh Lichi <i data-lucide="arrow-right"></i></a>
            <a href="<?= url('shop.php?sort=newest') ?>" class="btn btn-white btn-lg">Explore Products</a>
        </div>
        <div class="hero-stats">
            <div class="hero-stat"><b>100%</b><span>Freshly harvested</span></div>
            <div class="hero-stat"><b>64</b><span>Districts delivered</span></div>
            <div class="hero-stat"><b>24h</b><span>Fast delivery</span></div>
            <div class="hero-stat"><b>4.8★</b><span>Customer rating</span></div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="section">
    <div class="container">
        <div class="feature-grid">
            <?php
            $features = [
                ['icon' => 'sprout', 'title' => 'Farm Fresh', 'desc' => 'Harvested at peak ripeness'],
                ['icon' => 'shield-check', 'title' => 'Quality Check', 'desc' => 'Selected fruit by fruit'],
                ['icon' => 'package', 'title' => 'Safe Packaging', 'desc' => 'Carefully cushioned boxes'],
                ['icon' => 'truck', 'title' => 'Fast Delivery', 'desc' => 'Doorstep across Bangladesh'],
            ];
            foreach ($features as $f): ?>
            <div class="feature-card">
                <div class="feature-icon"><i data-lucide="<?= e($f['icon']) ?>"></i></div>
                <h3><?= e($f['title']) ?></h3>
                <p><?= e($f['desc']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Categories -->
<?php if ($categories): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <span class="section-eyebrow">Browse</span>
            <h2>Shop by Category</h2>
            <p>From fresh fruit to beautiful gift boxes, find exactly what you need.</p>
        </div>
        <div class="feature-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= url('shop.php?category=' . e($cat['slug'])) ?>" class="feature-card">
                <div class="feature-icon"><i data-lucide="basket"></i></div>
                <h3><?= e($cat['name']) ?></h3>
                <p><?= e($cat['description'] ? truncate($cat['description'], 60) : '') ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured products -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="section-eyebrow">Best Sellers</span>
            <h2>Featured Lichi</h2>
            <p>Hand-picked favourites our customers keep coming back for.</p>
        </div>
        <div class="product-grid">
            <?php foreach ($featured as $p):
                $minPrice = (float) ($p['min_price'] ?? $p['base_price']);
                $sale = null;
                if ($p['compare_price'] && (float) $p['compare_price'] > $minPrice) {
                    $sale = round((1 - $minPrice / (float) $p['compare_price']) * 100);
                }
            ?>
            <article class="product-card">
                <div class="product-media">
                    <a href="<?= url('product.php?id=' . (int) $p['id']) ?>">
                        <img src="<?= e($p['image'] ?: image_url('lychee_live')) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                    </a>
                    <?php if ($sale): ?><span class="product-badge">-<?= (int) $sale ?>%</span><?php endif; ?>
                    <?php if (!(float) $p['stock_qty'] > 0): ?><span class="product-badge badge-sale">Out of Stock</span><?php endif; ?>
                </div>
                <div class="product-body">
                    <span class="product-cat"><?= e($p['category_name'] ?? 'Lichi') ?></span>
                    <h3 class="product-name"><a href="<?= url('product.php?id=' . (int) $p['id']) ?>"><?= e($p['name']) ?></a></h3>
                    <div class="product-stars">
                        <span class="stars"><?= str_repeat('★', (int) round((float) $p['rating_avg'])) ?><span style="color:var(--line)"><?= str_repeat('★', 5 - (int) round((float) $p['rating_avg'])) ?></span></span>
                        <span>(<?= (int) $p['rating_count'] ?> reviews)</span>
                    </div>
                    <div class="product-price">
                        <span class="price"><?= money($minPrice) ?></span>
                        <?php if ($sale): ?><span class="compare"><?= money((float) $p['compare_price']) ?></span><?php endif; ?>
                    </div>
                    <div class="product-actions">
                        <a href="<?= url('product.php?id=' . (int) $p['id']) ?>" class="btn btn-ghost btn-sm">View</a>
                        <button class="btn btn-primary btn-sm" data-cart-update="<?= (int) $p['id'] ?>" data-cart-action="add-product" data-product-id="<?= (int) $p['id'] ?>">Add to Cart</button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Farm section -->
<section class="section section-alt">
    <div class="container" style="display:grid;gap:28px;align-items:center">
        <div style="border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-md)">
            <img src="<?= e(image_url('farm')) ?>" alt="<?= e(image_alt('farm')) ?>" loading="lazy">
        </div>
        <div>
            <span class="section-eyebrow">From Our Orchard</span>
            <h2 style="font-size:clamp(26px,4vw,38px);color:var(--green-900);letter-spacing:-.8px">The Taste of Freshness</h2>
            <p style="margin-top:14px;color:var(--ink-soft);max-width:560px">Every lichi is harvested at the peak of the season from trusted orchards, checked fruit by fruit, and delivered the same freshness to your door. No shortcuts — just nature's sweetest gift.</p>
            <div class="hero-cta" style="margin-top:24px">
                <a href="<?= url('about.php') ?>" class="btn btn-primary">Our Story <i data-lucide="arrow-right"></i></a>
                <a href="<?= url('faq.php') ?>" class="btn btn-outline">Read the FAQ</a>
            </div>
        </div>
    </div>
</section>

<!-- Promo banner -->
<section class="section">
    <div class="container">
        <div style="position:relative;border-radius:20px;overflow:hidden;background:var(--green-900);color:#fff;min-height:260px;display:flex;align-items:center">
            <img src="<?= e(image_url('annual_banner')) ?>" alt="<?= e(image_alt('annual_banner')) ?>" loading="lazy" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.35">
            <div style="position:relative;padding:36px 28px;max-width:560px">
                <span class="section-eyebrow" style="color:var(--lichi-400)">Lichi Season</span>
                <h2 style="font-size:clamp(24px,4vw,36px);letter-spacing:-.6px">Limited Season — Order Now</h2>
                <p style="margin-top:10px;color:rgba(255,255,255,.85)">Lichi season is short and sweet. Reserve your fresh batch today before the harvest runs out.</p>
                <a href="<?= url('shop.php') ?>" class="btn btn-lichi" style="margin-top:20px">Shop the Season <i data-lucide="arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
