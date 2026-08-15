/* ============================================================
   Lichi Lover — admin-components.js
   Injects the admin sidebar / topbar / footer shell.
   Placeholders: #admin-header (opens shell + sidebar + topbar),
                 #admin-footer (closes content, adds footer).
   Requires: data.js, store.js
   ============================================================ */

(function () {
  'use strict';

  function asset(path) {
    var base = window.LL_ASSET_BASE || '';
    return base + 'assets/' + String(path).replace(/^assets\//, '');
  }

  function navItems() {
    return [
      { url: 'index.html', page: 'dashboard', label: 'Dashboard', icon: 'layout-dashboard' },
      { url: 'products.html', page: 'products', label: 'Products', icon: 'package' },
      { url: 'product-add.html', page: 'product-add', label: 'Add Product', icon: 'plus-square' },
      { url: 'orders.html', page: 'orders', label: 'Orders', icon: 'shopping-cart' },
      { url: 'customers.html', page: 'customers', label: 'Customers', icon: 'users' },
      { url: 'coupons.html', page: 'coupons', label: 'Coupons', icon: 'ticket-percent' },
      { url: 'delivery.html', page: 'delivery', label: 'Delivery Zones', icon: 'truck' },
      { url: 'reviews.html', page: 'reviews', label: 'Reviews', icon: 'star' },
      { url: 'messages.html', page: 'messages', label: 'Messages', icon: 'mail' },
      { url: 'settings.html', page: 'settings', label: 'Settings', icon: 'settings' }
    ];
  }

  function headerHtml() {
    var S = window.Store;
    var admin = S.currentAdmin();
    var page = window.LL_ADMIN_PAGE || '';
    var title = window.LL_ADMIN_TITLE || 'Dashboard';
    var shopName = S.setting('shop_name', 'Lichi Lover');

    var unread = 0, pending = 0;
    try {
      S.messages().forEach(function (m) { if (!m.is_read) unread++; });
    } catch (e) {}
    try {
      S.reviews().forEach(function (r) { if (r.status === 'pending') pending++; });
    } catch (e) {}

    var nav = navItems().map(function (it) {
      var badge = '';
      if (it.page === 'messages' && unread > 0) badge = '<span class="nav-badge">' + unread + '</span>';
      if (it.page === 'reviews' && pending > 0) badge = '<span class="nav-badge">' + pending + '</span>';
      return '<a href="' + it.url + '"' + (page === it.page ? ' class="active"' : '') + '>' +
        '<i data-lucide="' + it.icon + '"></i><span>' + it.label + '</span>' + badge + '</a>';
    }).join('');

    var name = admin ? (admin.name || 'Admin') : 'Admin';
    var email = admin ? (admin.email || '') : '';
    return '' +
      '<div class="admin-shell">' +
      '<aside class="admin-sidebar" id="adminSidebar">' +
      '<a href="index.html" class="admin-brand">' +
      '<img src="' + asset('images/logo.svg') + '" alt="' + S.esc(shopName) + ' logo" width="34" height="34">' +
      '<span>' + S.esc(shopName) + '<small>Admin Panel</small></span></a>' +
      '<nav class="admin-nav">' + nav +
      '<a href="../index.html" target="_blank" rel="noopener"><i data-lucide="external-link"></i><span>View Site</span></a>' +
      '</nav></aside>' +
      '<div class="admin-main">' +
      '<header class="admin-topbar">' +
      '<button class="admin-menu-btn" id="adminMenuBtn" aria-label="Toggle menu"><i data-lucide="menu"></i></button>' +
      '<h1 class="admin-page-title">' + S.esc(title) + '</h1>' +
      '<div class="admin-user">' +
      '<span class="admin-avatar">' + S.esc(name.charAt(0).toUpperCase()) + '</span>' +
      '<div><strong>' + S.esc(name) + '</strong><span>' + S.esc(email) + '</span></div>' +
      '<a href="#" class="btn btn-ghost btn-sm" id="adminLogout" title="Logout"><i data-lucide="log-out"></i></a>' +
      '</div></header>' +
      '<main class="admin-content">' +
      '<div id="adminAlerts"></div>';
  }

  function footerHtml() {
    var S = window.Store;
    var shopName = S.setting('shop_name', 'Lichi Lover');
    return '' +
      '</main>' +
      '<footer class="admin-footer"><p>&copy; ' + new Date().getFullYear() + ' ' + S.esc(shopName) +
      ' &mdash; static demo &middot; <span class="demo-flag">Demo payment mode</span></p></footer>' +
      '</div></div>';
  }

  function showAlert(message, type, sticky) {
    type = type || 'error';
    var div = document.createElement('div');
    div.className = 'alert alert-' + type;
    var icon = type === 'success' ? 'check-circle' : (type === 'warning' ? 'alert-triangle' : 'x-circle');
    div.style.cssText = 'position:fixed;top:16px;right:16px;z-index:60;max-width:360px;box-shadow:var(--shadow-md);animation:fadeIn .2s;align-items:center';
    div.innerHTML = '<i data-lucide="' + icon + '"></i><span>' + window.Store.esc(message) + '</span>';
    var close = document.createElement('button');
    close.type = 'button';
    close.style.cssText = 'margin-left:auto;background:none;border:none;font-size:20px;line-height:1;cursor:pointer;color:inherit';
    close.innerHTML = '\u00D7';
    close.setAttribute('aria-label', 'Dismiss');
    close.addEventListener('click', function () { div.remove(); });
    div.appendChild(close);
    document.body.appendChild(div);
    if (window.lucide) window.lucide.createIcons();
    if (!sticky) {
      setTimeout(function () {
        div.style.opacity = '0';
        div.style.transition = 'opacity .4s';
        setTimeout(function () { div.remove(); }, 400);
      }, 6000);
    }
  }

  function boot() {
    var S = window.Store;
    if (!S) { return; }
    var page = window.LL_ADMIN_PAGE || '';

    if (page !== 'login' && !S.currentAdmin()) {
      window.location.replace('login.html');
      return;
    }

    var h = document.getElementById('admin-header');
    var f = document.getElementById('admin-footer');
    if (h) h.innerHTML = headerHtml();
    if (f) f.innerHTML = footerHtml();

    var logout = document.getElementById('adminLogout');
    if (logout) {
      logout.addEventListener('click', function (e) {
        e.preventDefault();
        S.logoutAdmin();
        window.location.href = 'login.html';
      });
    }
  }

  window.LLAdmin = { showAlert: showAlert, asset: asset };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
