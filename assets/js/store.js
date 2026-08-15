/* ============================================================
   Lichi Lover — Store (data layer)
   Replaces the PHP/MySQL backend with localStorage.
   - Hydrates seed data from window.LL_SEED on first load
   - Provides cart, auth, orders, reviews, messages, settings
   ============================================================ */

(function () {
  'use strict';

  var LS_PREFIX = 'll_';
  var DB_VERSION = '1.2';
  var db = {};
  // In-memory fallback for browsers that block localStorage (private mode,
  // strict privacy settings). Keeps the site functional for the page session.
  var memory = {};
  var hasStorage = (function () {
    try {
      var k = '__ll_test__';
      localStorage.setItem(k, '1');
      localStorage.removeItem(k);
      return true;
    } catch (e) {
      return false;
    }
  })();

  /* ---------- Persistence helpers ---------- */

  function storageGet(key) {
    if (hasStorage) {
      try { return localStorage.getItem(key); } catch (e) { /* ignore */ }
    }
    return Object.prototype.hasOwnProperty.call(memory, key) ? memory[key] : null;
  }
  function storageSet(key, value) {
    if (hasStorage) {
      try { localStorage.setItem(key, value); return; } catch (e) { /* ignore */ }
    }
    memory[key] = value;
  }
  function storageRemove(key) {
    if (hasStorage) {
      try { localStorage.removeItem(key); } catch (e) { /* ignore */ }
    }
    delete memory[key];
  }

  function load(key, fallback) {
    try {
      var raw = storageGet(LS_PREFIX + key);
      if (raw === null) return fallback;
      return JSON.parse(raw);
    } catch (e) {
      return fallback;
    }
  }

  function save(key, value) {
    storageSet(LS_PREFIX + key, JSON.stringify(value));
  }

  function uid() {
    return Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
  }

  function nowIso() {
    return new Date().toISOString();
  }

  /* ---------- DB hydration ---------- */

  function hydrate() {
    var seeded = load('seeded', false);
    if (seeded === DB_VERSION && window.LL_SEED) {
      // ensure settings exist
      if (!load('settings', null)) save('settings', window.LL_SEED.settings);
      return;
    }
    var seed = window.LL_SEED;
    if (!seed) {
      db.products = load('products', []);
      db.settings = load('settings', {});
      return;
    }
    save('settings', JSON.parse(JSON.stringify(seed.settings)));
    save('categories', JSON.parse(JSON.stringify(seed.categories)));
    save('products', JSON.parse(JSON.stringify(seed.products)));
    save('variants', JSON.parse(JSON.stringify(seed.variants)));
    save('zones', JSON.parse(JSON.stringify(seed.zones)));
    save('coupons', JSON.parse(JSON.stringify(seed.coupons)));
    save('admins', JSON.parse(JSON.stringify(seed.admins)));
    save('users', JSON.parse(JSON.stringify(seed.users)));
    // Reviews: store created_at timestamps for display
    var reviews = JSON.parse(JSON.stringify(seed.reviews));
    reviews.forEach(function (r) {
      if (!r.created_at) r.created_at = nowIso();
    });
    save('reviews', reviews);
    // Orders: seed demo orders (incl. the demo customer's) so the admin panel
    // shows real data in any browser. created_at is spread over the last week
    // so the dashboard chart has data; order numbers get a fresh year prefix.
    var orders = JSON.parse(JSON.stringify(seed.orders || []));
    var year = new Date().getFullYear();
    orders.forEach(function (o, i) {
      if (!o.created_at) {
        var ago = Number(o._days_ago) || 0;
        o.created_at = new Date(Date.now() - ago * 86400000).toISOString();
      }
      o.updated_at = o.created_at;
      delete o._days_ago;
      if (!o.order_number) o.order_number = 'LL-' + year + '-' + ('000000' + (i + 1)).slice(-6);
    });
    save('orders', orders);
    save('messages', []);
    save('newsletter', []);
    save('seeded', DB_VERSION);
  }

  function getCollection(name) {
    return load(name, []);
  }
  function setCollection(name, list) {
    save(name, list);
    return list;
  }
  function nextId(collectionName) {
    var list = getCollection(collectionName);
    var max = 0;
    list.forEach(function (item) { if (item.id > max) max = item.id; });
    return max + 1;
  }

  /* ---------- Settings ---------- */

  function settings() {
    return load('settings', window.LL_SEED ? window.LL_SEED.settings : {});
  }
  function setting(key, fallback) {
    var s = settings();
    var v = s[key];
    return (v === undefined || v === null || v === '') ? fallback : v;
  }
  function saveSettings(obj) {
    save('settings', obj);
  }

  /* ---------- Money / helpers ---------- */

  function money(amount) {
    var n = Number(amount) || 0;
    var formatted = n % 1 === 0 ? n.toLocaleString('en-US', { maximumFractionDigits: 0 }) : n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return '\u09F3' + formatted;
  }
  function moneyPlain(amount) {
    return (Number(amount) || 0).toLocaleString('en-US', { maximumFractionDigits: 0 });
  }
  function esc(value) {
    return String(value === null || value === undefined ? '' : value)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  function slugify(text) {
    return String(text).toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }
  function truncate(text, length, suffix) {
    text = String(text || '');
    length = length || 120;
    suffix = suffix === undefined ? '...' : suffix;
    if (text.length <= length) return text;
    return text.substring(0, length).replace(/\s+\S*$/, '') + suffix;
  }
  function stripTags(html) {
    var div = document.createElement('div');
    div.innerHTML = html || '';
    return div.textContent || div.innerText || '';
  }
  function bdDivisions() {
    return ['Dhaka', 'Chattogram', 'Rajshahi', 'Khulna', 'Barishal', 'Sylhet', 'Rangpur', 'Mymensingh'];
  }
  function normalizePhone(phone) {
    phone = String(phone || '').replace(/[^0-9]/g, '');
    if (phone.indexOf('880') === 0) phone = '0' + phone.slice(3);
    return phone;
  }
  function isValidBdPhone(phone) {
    phone = normalizePhone(phone);
    return /^01[3-9]\d{8}$/.test(phone);
  }
  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email || '').trim());
  }
  function imageUrl(key) {
    var s = window.LL_SEED;
    if (s && s.images) {
      var img = s.images[key];
      if (img) return typeof img === 'string' ? img : img.url;
    }
    return 'https://images.pexels.com/photos/16820482/pexels-photo-16820482.jpeg?auto=compress&cs=tinysrgb&w=900';
  }
  function imageAlt(key) {
    var s = window.LL_SEED;
    if (s && s.images && s.images[key] && typeof s.images[key] === 'object') {
      return s.images[key].alt;
    }
    return 'Lichi Lover fruit';
  }
  function starsHtml(rating, max) {
    max = max || 5;
    rating = Math.round(Number(rating) || 0);
    var filled = '';
    for (var i = 0; i < rating; i++) filled += '\u2605';
    var empty = '';
    for (var j = rating; j < max; j++) empty += '\u2605';
    return '<span class="stars">' + filled + '<span style="color:var(--line)">' + empty + '</span></span>';
  }

  /* ---------- Products / categories / variants ---------- */

  function categories(includeInactive) {
    var list = getCollection('categories');
    return list.filter(function (c) { return includeInactive || c.is_active === 1; })
      .sort(function (a, b) { return (a.sort_order || 0) - (b.sort_order || 0) || a.id - b.id; });
  }
  function categoryBySlug(slug) {
    var list = getCollection('categories');
    for (var i = 0; i < list.length; i++) if (list[i].slug === slug) return list[i];
    return null;
  }
  function productCountForCategory(categoryId) {
    return getCollection('products').filter(function (p) { return p.category_id === categoryId && p.is_active === 1; }).length;
  }

  function products() {
    return getCollection('products');
  }
  function productById(id) {
    id = Number(id);
    var list = getCollection('products');
    for (var i = 0; i < list.length; i++) if (list[i].id === id) return list[i];
    return null;
  }
  function variantsFor(productId) {
    productId = Number(productId);
    return getCollection('variants')
      .filter(function (v) { return v.product_id === productId && v.is_active === 1; })
      .sort(function (a, b) { return (b.is_default || 0) - (a.is_default || 0) || a.price - b.price; });
  }
  function variantById(id) {
    id = Number(id);
    var list = getCollection('variants');
    for (var i = 0; i < list.length; i++) if (list[i].id === id) return list[i];
    return null;
  }
  function productMinPrice(p) {
    var vs = getCollection('variants').filter(function (v) { return v.product_id === p.id && v.is_active === 1; });
    if (vs.length) {
      var prices = vs.map(function (v) { return v.price; });
      return Math.min.apply(null, prices);
    }
    return Number(p.base_price) || 0;
  }
  function productMinWeight(p) {
    var vs = getCollection('variants').filter(function (v) { return v.product_id === p.id && v.is_active === 1; });
    if (vs.length) {
      var weights = vs.map(function (v) { return Number(v.weight) || 0; });
      return Math.min.apply(null, weights);
    }
    return 0;
  }
  function defaultVariantForProduct(productId) {
    var vs = variantsFor(productId);
    if (!vs.length) return null;
    for (var i = 0; i < vs.length; i++) if (vs[i].is_default) return vs[i];
    return vs[0];
  }

  function productCard(p) {
    var cat = null;
    categories(true).forEach(function (c) { if (c.id === p.category_id) cat = c; });
    return {
      id: p.id,
      name: p.name,
      slug: p.slug,
      category_name: cat ? cat.name : 'Lichi',
      category_slug: cat ? cat.slug : '',
      image: p.image || imageUrl('lychee_live'),
      min_price: productMinPrice(p),
      compare_price: p.compare_price,
      stock_qty: p.stock_qty,
      rating_avg: Number(p.rating_avg) || 0,
      rating_count: Number(p.rating_count) || 0,
      sold_count: Number(p.sold_count) || 0
    };
  }

  /* ---------- Cart ---------- */

  function cartRaw() {
    return load('cart', {});
  }
  function cartCount() {
    var raw = cartRaw();
    var sum = 0;
    for (var k in raw) if (Object.prototype.hasOwnProperty.call(raw, k)) sum += Number(raw[k]) || 0;
    return sum;
  }
  function cartItems() {
    var raw = cartRaw();
    var items = [];
    var subtotal = 0;
    var count = 0;
    for (var vid in raw) {
      if (!Object.prototype.hasOwnProperty.call(raw, vid)) continue;
      var variant = variantById(Number(vid));
      if (!variant) continue;
      var product = productById(variant.product_id);
      if (!product || product.is_active !== 1) continue;
      var qty = Math.min(Number(raw[vid]) || 0, 50);
      if (qty <= 0) continue;
      var lineTotal = Number(variant.price) * qty;
      subtotal += lineTotal;
      count += qty;
      items.push({
        variant_id: Number(vid),
        product_id: product.id,
        product_name: product.name,
        slug: product.slug,
        image: product.image || imageUrl('lychee_live'),
        variant_name: variant.name,
        weight: Number(variant.weight) || 0,
        unit_price: Number(variant.price),
        compare_price: variant.compare_price ? Number(variant.compare_price) : null,
        stock_qty: Number(variant.stock_qty) || 0,
        qty: qty,
        line_total: lineTotal
      });
    }
    return { items: items, subtotal: subtotal, count: count };
  }
  function cartAdd(variantId, qty) {
    variantId = Number(variantId);
    qty = Math.max(1, Math.min(Number(qty) || 1, 50));
    var variant = variantById(variantId);
    if (!variant) return false;
    var raw = cartRaw();
    var existing = Number(raw[variantId]) || 0;
    if (variant.stock_qty > 0) {
      raw[variantId] = Math.min(Number(variant.stock_qty), existing + qty);
    } else {
      raw[variantId] = existing + qty;
    }
    save('cart', raw);
    return true;
  }
  function cartUpdate(variantId, qty) {
    variantId = Number(variantId);
    qty = Number(qty) || 0;
    var raw = cartRaw();
    if (qty <= 0) {
      delete raw[variantId];
    } else {
      raw[variantId] = Math.min(qty, 50);
    }
    save('cart', raw);
  }
  function cartRemove(variantId) {
    variantId = Number(variantId);
    var raw = cartRaw();
    delete raw[variantId];
    save('cart', raw);
  }
  function cartClear() {
    save('cart', {});
  }

  /* ---------- Auth (customer) ---------- */

  function currentUser() {
    var id = load('session_user', null);
    if (!id) return null;
    var list = getCollection('users');
    for (var i = 0; i < list.length; i++) if (list[i].id === id && list[i].is_active === 1) return list[i];
    return null;
  }
  function loginUser(login, password) {
    var list = getCollection('users');
    for (var i = 0; i < list.length; i++) {
      var u = list[i];
      if ((u.email.toLowerCase() === String(login).toLowerCase() || normalizePhone(u.phone) === normalizePhone(login)) && u.is_active === 1 && u.password === password) {
        save('session_user', u.id);
        return { success: true, user: u };
      }
    }
    return { success: false, message: 'Invalid login credentials. Please try again.' };
  }
  function registerUser(data) {
    var list = getCollection('users');
    var email = String(data.email || '').trim().toLowerCase();
    var phone = normalizePhone(data.phone);
    for (var i = 0; i < list.length; i++) {
      if (list[i].email.toLowerCase() === email || normalizePhone(list[i].phone) === phone) {
        return { success: false, message: 'An account with this email or phone already exists. Please login.' };
      }
    }
    var user = {
      id: nextId('users'),
      name: String(data.name || '').trim(),
      email: email,
      phone: phone,
      password: String(data.password || ''),
      is_active: 1
    };
    list.push(user);
    setCollection('users', list);
    save('session_user', user.id);
    return { success: true, user: user };
  }
  function logoutUser() {
    storageRemove(LS_PREFIX + 'session_user');
  }
  function updateProfile(id, data) {
    var list = getCollection('users');
    for (var i = 0; i < list.length; i++) {
      if (list[i].id === id) {
        var email = String(data.email || '').trim().toLowerCase();
        var phone = normalizePhone(data.phone);
        for (var j = 0; j < list.length; j++) {
          if (j !== i && (list[j].email.toLowerCase() === email || normalizePhone(list[j].phone) === phone)) {
            return { success: false, message: 'Another account already uses this email or phone.' };
          }
        }
        list[i].name = String(data.name || '').trim();
        list[i].email = email;
        list[i].phone = phone;
        setCollection('users', list);
        return { success: true };
      }
    }
    return { success: false, message: 'User not found.' };
  }
  function changePassword(id, current, newPass) {
    var list = getCollection('users');
    for (var i = 0; i < list.length; i++) {
      if (list[i].id === id) {
        if (list[i].password !== current) return { success: false, field: 'current_password', message: 'Your current password is incorrect.' };
        if (String(newPass).length < 8) return { success: false, field: 'new_password', message: 'New password must be at least 8 characters.' };
        list[i].password = String(newPass);
        setCollection('users', list);
        return { success: true };
      }
    }
    return { success: false, message: 'User not found.' };
  }

  /* ---------- Admin auth ---------- */

  function currentAdmin() {
    var id = load('session_admin', null);
    if (!id) return null;
    var list = getCollection('admins');
    for (var i = 0; i < list.length; i++) if (list[i].id === id && list[i].is_active === 1) return list[i];
    return null;
  }
  function loginAdmin(email, password) {
    var list = getCollection('admins');
    for (var i = 0; i < list.length; i++) {
      var a = list[i];
      if (a.email.toLowerCase() === String(email).toLowerCase() && a.is_active === 1 && a.password === password) {
        save('session_admin', a.id);
        return { success: true, admin: a };
      }
    }
    return { success: false, message: 'Invalid admin credentials.' };
  }
  function logoutAdmin() {
    storageRemove(LS_PREFIX + 'session_admin');
  }
  function changeAdminPassword(id, current, newPass) {
    var list = getCollection('admins');
    for (var i = 0; i < list.length; i++) {
      if (list[i].id === id) {
        if (list[i].password !== current) return { success: false, field: 'current_password', message: 'Current password is incorrect.' };
        if (String(newPass).length < 8) return { success: false, field: 'new_password', message: 'New password must be at least 8 characters.' };
        list[i].password = String(newPass);
        list[i].must_change_password = 0;
        setCollection('admins', list);
        return { success: true };
      }
    }
    return { success: false, message: 'Admin not found.' };
  }

  /* ---------- Delivery zones ---------- */

  function zones() {
    return getCollection('zones').filter(function (z) { return z.is_active === 1; })
      .sort(function (a, b) { return (a.sort_order || 0) - (b.sort_order || 0); });
  }
  function zoneByDistrict(district) {
    var list = getCollection('zones');
    for (var i = 0; i < list.length; i++) if (list[i].district === district && list[i].is_active === 1) return list[i];
    for (var j = 0; j < list.length; j++) if (list[j].district === 'Other Districts' && list[j].is_active === 1) return list[j];
    return null;
  }
  function deliveryFeeFor(district) {
    var z = zoneByDistrict(district);
    return z ? Number(z.delivery_fee) || 0 : 0;
  }
  function freeDeliveryThresholdFor(district) {
    var z = zoneByDistrict(district);
    return z ? Number(z.free_delivery_threshold) || 0 : 0;
  }

  /* ---------- Coupons ---------- */

  function coupons() {
    return getCollection('coupons');
  }
  function applyCoupon(code, subtotal) {
    var list = getCollection('coupons');
    for (var i = 0; i < list.length; i++) {
      var c = list[i];
      if (c.code === String(code).toUpperCase() && c.is_active === 1) {
        var now = new Date().toISOString().slice(0, 10);
        if (c.expires_at && c.expires_at < now) return { success: false, message: 'This coupon has expired.' };
        if (c.usage_limit > 0 && Number(c.used_count) >= Number(c.usage_limit)) return { success: false, message: 'This coupon has reached its usage limit.' };
        if (subtotal < Number(c.min_order)) return { success: false, message: 'This coupon requires a minimum order of ' + money(c.min_order) + '.' };
        var discount = 0;
        if (c.discount_type === 'percent') {
          discount = subtotal * (Number(c.discount_value) / 100);
          if (c.max_discount && discount > Number(c.max_discount)) discount = Number(c.max_discount);
        } else {
          discount = Math.min(Number(c.discount_value), subtotal);
        }
        return { success: true, discount: discount, coupon: c };
      }
    }
    return { success: false, message: 'Invalid coupon code.' };
  }
  function markCouponUsed(code) {
    var list = getCollection('coupons');
    for (var i = 0; i < list.length; i++) {
      if (list[i].code === code) {
        list[i].used_count = Number(list[i].used_count) + 1;
        break;
      }
    }
    setCollection('coupons', list);
  }

  /* ---------- Orders ---------- */

  function orders() {
    return getCollection('orders').slice().sort(function (a, b) {
      return String(b.created_at).localeCompare(String(a.created_at));
    });
  }
  function orderById(id) {
    id = Number(id);
    var list = getCollection('orders');
    for (var i = 0; i < list.length; i++) if (list[i].id === id) return list[i];
    return null;
  }
  function orderByNumber(num) {
    var list = getCollection('orders');
    for (var i = 0; i < list.length; i++) if (list[i].order_number === String(num).toUpperCase()) return list[i];
    return null;
  }
  function ordersForUser(userId) {
    return orders().filter(function (o) { return o.user_id === Number(userId); });
  }
  function createOrder(data) {
    var list = getCollection('orders');
    var year = new Date().getFullYear();
    var seq = 0;
    list.forEach(function (o) {
      if (String(o.order_number).indexOf('LL-' + year + '-') === 0) {
        var n = Number(o.order_number.split('-').pop());
        if (n > seq) seq = n;
      }
    });
    seq++;
    var orderNumber = 'LL-' + year + '-' + ('000000' + seq).slice(-6);
    var order = {
      id: nextId('orders'),
      order_number: orderNumber,
      user_id: data.user_id || null,
      full_name: data.full_name,
      phone: data.phone,
      email: data.email,
      division: data.division,
      district: data.district,
      upazila: data.upazila,
      address: data.address,
      delivery_note: data.delivery_note || '',
      delivery_zone_id: data.delivery_zone_id || null,
      subtotal: Number(data.subtotal) || 0,
      delivery_fee: Number(data.delivery_fee) || 0,
      discount: Number(data.discount) || 0,
      coupon_code: data.coupon_code || null,
      total: Number(data.total) || 0,
      status: 'pending',
      payment_method: data.payment_method || 'cod',
      payment_status: 'pending',
      items: data.items || [],
      payment: null,
      created_at: nowIso(),
      updated_at: nowIso()
    };
    list.push(order);
    setCollection('orders', list);

    // Decrement stock + increment sold
    var variants = getCollection('variants');
    var products = getCollection('products');
    (order.items).forEach(function (item) {
      var qty = Number(item.quantity) || Number(item.qty) || 0;
      variants.forEach(function (v) {
        if (v.id === item.variant_id && v.stock_qty > 0) v.stock_qty = Math.max(0, Number(v.stock_qty) - qty);
      });
      products.forEach(function (p) {
        if (p.id === item.product_id) {
          p.stock_qty = Math.max(0, Number(p.stock_qty) - qty);
          p.sold_count = Number(p.sold_count) + qty;
        }
      });
    });
    setCollection('variants', variants);
    setCollection('products', products);

    if (order.coupon_code) markCouponUsed(order.coupon_code);
    return order;
  }
  function updateOrderStatus(orderId, status) {
    var list = getCollection('orders');
    for (var i = 0; i < list.length; i++) {
      if (list[i].id === Number(orderId)) {
        list[i].status = status;
        list[i].updated_at = nowIso();
        if (status === 'delivered' && list[i].payment_method === 'cod') list[i].payment_status = 'paid';
        break;
      }
    }
    setCollection('orders', list);
  }
  function updateOrderPayment(orderId, paymentStatus) {
    var list = getCollection('orders');
    for (var i = 0; i < list.length; i++) {
      if (list[i].id === Number(orderId)) {
        list[i].payment_status = paymentStatus;
        list[i].updated_at = nowIso();
        break;
      }
    }
    setCollection('orders', list);
  }
  function setOrderPayment(orderNumber, method, paymentObj) {
    var list = getCollection('orders');
    for (var i = 0; i < list.length; i++) {
      if (list[i].order_number === orderNumber) {
        list[i].payment = paymentObj || null;
        if (paymentObj && paymentObj.status === 'paid') {
          list[i].payment_status = 'paid';
          list[i].status = 'confirmed';
        } else if (paymentObj && paymentObj.status === 'failed') {
          list[i].payment_status = 'failed';
        }
        list[i].updated_at = nowIso();
        break;
      }
    }
    setCollection('orders', list);
  }

  /* ---------- Reviews ---------- */

  function reviews() {
    return getCollection('reviews').slice().sort(function (a, b) {
      return String(b.created_at || '').localeCompare(String(a.created_at || ''));
    });
  }
  function reviewsForProduct(productId) {
    return reviews().filter(function (r) { return r.product_id === Number(productId) && r.status === 'approved'; });
  }
  function addReview(productId, userId, rating, reviewText) {
    var list = getCollection('reviews');
    var review = {
      id: nextId('reviews'),
      product_id: Number(productId),
      user_id: Number(userId),
      rating: Math.max(1, Math.min(5, Number(rating))),
      review: String(reviewText || '').trim(),
      status: 'pending',
      is_demo: 0,
      created_at: nowIso()
    };
    list.push(review);
    setCollection('reviews', list);
  }
  function reviewAction(reviewId, action) {
    var list = getCollection('reviews');
    for (var i = 0; i < list.length; i++) {
      if (list[i].id === Number(reviewId)) {
        if (action === 'approve') list[i].status = 'approved';
        if (action === 'reject') list[i].status = 'rejected';
        if (action === 'delete') { list.splice(i, 1); break; }
      }
    }
    setCollection('reviews', list);
  }

  /* ---------- Contact messages ---------- */

  function messages() {
    return getCollection('messages').slice().sort(function (a, b) {
      return String(b.created_at || '').localeCompare(String(a.created_at || ''));
    });
  }
  function addMessage(data) {
    var list = getCollection('messages');
    list.push({
      id: nextId('messages'),
      name: data.name,
      email: data.email,
      phone: data.phone || '',
      subject: data.subject || '',
      message: data.message,
      is_read: 0,
      created_at: nowIso()
    });
    setCollection('messages', list);
  }
  function messageAction(id, action) {
    var list = getCollection('messages');
    for (var i = 0; i < list.length; i++) {
      if (list[i].id === Number(id)) {
        if (action === 'mark_read') list[i].is_read = 1;
        if (action === 'delete') { list.splice(i, 1); break; }
      }
    }
    setCollection('messages', list);
  }

  /* ---------- Newsletter ---------- */

  function newsletterSubscribe(email) {
    var list = load('newsletter', []);
    if (list.indexOf(email) === -1) list.push(email);
    save('newsletter', list);
  }

  /* ---------- Admin CRUD ---------- */

  function slugUnique(base) {
    var products = getCollection('products');
    var slug = base, i = 1;
    var taken = function (s) {
      for (var k = 0; k < products.length; k++) if (products[k].slug === s) return true;
      return false;
    };
    while (taken(slug)) slug = base + '-' + (++i);
    return slug;
  }

  function createProduct(data, variants) {
    var products = getCollection('products');
    var variantsList = getCollection('variants');
    var id = nextId('products');
    var slug = slugUnique(slugify(data.name));
    var prices = variants.map(function (v) { return Number(v.price); });
    var minPrice = Math.min.apply(null, prices);
    var totalStock = 0;
    variants.forEach(function (v) { totalStock += Number(v.stock) || 0; });
    var image = String(data.image || '').trim() || imageUrl('lychee_live');
    var product = {
      id: id,
      category_id: Number(data.category_id),
      name: data.name,
      slug: slug,
      short_description: data.short_description || '',
      description: data.description || '',
      base_price: minPrice,
      compare_price: data.compare_price !== '' && data.compare_price !== undefined && data.compare_price !== null ? Number(data.compare_price) : null,
      image: image,
      gallery: [image],
      stock_qty: totalStock,
      sold_count: 0,
      rating_avg: 0,
      rating_count: 0,
      is_featured: data.is_featured ? 1 : 0,
      is_active: data.is_active ? 1 : 0,
      created_at: nowIso()
    };
    products.push(product);
    setCollection('products', products);

    var first = true;
    variants.forEach(function (v) {
      variantsList.push({
        id: nextId('variants'),
        product_id: id,
        name: v.name,
        weight: Number(v.weight) || 0,
        price: Number(v.price),
        compare_price: v.compare !== '' && v.compare !== undefined && v.compare !== null ? Number(v.compare) : null,
        stock_qty: Number(v.stock) || 0,
        is_default: first ? 1 : 0,
        is_active: 1
      });
      first = false;
    });
    setCollection('variants', variantsList);
    return product;
  }

  function updateProduct(id, data, variants, variantIds, variantDefault) {
    var products = getCollection('products');
    var variantsList = getCollection('variants');
    var product = null;
    products.forEach(function (p) { if (p.id === Number(id)) product = p; });
    if (!product) return null;

    var prices = variants.map(function (v) { return Number(v.price); });
    var minPrice = Math.min.apply(null, prices);
    var totalStock = 0;
    variants.forEach(function (v) { totalStock += Number(v.stock) || 0; });
    var image = String(data.image || '').trim() || product.image;

    product.category_id = Number(data.category_id);
    product.name = data.name;
    product.short_description = data.short_description || '';
    product.description = data.description || '';
    product.base_price = minPrice;
    product.compare_price = data.compare_price !== '' && data.compare_price !== undefined && data.compare_price !== null ? Number(data.compare_price) : null;
    product.image = image;
    product.stock_qty = totalStock;
    product.is_featured = data.is_featured ? 1 : 0;
    product.is_active = data.is_active ? 1 : 0;
    setCollection('products', products);

    var keptIds = [];
    var first = true;
    variants.forEach(function (v, i) {
      var vid = variantIds && variantIds[i] ? Number(variantIds[i]) : 0;
      var isDefault = (vid > 0 && vid === Number(variantDefault)) || (!Number(variantDefault) && first);
      if (vid > 0) {
        variantsList.forEach(function (x) {
          if (x.id === vid && x.product_id === product.id) {
            x.name = v.name;
            x.weight = Number(v.weight) || 0;
            x.price = Number(v.price);
            x.compare_price = v.compare !== '' && v.compare !== undefined && v.compare !== null ? Number(v.compare) : null;
            x.stock_qty = Number(v.stock) || 0;
            x.is_default = isDefault ? 1 : 0;
          }
        });
        keptIds.push(vid);
      }
      first = false;
    });
    variantsList = variantsList.filter(function (x) {
      if (x.product_id !== product.id) return true;
      return keptIds.indexOf(x.id) !== -1;
    });
    first = true;
    variants.forEach(function (v, i) {
      var vid = variantIds && variantIds[i] ? Number(variantIds[i]) : 0;
      if (vid > 0) return;
      var isDefault = !Number(variantDefault) && first;
      variantsList.push({
        id: nextId('variants'),
        product_id: product.id,
        name: v.name,
        weight: Number(v.weight) || 0,
        price: Number(v.price),
        compare_price: v.compare !== '' && v.compare !== undefined && v.compare !== null ? Number(v.compare) : null,
        stock_qty: Number(v.stock) || 0,
        is_default: isDefault ? 1 : 0,
        is_active: 1
      });
      first = false;
    });
    setCollection('variants', variantsList);
    return product;
  }

  function deleteProduct(id) {
    id = Number(id);
    setCollection('products', getCollection('products').filter(function (p) { return p.id !== id; }));
    setCollection('variants', getCollection('variants').filter(function (v) { return v.product_id !== id; }));
  }

  function toggleProductActive(id) {
    id = Number(id);
    var list = getCollection('products');
    list.forEach(function (p) { if (p.id === id) p.is_active = p.is_active === 1 ? 0 : 1; });
    setCollection('products', list);
  }

  function createCoupon(data) {
    var list = getCollection('coupons');
    var code = String(data.code || '').toUpperCase().trim();
    for (var i = 0; i < list.length; i++) if (list[i].code === code) return { success: false, message: 'This coupon code already exists.' };
    list.push({
      id: nextId('coupons'),
      code: code,
      discount_type: data.discount_type,
      discount_value: Number(data.discount_value) || 0,
      min_order: Number(data.min_order) || 0,
      max_discount: data.max_discount !== '' && data.max_discount !== undefined && data.max_discount !== null ? Number(data.max_discount) : null,
      expires_at: data.expires_at || null,
      usage_limit: Number(data.usage_limit) || 0,
      used_count: 0,
      is_active: data.is_active ? 1 : 0
    });
    setCollection('coupons', list);
    return { success: true };
  }

  function updateCoupon(id, data) {
    var list = getCollection('coupons');
    var code = String(data.code || '').toUpperCase().trim();
    for (var i = 0; i < list.length; i++) {
      if (list[i].id !== Number(id) && list[i].code === code) return { success: false, message: 'Another coupon already uses this code.' };
    }
    list.forEach(function (c) {
      if (c.id === Number(id)) {
        c.code = code;
        c.discount_type = data.discount_type;
        c.discount_value = Number(data.discount_value) || 0;
        c.min_order = Number(data.min_order) || 0;
        c.max_discount = data.max_discount !== '' && data.max_discount !== undefined && data.max_discount !== null ? Number(data.max_discount) : null;
        c.expires_at = data.expires_at || null;
        c.usage_limit = Number(data.usage_limit) || 0;
        c.is_active = data.is_active ? 1 : 0;
      }
    });
    setCollection('coupons', list);
    return { success: true };
  }

  function toggleCoupon(id) {
    id = Number(id);
    var list = getCollection('coupons');
    list.forEach(function (c) { if (c.id === id) c.is_active = c.is_active === 1 ? 0 : 1; });
    setCollection('coupons', list);
  }

  function deleteCoupon(id) {
    id = Number(id);
    setCollection('coupons', getCollection('coupons').filter(function (c) { return c.id !== id; }));
  }

  function createZone(data) {
    var list = getCollection('zones');
    var district = String(data.district || '').trim();
    if (!district) return { success: false, message: 'District is required.' };
    for (var i = 0; i < list.length; i++) if (list[i].district === district) return { success: false, message: 'This district already exists.' };
    list.push({
      id: nextId('zones'),
      district: district,
      division: data.division || '',
      delivery_fee: Number(data.delivery_fee) || 0,
      free_delivery_threshold: Number(data.free_delivery_threshold) || 0,
      is_active: data.is_active ? 1 : 0,
      sort_order: 999
    });
    setCollection('zones', list);
    return { success: true };
  }

  function updateZone(id, data) {
    id = Number(id);
    var list = getCollection('zones');
    list.forEach(function (z) {
      if (z.id === id) {
        z.delivery_fee = Number(data.delivery_fee) || 0;
        z.free_delivery_threshold = Number(data.free_delivery_threshold) || 0;
        z.is_active = data.is_active ? 1 : 0;
      }
    });
    setCollection('zones', list);
  }

  function updateSettingsFields(fields) {
    var s = settings();
    Object.keys(fields).forEach(function (k) {
      if (fields[k] !== undefined) s[k] = fields[k];
    });
    save('settings', s);
    return s;
  }

  function customers() {
    return getCollection('users').slice().sort(function (a, b) { return b.id - a.id; });
  }
  function customerStats() {
    var list = getCollection('users');
    return list.map(function (u) {
      var ords = getCollection('orders').filter(function (o) { return o.user_id === u.id; });
      var spent = 0;
      ords.forEach(function (o) { if (o.status !== 'cancelled') spent += Number(o.total) || 0; });
      return { user: u, order_count: ords.length, total_spent: spent };
    });
  }

  /* ---------- Export ---------- */

  window.Store = {
    money: money,
    moneyPlain: moneyPlain,
    esc: esc,
    slugify: slugify,
    truncate: truncate,
    stripTags: stripTags,
    bdDivisions: bdDivisions,
    normalizePhone: normalizePhone,
    isValidBdPhone: isValidBdPhone,
    isValidEmail: isValidEmail,
    imageUrl: imageUrl,
    imageAlt: imageAlt,
    starsHtml: starsHtml,
    nowIso: nowIso,
    uid: uid,
    hydrate: hydrate,

    settings: settings,
    setting: setting,
    saveSettings: saveSettings,

    categories: categories,
    categoryBySlug: categoryBySlug,
    productCountForCategory: productCountForCategory,
    products: products,
    productById: productById,
    variantsFor: variantsFor,
    variantById: variantById,
    productMinPrice: productMinPrice,
    productMinWeight: productMinWeight,
    defaultVariantForProduct: defaultVariantForProduct,
    productCard: productCard,

    cartRaw: cartRaw,
    cartCount: cartCount,
    cartItems: cartItems,
    cartAdd: cartAdd,
    cartUpdate: cartUpdate,
    cartRemove: cartRemove,
    cartClear: cartClear,

    currentUser: currentUser,
    loginUser: loginUser,
    registerUser: registerUser,
    logoutUser: logoutUser,
    updateProfile: updateProfile,
    changePassword: changePassword,

    currentAdmin: currentAdmin,
    loginAdmin: loginAdmin,
    logoutAdmin: logoutAdmin,
    changeAdminPassword: changeAdminPassword,

    zones: zones,
    zoneByDistrict: zoneByDistrict,
    deliveryFeeFor: deliveryFeeFor,
    freeDeliveryThresholdFor: freeDeliveryThresholdFor,

    coupons: coupons,
    applyCoupon: applyCoupon,
    markCouponUsed: markCouponUsed,

    orders: orders,
    orderById: orderById,
    orderByNumber: orderByNumber,
    ordersForUser: ordersForUser,
    createOrder: createOrder,
    updateOrderStatus: updateOrderStatus,
    updateOrderPayment: updateOrderPayment,
    setOrderPayment: setOrderPayment,

    reviews: reviews,
    reviewsForProduct: reviewsForProduct,
    addReview: addReview,
    reviewAction: reviewAction,

    messages: messages,
    addMessage: addMessage,
    messageAction: messageAction,

    newsletterSubscribe: newsletterSubscribe,

    getCollection: getCollection,
    setCollection: setCollection,
    nextId: nextId,

    createProduct: createProduct,
    updateProduct: updateProduct,
    deleteProduct: deleteProduct,
    toggleProductActive: toggleProductActive,
    createCoupon: createCoupon,
    updateCoupon: updateCoupon,
    toggleCoupon: toggleCoupon,
    deleteCoupon: deleteCoupon,
    createZone: createZone,
    updateZone: updateZone,
    updateSettingsFields: updateSettingsFields,
    customers: customers,
    customerStats: customerStats
  };

  hydrate();
})();
