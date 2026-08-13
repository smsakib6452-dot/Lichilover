<?php
declare(strict_types=1);

/**
 * Shared SEO metadata builder.
 */
if (!function_exists('seo_title')) {
    function seo_title(string $title): string
    {
        return trim($title) !== '' ? $title . ' — ' . APP_NAME : APP_NAME;
    }
}

// Page metadata (overridden per page before including header.php)
$page_title    = $page_title ?? 'Fresh Lichi Delivered in Bangladesh';
$page_meta     = $page_meta ?? 'Order fresh, naturally sweet lichi online in Bangladesh. Premium Rajshahi lichi, family packs and gift boxes delivered to your door.';
$page_canonical = $page_canonical ?? url(basename($_SERVER['SCRIPT_NAME']));
$page_og_image = $page_og_image ?? image_url('hero');
$product_schema = $product_schema ?? null;
$page_noindex  = $page_noindex ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(seo_title($page_title)) ?></title>
<meta name="description" content="<?= e($page_meta) ?>">
<meta name="robots" content="<?= $page_noindex ? 'noindex, nofollow' : 'index, follow' ?>">
<link rel="canonical" href="<?= e($page_canonical) ?>">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(APP_NAME) ?>">
<meta property="og:title" content="<?= e(seo_title($page_title)) ?>">
<meta property="og:description" content="<?= e($page_meta) ?>">
<meta property="og:url" content="<?= e($page_canonical) ?>">
<meta property="og:image" content="<?= e($page_og_image) ?>">
<meta property="og:locale" content="en_BD">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" type="image/svg+xml" href="<?= asset('images/logo.svg') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<script src="https://unpkg.com/lucide@latest"></script>
<script>
window.LL_AJAX_URL = <?= json_encode(url('ajax.php')) ?>;
window.LL_CSRF = <?= json_encode(generate_csrf_token()) ?>;
window.LL_BASE_URL = <?= json_encode(BASE_URL) ?>;
</script>

<?php if ($product_schema): ?>
<script type="application/ld+json"><?= json_encode($product_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endif; ?>
</head>
<body class="page-<?= e(basename($_SERVER['SCRIPT_NAME'], '.php')) ?>">
