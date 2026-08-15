<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$categories = fetch_all('SELECT * FROM categories ORDER BY name ASC');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $name             = trim((string) input('name'));
    $categoryId       = (int) input('category_id');
    $shortDesc        = trim((string) input('short_description'));
    $description      = trim((string) input('description'));
    $image            = trim((string) input('image'));
    $comparePrice     = input('compare_price') !== '' ? (float) input('compare_price') : null;
    $isFeatured       = isset($_POST['is_featured']) ? 1 : 0;
    $isActive         = isset($_POST['is_active']) ? 1 : 0;

    $variantNames   = $_POST['variant_names'] ?? [];
    $variantWeights = $_POST['variant_weights'] ?? [];
    $variantPrices  = $_POST['variant_prices'] ?? [];
    $variantCompares = $_POST['variant_compare'] ?? [];
    $variantStocks  = $_POST['variant_stocks'] ?? [];

    if ($name === '' || mb_strlen($name) < 3) $errors['name'] = 'Product name is required (min 3 chars).';
    if ($categoryId <= 0) $errors['category_id'] = 'Please select a category.';
    if ($image !== '' && !filter_var($image, FILTER_VALIDATE_URL)) $errors['image'] = 'Please enter a valid image URL.';

    $cleanVariants = [];
    foreach ($variantNames as $i => $vname) {
        $vname = trim((string) $vname);
        $w = (float) ($variantWeights[$i] ?? 0);
        $pr = (float) ($variantPrices[$i] ?? 0);
        $cp = isset($variantCompares[$i]) && $variantCompares[$i] !== '' ? (float) $variantCompares[$i] : null;
        $st = max(0, (int) ($variantStocks[$i] ?? 0));
        if ($vname === '' || $pr <= 0) continue;
        $cleanVariants[] = ['name' => $vname, 'weight' => $w, 'price' => $pr, 'compare' => $cp, 'stock' => $st];
    }
    if (!$cleanVariants) {
        $errors['variants'] = 'Add at least one valid variant (name + price).';
    }

    if (!$errors) {
        $slug = slugify($name);
        $base = slugify($name);
        $i = 1;
        while (fetch_one('SELECT id FROM products WHERE slug = ?', [$slug])) {
            $slug = $base . '-' . (++$i);
        }

        $totalStock = array_sum(array_column($cleanVariants, 'stock'));
        $minPrice = min(array_column($cleanVariants, 'price'));
        $image = $image !== '' ? $image : image_url('lychee_live');

        $db = db();
        $db->beginTransaction();
        try {
            $stmt = query(
                'INSERT INTO products (category_id, name, slug, short_description, description, base_price, compare_price, image, stock_qty, is_featured, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$categoryId, $name, $slug, $shortDesc, $description, $minPrice, $comparePrice, $image, $totalStock, $isFeatured, $isActive]
            );
            $productId = (int) $db->lastInsertId();

            $first = true;
            foreach ($cleanVariants as $v) {
                query(
                    'INSERT INTO product_variants (product_id, name, weight, price, compare_price, stock_qty, is_default, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)',
                    [$productId, $v['name'], $v['weight'], $v['price'], $v['compare'], $v['stock'], $first ? 1 : 0]
                );
                $first = false;
            }
            $db->commit();
            flash('success', 'Product created successfully.');
            redirect('product-edit.php?id=' . $productId);
        } catch (Throwable $e) {
            $db->rollBack();
            log_app('Product create error: ' . $e->getMessage());
            $errors['general'] = 'Could not save the product. Please try again.';
        }
    }

    with_old([
        'name' => $name, 'category_id' => $categoryId, 'short_description' => $shortDesc,
        'description' => $description, 'image' => $image, 'is_featured' => $isFeatured, 'is_active' => $isActive,
    ]);
}

$adminTitle = 'Add Product';
require_once __DIR__ . '/includes/header.php';
?>

<form method="post" action="<?= url('admin/product-add.php') ?>" data-loading>
    <?= csrf_field() ?>
    <?php if (isset($errors['general'])): ?>
    <div class="alert alert-error"><i data-lucide="alert-circle"></i><span><?= e($errors['general']) ?></span></div>
    <?php endif; ?>

    <div class="admin-card">
        <h3>Product Information</h3>
        <div class="form-grid">
            <div class="form-field full <?= isset($errors['name']) ? 'has-error' : '' ?>">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= e(old('name')) ?>" placeholder="e.g. Premium Rajshahi Lichi" required>
                <?php if (isset($errors['name'])): ?><span class="form-error"><?= e($errors['name']) ?></span><?php endif; ?>
            </div>
            <div class="form-field <?= isset($errors['category_id']) ? 'has-error' : '' ?>">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select category</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) old('category_id') === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?><span class="form-error"><?= e($errors['category_id']) ?></span><?php endif; ?>
            </div>
            <div class="form-field">
                <label>Short Description</label>
                <input type="text" name="short_description" value="<?= e(old('short_description')) ?>" maxlength="255" placeholder="Short tagline for cards">
            </div>
            <div class="form-field full">
                <label>Full Description (HTML allowed)</label>
                <textarea name="description" rows="6"><?= e(old('description')) ?></textarea>
            </div>
            <div class="form-field full <?= isset($errors['image']) ? 'has-error' : '' ?>">
                <label>Image URL</label>
                <input type="url" name="image" value="<?= e(old('image')) ?>" placeholder="https://images.pexels.com/photos/16820482/pexels-photo-16820482.jpeg?auto=compress&cs=tinysrgb&w=900">
                <?php if (isset($errors['image'])): ?><span class="form-error"><?= e($errors['image']) ?></span><?php endif; ?>
                <span class="form-hint">Leave empty to use a real lichi photo. Paste any Pexels or Unsplash image URL.</span>
            </div>
            <div class="form-field">
                <label>Compare Price (৳)</label>
                <input type="number" step="0.01" min="0" name="compare_price" value="<?= e(old('compare_price')) ?>" placeholder="Original price for discount badge">
            </div>
            <div class="form-field">
                <label>&nbsp;</label>
                <div style="display:flex;gap:16px;padding-top:8px">
                    <label style="display:flex;gap:6px;align-items:center;font-size:14px"><input type="checkbox" name="is_featured" value="1" <?= old('is_featured') ? 'checked' : '' ?>> Featured</label>
                    <label style="display:flex;gap:6px;align-items:center;font-size:14px"><input type="checkbox" name="is_active" value="1" <?= old('is_active', 1) ? 'checked' : '' ?>> Active</label>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-head">
            <h3>Variants (sizes &amp; pricing)</h3>
            <button type="button" class="btn btn-ghost btn-sm" id="addVariantBtn"><i data-lucide="plus"></i> Add Variant</button>
        </div>
        <?php if (isset($errors['variants'])): ?>
        <div class="alert alert-error"><i data-lucide="alert-circle"></i><span><?= e($errors['variants']) ?></span></div>
        <?php endif; ?>
        <div id="variantRows">
            <div class="variant-row" style="border:1px solid var(--line);border-radius:10px;padding:12px;display:grid;gap:10px;grid-template-columns:1fr 1fr 1fr 1fr 1fr auto;align-items:end;margin-bottom:10px">
                <div><label style="font-size:12px;font-weight:700">Name</label><input type="text" name="variant_names[]" placeholder="1 KG" required></div>
                <div><label style="font-size:12px;font-weight:700">Weight (kg)</label><input type="number" step="0.01" min="0" name="variant_weights[]"></div>
                <div><label style="font-size:12px;font-weight:700">Price (৳)</label><input type="number" step="0.01" min="0" name="variant_prices[]" required></div>
                <div><label style="font-size:12px;font-weight:700">Compare (৳)</label><input type="number" step="0.01" min="0" name="variant_compare[]"></div>
                <div><label style="font-size:12px;font-weight:700">Stock</label><input type="number" min="0" name="variant_stocks[]" value="0"></div>
                <div style="display:flex;gap:6px;align-items:center">
                    <label style="font-size:12px;font-weight:700"><input type="radio" name="variant_default" value="0" checked> Default</label>
                    <button type="button" class="btn btn-danger btn-sm remove-variant">×</button>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary btn-lg">Create Product</button>
        <a href="<?= url('admin/products.php') ?>" class="btn btn-ghost btn-lg">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>