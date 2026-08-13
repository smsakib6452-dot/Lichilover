<?php
declare(strict_types=1);

/**
 * Admin panel layout — header + sidebar.
 * Pages under /admin must define $adminTitle before including this.
 */

require_admin();

$adminUser = current_admin();
$adminTitle = $adminTitle ?? 'Dashboard';

$adminPages = [
    ['url' => 'index.php', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
    ['url' => 'products.php', 'label' => 'Products', 'icon' => 'package'],
    ['url' => 'product-add.php', 'label' => 'Add Product', 'icon' => 'plus-square'],
    ['url' => 'orders.php', 'label' => 'Orders', 'icon' => 'shopping-cart'],
    ['url' => 'customers.php', 'label' => 'Customers', 'icon' => 'users'],
    ['url' => 'coupons.php', 'label' => 'Coupons', 'icon' => 'ticket-percent'],
    ['url' => 'delivery.php', 'label' => 'Delivery Zones', 'icon' => 'truck'],
    ['url' => 'reviews.php', 'label' => 'Reviews', 'icon' => 'star'],
    ['url' => 'messages.php', 'label' => 'Messages', 'icon' => 'mail'],
    ['url' => 'settings.php', 'label' => 'Settings', 'icon' => 'settings'],
];
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$unreadMessages = (int) fetch_val('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');
$pendingReviews = (int) fetch_val('SELECT COUNT(*) FROM reviews WHERE status = "pending"');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($adminTitle) ?> — Admin | <?= e(APP_NAME) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
<script src="https://unpkg.com/lucide@latest"></script>
<script>
window.LL_BASE_URL = <?= json_encode(BASE_URL) ?>;
window.LL_CSRF = <?= json_encode(generate_csrf_token()) ?>;
window.ADMIN_PATH = <?= json_encode(url('admin/')) ?>;
</script>
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="<?= url('admin/index.php') ?>" class="admin-brand">
            <img src="<?= asset('images/logo.svg') ?>" alt="Logo" width="34" height="34">
            <span><?= e(APP_NAME) ?> <small>Admin</small></span>
        </a>
        <nav class="admin-nav">
            <?php foreach ($adminPages as $p): ?>
            <a href="<?= url('admin/' . $p['url']) ?>" class="<?= $currentPage === $p['url'] ? 'active' : '' ?>">
                <i data-lucide="<?= e($p['icon']) ?>"></i>
                <span><?= e($p['label']) ?></span>
                <?php if ($p['url'] === 'messages.php' && $unreadMessages > 0): ?>
                    <span class="nav-badge"><?= (int) $unreadMessages ?></span>
                <?php endif; ?>
                <?php if ($p['url'] === 'reviews.php' && $pendingReviews > 0): ?>
                    <span class="nav-badge"><?= (int) $pendingReviews ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <a href="<?= url('index.php') ?>" target="_blank" rel="noopener">
                <i data-lucide="external-link"></i>
                <span>View Site</span>
            </a>
        </nav>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-menu-btn" id="adminMenuBtn" aria-label="Toggle menu"><i data-lucide="menu"></i></button>
            <h1 class="admin-page-title"><?= e($adminTitle) ?></h1>
            <div class="admin-user">
                <span class="admin-avatar"><?= e(strtoupper(mb_substr($adminUser['name'], 0, 1))) ?></span>
                <div>
                    <strong><?= e($adminUser['name']) ?></strong>
                    <span><?= e($adminUser['email']) ?></span>
                </div>
                <a href="<?= url('admin/logout.php') ?>" class="btn btn-ghost btn-sm" title="Logout"><i data-lucide="log-out"></i></a>
            </div>
        </header>

        <main class="admin-content">
            <?php if ($flash = get_flash()): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle-2' : ($flash['type'] === 'error' ? 'alert-circle' : 'info') ?>"></i>
                <span><?= e($flash['message']) ?></span>
            </div>
            <?php endif; ?>

            <?php if ($adminUser['must_change_password']): ?>
            <div class="alert alert-warning">
                <i data-lucide="alert-triangle"></i>
                <span>You are using the demo password. Please <a href="<?= url('admin/settings.php') ?>#password">change your password</a> before going live. (DEVELOPMENT ONLY account)</span>
            </div>
            <?php endif; ?>
