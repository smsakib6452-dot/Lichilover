<?php
declare(strict_types=1);

$announcement = settings('announcement') ?: 'Fresh Lichi Delivered to Your Door 🍒';
$cartCount = cart_count();
$user = current_user();
?>
<!-- Announcement bar -->
<div class="announcement-bar">
    <div class="container announcement-inner">
        <p class="announcement-text"><?= e($announcement) ?></p>
    </div>
</div>

<!-- Navbar -->
<header class="navbar">
    <div class="container navbar-inner">
        <a href="<?= url('index.php') ?>" class="brand" aria-label="<?= e(APP_NAME) ?> home">
            <img src="<?= asset('images/logo.svg') ?>" alt="<?= e(APP_NAME) ?> logo" width="38" height="38">
            <span class="brand-name"><?= e(APP_NAME) ?></span>
        </a>

        <nav class="nav-links" id="navLinks" aria-label="Main navigation">
            <a href="<?= url('index.php') ?>" class="<?= basename($_SERVER['SCRIPT_NAME']) === 'index.php' ? 'active' : '' ?>">Home</a>
            <a href="<?= url('shop.php') ?>" class="<?= in_array(basename($_SERVER['SCRIPT_NAME']), ['shop.php', 'product.php']) ? 'active' : '' ?>">Shop</a>
            <a href="<?= url('about.php') ?>" class="<?= basename($_SERVER['SCRIPT_NAME']) === 'about.php' ? 'active' : '' ?>">About</a>
            <a href="<?= url('contact.php') ?>" class="<?= basename($_SERVER['SCRIPT_NAME']) === 'contact.php' ? 'active' : '' ?>">Contact</a>
            <a href="<?= url('faq.php') ?>" class="<?= basename($_SERVER['SCRIPT_NAME']) === 'faq.php' ? 'active' : '' ?>">FAQ</a>
            <a href="<?= url('track-order.php') ?>" class="<?= basename($_SERVER['SCRIPT_NAME']) === 'track-order.php' ? 'active' : '' ?>">Track Order</a>
        </nav>

        <div class="nav-actions">
            <a href="<?= url('shop.php') ?>" class="icon-btn" aria-label="Search products" data-search-toggle>
                <i data-lucide="search"></i>
            </a>
            <?php if ($user): ?>
                <a href="<?= url('account.php') ?>" class="icon-btn" aria-label="My account">
                    <i data-lucide="user"></i>
                </a>
            <?php else: ?>
                <a href="<?= url('login.php') ?>" class="icon-btn" aria-label="Login">
                    <i data-lucide="user"></i>
                </a>
            <?php endif; ?>
            <a href="<?= url('cart.php') ?>" class="icon-btn cart-btn" aria-label="Shopping cart">
                <i data-lucide="shopping-basket"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-count" id="cartCountBadge"><?= e($cartCount) ?></span>
                <?php endif; ?>
            </a>
            <button class="icon-btn menu-toggle" id="menuToggle" aria-label="Open menu" aria-expanded="false">
                <i data-lucide="menu"></i>
            </button>
        </div>
    </div>
</header>

<!-- Search overlay -->
<div class="search-overlay" id="searchOverlay" hidden>
    <div class="search-overlay-content">
        <form action="<?= url('shop.php') ?>" method="get" class="search-form" role="search">
            <input type="search" name="q" placeholder="Search lichi, boxes, combos..." aria-label="Search products" autocomplete="off">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="search" style="width:18px;height:18px"></i>
                Search
            </button>
        </form>
        <button class="search-close" data-search-toggle aria-label="Close search">
            <i data-lucide="x"></i>
        </button>
    </div>
</div>
