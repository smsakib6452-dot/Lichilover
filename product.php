<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);

$product = fetch_one(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.is_active = 1",
    [$id]
);

if (!$product) {
    http_response_code(404);
    $page_title = 'Product not found';
    $page_noindex = true;
    require_once INCLUDES_PATH . '/header.php';
    require_once INCLUDES_PATH . '/navbar.php';
    ?>
    <div class="not-found">
        <h1>404</h1>
        <h2>Product not found</h2>
        <p>The product you are looking for is unavailable.</p>
        <a href="<?= url('shop.php') ?>" class="btn btn-primary">Back to Shop</a>
    </div>
    <?php
    require_once INCLUDES_PATH . '/footer.php';
    exit;
}

$variants = fetch_all('SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY is_default DESC, price ASC', [$product['id']]);
if (!$variants) {
    http_response_code(404);
    $page_title = 'Product not available';
    $page_noindex = true;
    require_once INCLUDES_PATH . '/header.php';
    require_once INCLUDES_PATH . '/navbar.php';
    ?>
    <div class="not-found">
        <h1>Oops</h1>
        <h2>This product has no sizes available</h2>
        <p>Please check back soon — fresh stock is on the way.</p>
        <a href="<?= url('shop.php') ?>" class="btn btn-primary">Back to Shop</a>
    </div>
    <?php
    require_once INCLUDES_PATH . '/footer.php';
    exit;
}

$gallery = json_decode((string) $product['gallery'], true);
if (!is_array($gallery) || $gallery === []) {
    $gallery = [$product['image']];
}

$totalStock = array_sum(array_column($variants, 'stock_qty'));

// Reviews
$reviews = fetch_all(
    'SELECT r.*, u.name AS user_name FROM reviews r
     JOIN users u ON u.id = r.user_id
     WHERE r.product_id = ? AND r.status = "approved"
     ORDER BY r.created_at DESC LIMIT 20',
    [$product['id']]
);

$related = fetch_all(
    'SELECT p.*, MIN(v.price) AS min_price,
            (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1) AS min_variant_price
     FROM products p
     LEFT JOIN product_variants v ON v.product_id = p.id AND v.is_active = 1
     WHERE p.category_id = ? AND p.id <> ? AND p.is_active = 1
     GROUP BY p.id
     ORDER BY p.sold_count DESC LIMIT 4',
    [$product['category_id'], $product['id']]
);

$avgRating = (float) $product['rating_avg'];
$page_title = $product['name'];
$page_meta  = $product['short_description'] ?: truncate(strip_tags((string) $product['description']), 160);
$page_canonical = url('product.php?id=' . $product['id']);

$product_schema = [
    '@context'   => 'https://schema.org',
    '@type'      => 'Product',
    'name'       => $product['name'],
    'image'      => [$product['image']],
    'description'=> $product['short_description'] ?: truncate(strip_tags((string) $product['description']), 200),
    'sku'        => 'LL-P' . $product['id'],
    'brand'      => ['@type' => 'Brand', 'name' => APP_NAME],
    'offers'     => [
        '@type'         => 'Offer',
        'url'           => $page_canonical,
        'priceCurrency' => 'BDT',
        'price'         => (string) min(array_column($variants, 'price')),
        'availability'  => $totalStock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    ],
];
if ($avgRating > 0) {
    $product_schema['aggregateRating'] = [
        '@type'       => 'AggregateRating',
        'ratingValue' => number_format($avgRating, 1),
        'reviewCount' => (int) $product['rating_count'],
    ];
}

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';

$user = current_user();
?>

<div class="crumbs">
    <div class="container">
        <ul>
            <li><a href="<?= url('index.php') ?>">Home</a></li>
            <li><a href="<?= url('shop.php') ?>">Shop</a></li>
            <?php if ($product['category_slug']): ?>
            <li><a href="<?= url('shop.php?category=' . e($product['category_slug'])) ?>"><?= e($product['category_name']) ?></a></li>
            <?php endif; ?>
            <li><?= e($product['name']) ?></li>
        </ul>
    </div>
</div>

<section class="section" style="padding-top:8px">
    <div class="container">
        <div class="pd-layout">
            <!-- Gallery -->
            <div class="pd-gallery">
                <div class="pd-main-img">
                    <img id="pdMainImg" src="<?= e($gallery[0]) ?>" alt="<?= e($product['name']) ?>">
                </div>
                <?php if (count($gallery) > 1): ?>
                <div class="pd-thumbs">
                    <?php foreach ($gallery as $i => $img): ?>
                    <button type="button" class="pd-thumb <?= $i === 0 ? 'active' : '' ?>" data-full="<?= e($img) ?>" alt="<?= e($product['name']) ?> view <?= $i + 1 ?>">
                        <img src="<?= e($img) ?>" alt="<?= e($product['name']) ?> thumbnail <?= $i + 1 ?>" loading="lazy">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="pd-info">
                <h1><?= e($product['name']) ?></h1>
                <div class="pd-meta">
                    <span class="stars" style="color:var(--gold)">
                        <?= str_repeat('★', (int) round($avgRating)) ?><span style="color:var(--line)"><?= str_repeat('★', 5 - (int) round($avgRating)) ?></span>
                    </span>
                    <span><?= (int) $product['rating_count'] ?> review<?= (int) $product['rating_count'] === 1 ? '' : 's' ?></span>
                    <span><?= (int) $product['sold_count'] ?> sold</span>
                </div>

                <div class="pd-price">
                    <span class="price" id="pdPrice"><?= money((float) $variants[0]['price']) ?></span>
                    <?php if ($variants[0]['compare_price'] && (float) $variants[0]['compare_price'] > (float) $variants[0]['price']): ?>
                        <span class="compare" id="pdCompare"><?= money((float) $variants[0]['compare_price']) ?></span>
                        <span class="pd-save" id="pdSave">Save <?= (int) round((1 - (float) $variants[0]['price'] / (float) $variants[0]['compare_price']) * 100) ?>%</span>
                    <?php else: ?>
                        <span class="compare" id="pdCompare" style="display:none"></span>
                        <span class="pd-save" id="pdSave" style="display:none"></span>
                    <?php endif; ?>
                </div>

                <div class="pd-desc">
                    <?= (string) $product['description'] ?>
                </div>

                <form id="addToCartForm" method="post" action="<?= url('ajax.php') ?>" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                    <input type="hidden" name="variant_id" id="selectedVariant" value="<?= (int) $variants[0]['id'] ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="pd-variants">
                        <h4>Choose Size</h4>
                        <div class="variant-list" id="variantList">
                            <?php foreach ($variants as $v): ?>
                            <button type="button"
                                class="variant-pill <?= $v['is_default'] ? 'active' : '' ?> <?= (int) $v['stock_qty'] <= 0 ? 'out' : '' ?>"
                                data-id="<?= (int) $v['id'] ?>"
                                data-price="<?= e(number_format((float) $v['price'], 2, '.', '')) ?>"
                                data-compare="<?= $v['compare_price'] ? e(number_format((float) $v['compare_price'], 2, '.', '')) : '' ?>"
                                data-stock="<?= (int) $v['stock_qty'] ?>">
                                <span class="v-name"><?= e($v['name']) ?></span>
                                <span class="v-price"><?= money((float) $v['price']) ?></span>
                                <?php if ((int) $v['stock_qty'] <= 0): ?>
                                    <span class="v-stock">Out of stock</span>
                                <?php else: ?>
                                    <span class="v-stock"><?= (int) $v['stock_qty'] ?> available</span>
                                <?php endif; ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="pd-qty">
                        <div class="qty-control">
                            <button type="button" class="qty-minus" aria-label="Decrease quantity">&minus;</button>
                            <input type="number" id="pdQty" name="qty" value="1" min="1" max="50" inputmode="numeric" aria-label="Quantity">
                            <button type="button" class="qty-plus" aria-label="Increase quantity">+</button>
                        </div>
                    </div>

                    <div class="pd-stock-note">
                        <?php if ($totalStock > 0): ?>
                            <span class="stock-in" id="pdStockIn"><i data-lucide="check-circle-2"></i> In stock — <?= e((string) $totalStock) ?> units available</span>
                            <span class="stock-out" id="pdStockOut" style="display:none"><i data-lucide="alert-circle"></i> Currently out of stock</span>
                        <?php else: ?>
                            <span class="stock-in" id="pdStockIn" style="display:none"></span>
                            <span class="stock-out" id="pdStockOut"><i data-lucide="alert-circle"></i> Currently out of stock</span>
                        <?php endif; ?>
                    </div>

                    <div class="pd-buy">
                        <button type="submit" id="addToCartBtn" class="btn btn-primary btn-lg" <?= $totalStock > 0 ? '' : 'disabled' ?>>
                            <i data-lucide="shopping-basket"></i> Add to Cart
                        </button>
                        <button type="submit" id="buyNowBtn" class="btn btn-lichi btn-lg" formaction="<?= url('checkout.php') ?>" <?= $totalStock > 0 ? '' : 'disabled' ?>>
                            <i data-lucide="zap"></i> Buy Now
                        </button>
                    </div>
                </form>

                <div class="pd-extra">
                    <div class="pd-extra-item"><i data-lucide="leaf"></i><span>Harvested fresh from trusted orchards.</span></div>
                    <div class="pd-extra-item"><i data-lucide="shield-check"></i><span>Quality checked fruit by fruit before packing.</span></div>
                    <div class="pd-extra-item"><i data-lucide="truck"></i><span>Fast delivery across Bangladesh.</span></div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="pd-tabs" style="margin-top:50px">
            <div class="pd-tabbar">
                <button class="pd-tab active" data-tab="tab-reviews">Reviews (<?= count($reviews) ?>)</button>
                <button class="pd-tab" data-tab="tab-shipping">Shipping &amp; Care</button>
            </div>

            <div class="pd-tabpanel" id="tab-reviews">
                <div style="max-width:760px">
                    <div class="review-list">
                        <?php if (!$reviews): ?>
                            <p class="empty-state">No reviews yet. Be the first to review this product!</p>
                        <?php endif; ?>
                        <?php foreach ($reviews as $r): ?>
                        <div class="review-item">
                            <div class="review-head">
                                <div class="review-avatar"><?= e(strtoupper(mb_substr($r['user_name'], 0, 1))) ?></div>
                                <div>
                                    <div class="name"><?= e($r['user_name']) ?>
                                        <?php if ($r['is_demo']): ?><span class="review-demo-tag">Sample</span><?php endif; ?>
                                    </div>
                                    <div class="meta">
                                        <span class="stars" style="color:var(--gold)"><?= str_repeat('★', (int) $r['rating']) ?></span>
                                        &middot; <?= e(date('M j, Y', strtotime($r['created_at']))) ?>
                                        <?php if ($r['is_demo']): ?><span style="color:var(--muted)">— sample/demo review</span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($r['review']): ?>
                            <p class="review-body"><?= e($r['review']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($user): ?>
                    <div class="review-form">
                        <h3 style="margin-bottom:10px">Write a Review</h3>
                        <form method="post" action="<?= url('product.php?id=' . (int) $product['id']) ?>" data-loading>
                            <?= csrf_field() ?>
                            <input type="hidden" name="submit_review" value="1">
                            <div class="form-field" style="margin-bottom:12px">
                                <label>Your Rating</label>
                                <div class="rating-input" id="ratingInput">
                                    <input type="hidden" name="rating" value="5">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button" data-value="<?= $i ?>" class="<?= $i <= 5 ? 'active' : '' ?>" aria-label="<?= $i ?> stars">★</button>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="form-field">
                                <label>Your Review</label>
                                <textarea name="review" rows="4" placeholder="Share your experience with this lichi..." maxlength="1000"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-top:12px">Submit Review</button>
                            <p style="font-size:12px;color:var(--muted);margin-top:8px">Reviews are moderated and will appear after approval.</p>
                        </form>
                    </div>
                    <?php else: ?>
                    <p style="margin-top:20px;color:var(--muted)">
                        <a href="<?= url('login.php') ?>">Login</a> to write a review.
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pd-tabpanel" id="tab-shipping" hidden>
                <div class="prose" style="margin:0">
                    <h2>Shipping</h2>
                    <p>We deliver fresh lichi to all 64 districts of Bangladesh. Delivery charges depend on your location and are shown at checkout.</p>
                    <h2>Storage &amp; Care</h2>
                    <ul>
                        <li>Keep lichi in a cool, dry place.</li>
                        <li>Refrigerate to stay fresh for 2–3 days.</li>
                        <li>Consume within 2 days for the best taste.</li>
                        <li>Wash gently before eating.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Related -->
        <?php if ($related): ?>
        <div class="section">
            <div class="section-head">
                <span class="section-eyebrow">You may also like</span>
                <h2>Related Products</h2>
            </div>
            <div class="product-grid">
                <?php foreach ($related as $p):
                    $rp = (float) ($p['min_variant_price'] ?? $p['base_price']);
                ?>
                <article class="product-card">
                    <div class="product-media">
                        <a href="<?= url('product.php?id=' . (int) $p['id']) ?>">
                            <img src="<?= e($p['image'] ?: image_url('lychee_live')) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                        </a>
                    </div>
                    <div class="product-body">
                        <h3 class="product-name"><a href="<?= url('product.php?id=' . (int) $p['id']) ?>"><?= e($p['name']) ?></a></h3>
                        <div class="product-price">
                            <span class="price"><?= money($rp) ?></span>
                        </div>
                        <div class="product-actions">
                            <a href="<?= url('product.php?id=' . (int) $p['id']) ?>" class="btn btn-primary btn-sm btn-block">View Product</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    require_csrf();
    if (!$user) {
        flash('error', 'You must be logged in to leave a review.');
        redirect('login.php');
    }
    $rating = min(5, max(1, (int) input('rating', 5)));
    $review = trim((string) input('review'));
    if ($rating < 1 || $rating > 5) {
        flash('error', 'Please select a valid rating.');
        redirect('product.php?id=' . $product['id']);
    }
    if (mb_strlen($review) < 3) {
        flash('error', 'Please write a short review (at least 3 characters).');
        redirect('product.php?id=' . $product['id']);
    }
    query(
        'INSERT INTO reviews (product_id, user_id, rating, review, status, is_demo) VALUES (?, ?, ?, ?, "pending", 0)',
        [$product['id'], $user['id'], $rating, $review]
    );
    flash('success', 'Thank you! Your review has been submitted and will appear after approval.');
    redirect('product.php?id=' . $product['id']);
}
?>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>