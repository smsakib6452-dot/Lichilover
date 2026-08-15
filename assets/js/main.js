/* ============================================================
   Lichi Lover — main.js
   Navbar, search, product variants, qty controls, cart AJAX,
   rating input, accordions, loading states.
   ============================================================ */

(function () {
  'use strict';

  function initLucide() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  }

  // ---------- Mobile nav ----------
  function initNav() {
    var toggle = document.getElementById('menuToggle');
    var nav = document.getElementById('navLinks');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open && !document.querySelector('.menu-close-btn')) {
        var close = document.createElement('button');
        close.className = 'menu-close-btn';
        close.setAttribute('aria-label', 'Close menu');
        close.innerHTML = '<i data-lucide="x"></i>';
        nav.prepend(close);
        close.addEventListener('click', function () {
          nav.classList.remove('open');
          toggle.setAttribute('aria-expanded', 'false');
        });
        initLucide();
      }
    });
  }

  // ---------- Search overlay ----------
  function initSearch() {
    var overlay = document.getElementById('searchOverlay');
    var toggles = document.querySelectorAll('[data-search-toggle]');
    if (!overlay) return;
    toggles.forEach(function (el) {
      el.addEventListener('click', function () {
        var show = overlay.hasAttribute('hidden');
        overlay.toggleAttribute('hidden');
        if (show) {
          var input = overlay.querySelector('input[type="search"]');
          if (input) setTimeout(function () { input.focus(); }, 50);
        }
      });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !overlay.hasAttribute('hidden')) overlay.setAttribute('hidden', '');
    });
  }

  // ---------- Quantity controls ----------
  function initQtyControls() {
    document.querySelectorAll('.qty-control').forEach(function (ctl) {
      var input = ctl.querySelector('input');
      var min = parseInt(input.getAttribute('data-min') || '1', 10);
      var max = parseInt(input.getAttribute('data-max') || '50', 10);
      var minus = ctl.querySelector('.qty-minus');
      var plus = ctl.querySelector('.qty-plus');

      function setVal(v) {
        v = Math.max(min, Math.min(max, v));
        input.value = v;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }

      if (minus) minus.addEventListener('click', function () { setVal((parseInt(input.value, 10) || min) - 1); });
      if (plus) plus.addEventListener('click', function () { setVal((parseInt(input.value, 10) || min) + 1); });
      input.addEventListener('change', function () {
        var v = parseInt(input.value, 10);
        if (isNaN(v)) v = min;
        setVal(v);
      });
    });
  }

  // ---------- Product variants ----------
  function initVariants() {
    var list = document.getElementById('variantList');
    var qtyInput = document.getElementById('pdQty');
    if (!list || !qtyInput) return;

    var priceEl = document.getElementById('pdPrice');
    var compareEl = document.getElementById('pdCompare');
    var saveEl = document.getElementById('pdSave');
    var addBtn = document.getElementById('addToCartBtn');
    var buyBtn = document.getElementById('buyNowBtn');
    var stockNote = document.getElementById('pdStockNote');
    var stockIn = document.getElementById('pdStockIn');
    var stockOut = document.getElementById('pdStockOut');
    var hidden = document.getElementById('selectedVariant');

    function select(btn) {
      list.querySelectorAll('.variant-pill').forEach(function (p) { p.classList.remove('active'); });
      btn.classList.add('active');
      var price = parseFloat(btn.getAttribute('data-price'));
      var compare = btn.getAttribute('data-compare');
      var stock = parseInt(btn.getAttribute('data-stock'), 10);
      if (hidden) hidden.value = btn.getAttribute('data-id');
      if (priceEl) priceEl.textContent = '\u09F3' + price.toLocaleString('en-US', { maximumFractionDigits: 0 });
      if (compareEl) {
        if (compare && parseFloat(compare) > price) {
          compareEl.style.display = '';
          compareEl.textContent = '\u09F3' + parseFloat(compare).toLocaleString('en-US', { maximumFractionDigits: 0 });
        } else {
          compareEl.style.display = 'none';
        }
      }
      if (saveEl) {
        if (compare && parseFloat(compare) > price) {
          var pct = Math.round((1 - price / parseFloat(compare)) * 100);
          saveEl.textContent = 'Save ' + pct + '%';
          saveEl.style.display = '';
        } else {
          saveEl.style.display = 'none';
        }
      }
      if (stockNote) {
        if (stock > 0) {
          stockIn.style.display = '';
          stockOut.style.display = 'none';
          stockIn.textContent = 'In stock — ' + stock + ' available';
          if (qtyInput) { qtyInput.max = Math.max(1, stock); qtyInput.setAttribute('data-max', String(Math.max(1, stock))); }
        } else {
          stockIn.style.display = 'none';
          stockOut.style.display = '';
        }
      }
      if (addBtn) addBtn.disabled = stock <= 0;
      if (buyBtn) buyBtn.disabled = stock <= 0;
    }

    list.querySelectorAll('.variant-pill').forEach(function (pill) {
      pill.addEventListener('click', function () { select(pill); });
    });

    var first = list.querySelector('.variant-pill.active') || list.querySelector('.variant-pill:not(.out)');
    if (first) select(first);

    // Buy Now: add to cart first, then go to checkout
    var buyBtn = document.getElementById('buyNowBtn');
    var addBtn = document.getElementById('addToCartBtn');
    var form = document.getElementById('addToCartForm');
    function postFormToCart(btn, redirectAfter) {
      btn.classList.add('loading');
      btn.disabled = true;
      var fd = new FormData(form);
      fd.set('action', 'add');
      fd.set('csrf_token', window.LL_CSRF || '');
      fetch(window.LL_AJAX_URL, { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.success) { showToast(data.message || 'Could not add to cart.', 'error'); return; }
          showToast(data.message || 'Added to cart!', 'success');
          var badge = document.getElementById('cartCountBadge');
          if (badge) { badge.textContent = data.count; badge.style.display = ''; }
          if (redirectAfter) {
            window.location.href = (window.LL_BASE_URL || '') + 'checkout.php';
          }
        })
        .catch(function () { showToast('Could not add to cart.', 'error'); })
        .finally(function () { btn.classList.remove('loading'); btn.disabled = false; });
    }
    if (buyBtn && form) {
      buyBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (buyBtn.disabled) return;
        postFormToCart(buyBtn, true);
      });
    }
    if (addBtn && form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (addBtn.disabled) return;
        postFormToCart(addBtn, false);
      });
    }
  }

  // ---------- Product gallery ----------
  function initGallery() {
    var main = document.getElementById('pdMainImg');
    if (!main) return;
    document.querySelectorAll('.pd-thumb').forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        document.querySelectorAll('.pd-thumb').forEach(function (t) { t.classList.remove('active'); });
        thumb.classList.add('active');
        main.src = thumb.getAttribute('data-full');
        main.alt = thumb.getAttribute('alt') || main.alt;
      });
    });
  }

  // ---------- Rating input ----------
  function initRating() {
    var container = document.getElementById('ratingInput');
    if (!container) return;
    var hidden = container.querySelector('input[type="hidden"]');
    var buttons = container.querySelectorAll('button');
    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var val = btn.getAttribute('data-value');
        hidden.value = val;
        buttons.forEach(function (b) {
          b.classList.toggle('active', parseInt(b.getAttribute('data-value'), 10) <= parseInt(val, 10));
        });
      });
    });
  }

  // ---------- Tabs ----------
  function initTabs() {
    document.querySelectorAll('.pd-tabbar').forEach(function (bar) {
      bar.querySelectorAll('.pd-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
          var target = tab.getAttribute('data-tab');
          bar.querySelectorAll('.pd-tab').forEach(function (t) { t.classList.remove('active'); });
          tab.classList.add('active');
          document.querySelectorAll('.pd-tabpanel').forEach(function (panel) {
            panel.hidden = panel.id !== target;
          });
        });
      });
    });
  }

  // ---------- FAQ accordion ----------
  function initFaq() {
    document.querySelectorAll('.faq-item').forEach(function (item) {
      var q = item.querySelector('.faq-q');
      var a = item.querySelector('.faq-a');
      if (!q || !a) return;
      q.addEventListener('click', function () {
        var open = item.classList.toggle('open');
        a.style.maxHeight = open ? a.scrollHeight + 'px' : '0px';
      });
    });
  }

  // ---------- Loading states on forms ----------
  function initForms() {
    document.querySelectorAll('form[data-loading]').forEach(function (form) {
      form.addEventListener('submit', function () {
        var btn = form.querySelector('[type="submit"]');
        if (btn && !btn.disabled) {
          btn.classList.add('loading');
          btn.disabled = true;
        }
      });
    });
  }

  // ---------- Shop filter mobile toggle ----------
  function initShopFilters() {
    var toggle = document.getElementById('shopFiltersToggle');
    var sidebar = document.getElementById('shopSidebar');
    if (toggle && sidebar) {
      toggle.addEventListener('click', function () {
        sidebar.classList.toggle('open');
      });
    }
  }

  // ---------- Cart AJAX ----------
  function bindCartButtons() {
    document.querySelectorAll('[data-cart-update]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var variantId = btn.getAttribute('data-cart-update');
        var action = btn.getAttribute('data-cart-action') || 'add';
        var qtyEl = document.getElementById('pdQty') || document.getElementById('cartQty_' + variantId);
        var qty = qtyEl ? parseInt(qtyEl.value, 10) || 1 : 1;
        var productId = btn.getAttribute('data-product-id') || '';
        postCart(variantId, action, qty, btn, productId);
      });
    });
    document.querySelectorAll('[data-cart-qty]').forEach(function (input) {
      input.addEventListener('change', function () {
        var variantId = input.getAttribute('data-cart-qty');
        postCart(variantId, 'update', parseInt(input.value, 10) || 0, input);
      });
    });
    document.querySelectorAll('[data-cart-remove]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var variantId = btn.getAttribute('data-cart-remove');
        postCart(variantId, 'remove', 0, btn);
      });
    });
  }

  function postCart(variantId, action, qty, trigger, productId) {
    var formData = new FormData();
    formData.append('action', action);
    formData.append('variant_id', variantId);
    formData.append('product_id', productId || '');
    formData.append('qty', qty);
    formData.append('csrf_token', window.LL_CSRF || '');

    fetch(window.LL_AJAX_URL, { method: 'POST', body: formData })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success) {
          showToast(data.message || 'Something went wrong.', 'error');
          return;
        }
        var badge = document.getElementById('cartCountBadge');
        if (badge) {
          if (data.count > 0) { badge.textContent = data.count; badge.style.display = ''; }
          else badge.remove();
        }
        if (window.location.pathname.indexOf('cart.php') !== -1) {
          window.location.reload();
          return;
        }
        showToast(data.message || 'Cart updated.', 'success');
        if (action === 'add' && typeof data.redirect === 'string') {
          setTimeout(function () { window.location.href = data.redirect; }, 600);
        }
      })
      .catch(function () {
        showToast('Could not update your cart. Please try again.', 'error');
        if (trigger) { trigger.classList.remove('loading'); trigger.disabled = false; }
      })
      .finally(function () {
        if (trigger && trigger.classList) {
          trigger.classList.remove('loading');
          trigger.disabled = false;
        }
      });
  }

  // ---------- Password visibility toggle ----------
  function initPasswordToggle() {
    document.querySelectorAll('input[type="password"]').forEach(function (input) {
      if (input.closest('.pw-wrap')) return;

      var wrap = document.createElement('div');
      wrap.className = 'pw-wrap';
      input.parentNode.insertBefore(wrap, input);
      wrap.appendChild(input);

      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'pw-toggle';
      btn.setAttribute('aria-label', 'Show password');
      btn.setAttribute('aria-pressed', 'false');
      btn.innerHTML =
        '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
      wrap.appendChild(btn);

      btn.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        btn.classList.toggle('active', show);
        btn.innerHTML =
          '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
          (show
            ? '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>'
            : '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>') +
          '</svg>';
        input.focus();
      });
    });
  }

  // ---------- Toast ----------
  function showToast(message, type) {
    var toast = document.createElement('div');
    toast.className = 'toast toast-' + (type || 'info');
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function () {
      toast.classList.add('show');
    }, 10);
    setTimeout(function () {
      toast.classList.remove('show');
      setTimeout(function () { toast.remove(); }, 300);
    }, 2600);
  }

  // ---------- Newsletter AJAX ----------
  function initNewsletter() {
    document.querySelectorAll('form[data-newsletter]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = form.querySelector('[type="submit"]');
        var email = form.querySelector('input[name="email"]').value;
        btn.classList.add('loading'); btn.disabled = true;
        var fd = new FormData();
        fd.append('newsletter', '1');
        fd.append('email', email);
        fd.append('csrf_token', window.LL_CSRF || '');
        fetch(window.LL_AJAX_URL, { method: 'POST', body: fd })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            showToast(data.message || 'Thank you!', data.success ? 'success' : 'error');
            form.reset();
          })
          .catch(function () { showToast('Could not subscribe right now.', 'error'); })
          .finally(function () { btn.classList.remove('loading'); btn.disabled = false; });
      });
    });
  }

  // ---------- Init ----------
  function init() {
    initLucide();
    initNav();
    initSearch();
    initQtyControls();
    initVariants();
    initGallery();
    initRating();
    initTabs();
    initFaq();
    initForms();
    initShopFilters();
    bindCartButtons();
    initNewsletter();
    initPasswordToggle();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.LL = { showToast: showToast };
})();
