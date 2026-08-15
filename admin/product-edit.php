<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$product = fetch_one('SELECT * FROM products WHERE id = ?', [$id]);
if (!$product) {
    flash('error', 'Product not found.');
    redirect('products.php');
}

$categories = fetch_all('SELECT * FROM categories ORDER BY name ASC');
$variants = fetch_all('SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC', [$id]);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $action = input('action');

    if ($action === 'delete') {
        query('DELETE FROM products WHERE id = ?', [$id]);
        flash('success', 'Product deleted.');
        redirect('products.php');
    }

    if ($action === 'update') {
        $name        = trim((string) input('name'));
        $categoryId  = (int) input('category_id');
        $shortDesc   = trim((string) input('short_description'));
        $description = trim((string) input('description'));
        $image       = trim((string) input('image'));
        $comparePrice = input('compare_price') !== '' ? (float) input('compare_price') : null;
        $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;
        $isActive    = isset($_POST['is_active']) ? 1 : 0;

        $variantNames    = $_POST['variant_names'] ?? [];
        $variantWeights  = $_POST['variant_weights'] ?? [];
        $variantPrices   = $_POST['variant_prices'] ?? [];
        $variantCompares = $_POST['variant_compare'] ?? [];
        $variantStocks   = $_POST['variant_stocks'] ?? [];
        $variantIds      = $_POST['variant_ids'] ?? [];
        $variantDefault  = (int) ($_POST['variant_default'] ?? 0);

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
            $vid = isset($variantIds[$i]) ? (int) $variantIds[$i] : 0;
            if ($vname === '' || $pr <= 0) continue;
            $cleanVariants[] = ['id' => $vid, 'name' => $vname, 'weight' => $w, 'price' => $pr, 'compare' => $cp, 'stock' => $st];
        }
        if (!$cleanVariants) {
            $errors['variants'] = 'Add at least one valid variant (name + price).';
        }

        if (!$errors) {
            $totalStock = array_sum(array_column($cleanVariants, 'stock'));
            $minPrice = min(array_column($cleanVariants, 'price'));
            $image = $image !== '' ? $image : $product['image'];

            $db = db();
            $db->beginTransaction();
            try {
                query(
                    'UPDATE products SET category_id = ?, name = ?, short_description = ?, description = ?, base_price = ?, compare_price = ?, image = ?, stock_qty = ?, is_featured = ?, is_active = ? WHERE id = ?',
                    [$categoryId, $name, $shortDesc, $description, $minPrice, $comparePrice, $image, $totalStock, $isFeatured, $isActive, $id]
                );

                $existingIds = [];
                $first = true;
                foreach ($cleanVariants as $v) {
                    $isDefault = ($v['id'] > 0 && $v['id'] === $variantDefault) || ($variantDefault === 0 && $first);
                    if ($v['id'] > 0) {
                        query(
                            'UPDATE product_variants SET name = ?, weight = ?, price = ?, compare_price = ?, stock_qty = ?, is_default = ? WHERE id = ? AND product_id = ?',
                            [$v['name'], $v['weight'], $v['price'], $v['compare'], $v['stock'], $isDefault ? 1 : 0, $v['id'], $id]
                        );
                        $existingIds[] = $v['id'];
                    } else {
                        query(
                            'INSERT INTO product_variants (product_id, name, weight, price, compare_price, stock_qty, is_default, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)',
                            [$id, $v['name'], $v['weight'], $v['price'], $v['compare'], $v['stock'], $isDefault ? 1 : 0]
                        );
                    }
                    $first = false;
                }
                // Remove variants not in the submitted list
                if ($existingIds) {
                    $place = implode(',', array_fill(0, count($existingIds), '?'));
                    query("DELETE FROM product_variants WHERE product_id = ? AND id NOT IN ($place)", array_merge([$id], $existingIds));
                } else {
                    query('DELETE FROM product_variants WHERE product_id = ?', [$id]);
                }

                $db->commit();
                flash('success', 'Product updated successfully.');
                redirect('product-edit.php?id=' . $id);
            } catch (Throwable $e) {
                $db->rollBack();
                log_app('Product update error: ' . $e->getMessage());
                $errors['general'] = 'Could not save the product. Please try again.';
            }
        }
    }
}

$adminTitle = 'Edit Product';
require_once __DIR__ . '/includes/header.php';
?>

<form method="post" action="<?= url('admin/product-edit.php?id=' . $id) ?>" data-loading>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update">

    <div class="filter-bar">
        <a href="<?= url('admin/products.php') ?>" class="btn btn-ghost btn-sm">&larr; Back to Products</a>
        <div class="spacer"></div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>

    <?php if (isset($errors['general'])): ?>
    <div class="alert alert-error"><i data-lucide="alert-circle"></i><span><?= e($errors['general']) ?></span></div>
    <?php endif; ?>

    <div class="admin-card">
        <h3>Product Information</h3>
        <div class="form-grid">
            <div class="form-field full <?= isset($errors['name']) ? 'has-error' : '' ?>">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= e(old('name', $product['name'])) ?>" required>
                <?php if (isset($errors['name'])): ?><span class="form-error"><?= e($errors['name']) ?></span><?php endif; ?>
            </div>
            <div class="form-field <?= isset($errors['category_id']) ? 'has-error' : '' ?>">
                <label>Category</label>
                <select name="category_id">
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) $product['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?><span class="form-error"><?= e($errors['category_id']) ?></span><?php endif; ?>
            </div>
            <div class="form-field">
                <label>Short Description</label>
                <input type="text" name="short_description" value="<?= e($product['short_description']) ?>" maxlength="255">
            </div>
            <div class="form-field full">
                <label>Full Description (HTML allowed)</label>
                <textarea name="description" rows="6"><?= e($product['description']) ?></textarea>
            </div>
            <div class="form-field full <?= isset($errors['image']) ? 'has-error' : '' ?>">
                <label>Image URL</label>
                <input type="url" name="image" value="<?= e($product['image']) ?>" placeholder="https://images.pexels.com/photos/16820482/pexels-photo-16820482.jpeg?auto=compress&cs=tinysrgb&w=900">
                <?php if (isset($errors['image'])): ?><span class="form-error"><?= e($errors['image']) ?></span><?php endif; ?>
                <div style="margin-top:8px"><img src="<?= e($product['image'] ?: image_url('lychee_live')) ?>" alt="" style="width:90px;height:90px;border-radius:10px;object-fit:cover"></div>
            </div>
            <div class="form-field">
                <label>Compare Price (৳)</label>
                <input type="number" step="0.01" min="0" name="compare_price" value="<?= e($product['compare_price']) ?>">
            </div>
            <div class="form-field">
                <label>&nbsp;</label>
                <div style="display:flex;gap:16px;padding-top:8px">
                    <label style="display:flex;gap:6px;align-items:center;font-size:14px"><input type="checkbox" name="is_featured" value="1" <?= (int) $product['is_featured'] === 1 ? 'checked' : '' ?>> Featured</label>
                    <label style="display:flex;gap:6px;align-items:center;font-size:14px"><input type="checkbox" name="is_active" value="1" <?= (int) $product['is_active'] === 1 ? 'checked' : '' ?>> Active</label>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-head">
            <h3>Variants</h3>
            <button type="button" class="btn btn-ghost btn-sm" id="addVariantBtn"><i data-lucide="plus"></i> Add Variant</button>
        </div>
        <?php if (isset($errors['variants'])): ?>
        <div class="alert alert-error"><i data-lucide="alert-circle"></i><span><?= e($errors['variants']) ?></span></div>
        <?php endif; ?>
        <div id="variantRows">
            <?php foreach ($variants as $v): ?>
            <div class="variant-row" style="border:1px solid var(--line);border-radius:10px;padding:12px;display:grid;gap:10px;grid-template-columns:1fr 1fr 1fr 1fr 1fr auto;align-items:end;margin-bottom:10px">
                <input type="hidden" name="variant_ids[]" value="<?= (int) $v['id'] ?>">
                <div><label style="font-size:12px;font-weight:700">Name</label><input type="text" name="variant_names[]" value="<?= e($v['name']) ?>" required></div>
                <div><label style="font-size:12px;font-weight:700">Weight (kg)</label><input type="number" step="0.01" min="0" name="variant_weights[]" value="<?= e($v['weight']) ?>"></div>
                <div><label style="font-size:12px;font-weight:700">Price (৳)</label><input type="number" step="0.01" min="0" name="variant_prices[]" value="<?= e($v['price']) ?>" required></div>
                <div><label style="font-size:12px;font-weight:700">Compare (৳)</label><input type="number" step="0.01" min="0" name="variant_compare[]" value="<?= e($v['compare_price']) ?>"></div>
                <div><label style="font-size:12px;font-weight:700">Stock</label><input type="number" min="0" name="variant_stocks[]" value="<?= (int) $v['stock_qty'] ?>"></div>
                <div style="display:flex;gap:6px;align-items:center">
                    <label style="font-size:12px;font-weight:700"><input type="radio" name="variant_default" value="<?= (int) $v['id'] ?>" <?= (int) $v['is_default'] === 1 ? 'checked' : '' ?>> Default</label>
                    <button type="button" class="btn btn-danger btn-sm remove-variant">×</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary btn-lg">Save Product</button>
        <a href="<?= url('admin/products.php') ?>" class="btn btn-ghost btn-lg">Cancel</a>
    </div>
</form>

<div class="admin-card" style="margin-top:24px;border-color:#ef9aa4">
    <h3 style="color:var(--lichi-600)">Danger Zone</h3>
    <form method="post" action="<?= url('admin/product-edit.php?id=' . $id) ?>" data-confirm="Delete this product permanently? This cannot be undone.">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="btn btn-danger">Delete Product</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>