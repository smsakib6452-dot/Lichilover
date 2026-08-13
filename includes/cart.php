<?php
declare(strict_types=1);

/**
 * Shopping cart stored in PHP sessions.
 * Cart data is keyed by variant ID. Prices are always re-fetched
 * from MySQL — never trusted from the client.
 */

const CART_SESSION_KEY = 'cart';

function cart_session(): array
{
    if (!isset($_SESSION[CART_SESSION_KEY]) || !is_array($_SESSION[CART_SESSION_KEY])) {
        $_SESSION[CART_SESSION_KEY] = [];
    }
    return $_SESSION[CART_SESSION_KEY];
}

function cart_save(array $cart): void
{
    $_SESSION[CART_SESSION_KEY] = $cart;
}

/**
 * Get the raw quantity per variant id: [variant_id => qty].
 */
function cart_items_raw(): array
{
    return cart_session();
}

/**
 * Add a variant to the cart.
 */
function cart_add(int $variantId, int $qty = 1): bool
{
    $variant = fetch_one(
        'SELECT v.*, p.name as product_name, p.id as product_id, p.image as product_image, p.slug as product_slug
         FROM product_variants v
         JOIN products p ON p.id = v.product_id
         WHERE v.id = ? AND v.is_active = 1 AND p.is_active = 1',
        [$variantId]
    );
    if (!$variant) {
        return false;
    }
    $cart = cart_session();
    $qty = max(1, min((int) $qty, 50));
    if ($variant['stock_qty'] > 0) {
        $existing = $cart[$variantId] ?? 0;
        $cart[$variantId] = min((int) $variant['stock_qty'], $existing + $qty);
    } else {
        $cart[$variantId] = ($cart[$variantId] ?? 0) + $qty;
    }
    cart_save($cart);
    return true;
}

/**
 * Update quantity for a variant (0 removes it).
 */
function cart_update(int $variantId, int $qty): void
{
    $cart = cart_session();
    if ($qty <= 0) {
        unset($cart[$variantId]);
    } else {
        $qty = min((int) $qty, 50);
        $cart[$variantId] = $qty;
    }
    cart_save($cart);
}

/**
 * Remove a variant from the cart.
 */
function cart_remove(int $variantId): void
{
    $cart = cart_session();
    unset($cart[$variantId]);
    cart_save($cart);
}

function cart_clear(): void
{
    cart_save([]);
}

/**
 * Total number of units in the cart.
 */
function cart_count(): int
{
    return array_sum(cart_session());
}

/**
 * Build cart line items with live prices from MySQL.
 * Returns [items => [...], subtotal, discount, total, count].
 */
function cart_checkout(): array
{
    $cart   = cart_session();
    $items  = [];
    $subtotal = 0.0;
    $discount = 0.0;
    $count  = 0;

    if ($cart) {
        $ids   = array_keys($cart);
        $place = implode(',', array_fill(0, count($ids), '?'));
        $rows  = fetch_all(
            "SELECT v.id as variant_id, v.name as variant_name, v.weight, v.price, v.compare_price,
                    v.stock_qty, v.product_id, p.name as product_name, p.slug, p.image
             FROM product_variants v
             JOIN products p ON p.id = v.product_id
             WHERE v.id IN ($place) AND v.is_active = 1 AND p.is_active = 1",
            $ids
        );
        $byId = [];
        foreach ($rows as $row) {
            $byId[(int) $row['variant_id']] = $row;
        }
        foreach ($cart as $variantId => $qty) {
            $row = $byId[$variantId] ?? null;
            if (!$row) {
                continue;
            }
            $qty = min((int) $qty, 50);
            $price = (float) $row['price'];
            $lineTotal = $price * $qty;
            $subtotal += $lineTotal;
            $count += $qty;
            $items[] = [
                'variant_id'    => (int) $variantId,
                'product_id'    => (int) $row['product_id'],
                'product_name'  => $row['product_name'],
                'slug'          => $row['slug'],
                'image'         => $row['image'],
                'variant_name'  => $row['variant_name'],
                'weight'        => (float) $row['weight'],
                'unit_price'    => $price,
                'compare_price' => $row['compare_price'] ? (float) $row['compare_price'] : null,
                'stock_qty'     => (int) $row['stock_qty'],
                'qty'           => $qty,
                'line_total'    => $lineTotal,
            ];
        }
    }

    return [
        'items'     => $items,
        'subtotal'  => $subtotal,
        'discount'  => $discount,
        'total'     => $subtotal,
        'count'     => $count,
    ];
}