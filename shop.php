<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$q         = trim((string) ($_GET['q'] ?? ''));
$catSlug   = trim((string) ($_GET['category'] ?? ''));
$minPrice  = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float) $_GET['min_price'] : null;
$maxPrice  = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float) $_GET['max_price'] : null;
$maxWeight = isset($_GET['max_weight']) && $_GET['max_weight'] !== '' ? (float) $_GET['max_weight'] : null;
$inStock   = isset($_GET['in_stock']) && (int) $_GET['in_stock'] === 1;
$sort      = $_GET['sort'] ?? 'popular';
$perPage   = 12;

$allowedSorts = [
    'popular'      => 'p.sold_count DESC, p.rating_avg DESC',
    'newest'       => 'p.created_at DESC',
    'price_low'    => 'min_variant_price ASC',
    'price_high'   => 'min_variant_price DESC',
    'rating'       => 'p.rating_avg DESC',
];
$orderBy = $allowedSorts[$sort] ?? $allowedSorts['popular'];

$where  = ['p.is_active = 1'];
$params = [];

if ($catSlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $catSlug;
}
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.short_description LIKE ? OR p.description LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($minPrice !== null) {
    $where[] = 'min_variant_price >= ?';
    $params[] = $minPrice;
}
if ($maxPrice !== null) {
    $where[] = 'min_variant_price <= ?';
    $params[] = $maxPrice;
}
if ($maxWeight !== null) {
    $where[] = 'min_variant_weight <= ?';
    $params[] = $maxWeight;
}
if ($inStock) {
    $where[] = 'p.stock_qty > 0';
}

$whereSql = implode(' AND ', $where);

$baseSelect = "
    SELECT p.*, c.name AS category_name, c.slug AS category_slug,
           COALESCE((SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1), p.base_price) AS min_variant_price,
           COALESCE((SELECT MIN(v.weight) FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1), 0) AS min_variant_weight
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE $whereSql
";

$total   = (int) fetch_val("SELECT COUNT(*) FROM ($baseSelect) t", $params);
$pager   = paginate($total, $perPage);
$sql     = $baseSelect . " ORDER BY $orderBy LIMIT {$pager['perPage']} OFFSET {$pager['offset']}";
$product = fetch_all($sql, $params);

$categories = fetch_all('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS product_count FROM categories c WHERE c.is_active = 1 ORDER BY c.sort_order ASC');

$weightOptions = [
    '0.5' => 'Up to 500 GM',
    '1'   => 'Up to 1 KG',
    '2'   => 'Up to 2 KG',
    '3'   => 'Up to 3 KG',
    '5'   => 'Up to 5 KG',
    '10'  => 'Up to 10 KG',
];

$page_title = $q !== '' ? "Search results for \"" . $q . "\"" : 'Shop Fresh Lichi Online';
$page_entry = $q !== '' ? 'Search results for "' . e($q) . '"' : (($catSlug && isset($categories)) ? 'Category' : 'Shop');
$page_meta  = 'Browse our fresh lichi collection — premium Rajshahi lichi, family packs, gift boxes and combos delivered across Bangladesh.';
$page_canonical = url('shop.php');
$activeCat = $catSlug;

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

<div class="page-hero">
    <h1>Shop Fresh Lichi</h1>
    <p>Hand-picked, naturally sweet lichi delivered fresh across Bangladesh.</p>
</div>

<section class="section">
    <div class="container">
        <div class="shop-layout">
            <!-- Sidebar -->
            <aside class="shop-sidebar" id="shopSidebar">
                <form method="get" action="<?= url('shop.php') ?>" class="shop-filter-form">
                    <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
                    <?php if ($sort !== 'popular'): ?><input type="hidden" name="sort" value="<?= e($sort) ?>"><?php endif; ?>

                    <div class="filter-group">
                        <h4>Categories</h4>
                        <ul>
                            <li>
                                <label>
                                    <input type="radio" name="category" value="" <?= $catSlug === '' ? 'checked' : '' ?>>
                                    All Products
                                </label>
                            </li>
                            <?php foreach ($categories as $c): ?>
                            <li>
                                <label>
                                    <input type="radio" name="category" value="<?= e($c['slug']) ?>" <?= $catSlug === $c['slug'] ? 'checked' : '' ?>>
                                    <?= e($c['name']) ?>
                                    <span class="filter-count"><?= (int) $c['product_count'] ?></span>
                                </label>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="filter-group">
                        <h4>Price (৳)</h4>
                        <div class="price-range-inputs">
                            <input type="number" name="min_price" min="0" placeholder="Min" value="<?= $minPrice !== null ? e((string) $minPrice) : '' ?>">
                            <span>–</span>
                            <input type="number" name="max_price" min="0" placeholder="Max" value="<?= $maxPrice !== null ? e((string) $maxPrice) : '' ?>">
                        </div>
                    </div>

                    <div class="filter-group">
                        <h4>Weight</h4>
                        <ul>
                            <?php foreach ($weightOptions as $w => $label): ?>
                            <li>
                                <label>
                                    <input type="radio" name="max_weight" value="<?= (string) $w ?>" <?= $maxWeight !== null && abs($maxWeight - $w) < 0.001 ? 'checked' : '' ?>>
                                    <?= e($label) ?>
                                </label>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="filter-group">
                        <h4>Availability</h4>
                        <ul>
                            <li>
                                <label>
                                    <input type="checkbox" name="in_stock" value="1" <?= $inStock ? 'checked' : '' ?>>
                                    In stock only
                                </label>
                            </li>
                        </ul>
                    </div>

                    <div style="display:flex;gap:8px;margin-top:16px">
                        <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                        <a href="<?= url('shop.php') ?>" class="btn btn-ghost btn-block">Reset</a>
                    </div>
                </form>
            </aside>

            <!-- Products -->
            <div>
                <div class="shop-toolbar">
                    <span class="result-count"><?= e($total) ?> product<?= $total === 1 ? '' : 's' ?></span>
                    <button class="btn btn-ghost btn-sm shop-filter-toggle" id="shopFiltersToggle" type="button">
                        <i data-lucide="sliders-horizontal"></i> Filters
                    </button>
                    <form method="get" action="<?= url('shop.php') ?>" style="margin-left:auto">
                        <?php
                        $keep = ['q', 'category', 'min_price', 'max_price', 'max_weight', 'in_stock'];
                        foreach ($keep as $k) {
                            if ($k === 'in_stock') {
                                if ($inStock) echo '<input type="hidden" name="in_stock" value="1">';
                                continue;
                            }
                            $v = $_GET[$k] ?? '';
                            if ($v !== '' && $k !== 'q') echo '<input type="hidden" name="' . e($k) . '" value="' . e((string) $v) . '">';
                            if ($k === 'q' && $q !== '') echo '<input type="hidden" name="q" value="' . e($q) . '">';
                        }
                        ?>
                        <select name="sort" onchange="this.form.submit()" aria-label="Sort products">
                            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low → High</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High → Low</option>
                            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Top Rated</option>
                        </select>
                    </form>
                </div>

                <?php if ($total === 0): ?>
                <div class="empty-state">
                    <i data-lucide="search-x" style="width:48px;height:48px;color:var(--green-300);margin-bottom:12px"></i>
                    <h3 style="margin-bottom:6px">No products found</h3>
                    <p>Try adjusting your filters or search terms.</p>
                </div>
                <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($product as $p):
                        $price = (float) $p['min_variant_price'];
                        $sale = null;
                        if ($p['compare_price'] && (float) $p['compare_price'] > $price) {
                            $sale = round((1 - $price / (float) $p['compare_price']) * 100);
                        }
                    ?>
                    <article class="product-card">
                        <div class="product-media">
                            <a href="<?= url('product.php?id=' . (int) $p['id']) ?>">
                                <img src="<?= e($p['image'] ?: image_url('lychee_live')) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                            </a>
                            <?php if ($sale): ?><span class="product-badge">-<?= (int) $sale ?>%</span><?php endif; ?>
                            <?php if (!((int) $p['stock_qty'] > 0)): ?><span class="product-badge badge-sale">Out of Stock</span><?php endif; ?>
                        </div>
                        <div class="product-body">
                            <span class="product-cat"><?= e($p['category_name'] ?? 'Lichi') ?></span>
                            <h3 class="product-name"><a href="<?= url('product.php?id=' . (int) $p['id']) ?>"><?= e($p['name']) ?></a></h3>
                            <div class="product-stars">
                                <span class="stars"><?= str_repeat('★', (int) round((float) $p['rating_avg'])) ?><span style="color:var(--line)"><?= str_repeat('★', 5 - (int) round((float) $p['rating_avg'])) ?></span></span>
                                <span>(<?= (int) $p['rating_count'] ?>)</span>
                            </div>
                            <div class="product-price">
                                <span class="price"><?= money($price) ?></span>
                                <?php if ($sale): ?><span class="compare"><?= money((float) $p['compare_price']) ?></span><?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a href="<?= url('product.php?id=' . (int) $p['id']) ?>" class="btn btn-ghost btn-sm">View</a>
                                <button class="btn btn-primary btn-sm" data-cart-update="<?= (int) $p['id'] ?>" data-cart-action="add-product" data-product-id="<?= (int) $p['id'] ?>" <?= (int) $p['stock_qty'] > 0 ? '' : 'disabled' ?>>Add to Cart</button>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($pager['totalPages'] > 1): ?>
                <nav class="pagination" style="display:flex;gap:8px;justify-content:center;margin-top:32px">
                    <?php if ($pager['page'] > 1): ?>
                        <a class="btn btn-ghost btn-sm" href="<?= url('shop.php?' . pagination_query(['page' => $pager['page'] - 1])) ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $pager['totalPages']; $i++): ?>
                        <a class="btn <?= $i === $pager['page'] ? 'btn-primary' : 'btn-ghost' ?> btn-sm" href="<?= url('shop.php?' . pagination_query(['page' => $i])) ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($pager['page'] < $pager['totalPages']): ?>
                        <a class="btn btn-ghost btn-sm" href="<?= url('shop.php?' . pagination_query(['page' => $pager['page'] + 1])) ?>">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>