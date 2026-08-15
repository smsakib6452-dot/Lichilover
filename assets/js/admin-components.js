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

  function icon(name) {
    return '<i data-lucide="' + name + '"></i>';
  }

  function buildShell() {
    var S = window.Store;
    var page = window.LL_ADMIN_PAGE || '';
    var title = window.LL_ADMIN_TITLE || 'Dashboard';
    var shopName = S.setting('shop_name', 'Lichi Lover');
    var admin = S.currentAdmin();
    var name = admin ? (admin.name || 'Admin') : 'Admin';
    var email = admin ? (admin.email || '') : '';

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
        icon(it.icon) + '<span>' + it.label + '</span>' + badge + '</a>';
    }).join('');

    var shell = document.createElement('div');
    shell.className = 'admin-shell';

    var aside = document.createElement('aside');
    aside.className = 'admin-sidebar';
    aside.id = 'adminSidebar';
    aside.innerHTML =
      '<a href="index.html" class="admin-brand">' +
      '<img src="' + asset('images/logo.svg') + '" alt="' + S.esc(shopName) + ' logo" width="34" height="34">' +
      '<span>' + S.esc(shopName) + '<small>Admin Panel</small></span></a>' +
      '<nav class="admin-nav">' + nav +
      '<a href="../index.html" target="_blank" rel="noopener">' + icon('external-link') + '<span>View Site</span></a>' +
      '</nav>';

    var mainWrap = document.createElement('div');
    mainWrap.className = 'admin-main';

    var topbar = document.createElement('header');
    topbar.className = 'admin-topbar';
    topbar.innerHTML =
      '<button class="admin-menu-btn" id="adminMenuBtn" aria-label="Toggle menu">' + icon('menu') + '</button>' +
      '<h1 class="admin-page-title">' + S.esc(title) + '</h1>' +
      '<div class="admin-user">' +
      '<span class="admin-avatar">' + S.esc(name.charAt(0).toUpperCase()) + '</span>' +
      '<div><strong>' + S.esc(name) + '</strong><span>' + S.esc(email) + '</span></div>' +
      '<a href="#" class="btn btn-ghost btn-sm" id="adminLogout" title="Logout">' + icon('log-out') + '</a>' +
      '</div>';

    var footer = document.createElement('footer');
    footer.className = 'admin-footer';
    footer.innerHTML = '<p>&copy; ' + new Date().getFullYear() + ' ' + S.esc(shopName) +
      ' &mdash; static demo &middot; <span class="demo-flag">Demo payment mode</span></p>';

    mainWrap.appendChild(topbar);
    mainWrap.appendChild(footer);
    shell.appendChild(aside);
    shell.appendChild(mainWrap);

    return { shell: shell, mainWrap: mainWrap };
  }

  function showAlert(message, type, sticky) {
    type = type || 'error';
    var div = document.createElement('div');
    div.className = 'alert alert-' + type;
    var iconName = type === 'success' ? 'check-circle' : (type === 'warning' ? 'alert-triangle' : 'x-circle');
    div.style.cssText = 'position:fixed;top:16px;right:16px;z-index:60;max-width:360px;box-shadow:var(--shadow-md);animation:fadeIn .2s;align-items:center';
    div.innerHTML = '<i data-lucide="' + iconName + '"></i><span>' + window.Store.esc(message) + '</span>';
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
    if (!h || !f) return;

    // Gather the page's own content sitting between the two placeholders.
    var contentNodes = [];
    var node = h.nextSibling;
    while (node && node !== f) {
      var next = node.nextSibling;
      contentNodes.push(node);
      node = next;
    }

    // The real content <main> wraps the page's markup (not just a string,
    // so it cannot be auto-closed by the HTML parser).
    var content = document.createElement('main');
    content.className = 'admin-content';
    contentNodes.forEach(function (c) { content.appendChild(c); });

    var parts = buildShell();
    // Insert <main> between topbar and footer.
    parts.mainWrap.insertBefore(content, parts.mainWrap.querySelector('footer.admin-footer'));

    var parent = h.parentNode;
    parent.insertBefore(parts.shell, f);
    h.remove();
    f.remove();

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
