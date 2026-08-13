<?php
declare(strict_types=1);

/**
 * XML Sitemap.
 */

require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$staticPages = ['index.php', 'shop.php', 'about.php', 'contact.php', 'faq.php', 'track-order.php', 'privacy-policy.php', 'terms.php', 'refund-policy.php'];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($staticPages as $page) {
    echo '  <url>' . "\n";
    echo '    <loc>' . e(url($page)) . '</loc>' . "\n";
    echo '    <changefreq>weekly</changefreq>' . "\n";
    echo '  </url>' . "\n";
}

try {
    $products = fetch_all('SELECT id, updated_at FROM products WHERE is_active = 1');
    foreach ($products as $p) {
        echo '  <url>' . "\n";
        echo '    <loc>' . e(url('product.php?id=' . (int) $p['id'])) . '</loc>' . "\n";
        echo '    <lastmod>' . e(date('c', strtotime($p['updated_at']))) . '</lastmod>' . "\n";
        echo '    <changefreq>weekly</changefreq>' . "\n";
        echo '  </url>' . "\n";
    }
} catch (Throwable $e) {
    // Database may not be configured yet — output an empty sitemap.
}

echo '</urlset>' . "\n";