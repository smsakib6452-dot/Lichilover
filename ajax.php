<?php
declare(strict_types=1);

/**
 * AJAX endpoint for cart and newsletter operations.
 */

require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
}

require_csrf();

$action = input('action');

switch ($action) {
    case 'add':
    case 'add-product':
        $variantId = (int) input('variant_id');
        $productId = (int) input('product_id');
        $qty = max(1, (int) input('qty', 1));

        if ($action === 'add-product' && $variantId <= 0) {
            // Resolve the default (cheapest) active variant for a product.
            $row = fetch_one(
                'SELECT id FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY is_default DESC, price ASC LIMIT 1',
                [$productId]
            );
            if (!$row) {
                json_response(['success' => false, 'message' => 'This product is not available.']);
            }
            $variantId = (int) $row['id'];
        }

        if ($variantId <= 0) {
            json_response(['success' => false, 'message' => 'Invalid product.']);
        }

        $variant = fetch_one(
            'SELECT v.* FROM product_variants v JOIN products p ON p.id = v.product_id
             WHERE v.id = ? AND v.is_active = 1 AND p.is_active = 1',
            [$variantId]
        );
        if (!$variant) {
            json_response(['success' => false, 'message' => 'Product not found.']);
        }

        if ((int) $variant['stock_qty'] > 0 && $qty > (int) $variant['stock_qty']) {
            json_response(['success' => false, 'message' => 'Only ' . (int) $variant['stock_qty'] . ' in stock.']);
        }

        cart_add($variantId, $qty);
        json_response([
            'success' => true,
            'message' => 'Added to cart!',
            'count'   => cart_count(),
            'variant_id' => $variantId,
        ]);
        break;

    case 'update':
        $variantId = (int) input('variant_id');
        $qty = (int) input('qty', 0);
        cart_update($variantId, $qty);
        json_response(['success' => true, 'message' => 'Cart updated.', 'count' => cart_count()]);
        break;

    case 'remove':
        $variantId = (int) input('variant_id');
        cart_remove($variantId);
        json_response(['success' => true, 'message' => 'Removed from cart.', 'count' => cart_count()]);
        break;

    case 'newsletter':
        $email = filter_var(input('email'), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            json_response(['success' => false, 'message' => 'Please enter a valid email address.']);
        }
        try {
            query('INSERT INTO newsletter_subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE is_active = 1', [$email]);
            json_response(['success' => true, 'message' => 'Thanks for subscribing! Fresh lichi updates are on the way.']);
        } catch (Throwable $e) {
            json_response(['success' => false, 'message' => 'Could not subscribe right now.']);
        }
        break;

    default:
        json_response(['success' => false, 'message' => 'Unknown action.'], 400);
}
