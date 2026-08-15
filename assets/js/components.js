/* ============================================================
   Lichi Lover — Components
   Injects the shared announcement bar, navbar and footer into
   every page via #site-header / #site-footer placeholders.
   Also bootstraps the data store.
   ============================================================ */

(function () {
  'use strict';

  function asset(path) {
    return (window.LL_ASSET_BASE || '') + 'assets/' + path.replace(/^assets\//, '');
  }

  function activeClass(name) {
    return window.LL_PAGE === name ? 'active' : '';
  }

  function navbarHtml() {
    var S = window.Store;
    var user = S.currentUser();
    var count = S.cartCount();
    var announcement = S.setting('announcement', 'Fresh Lichi Delivered to Your Door 🍒');
    var shopName = S.setting('shop_name', 'Lichi Lover');

    return '' +
      '<div class="announcement-bar">' +
      '  <div class="container announcement-inner">' +
      '    <p class="announcement-text">' + S.esc(announcement) + '</p>' +
      '  </div>' +
      '</div>' +
      '<header class="navbar">' +
      '  <div class="container navbar-inner">' +
      '    <a href="index.html" class="brand" aria-label="' + S.esc(shopName) + ' home">' +
      '      <img src="' + asset('images/logo.svg') + '" alt="' + S.esc(shopName) + ' logo" width="38" height="38">' +
      '      <span class="brand-name">' + S.esc(shopName) + '</span>' +
      '    </a>' +
      '    <nav class="nav-links" id="navLinks" aria-label="Main navigation">' +
      '      <a href="index.html" class="' + activeClass('index') + '">Home</a>' +
      '      <a href="shop.html" class="' + activeClass('shop') + '">Shop</a>' +
      '      <a href="about.html" class="' + activeClass('about') + '">About</a>' +
      '      <a href="contact.html" class="' + activeClass('contact') + '">Contact</a>' +
      '      <a href="faq.html" class="' + activeClass('faq') + '">FAQ</a>' +
      '      <a href="track-order.html" class="' + activeClass('track-order') + '">Track Order</a>' +
      '    </nav>' +
      '    <div class="nav-actions">' +
      '      <a href="shop.html" class="icon-btn" aria-label="Search products" data-search-toggle>' +
      '        <i data-lucide="search"></i>' +
      '      </a>' +
      (user
        ? '      <a href="account.html" class="icon-btn" aria-label="My account"><i data-lucide="user"></i></a>'
        : '      <a href="login.html" class="icon-btn" aria-label="Login"><i data-lucide="user"></i></a>') +
      '      <a href="cart.html" class="icon-btn cart-btn" aria-label="Shopping cart">' +
      '        <i data-lucide="shopping-basket"></i>' +
      (count > 0 ? '        <span class="cart-count" id="cartCountBadge">' + count + '</span>' : '') +
      '      </a>' +
      '      <button class="icon-btn menu-toggle" id="menuToggle" aria-label="Open menu" aria-expanded="false">' +
      '        <i data-lucide="menu"></i>' +
      '      </button>' +
      '    </div>' +
      '  </div>' +
      '</header>' +
      '<div class="search-overlay" id="searchOverlay" hidden>' +
      '  <div class="search-overlay-content">' +
      '    <form action="shop.html" method="get" class="search-form" role="search">' +
      '      <input type="search" name="q" placeholder="Search lichi, boxes, combos..." aria-label="Search products" autocomplete="off">' +
      '      <button type="submit" class="btn btn-primary"><i data-lucide="search" style="width:18px;height:18px"></i> Search</button>' +
      '    </form>' +
      '    <button class="search-close" data-search-toggle aria-label="Close search"><i data-lucide="x"></i></button>' +
      '  </div>' +
      '</div>';
  }

  function footerHtml() {
    var S = window.Store;
    var shopName = S.setting('shop_name', 'Lichi Lover');
    var tagline = S.setting('shop_tagline', 'Freshness You Can Taste.');
    var shopEmail = S.setting('shop_email', 'hello@lichilover.example');
    var shopPhone = S.setting('shop_phone', '');
    var shopAddress = S.setting('shop_address', '');
    var whatsapp = S.setting('whatsapp_number', '');

    var quickLinks = [
      { label: 'Home', url: 'index.html' },
      { label: 'Shop', url: 'shop.html' },
      { label: 'About', url: 'about.html' },
      { label: 'Contact', url: 'contact.html' },
      { label: 'FAQ', url: 'faq.html' }
    ];
    var policyLinks = [
      { label: 'Track Order', url: 'track-order.html' },
      { label: 'Privacy Policy', url: 'privacy-policy.html' },
      { label: 'Terms', url: 'terms.html' },
      { label: 'Refund Policy', url: 'refund-policy.html' }
    ];

    return '' +
      '<footer class="footer">' +
      '  <div class="container footer-grid">' +
      '    <div class="footer-brand">' +
      '      <div class="footer-logo">' +
      '        <img src="' + asset('images/logo.svg') + '" alt="' + S.esc(shopName) + ' logo" width="40" height="40">' +
      '        <span>' + S.esc(shopName) + '</span>' +
      '      </div>' +
      '      <p>' + S.esc(tagline) + '</p>' +
      '      <div class="payment-badges" aria-label="Accepted payment methods">' +
      '        <span class="pay-badge pay-bkash">bKash</span>' +
      '        <span class="pay-badge pay-nagad">Nagad</span>' +
      '        <span class="pay-badge pay-cod">Cash on Delivery</span>' +
      '      </div>' +
      '    </div>' +
      '    <div class="footer-col">' +
      '      <h4>Quick Links</h4><ul>' +
      quickLinks.map(function (l) { return '<li><a href="' + l.url + '">' + S.esc(l.label) + '</a></li>'; }).join('') +
      '      </ul>' +
      '    </div>' +
      '    <div class="footer-col">' +
      '      <h4>Policies</h4><ul>' +
      policyLinks.map(function (l) { return '<li><a href="' + l.url + '">' + S.esc(l.label) + '</a></li>'; }).join('') +
      '      </ul>' +
      '    </div>' +
      '    <div class="footer-col footer-contact">' +
      '      <h4>Get in Touch</h4><ul>' +
      '        <li><i data-lucide="mail"></i><a href="mailto:' + S.esc(shopEmail) + '">' + S.esc(shopEmail) + '</a></li>' +
      (shopPhone ? '        <li><i data-lucide="phone"></i><a href="tel:' + S.esc(shopPhone.replace(/[^0-9+]/g, '')) + '">' + S.esc(shopPhone) + '</a></li>' : '') +
      (shopAddress ? '        <li><i data-lucide="map-pin"></i><span>' + S.esc(shopAddress) + '</span></li>' : '') +
      '      </ul>' +
      '      <form class="newsletter-form" data-newsletter>' +
      '        <input type="email" name="email" placeholder="Your email for fresh updates" aria-label="Email for newsletter" required>' +
      '        <button type="submit" class="btn btn-primary btn-sm">Subscribe</button>' +
      '      </form>' +
      '    </div>' +
      '  </div>' +
      '  <div class="footer-bottom">' +
      '    <div class="container">' +
      '      <p>© ' + new Date().getFullYear() + ' ' + S.esc(shopName) + '. ' + S.esc(tagline) + ' All rights reserved.</p>' +
      '      <p class="footer-made">Made with <span class="heart">♥</span> in Bangladesh</p>' +
      '    </div>' +
      '  </div>' +
      '</footer>' +
      (whatsapp
        ? '<a href="https://wa.me/' + S.esc(whatsapp.replace(/[^0-9]/g, '')) + '" class="whatsapp-float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><i data-lucide="message-circle"></i></a>'
        : '');
  }

  function boot() {
    var headerEl = document.getElementById('site-header');
    var footerEl = document.getElementById('site-footer');
    if (headerEl) headerEl.innerHTML = navbarHtml();
    if (footerEl) footerEl.innerHTML = footerHtml();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
