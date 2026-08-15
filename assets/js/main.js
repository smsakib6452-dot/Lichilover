/* ============================================================
   Litchi Lover — main.js (static edition)
   Renders products, cart, checkout, orders, reviews, etc.
   using the localStorage Store layer. No backend required.
   ============================================================ */

(function () {
  'use strict';

  var S = window.Store;

  /* ---------- Toast ---------- */
  function showToast(message, type) {
    var toast = document.createElement('div');
    toast.className = 'toast toast-' + (type || 'info');
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function () { toast.classList.add('show'); }, 10);
    setTimeout(function () {
      toast.classList.remove('show');
      setTimeout(function () { toast.remove(); }, 300);
    }, 2600);
  }

  function initLucide() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  }

  /* ---------- Mobile nav ---------- */
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

  /* ---------- Search overlay ---------- */
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

  /* ---------- Quantity controls ---------- */
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

  /* ---------- Product variants ---------- */
  function initVariants() {
    var list = document.getElementById('variantList');
    var qtyInput = document.getElementById('pdQty');
    if (!list || !qtyInput) return;
    var priceEl = document.getElementById('pdPrice');
    var compareEl = document.getElementById('pdCompare');
    var saveEl = document.getElementById('pdSave');
    var addBtn = document.getElementById('addToCartBtn');
    var buyBtn = document.getElementById('buyNowBtn');
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
      if (priceEl) priceEl.textContent = S.money(price);
      if (compareEl) {
        if (compare && parseFloat(compare) > price) {
          compareEl.style.display = '';
          compareEl.textContent = S.money(compare);
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
      if (stockIn && stockOut) {
        if (stock > 0) {
          stockIn.style.display = '';
          stockOut.style.display = 'none';
          stockIn.innerHTML = '<i data-lucide="check-circle-2"></i> In stock — ' + stock + ' available';
          if (qtyInput) { qtyInput.max = Math.max(1, stock); qtyInput.setAttribute('data-max', String(Math.max(1, stock))); }
        } else {
          stockIn.style.display = 'none';
          stockOut.style.display = '';
        }
      }
      if (addBtn) addBtn.disabled = stock <= 0;
      if (buyBtn) buyBtn.disabled = stock <= 0;
      initLucide();
    }

    list.querySelectorAll('.variant-pill').forEach(function (pill) {
      pill.addEventListener('click', function () { select(pill); });
    });

    var first = list.querySelector('.variant-pill.active') || list.querySelector('.variant-pill:not(.out)');
    if (first) select(first);

    var form = document.getElementById('addToCartForm');
    if (buyBtn && form) {
      buyBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (buyBtn.disabled) return;
        var vid = Number(hidden ? hidden.value : 0);
        var qty = parseInt(qtyInput.value, 10) || 1;
        if (S.cartAdd(vid, qty)) {
          showToast('Added to cart!', 'success');
          updateCartBadge();
          setTimeout(function () { window.location.href = 'checkout.html'; }, 400);
        } else {
          showToast('Could not add to cart.', 'error');
        }
      });
    }
    if (addBtn && form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (addBtn.disabled) return;
        var vid = Number(hidden ? hidden.value : 0);
        var qty = parseInt(qtyInput.value, 10) || 1;
        if (S.cartAdd(vid, qty)) {
          showToast('Added to cart!', 'success');
          updateCartBadge();
        } else {
          showToast('Could not add to cart.', 'error');
        }
      });
    }
  }

  /* ---------- Product gallery ---------- */
  function initGallery() {
    var main = document.getElementById('pdMainImg');
    if (!main) return;
    document.querySelectorAll('.pd-thumb').forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        document.querySelectorAll('.pd-thumb').forEach(function (t) { t.classList.remove('active'); });
        thumb.classList.add('active');
        main.src = thumb.getAttribute('data-full');
      });
    });
  }

  /* ---------- Rating input ---------- */
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

  /* ---------- Tabs ---------- */
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

  /* ---------- FAQ accordion ---------- */
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

  /* ---------- Shop filter mobile toggle ---------- */
  function initShopFilters() {
    var toggle = document.getElementById('shopFiltersToggle');
    var sidebar = document.getElementById('shopSidebar');
    if (toggle && sidebar) {
      toggle.addEventListener('click', function () {
        sidebar.classList.toggle('open');
      });
    }
  }

  /* ---------- Cart badge ---------- */
  function updateCartBadge() {
    var badge = document.getElementById('cartCountBadge');
    var count = S.cartCount();
    if (badge) {
      if (count > 0) { badge.textContent = count; badge.style.display = ''; }
      else if (badge.parentNode) badge.remove();
      return;
    }
    var cartBtn = document.querySelector('.cart-btn');
    if (cartBtn && count > 0) {
      var b = document.createElement('span');
      b.className = 'cart-count';
      b.id = 'cartCountBadge';
      b.textContent = count;
      cartBtn.appendChild(b);
    }
  }

  /* ---------- Cart buttons (cards / product page) ---------- */
  function bindCartButtons() {
    document.querySelectorAll('[data-cart-update]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var productId = btn.getAttribute('data-product-id');
        var variant = S.defaultVariantForProduct(Number(productId));
        if (!variant) { showToast('This product is not available.', 'error'); return; }
        if (variant.stock_qty > 0 && Number(variant.stock_qty) <= 0) { showToast('Out of stock.', 'error'); return; }
        if (S.cartAdd(variant.id, 1)) {
          showToast('Added to cart!', 'success');
          updateCartBadge();
        } else {
          showToast('Could not add to cart.', 'error');
        }
      });
    });
  }

  /* ---------- Password visibility toggle ---------- */
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
      btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
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
            ? '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7 10 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>'
            : '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>') +
          '</svg>';
        input.focus();
      });
    });
  }

  /* ---------- Newsletter ---------- */
  function initNewsletter() {
    document.querySelectorAll('form[data-newsletter]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var email = form.querySelector('input[name="email"]').value;
        if (!S.isValidEmail(email)) { showToast('Please enter a valid email address.', 'error'); return; }
        S.newsletterSubscribe(email.trim().toLowerCase());
        showToast('Thanks for subscribing! Fresh litchi updates are on the way.', 'success');
        form.reset();
      });
    });
  }

  /* ---------- Page renderers ---------- */

  function productCardHtml(p) {
    var pc = S.productCard(p);
    var sale = null;
    if (pc.compare_price && Number(pc.compare_price) > pc.min_price) {
      sale = Math.round((1 - pc.min_price / Number(pc.compare_price)) * 100);
    }
    var stockDisabled = pc.stock_qty <= 0 ? 'disabled' : '';
    return '' +
      '<article class="product-card">' +
      '  <div class="product-media">' +
      '    <a href="product.html?id=' + pc.id + '"><img src="' + S.esc(pc.image) + '" alt="' + S.esc(pc.name) + '" loading="lazy"></a>' +
      (sale ? '    <span class="product-badge">-' + sale + '%</span>' : '') +
      (pc.stock_qty <= 0 ? '    <span class="product-badge badge-sale">Out of Stock</span>' : '') +
      '  </div>' +
      '  <div class="product-body">' +
      '    <span class="product-cat">' + S.esc(pc.category_name) + '</span>' +
      '    <h3 class="product-name"><a href="product.html?id=' + pc.id + '">' + S.esc(pc.name) + '</a></h3>' +
      '    <div class="product-stars">' + S.starsHtml(pc.rating_avg) + '<span>(' + pc.rating_count + ' reviews)</span></div>' +
      '    <div class="product-price">' +
      '      <span class="price">' + S.money(pc.min_price) + '</span>' +
      (sale ? '      <span class="compare">' + S.money(pc.compare_price) + '</span>' : '') +
      '    </div>' +
      '    <div class="product-actions">' +
      '      <a href="product.html?id=' + pc.id + '" class="btn btn-ghost btn-sm">View</a>' +
      '      <button class="btn btn-primary btn-sm" data-cart-update="' + pc.id + '" data-product-id="' + pc.id + '" ' + stockDisabled + '>Add to Cart</button>' +
      '    </div>' +
      '  </div>' +
      '</article>';
  }

  function renderHome() {
    var grid = document.getElementById('featuredGrid');
    if (grid) {
      var featured = S.products().filter(function (p) { return p.is_active === 1 && p.is_featured === 1; })
        .sort(function (a, b) { return (Number(b.sold_count) || 0) - (Number(a.sold_count) || 0); })
        .slice(0, 8);
      grid.innerHTML = featured.map(productCardHtml).join('');
    }
    var catGrid = document.getElementById('categoryGrid');
    if (catGrid) {
      catGrid.innerHTML = S.categories().map(function (c) {
        return '<a href="shop.html?category=' + S.esc(c.slug) + '" class="feature-card">' +
          '<div class="feature-icon"><i data-lucide="basket"></i></div>' +
          '<h3>' + S.esc(c.name) + '</h3>' +
          '<p>' + S.esc(S.truncate(c.description || '', 60)) + '</p></a>';
      }).join('');
    }
    // Hero content from settings
    var heroHeadline = document.getElementById('heroHeadline');
    if (heroHeadline) heroHeadline.textContent = S.setting('hero_headline', 'Fresh Litchi, Straight to Your Door');
    var heroSub = document.getElementById('heroSubheadline');
    if (heroSub) heroSub.textContent = S.setting('hero_subheadline', 'Enjoy naturally sweet, juicy and freshly selected litchi delivered across Bangladesh.');
    var heroTag = document.getElementById('heroTag');
    if (heroTag) heroTag.textContent = S.setting('shop_tagline', 'Freshness You Can Taste.');
    initLucide();
    bindCartButtons();
  }

  function renderShop() {
    var params = new URLSearchParams(window.location.search);
    var q = params.get('q') || '';
    var catSlug = params.get('category') || '';
    var minPrice = params.get('min_price') !== null && params.get('min_price') !== '' ? Number(params.get('min_price')) : null;
    var maxPrice = params.get('max_price') !== null && params.get('max_price') !== '' ? Number(params.get('max_price')) : null;
    var maxWeight = params.get('max_weight') !== null && params.get('max_weight') !== '' ? Number(params.get('max_weight')) : null;
    var inStock = params.get('in_stock') === '1';
    var sort = params.get('sort') || 'popular';

    // Sidebar categories
    var catList = document.getElementById('filterCategories');
    if (catList) {
      var html = '<li><label><input type="radio" name="category" value=""' + (catSlug === '' ? ' checked' : '') + '> All Products</label></li>';
      S.categories().forEach(function (c) {
        html += '<li><label><input type="radio" name="category" value="' + S.esc(c.slug) + '"' + (catSlug === c.slug ? ' checked' : '') + '> ' +
          S.esc(c.name) + ' <span class="filter-count">' + S.productCountForCategory(c.id) + '</span></label></li>';
      });
      catList.innerHTML = html;
    }
    var sortSel = document.getElementById('sortSelect');
    if (sortSel) sortSel.value = sort;

    // Filter products
    var list = S.products().filter(function (p) {
      if (p.is_active !== 1) return false;
      if (catSlug) {
        var c = S.categoryBySlug(catSlug);
        if (!c || p.category_id !== c.id) return false;
      }
      if (q) {
        var hay = (p.name + ' ' + (p.short_description || '') + ' ' + (p.description || '')).toLowerCase();
        if (hay.indexOf(q.toLowerCase()) === -1) return false;
      }
      var price = S.productMinPrice(p);
      if (minPrice !== null && price < minPrice) return false;
      if (maxPrice !== null && price > maxPrice) return false;
      if (maxWeight !== null && S.productMinWeight(p) > maxWeight) return false;
      if (inStock && !(Number(p.stock_qty) > 0)) return false;
      return true;
    });

    var sorters = {
      popular: function (a, b) { return (Number(b.sold_count) || 0) - (Number(a.sold_count) || 0) || (Number(b.rating_avg) || 0) - (Number(a.rating_avg) || 0); },
      newest: function (a, b) { return String(b.created_at || '').localeCompare(String(a.created_at || '')) || b.id - a.id; },
      price_low: function (a, b) { return S.productMinPrice(a) - S.productMinPrice(b); },
      price_high: function (a, b) { return S.productMinPrice(b) - S.productMinPrice(a); },
      rating: function (a, b) { return (Number(b.rating_avg) || 0) - (Number(a.rating_avg) || 0); }
    };
    list.sort(sorters[sort] || sorters.popular);

    var countEl = document.getElementById('resultCount');
    if (countEl) countEl.textContent = list.length + ' product' + (list.length === 1 ? '' : 's');

    var grid = document.getElementById('shopGrid');
    var emptyEl = document.getElementById('shopEmpty');
    var paginationEl = document.getElementById('shopPagination');
    var perPage = 12;
    var page = Math.max(1, Number(params.get('page')) || 1);
    var totalPages = Math.max(1, Math.ceil(list.length / perPage));
    page = Math.min(page, totalPages);
    var slice = list.slice((page - 1) * perPage, page * perPage);

    if (grid && emptyEl) {
      if (list.length === 0) {
        grid.innerHTML = '';
        emptyEl.style.display = '';
      } else {
        grid.innerHTML = slice.map(productCardHtml).join('');
        emptyEl.style.display = 'none';
      }
    }
    if (paginationEl) {
      if (totalPages <= 1) { paginationEl.innerHTML = ''; }
      else {
        var ph = '';
        if (page > 1) ph += '<a class="btn btn-ghost btn-sm" href="' + buildShopUrl(params, page - 1) + '">&laquo; Prev</a>';
        for (var i = 1; i <= totalPages; i++) {
          ph += '<a class="btn ' + (i === page ? 'btn-primary' : 'btn-ghost') + ' btn-sm" href="' + buildShopUrl(params, i) + '">' + i + '</a>';
        }
        if (page < totalPages) ph += '<a class="btn btn-ghost btn-sm" href="' + buildShopUrl(params, page + 1) + '">Next &raquo;</a>';
        paginationEl.innerHTML = ph;
      }
    }
    bindCartButtons();
  }

  function buildShopUrl(params, page) {
    var p = new URLSearchParams();
    ['q', 'category', 'min_price', 'max_price', 'max_weight', 'in_stock', 'sort'].forEach(function (k) {
      if (params.get(k) !== null && params.get(k) !== '') p.set(k, params.get(k));
    });
    if (page > 1) p.set('page', page);
    var qs = p.toString();
    return 'shop.html' + (qs ? '?' + qs : '');
  }

  function renderProduct() {
    var params = new URLSearchParams(window.location.search);
    var id = Number(params.get('id')) || 0;
    var product = S.productById(id);
    var notFound = document.getElementById('productNotFound');
    var content = document.getElementById('productContent');

    if (!product || product.is_active !== 1) {
      if (notFound) notFound.style.display = '';
      if (content) content.style.display = 'none';
      return;
    }
    if (notFound) notFound.style.display = 'none';
    if (content) content.style.display = '';

    var variants = S.variantsFor(id);
    var gallery = (product.gallery && product.gallery.length ? product.gallery : [product.image]);
    var totalStock = 0;
    variants.forEach(function (v) { totalStock += Number(v.stock_qty) || 0; });

    document.title = product.name + ' — Litchi Lover';

    // Crumb
    var crumbCat = document.getElementById('crumbCategory');
    if (crumbCat) {
      var c = null;
      S.categories(true).forEach(function (x) { if (x.id === product.category_id) c = x; });
      if (c) crumbCat.innerHTML = '<li><a href="shop.html?category=' + S.esc(c.slug) + '">' + S.esc(c.name) + '</a></li>';
    }
    document.getElementById('crumbProduct').textContent = product.name;

    // Gallery
    var galleryEl = document.getElementById('pdGallery');
    if (galleryEl) {
      var ghtml = '<div class="pd-main-img"><img id="pdMainImg" src="' + S.esc(gallery[0]) + '" alt="' + S.esc(product.name) + '"></div>';
      if (gallery.length > 1) {
        ghtml += '<div class="pd-thumbs">';
        gallery.forEach(function (img, i) {
          ghtml += '<button type="button" class="pd-thumb' + (i === 0 ? ' active' : '') + '" data-full="' + S.esc(img) + '"><img src="' + S.esc(img) + '" alt="' + S.esc(product.name) + ' thumbnail ' + (i + 1) + '" loading="lazy"></button>';
        });
        ghtml += '</div>';
      }
      galleryEl.innerHTML = ghtml;
    }

    // Info
    document.getElementById('pdName').textContent = product.name;
    var meta = document.getElementById('pdMeta');
    if (meta) {
      meta.innerHTML = S.starsHtml(product.rating_avg) +
        '<span>' + product.rating_count + ' review' + (product.rating_count === 1 ? '' : 's') + '</span>' +
        '<span>' + product.sold_count + ' sold</span>';
    }
    var desc = document.getElementById('pdDesc');
    if (desc) desc.innerHTML = product.description || '';
    var galleryLarge = document.getElementById('pdGalleryLarge');
    if (galleryLarge) galleryLarge.innerHTML = galleryEl ? galleryEl.innerHTML : '';
    initGallery();

    // Variants
    var variantList = document.getElementById('variantList');
    if (variantList) {
      variantList.innerHTML = variants.map(function (v) {
        return '<button type="button" class="variant-pill' + (v.is_default ? ' active' : '') + (Number(v.stock_qty) <= 0 ? ' out' : '') + '"' +
          ' data-id="' + v.id + '" data-price="' + v.price + '" data-compare="' + (v.compare_price || '') + '" data-stock="' + v.stock_qty + '">' +
          '<span class="v-name">' + S.esc(v.name) + '</span>' +
          '<span class="v-price">' + S.money(v.price) + '</span>' +
          '<span class="v-stock">' + (Number(v.stock_qty) <= 0 ? 'Out of stock' : (v.stock_qty + ' available')) + '</span>' +
          '</button>';
      }).join('');
    }
    document.getElementById('selectedVariant').value = variants.length ? variants[0].id : 0;

    // Stock note
    var stockIn = document.getElementById('pdStockIn');
    var stockOut = document.getElementById('pdStockOut');
    if (totalStock > 0) {
      stockIn.innerHTML = '<i data-lucide="check-circle-2"></i> In stock — ' + totalStock + ' units available';
      stockIn.style.display = '';
      stockOut.style.display = 'none';
    } else {
      stockIn.style.display = 'none';
      stockOut.style.display = '';
    }

    initVariants();

    // Reviews
    var reviewList = document.getElementById('reviewList');
    var reviews = S.reviewsForProduct(id);
    var user = S.currentUser();
    if (reviewList) {
      if (!reviews.length) {
        reviewList.innerHTML = '<p class="empty-state">No reviews yet. Be the first to review this product!</p>';
      } else {
        var rh = '';
        reviews.forEach(function (r) {
          var u = null;
          S.getCollection('users').forEach(function (x) { if (x.id === r.user_id) u = x; });
          var userName = u ? u.name : 'Customer';
          var dateStr = '';
          if (r.created_at) {
            var d = new Date(r.created_at);
            dateStr = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
          }
          rh += '<div class="review-item">' +
            '<div class="review-head">' +
            '<div class="review-avatar">' + S.esc(userName.charAt(0).toUpperCase()) + '</div>' +
            '<div><div class="name">' + S.esc(userName) + (r.is_demo ? ' <span class="review-demo-tag">Sample</span>' : '') + '</div>' +
            '<div class="meta">' + S.starsHtml(r.rating) + ' &middot; ' + dateStr + (r.is_demo ? ' <span style="color:var(--muted)">— sample/demo review</span>' : '') + '</div></div>' +
            '</div>' +
            (r.review ? '<p class="review-body">' + S.esc(r.review) + '</p>' : '') +
            '</div>';
        });
        reviewList.innerHTML = rh;
      }
    }
    var reviewFormWrap = document.getElementById('reviewFormWrap');
    if (reviewFormWrap) {
      if (user) {
        reviewFormWrap.innerHTML =
          '<div class="review-form">' +
          '<h3 style="margin-bottom:10px">Write a Review</h3>' +
          '<form data-review-form>' +
          '<div class="form-field" style="margin-bottom:12px"><label>Your Rating</label>' +
          '<div class="rating-input" id="ratingInput"><input type="hidden" name="rating" value="5">' +
          [1, 2, 3, 4, 5].map(function (i) { return '<button type="button" data-value="' + i + '" class="active" aria-label="' + i + ' stars">★</button>'; }).join('') +
          '</div></div>' +
          '<div class="form-field"><label>Your Review</label><textarea name="review" rows="4" placeholder="Share your experience with this litchi..." maxlength="1000"></textarea></div>' +
          '<button type="submit" class="btn btn-primary" style="margin-top:12px">Submit Review</button>' +
          '<p style="font-size:12px;color:var(--muted);margin-top:8px">Reviews are moderated and will appear after approval.</p>' +
          '</form></div>';
        var form = reviewFormWrap.querySelector('form[data-review-form]');
        var subBtn = form.querySelector('[type="submit"]');
        var reviewText = form.querySelector('textarea[name="review"]');
        form.addEventListener('submit', function (e) {
          e.preventDefault();
          var rating = Number(form.querySelector('input[name="rating"]').value) || 5;
          if (String(reviewText.value || '').trim().length < 3) {
            showToast('Please write a short review (at least 3 characters).', 'error');
            return;
          }
          S.addReview(id, user.id, rating, reviewText.value);
          showToast('Thank you! Your review has been submitted and will appear after approval.', 'success');
          form.remove();
          initRating();
        });
        initRating();
      } else {
        reviewFormWrap.innerHTML = '<p style="margin-top:20px;color:var(--muted)"><a href="login.html">Login</a> to write a review.</p>';
      }
    }

    // Related products
    var related = document.getElementById('relatedGrid');
    if (related) {
      var rel = S.products().filter(function (p) {
        return p.is_active === 1 && p.id !== product.id && p.category_id === product.category_id;
      }).sort(function (a, b) { return (Number(b.sold_count) || 0) - (Number(a.sold_count) || 0); }).slice(0, 4);
      if (rel.length) {
        document.getElementById('relatedSection').style.display = '';
        related.innerHTML = rel.map(productCardHtml).join('');
      }
    }
    initLucide();
  }

  function renderCart() {
    var wrap = document.getElementById('cartContent');
    var empty = document.getElementById('cartEmpty');
    var checkout = S.cartItems();
    if (!checkout.items.length) {
      if (wrap) wrap.style.display = 'none';
      if (empty) empty.style.display = '';
      return;
    }
    if (wrap) wrap.style.display = '';
    if (empty) empty.style.display = 'none';

    var listEl = document.getElementById('cartItemsList');
    if (listEl) {
      listEl.innerHTML = checkout.items.map(function (item) {
        var weightStr = '';
        if (item.weight) {
          var w = String(item.weight % 1 === 0 ? item.weight : item.weight.toFixed(2)).replace(/\.?0+$/, '');
          weightStr = ' · ' + w + ' KG';
        }
        return '<div class="cart-item" id="cartItem_' + item.variant_id + '">' +
          '<div class="cart-item-img"><img src="' + S.esc(item.image) + '" alt="' + S.esc(item.product_name) + '"></div>' +
          '<div class="cart-item-info">' +
          '<div class="name"><a href="product.html?id=' + item.product_id + '">' + S.esc(item.product_name) + '</a></div>' +
          '<div class="variant">' + S.esc(item.variant_name) + weightStr + '</div>' +
          '<div class="line">' + S.money(item.unit_price) + ' each</div>' +
          '</div>' +
          '<div class="cart-item-right">' +
          '<div class="qty-control">' +
          '<button type="button" class="qty-minus" data-cart-qty-btn="' + item.variant_id + '" data-step="-1" aria-label="Decrease">−</button>' +
          '<input type="number" id="cartQty_' + item.variant_id + '" data-cart-qty="' + item.variant_id + '" value="' + item.qty + '" min="1" max="50" inputmode="numeric" aria-label="Quantity for ' + S.esc(item.product_name) + '">' +
          '<button type="button" class="qty-plus" data-cart-qty-btn="' + item.variant_id + '" data-step="1" aria-label="Increase">+</button>' +
          '</div>' +
          '<div class="cart-item-total" id="lineTotal_' + item.variant_id + '">' + S.money(item.line_total) + '</div>' +
          '<button class="cart-remove-btn" data-cart-remove="' + item.variant_id + '" aria-label="Remove ' + S.esc(item.product_name) + '"><i data-lucide="trash-2"></i></button>' +
          '</div></div>';
      }).join('');
      initLucide();
    }

    var subEl = document.getElementById('cartSubtotal');
    var totalEl = document.getElementById('cartTotal');
    if (subEl) subEl.textContent = S.money(checkout.subtotal);
    if (totalEl) totalEl.textContent = S.money(checkout.subtotal);

    wireCartPage();
  }

  function wireCartPage() {
    document.querySelectorAll('[data-cart-qty-btn]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var vid = btn.getAttribute('data-cart-qty-btn');
        var input = document.getElementById('cartQty_' + vid);
        if (!input) return;
        var step = parseInt(btn.getAttribute('data-step'), 10);
        var v = parseInt(input.value, 10) || 1;
        input.value = Math.max(1, v + step);
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });
    });
    document.querySelectorAll('[data-cart-qty]').forEach(function (input) {
      input.addEventListener('change', function () {
        var vid = Number(input.getAttribute('data-cart-qty'));
        var v = parseInt(input.value, 10) || 0;
        S.cartUpdate(vid, v);
        var item = document.getElementById('cartItem_' + vid);
        var checkout = S.cartItems();
        var current = null;
        checkout.items.forEach(function (i) { if (i.variant_id === vid) current = i; });
        if (!current) { if (item) item.remove(); }
        else {
          if (item) item.querySelector('.cart-item-total').textContent = S.money(current.line_total);
          input.value = current.qty;
        }
        var subEl = document.getElementById('cartSubtotal');
        var totalEl = document.getElementById('cartTotal');
        if (subEl) subEl.textContent = S.money(checkout.subtotal);
        if (totalEl) totalEl.textContent = S.money(checkout.subtotal);
        updateCartBadge();
        if (!checkout.items.length) {
          var wrap = document.getElementById('cartContent');
          var empty = document.getElementById('cartEmpty');
          if (wrap) wrap.style.display = 'none';
          if (empty) empty.style.display = '';
        }
      });
    });
    document.querySelectorAll('[data-cart-remove]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var vid = Number(btn.getAttribute('data-cart-remove'));
        S.cartRemove(vid);
        var item = document.getElementById('cartItem_' + vid);
        if (item) item.remove();
        var checkout = S.cartItems();
        var subEl = document.getElementById('cartSubtotal');
        var totalEl = document.getElementById('cartTotal');
        if (subEl) subEl.textContent = S.money(checkout.subtotal);
        if (totalEl) totalEl.textContent = S.money(checkout.subtotal);
        updateCartBadge();
        if (!checkout.items.length) {
          var wrap = document.getElementById('cartContent');
          var empty = document.getElementById('cartEmpty');
          if (wrap) wrap.style.display = 'none';
          if (empty) empty.style.display = '';
        }
      });
    });
    initQtyControls();
  }

  function renderCheckout() {
    var checkout = S.cartItems();
    var emptyEl = document.getElementById('checkoutEmpty');
    var contentEl = document.getElementById('checkoutContent');
    if (!checkout.items.length) {
      if (contentEl) contentEl.style.display = 'none';
      if (emptyEl) emptyEl.style.display = '';
      return;
    }
    if (contentEl) contentEl.style.display = '';
    if (emptyEl) emptyEl.style.display = 'none';

    var user = S.currentUser();
    if (user) {
      var fn = document.getElementById('coFullName');
      var ph = document.getElementById('coPhone');
      var em = document.getElementById('coEmail');
      if (fn) fn.value = user.name;
      if (ph) ph.value = user.phone;
      if (em) em.value = user.email;
    }

    // Order summary
    var summaryList = document.getElementById('checkoutSummaryItems');
    if (summaryList) {
      summaryList.innerHTML = checkout.items.map(function (item) {
        return '<div class="os-item">' +
          '<img src="' + S.esc(item.image) + '" alt="' + S.esc(item.product_name) + '">' +
          '<span class="os-name">' + S.esc(item.product_name) + ' <span class="os-qty">×' + item.qty + '</span></span>' +
          '<span class="os-price">' + S.money(item.line_total) + '</span></div>';
      }).join('');
    }
    document.getElementById('coSubtotal').textContent = S.money(checkout.subtotal);

    // District + division selects
    var divisionSel = document.getElementById('divisionSelect');
    var districtSel = document.getElementById('districtSelect');
    if (divisionSel) {
      divisionSel.innerHTML = '<option value="">Select division</option>' +
        S.bdDivisions().map(function (d) { return '<option value="' + S.esc(d) + '">' + S.esc(d) + '</option>'; }).join('');
    }
    if (districtSel) {
      districtSel.innerHTML = '<option value="">Select district</option>' +
        S.zones().map(function (z) {
          return '<option value="' + S.esc(z.district) + '" data-division="' + S.esc(z.division || '') + '" data-fee="' + z.delivery_fee + '" data-free="' + z.free_delivery_threshold + '">' + S.esc(z.district) + '</option>';
        }).join('');
      var divSel = divisionSel;
      var distSel = districtSel;
      var feeDisplay = document.getElementById('deliveryFeeDisplay');
      var totalDisplay = document.getElementById('totalDisplay');

      function updateTotals() {
        var subtotal = checkout.subtotal;
        var fee = 0;
        if (distSel.value) {
          var opt = distSel.selectedOptions[0];
          fee = Number(opt.getAttribute('data-fee')) || 0;
          var free = Number(opt.getAttribute('data-free')) || 0;
          if (free > 0 && subtotal >= free) fee = 0;
        }
        if (feeDisplay) feeDisplay.textContent = fee > 0 ? S.money(fee) : 'Free';
        if (totalDisplay) totalDisplay.textContent = S.money(subtotal + fee);
      }
      function filterDistricts() {
        var div = divSel.value;
        Array.prototype.forEach.call(distSel.options, function (o) {
          if (!o.value) return;
          var show = !div || o.getAttribute('data-division') === div || o.getAttribute('data-division') === '';
          o.style.display = show ? '' : 'none';
        });
        if (distSel.value && distSel.selectedOptions[0] && distSel.selectedOptions[0].style.display !== 'none') return;
        distSel.value = '';
        updateTotals();
      }
      divSel.addEventListener('change', filterDistricts);
      distSel.addEventListener('change', updateTotals);
      filterDistricts();
      updateTotals();
    }

    var form = document.getElementById('checkoutForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(form);
        var data = {};
        fd.forEach(function (v, k) { data[k] = v; });

        var errors = [];
        function setError(field, msg) {
          errors.push(msg);
          var el = form.querySelector('[name="' + field + '"]');
          var wrap = el ? el.closest('.form-field') : null;
          if (wrap) {
            wrap.classList.add('has-error');
            var existing = wrap.querySelector('.form-error');
            if (existing) existing.remove();
            var sp = document.createElement('span');
            sp.className = 'form-error';
            sp.textContent = msg;
            wrap.appendChild(sp);
          }
        }
        form.querySelectorAll('.form-field.has-error').forEach(function (w) {
          w.classList.remove('has-error');
          var ex = w.querySelector('.form-error');
          if (ex) ex.remove();
        });
        if (String(data.full_name || '').trim().length < 3) setError('full_name', 'Please enter your full name.');
        if (!S.isValidBdPhone(data.phone)) setError('phone', 'Please enter a valid Bangladeshi phone number (e.g. 01712345678).');
        if (!S.isValidEmail(data.email)) setError('email', 'Email is required for order updates.');
        if (!data.division) setError('division', 'Please select your division.');
        if (!data.district) setError('district', 'Please select your district.');
        if (!String(data.upazila || '').trim()) setError('upazila', 'Please enter your upazila / thana.');
        if (String(data.address || '').trim().length < 10) setError('address', 'Please enter your full address (at least 10 characters).');
        if (!['cod', 'bkash', 'nagad'].indexOf(data.payment_method) >= 0 && !['cod', 'bkash', 'nagad'].includes(data.payment_method)) setError('payment_method', 'Please choose a payment method.');

        var generalEl = document.getElementById('checkoutGeneralError');
        if (generalEl) generalEl.innerHTML = '';

        if (errors.length) {
          if (generalEl) generalEl.innerHTML = '<div class="alert alert-error"><i data-lucide="alert-circle"></i><span>' + errors[0] + '</span></div>';
          initLucide();
          return;
        }

        var subtotal = checkout.subtotal;
        var zone = S.zoneByDistrict(data.district);
        var fee = zone ? Number(zone.delivery_fee) : 0;
        var free = zone ? Number(zone.free_delivery_threshold) : 0;
        if (free > 0 && subtotal >= free) fee = 0;

        // Coupon
        var discount = 0;
        var couponCode = null;
        var couponInput = String(data.coupon_code || '').toUpperCase().trim();
        if (couponInput) {
          var couponRes = S.applyCoupon(couponInput, subtotal);
          if (!couponRes.success) {
            if (generalEl) generalEl.innerHTML = '<div class="alert alert-error"><i data-lucide="alert-circle"></i><span>' + S.esc(couponRes.message) + '</span></div>';
            initLucide();
            return;
          }
          discount = couponRes.discount;
          couponCode = couponInput;
        }

        var total = Math.max(0, subtotal - discount) + fee;
        var items = checkout.items.map(function (i) {
          return { variant_id: i.variant_id, product_id: i.product_id, product_name: i.product_name, variant_name: i.variant_name, weight: i.weight, unit_price: i.unit_price, quantity: i.qty, line_total: i.line_total };
        });

        var order = S.createOrder({
          user_id: user ? user.id : null,
          full_name: data.full_name.trim(),
          phone: S.normalizePhone(data.phone),
          email: data.email.trim().toLowerCase(),
          division: data.division,
          district: data.district,
          upazila: data.upazila.trim(),
          address: data.address.trim(),
          delivery_note: data.delivery_note || '',
          delivery_zone_id: zone ? zone.id : null,
          subtotal: subtotal,
          delivery_fee: fee,
          discount: discount,
          coupon_code: couponCode,
          total: total,
          payment_method: data.payment_method,
          items: items
        });
        S.cartClear();
        window.location.href = 'pay.html?order=' + encodeURIComponent(order.order_number);
      });
    }

    // Payment method selection highlight
    document.querySelectorAll('.pay-method').forEach(function (label) {
      var input = label.querySelector('input[type="radio"]');
      input.addEventListener('change', function () {
        document.querySelectorAll('.pay-method').forEach(function (l) { l.classList.toggle('active', l.querySelector('input').checked); });
      });
      if (input.checked) label.classList.add('active');
    });
  }

  function renderPay() {
    var params = new URLSearchParams(window.location.search);
    var orderNumber = params.get('order') || '';
    var order = S.orderByNumber(orderNumber);
    var notFound = document.getElementById('payNotFound');
    if (!order) {
      if (notFound) notFound.style.display = '';
      document.getElementById('payContent').style.display = 'none';
      return;
    }
    if (notFound) notFound.style.display = 'none';

    // If already paid, go to success
    if (order.payment_status === 'paid') {
      window.location.href = 'order-success.html?order=' + encodeURIComponent(orderNumber);
      return;
    }

    var method = order.payment_method;
    var labels = { cod: 'Cash on Delivery', bkash: 'bKash', nagad: 'Nagad' };

    document.getElementById('payMethodLabel').textContent = labels[method] || method;
    document.getElementById('payMethodBadge').textContent = method === 'cod' ? 'COD' : method.toUpperCase();
    document.getElementById('payOrderNumber').textContent = order.order_number;

    var itemsEl = document.getElementById('payItems');
    if (itemsEl) {
      itemsEl.innerHTML = order.items.map(function (item) {
        return '<div class="os-item">' +
          '<span class="os-name">' + S.esc(item.product_name) + ' <span class="os-qty">×' + item.quantity + '</span></span>' +
          '<span class="os-price">' + S.money(item.line_total) + '</span></div>';
      }).join('');
    }
    document.getElementById('paySubtotal').textContent = S.money(order.subtotal);
    document.getElementById('payDelivery').textContent = S.money(order.delivery_fee);
    if (order.discount > 0) {
      var discRow = document.getElementById('payDiscountRow');
      discRow.style.display = 'flex';
      discRow.querySelector('.summary-row span:last-child').textContent = '-' + S.money(order.discount);
    }
    document.getElementById('payTotal').textContent = S.money(order.total);

    var payForm = document.getElementById('payForm');
    if (payForm) {
      payForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = payForm.querySelector('[type="submit"]');
        btn.classList.add('loading');
        btn.disabled = true;
        setTimeout(function () {
          var paymentObj;
          if (method === 'cod') {
            paymentObj = {
              method: 'cod',
              payment_id: 'COD-' + order.order_number,
              transaction_id: null,
              amount: order.total,
              status: 'processing',
              gateway_response: { cod: true, note: 'Cash on delivery — payment to be collected at the door.', demo: true, method: 'cod', order: order.order_number }
            };
            S.setOrderPayment(order.order_number, 'cod', paymentObj);
            S.updateOrderStatus(order.id, 'confirmed');
            S.updateOrderPayment(order.id, 'pending');
          } else {
            var txId = 'DEMO-' + method.toUpperCase() + '-' + Math.random().toString(16).slice(2, 10).toUpperCase();
            paymentObj = {
              method: method,
              payment_id: 'DEMO-' + method.toUpperCase() + '-' + order.order_number,
              transaction_id: txId,
              amount: order.total,
              status: 'paid',
              gateway_response: { status: 'paid', transaction_id: txId, demo: true, method: method, order: order.order_number }
            };
            S.setOrderPayment(order.order_number, method, paymentObj);
          }
          window.location.href = 'order-success.html?order=' + encodeURIComponent(order.order_number);
        }, 700);
      });
    }
    initLucide();
  }

  function renderOrderSuccess() {
    var params = new URLSearchParams(window.location.search);
    var orderNumber = params.get('order') || '';
    var order = S.orderByNumber(orderNumber);
    var notFound = document.getElementById('successNotFound');
    var content = document.getElementById('successContent');
    if (!order) {
      if (notFound) notFound.style.display = '';
      if (content) content.style.display = 'none';
      return;
    }
    if (notFound) notFound.style.display = 'none';
    if (content) content.style.display = '';
    document.getElementById('successOrderNo').textContent = 'Order Number: ' + order.order_number;

    var isDemo = order.payment && order.payment.gateway_response && order.payment.gateway_response.demo;
    var demoBanner = document.getElementById('demoPaymentBanner');
    if (demoBanner) demoBanner.style.display = isDemo ? '' : 'none';

    var itemsEl = document.getElementById('successItems');
    if (itemsEl) {
      itemsEl.innerHTML = order.items.map(function (item) {
        return '<div class="os-item" style="margin-bottom:10px">' +
          '<span class="os-name">' + S.esc(item.product_name) + ' <span class="os-qty">×' + item.quantity + '</span></span>' +
          '<span class="os-price">' + S.money(item.line_total) + '</span></div>';
      }).join('');
    }
    document.getElementById('successSubtotal').textContent = S.money(order.subtotal);
    document.getElementById('successDelivery').textContent = S.money(order.delivery_fee);
    var discRow = document.getElementById('successDiscountRow');
    if (order.discount > 0) {
      discRow.style.display = 'flex';
      discRow.querySelector('.summary-row span:last-child').textContent = '-' + S.money(order.discount);
    }
    document.getElementById('successTotal').textContent = S.money(order.total);
    document.getElementById('successDeliverTo').textContent = order.full_name + ', ' + order.phone;
    document.getElementById('successAddress').textContent = order.address + ', ' + order.upazila + ', ' + order.district + ', ' + order.division;
  }

  function renderTrackOrder() {
    var form = document.getElementById('trackForm');
    var results = document.getElementById('trackResults');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var orderNumber = String(form.querySelector('[name="order_number"]').value || '').toUpperCase().trim();
        var phone = S.normalizePhone(form.querySelector('[name="phone"]').value);
        if (!orderNumber) { showToast('Please enter your order number.', 'error'); return; }
        if (!S.isValidBdPhone(phone)) { showToast('Please enter the phone number used for this order.', 'error'); return; }
        var order = S.orderByNumber(orderNumber);
        if (!order || S.normalizePhone(order.phone) !== phone) {
          showToast('No order found with that order number and phone. Please check and try again.', 'error');
          return;
        }
        renderTrackResult(order);
      });
      // Autofill from query string if present
      var params = new URLSearchParams(window.location.search);
      var prefillOrder = params.get('order') || '';
      if (prefillOrder) {
        form.querySelector('[name="order_number"]').value = prefillOrder;
        var user = S.currentUser();
        if (user) form.querySelector('[name="phone"]').value = user.phone;
        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
      }
    }
  }

  function renderTrackResult(order) {
    var results = document.getElementById('trackResults');
    if (!results) return;
    var steps = [
      { key: 'pending', label: 'Order Placed', desc: 'We received your order' },
      { key: 'confirmed', label: 'Confirmed', desc: 'Order confirmed by our team' },
      { key: 'processing', label: 'Processing', desc: 'Fresh litchi being packed' },
      { key: 'shipped', label: 'Shipped', desc: 'On the way to your address' },
      { key: 'delivered', label: 'Delivered', desc: 'Delivered to your doorstep' }
    ];
    var statusOrder = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
    var currentIndex = statusOrder.indexOf(order.status);
    var dateStr = order.created_at ? new Date(order.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' }) : '';

    var badge = '';
    if (order.status === 'cancelled') badge = '<span class="badge badge-cancelled">Cancelled</span>';
    else {
      badge = '<span class="badge badge-' + order.status + '">' + order.status.charAt(0).toUpperCase() + order.status.slice(1) + '</span>';
      badge += ' &middot; ';
      if (order.payment_status === 'paid') badge += '<span class="badge badge-paid">Paid</span>';
      else if (order.payment_method === 'cod') badge += '<span class="badge badge-pending">Pay on Delivery</span>';
      else badge += '<span class="badge badge-' + order.payment_status + '">' + order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1) + '</span>';
    }

    var html = '<div class="checkout-form-card" style="text-align:center;margin-bottom:24px">' +
      '<p class="order-no">' + S.esc(order.order_number) + '</p>' +
      '<p style="color:var(--muted);font-size:14px">Placed on ' + dateStr + '</p>' +
      '<p style="margin-top:8px">' + badge + '</p></div>';

    if (order.status === 'cancelled') {
      html += '<div class="alert alert-error"><i data-lucide="alert-circle"></i><span>This order was cancelled. Please contact support for assistance.</span></div>';
    } else {
      html += '<div class="timeline">';
      steps.forEach(function (step) {
        var idx = statusOrder.indexOf(step.key);
        var state = idx < currentIndex ? 'done' : (idx === currentIndex ? 'active' : '');
        html += '<div class="timeline-step ' + state + '"><div class="tl-dot"></div>' +
          '<div class="tl-body"><b>' + step.label + '</b><span>' + step.desc + '</span></div></div>';
      });
      html += '</div>';
    }
    results.innerHTML = html;
    initLucide();
  }

  function renderAccount() {
    var user = S.currentUser();
    if (!user) {
      window.location.href = 'login.html';
      return;
    }
    document.getElementById('accountGreeting').textContent = 'Hello, ' + user.name + ' 👋';
    var fn = document.getElementById('accName');
    var em = document.getElementById('accEmail');
    var ph = document.getElementById('accPhone');
    var since = document.getElementById('accSince');
    if (fn) fn.value = user.name;
    if (em) em.value = user.email;
    if (ph) ph.value = user.phone;
    if (since) since.value = user.created_at ? new Date(user.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'Recently';

    var form = document.getElementById('profileForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var name = form.querySelector('[name="name"]').value.trim();
        var email = form.querySelector('[name="email"]').value.trim();
        var phone = form.querySelector('[name="phone"]').value;
        if (name.length < 3) { showToast('Please enter your full name.', 'error'); return; }
        if (!S.isValidEmail(email)) { showToast('Please enter a valid email.', 'error'); return; }
        if (!S.isValidBdPhone(phone)) { showToast('Please enter a valid Bangladeshi phone number.', 'error'); return; }
        var res = S.updateProfile(user.id, { name: name, email: email, phone: phone });
        showToast(res.success ? 'Profile updated successfully.' : res.message, res.success ? 'success' : 'error');
      });
    }

    var pwForm = document.getElementById('passwordForm');
    if (pwForm) {
      pwForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var current = pwForm.querySelector('[name="current_password"]').value;
        var newPass = pwForm.querySelector('[name="new_password"]').value;
        var confirm = pwForm.querySelector('[name="confirm_password"]').value;
        if (newPass !== confirm) { showToast('Passwords do not match.', 'error'); return; }
        var res = S.changePassword(user.id, current, newPass);
        showToast(res.success ? 'Password changed successfully.' : res.message, res.success ? 'success' : 'error');
        if (res.success) pwForm.reset();
      });
    }

    // Recent orders
    var ordersWrap = document.getElementById('recentOrdersWrap');
    var userOrders = S.ordersForUser(user.id).slice(0, 5);
    if (userOrders.length) {
      ordersWrap.style.display = '';
      var body = document.getElementById('recentOrdersBody');
      body.innerHTML = userOrders.map(function (o) {
        var dateStr = new Date(o.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        return '<tr>' +
          '<td><a href="order-detail.html?id=' + o.id + '">' + S.esc(o.order_number) + '</a></td>' +
          '<td>' + dateStr + '</td>' +
          '<td>' + S.money(o.total) + '</td>' +
          '<td><span class="badge badge-' + o.status + '">' + o.status.charAt(0).toUpperCase() + o.status.slice(1) + '</span></td>' +
          '<td><a href="order-detail.html?id=' + o.id + '" class="btn btn-ghost btn-sm">View</a></td>' +
          '</tr>';
      }).join('');
    }
  }

  function renderOrders() {
    var user = S.currentUser();
    if (!user) { window.location.href = 'login.html'; return; }
    var userOrders = S.ordersForUser(user.id);
    var empty = document.getElementById('ordersEmpty');
    var table = document.getElementById('ordersTable');
    if (!userOrders.length) {
      if (empty) empty.style.display = '';
      if (table) table.style.display = 'none';
      return;
    }
    if (empty) empty.style.display = 'none';
    if (table) table.style.display = '';
    var body = document.getElementById('ordersBody');
    body.innerHTML = userOrders.map(function (o) {
      var dateStr = new Date(o.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
      var payBadge = '';
      if (o.payment_status === 'paid') payBadge = '<span class="badge badge-paid">Paid</span>';
      else if (o.payment_method === 'cod') payBadge = '<span class="badge badge-pending">COD</span>';
      else payBadge = '<span class="badge badge-' + o.payment_status + '">' + o.payment_status.charAt(0).toUpperCase() + o.payment_status.slice(1) + '</span>';
      return '<tr>' +
        '<td><a href="order-detail.html?id=' + o.id + '">' + S.esc(o.order_number) + '</a></td>' +
        '<td>' + dateStr + '</td>' +
        '<td>' + o.items.length + ' item' + (o.items.length === 1 ? '' : 's') + '</td>' +
        '<td>' + S.money(o.total) + '</td>' +
        '<td><span class="badge badge-' + o.status + '">' + o.status.charAt(0).toUpperCase() + o.status.slice(1) + '</span></td>' +
        '<td>' + payBadge + '</td>' +
        '<td><div style="display:flex;gap:8px">' +
        '<a href="order-detail.html?id=' + o.id + '" class="btn btn-ghost btn-sm">Details</a>' +
        '<a href="track-order.html?order=' + encodeURIComponent(o.order_number) + '" class="btn btn-ghost btn-sm">Track</a>' +
        '</div></td>' +
        '</tr>';
    }).join('');
  }

  function renderOrderDetail() {
    var user = S.currentUser();
    if (!user) { window.location.href = 'login.html'; return; }
    var params = new URLSearchParams(window.location.search);
    var id = Number(params.get('id')) || 0;
    var order = S.orderById(id);
    var notFound = document.getElementById('orderNotFound');
    var content = document.getElementById('orderContent');
    if (!order || order.user_id !== user.id) {
      if (notFound) notFound.style.display = '';
      if (content) content.style.display = 'none';
      return;
    }
    if (notFound) notFound.style.display = 'none';
    if (content) content.style.display = '';

    var placedDate = new Date(order.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
    document.getElementById('odNumber').textContent = order.order_number;
    document.getElementById('odPlaced').textContent = 'Placed ' + placedDate;
    document.getElementById('odStatusBadge').textContent = order.status.charAt(0).toUpperCase() + order.status.slice(1);
    document.getElementById('odStatusBadge').className = 'badge badge-' + order.status;
    var payBadge = document.getElementById('odPayBadge');
    if (order.payment_status === 'paid') { payBadge.textContent = 'Paid'; payBadge.className = 'badge badge-paid'; }
    else if (order.payment_method === 'cod') { payBadge.textContent = 'Pay on Delivery'; payBadge.className = 'badge badge-pending'; }
    else { payBadge.textContent = order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1); payBadge.className = 'badge badge-' + order.payment_status; }

    var itemsEl = document.getElementById('odItems');
    itemsEl.innerHTML = order.items.map(function (item) {
      return '<div class="os-item" style="margin-bottom:12px">' +
        '<span class="os-name">' + S.esc(item.product_name) + ' <span class="os-qty">×' + item.quantity + '</span></span>' +
        '<span class="os-price">' + S.money(item.line_total) + '</span></div>';
    }).join('');
    document.getElementById('odSubtotal').textContent = S.money(order.subtotal);
    document.getElementById('odDelivery').textContent = S.money(order.delivery_fee);
    var odDiscountRow = document.getElementById('odDiscountRow');
    if (order.discount > 0) {
      odDiscountRow.style.display = 'flex';
      odDiscountRow.querySelector('.summary-row span:last-child').textContent = '-' + S.money(order.discount);
    }
    document.getElementById('odTotal').textContent = S.money(order.total);
    document.getElementById('odCustomer').textContent = order.full_name + ' — ' + order.phone + (order.email ? ' (' + order.email + ')' : '');
    document.getElementById('odAddress').textContent = order.address + ', ' + order.upazila + ', ' + order.district + ', ' + order.division;
    var noteEl = document.getElementById('odNote');
    if (order.delivery_note) { noteEl.textContent = 'Note: ' + order.delivery_note; noteEl.style.display = ''; }

    document.getElementById('odTrackBtn').href = 'track-order.html?order=' + encodeURIComponent(order.order_number);
  }

  function initLogin() {
    var form = document.getElementById('loginForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var login = form.querySelector('[name="login"]').value.trim();
      var password = form.querySelector('[name="password"]').value;
      var res = S.loginUser(login, password);
      if (res.success) {
        var redirect = new URLSearchParams(window.location.search).get('redirect') || 'account.html';
        window.location.href = redirect;
      } else {
        var el = document.getElementById('loginGeneralError');
        if (el) el.innerHTML = '<div class="alert alert-error"><i data-lucide="alert-circle"></i><span>' + S.esc(res.message) + '</span></div>';
        initLucide();
      }
    });
  }

  function initRegister() {
    var form = document.getElementById('registerForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = form.querySelector('[name="name"]').value.trim();
      var email = form.querySelector('[name="email"]').value.trim();
      var phone = form.querySelector('[name="phone"]').value;
      var password = form.querySelector('[name="password"]').value;
      var confirm = form.querySelector('[name="password_confirm"]').value;
      var errEl = document.getElementById('registerGeneralError');
      if (errEl) errEl.innerHTML = '';

      function fail(msg) {
        if (errEl) errEl.innerHTML = '<div class="alert alert-error"><i data-lucide="alert-circle"></i><span>' + S.esc(msg) + '</span></div>';
        initLucide();
      }
      if (name.length < 3) return fail('Please enter your full name.');
      if (!S.isValidEmail(email)) return fail('Please enter a valid email address.');
      if (!S.isValidBdPhone(phone)) return fail('Please enter a valid Bangladeshi phone number (e.g. 01712345678).');
      if (password.length < 8) return fail('Password must be at least 8 characters.');
      if (password !== confirm) return fail('Passwords do not match.');
      var res = S.registerUser({ name: name, email: email, phone: phone, password: password });
      if (res.success) {
        window.location.href = 'account.html';
      } else {
        fail(res.message);
      }
    });
  }

  function initContact() {
    var form = document.getElementById('contactForm');
    if (!form) return;
    var successEl = document.getElementById('contactSuccess');
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = form.querySelector('[name="name"]').value.trim();
      var email = form.querySelector('[name="email"]').value.trim();
      var phone = form.querySelector('[name="phone"]').value;
      var subject = form.querySelector('[name="subject"]').value.trim();
      var message = form.querySelector('[name="message"]').value.trim();

      function fail(msg) {
        var el = document.getElementById('contactGeneralError');
        if (el) el.innerHTML = '<div class="alert alert-error"><i data-lucide="alert-circle"></i><span>' + S.esc(msg) + '</span></div>';
        initLucide();
      }
      if (name.length < 3) return fail('Please enter your name.');
      if (!S.isValidEmail(email)) return fail('Please enter a valid email.');
      if (phone && !S.isValidBdPhone(phone)) return fail('Please enter a valid Bangladeshi phone number.');
      if (message.length < 10) return fail('Please write a message of at least 10 characters.');
      S.addMessage({ name: name, email: email, phone: phone, subject: subject, message: message });
      form.reset();
      if (successEl) successEl.innerHTML = '<div class="alert alert-success"><i data-lucide="check-circle-2"></i><span>Thank you! Your message has been sent. We\'ll get back to you soon.</span></div>';
      initLucide();
    });
  }

  /* ---------- Init ---------- */
  function init() {
    var page = window.LL_PAGE || '';
    if (page === 'index') renderHome();
    if (page === 'shop') renderShop();
    if (page === 'product') renderProduct();
    if (page === 'cart') renderCart();
    if (page === 'checkout') renderCheckout();
    if (page === 'pay') renderPay();
    if (page === 'order-success') renderOrderSuccess();
    if (page === 'track-order') renderTrackOrder();
    if (page === 'account') renderAccount();
    if (page === 'orders') renderOrders();
    if (page === 'order-detail') renderOrderDetail();
    if (page === 'login') initLogin();
    if (page === 'register') initRegister();
    if (page === 'contact') initContact();

    initLucide();
    initNav();
    initSearch();
    initQtyControls();
    initGallery();
    initTabs();
    initFaq();
    initShopFilters();
    initPasswordToggle();
    initNewsletter();
    updateCartBadge();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.LL = { showToast: showToast };
})();
