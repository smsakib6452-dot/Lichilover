/* ============================================================
   Litchi Lover — admin-pages.js
   Renders and wires all admin pages against window.Store.
   Requires: data.js, store.js, admin-components.js
   ============================================================ */

(function () {
  'use strict';

  var S = window.Store;
  var PAGE = window.LL_ADMIN_PAGE || '';

  function qs(key) {
    return new URLSearchParams(window.location.search).get(key) || '';
  }
  function pageNum() {
    var p = parseInt(qs('page'), 10);
    return (isNaN(p) || p < 1) ? 1 : p;
  }
  function cap(s) { return s ? String(s).charAt(0).toUpperCase() + String(s).slice(1) : ''; }
  function money(v) { return S.money(Number(v) || 0); }
  function esc(v) { return S.esc(v == null ? '' : String(v)); }

  function fmtDate(iso) {
    if (!iso) return '—';
    var d = new Date(iso);
    if (isNaN(d.getTime())) return '—';
    var m = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return m[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
  }
  function fmtDateTime(iso) {
    if (!iso) return '—';
    var d = new Date(iso);
    if (isNaN(d.getTime())) return '—';
    var h = d.getHours() % 12 || 12;
    var ap = d.getHours() < 12 ? 'AM' : 'PM';
    return fmtDate(iso) + ' ' + h + ':' + ('0' + d.getMinutes()).slice(-2) + ' ' + ap;
  }
  function truncate(s, n) {
    s = String(s || '');
    return s.length > n ? s.slice(0, n - 1) + '\u2026' : s;
  }
  function stars(n) {
    n = Math.max(0, Math.min(5, Math.round(Number(n) || 0)));
    return '\u2605'.repeat(n);
  }
  function statusBadge(status) {
    return '<span class="badge badge-' + esc(status) + '">' + esc(cap(status)) + '</span>';
  }
  function paymentBadge(o) {
    if (o.payment_status === 'paid') return '<span class="badge badge-paid">Paid</span>';
    if (o.payment_method === 'cod') return '<span class="badge badge-pending">COD</span>';
    return '<span class="badge badge-' + esc(o.payment_status || 'pending') + '">' + esc(cap(o.payment_status || 'pending')) + '</span>';
  }
  function stockBadge(stock) {
    stock = Number(stock) || 0;
    var cls = stock <= 0 ? 'badge-failed' : (stock <= 10 ? 'badge-pending' : 'badge-active');
    return '<span class="badge ' + cls + '">' + stock + '</span>';
  }
  function activeBadge(active) {
    return active === 1
      ? '<span class="badge badge-active">Active</span>'
      : '<span class="badge badge-inactive">Inactive</span>';
  }

  function paginate(list, page, per) {
    per = per || 20;
    page = Math.max(1, page);
    var start = (page - 1) * per;
    var totalPages = Math.max(1, Math.ceil(list.length / per));
    return { items: list.slice(start, start + per), page: page, totalPages: totalPages, total: list.length };
  }
  function paginationHtml(list, page, per, baseQuery) {
    var totalPages = Math.max(1, Math.ceil(list.length / (per || 20)));
    if (totalPages <= 1) return '';
    var html = '<div class="pagination">';
    for (var i = 1; i <= totalPages; i++) {
      var params = new URLSearchParams(baseQuery || {});
      params.set('page', String(i));
      html += '<a class="btn ' + (i === page ? 'btn-primary' : 'btn-ghost') + ' btn-sm" href="?' + params.toString() + '">' + i + '</a>';
    }
    return html + '</div>';
  }

  function setStat(id, value) {
    var el = document.getElementById(id);
    if (el) el.textContent = value;
  }

  function showError(id, msg) {
    var el = document.getElementById(id);
    if (el) { el.innerHTML = S.esc(msg); el.style.display = 'block'; }
  }
  function clearError(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = 'none';
  }
  function fieldError(name, msg) {
    document.querySelectorAll('[data-error-for="' + name + '"]').forEach(function (el) {
      el.textContent = msg || '';
      el.style.display = msg ? 'block' : 'none';
    });
  }

  /* ============================================================
     LOGIN
     ============================================================ */
  function wireLogin() {
    var form = document.getElementById('loginForm');
    if (!form) return;
    clearError('loginGeneralError');
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearError('loginGeneralError');
      var email = (document.getElementById('loginEmail').value || '').trim();
      var pass = document.getElementById('loginPassword').value || '';
      if (!email) { fieldError('loginEmail', 'Please enter your email.'); return; }
      if (!pass) { fieldError('loginPassword', 'Please enter your password.'); return; }
      var res = S.loginAdmin(email, pass);
      if (res.success) {
        window.location.href = 'index.html';
      } else {
        showError('loginGeneralError', res.message || 'Invalid admin credentials.');
      }
    });
  }

  /* ============================================================
     DASHBOARD
     ============================================================ */
  function renderDashboard() {
    var allOrders = S.orders();
    var allProducts = S.products();
    var users = S.customers();

    var totalSales = 0, pending = 0, delivered = 0;
    allOrders.forEach(function (o) {
      if (o.status !== 'cancelled') totalSales += Number(o.total) || 0;
      if (o.status === 'pending') pending++;
      if (o.status === 'delivered') delivered++;
    });
    var lowStock = 0, outOfStock = 0;
    allProducts.forEach(function (p) {
      var st = Number(p.stock_qty) || 0;
      if (st <= 0) outOfStock++;
      else if (st <= 10) lowStock++;
    });

    setStat('statSales', money(totalSales));
    setStat('statOrders', String(allOrders.length));
    setStat('statPending', String(pending));
    setStat('statDelivered', String(delivered));
    setStat('statCustomers', String(users.length));
    setStat('statProducts', String(allProducts.length));
    setStat('statLowStock', String(lowStock));
    setStat('statOutOfStock', String(outOfStock));

    // 7-day sales
    var salesData = [];
    for (var i = 6; i >= 0; i--) {
      var day = new Date();
      day.setDate(day.getDate() - i);
      var dayKey = day.getFullYear() + '-' + ('0' + (day.getMonth() + 1)).slice(-2) + '-' + ('0' + day.getDate()).slice(-2);
      var sum = 0;
      allOrders.forEach(function (o) {
        if (o.status === 'cancelled') return;
        var ck = String(o.created_at || '').slice(0, 10);
        if (ck === dayKey) sum += Number(o.total) || 0;
      });
      salesData.push({ day: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][day.getDay()], total: sum });
    }
    window.SALES_DATA = salesData;

    // Status distribution
    var statusCounts = { pending: 0, confirmed: 0, processing: 0, shipped: 0, delivered: 0, cancelled: 0 };
    allOrders.forEach(function (o) {
      if (statusCounts[o.status] !== undefined) statusCounts[o.status]++;
    });
    window.STATUS_DATA = statusCounts;

    // Recent orders
    var body = document.getElementById('recentOrdersBody');
    if (!body) return;
    var recent = allOrders.slice(0, 8);
    body.innerHTML = recent.map(function (o) {
      return '<tr>' +
        '<td><a href="order-view.html?id=' + o.id + '">' + esc(o.order_number) + '</a></td>' +
        '<td>' + esc(o.full_name) + '</td>' +
        '<td>' + esc(o.phone) + '</td>' +
        '<td>' + money(o.total) + '</td>' +
        '<td>' + statusBadge(o.status) + '</td>' +
        '<td>' + paymentBadge(o) + '</td>' +
        '<td>' + fmtDate(o.created_at) + '</td>' +
        '<td><a href="order-view.html?id=' + o.id + '" class="btn btn-ghost btn-sm">View</a></td>' +
        '</tr>';
    }).join('') || '<tr><td colspan="8" class="empty-state">No orders yet.</td></tr>';
  }

  /* ============================================================
     ORDERS LIST
     ============================================================ */
  function renderOrders() {
    var form = document.getElementById('ordersFilterForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var params = new URLSearchParams();
        var qv = (form.querySelector('[name="q"]').value || '').trim();
        var sv = form.querySelector('[name="status"]').value;
        if (qv) params.set('q', qv);
        if (sv && sv !== 'all') params.set('status', sv);
        window.location.href = 'orders.html?' + params.toString();
      });
    }

    var q = qs('q').toLowerCase();
    var status = qs('status') || 'all';
    var list = S.orders().filter(function (o) {
      if (status !== 'all' && o.status !== status) return false;
      if (q && String(o.order_number).toLowerCase().indexOf(q) === -1 &&
          String(o.full_name).toLowerCase().indexOf(q) === -1 &&
          String(o.phone).indexOf(q) === -1) return false;
      return true;
    });

    var body = document.getElementById('ordersBody');
    if (!body) return;
    var pg = paginate(list, pageNum(), 20);
    body.innerHTML = pg.items.map(function (o) {
      return '<tr>' +
        '<td><a href="order-view.html?id=' + o.id + '">' + esc(o.order_number) + '</a></td>' +
        '<td>' + esc(o.full_name) + '</td>' +
        '<td>' + esc(o.phone) + '</td>' +
        '<td>' + esc(o.district) + '</td>' +
        '<td>' + money(o.total) + '</td>' +
        '<td>' + statusBadge(o.status) + '</td>' +
        '<td>' + paymentBadge(o) + '</td>' +
        '<td>' + fmtDate(o.created_at) + '</td>' +
        '<td><a href="order-view.html?id=' + o.id + '" class="btn btn-ghost btn-sm">View</a></td>' +
        '</tr>';
    }).join('') || '<tr><td colspan="9" class="empty-state">No orders found.</td></tr>';

    var pager = document.getElementById('ordersPagination');
    if (pager) pager.innerHTML = paginationHtml(list, pg.page, 20, { q: qs('q'), status: status });
  }

  /* ============================================================
     ORDER VIEW
     ============================================================ */
  var VIEW_ORDER = null;
  function renderOrderView() {
    var id = Number(qs('id')) || 0;
    var order = S.orderById(id);
    var notFound = document.getElementById('orderNotFound');
    var content = document.getElementById('orderContent');
    if (!order) {
      if (notFound) notFound.style.display = 'block';
      if (content) content.style.display = 'none';
      return;
    }
    VIEW_ORDER = order;
    if (notFound) notFound.style.display = 'none';
    if (content) content.style.display = 'block';

    document.getElementById('ovNumber').textContent = order.order_number;
    document.getElementById('ovPlaced').textContent = 'Placed ' + fmtDateTime(order.created_at);
    document.getElementById('ovUpdated').textContent = 'Updated ' + fmtDateTime(order.updated_at);
    document.getElementById('ovBadges').innerHTML =
      statusBadge(order.status) +
      '<span class="badge badge-' + esc(order.payment_status) + '">Payment: ' + esc(cap(order.payment_status)) + '</span>' +
      '<span class="badge badge-active">' + esc(String(order.payment_method).toUpperCase()) + '</span>';

    var itemsBody = document.getElementById('ovItemsBody');
    itemsBody.innerHTML = '';
    (order.items || []).forEach(function (it) {
      var row = document.createElement('tr');
      row.innerHTML =
        '<td>' + esc(it.product_name) + '</td>' +
        '<td>' + esc(it.variant_name || '\u2014') + '</td>' +
        '<td>' + (Number(it.qty) || Number(it.quantity) || 0) + '</td>' +
        '<td>' + money(it.unit_price) + '</td>' +
        '<td>' + money(it.line_total) + '</td>';
      itemsBody.appendChild(row);
    });

    document.getElementById('ovSubtotal').textContent = money(order.subtotal);
    document.getElementById('ovDelivery').textContent = money(order.delivery_fee);
    var discountRow = document.getElementById('ovDiscountRow');
    if (Number(order.discount) > 0) {
      discountRow.style.display = 'flex';
      discountRow.querySelector('.ov-coupon').textContent = order.coupon_code ? ' (' + order.coupon_code + ')' : '';
      discountRow.querySelector('.ov-discount').textContent = '-' + money(order.discount);
    } else {
      discountRow.style.display = 'none';
    }
    document.getElementById('ovTotal').textContent = money(order.total);

    document.getElementById('ovCustomer').innerHTML =
      '<strong>' + esc(order.full_name) + '</strong> \u2014 ' + esc(order.phone) +
      (order.email ? '<br>Email: ' + esc(order.email) : '');
    document.getElementById('ovAddress').innerHTML =
      esc(order.address) + '<br>' + esc(order.upazila || '') + ', ' + esc(order.district) + ', ' + esc(order.division);
    var noteEl = document.getElementById('ovNote');
    if (order.delivery_note) {
      noteEl.style.display = 'block';
      noteEl.innerHTML = '<em>Note: ' + esc(order.delivery_note) + '</em>';
    } else {
      noteEl.style.display = 'none';
    }

    // Forms (wire once)
    var statusForm = document.getElementById('orderStatusForm');
    if (statusForm && !statusForm.getAttribute('data-wired')) {
      statusForm.setAttribute('data-wired', '1');
      statusForm.addEventListener('submit', function (e) {
        e.preventDefault();
        S.updateOrderStatus(order.id, statusForm.querySelector('[name="status"]').value);
        LLAdmin.showAlert('Order status updated.', 'success');
        renderOrderView();
      });
    }
    if (statusForm) statusForm.querySelector('[name="status"]').value = order.status;

    var payForm = document.getElementById('paymentStatusForm');
    if (payForm && !payForm.getAttribute('data-wired')) {
      payForm.setAttribute('data-wired', '1');
      payForm.addEventListener('submit', function (e) {
        e.preventDefault();
        S.updateOrderPayment(order.id, payForm.querySelector('[name="payment_status"]').value);
        LLAdmin.showAlert('Payment status updated.', 'success');
        renderOrderView();
      });
    }
    if (payForm) payForm.querySelector('[name="payment_status"]').value = order.payment_status;

    var paymentsBox = document.getElementById('paymentsRecorded');
    if (paymentsBox) {
      if (order.payment) {
        var p = order.payment;
        paymentsBox.innerHTML =
          '<div style="border:1px solid var(--line);border-radius:10px;padding:12px">' +
          '<div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:6px">' +
          '<strong>' + esc(String(p.method || order.payment_method).toUpperCase()) + '</strong>' +
          '<span class="badge badge-' + esc(p.status) + '">' + esc(cap(p.status)) + '</span></div>' +
          '<p style="font-size:13px;margin-top:6px">Amount: ' + money(p.amount) + '</p>' +
          '<p style="font-size:12px;color:var(--muted)">Payment ID: ' + esc(p.payment_id || '\u2014') + '</p>' +
          (p.transaction_id ? '<p style="font-size:12px;color:var(--muted)">Transaction: ' + esc(p.transaction_id) + '</p>' : '') +
          '</div>';
      } else {
        paymentsBox.innerHTML = '<p class="empty-state">No payment record yet.</p>';
      }
    }
  }

  /* ============================================================
     PRODUCTS LIST
     ============================================================ */
  function renderProducts() {
    var form = document.getElementById('productsFilterForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var params = new URLSearchParams();
        var qv = (form.querySelector('[name="q"]').value || '').trim();
        var sv = form.querySelector('[name="status"]').value;
        if (qv) params.set('q', qv);
        if (sv && sv !== 'all') params.set('status', sv);
        window.location.href = 'products.html?' + params.toString();
      });
    }

    var q = qs('q').toLowerCase();
    var status = qs('status') || 'all';
    var list = S.products().filter(function (p) {
      if (status === 'active' && p.is_active !== 1) return false;
      if (status === 'inactive' && p.is_active !== 0) return false;
      if (status === 'out' && (Number(p.stock_qty) || 0) > 0) return false;
      if (q && String(p.name).toLowerCase().indexOf(q) === -1 && String(p.slug).toLowerCase().indexOf(q) === -1) return false;
      return true;
    }).slice().sort(function (a, b) { return b.id - a.id; });

    var body = document.getElementById('productsBody');
    if (!body) return;
    var pg = paginate(list, pageNum(), 20);
    body.innerHTML = pg.items.map(function (p) {
      var variants = S.variantsFor(p.id);
      var cat = null;
      S.categories(true).forEach(function (c) { if (c.id === p.category_id) cat = c; });
      var row =
        '<tr>' +
        '<td>' + p.id + '</td>' +
        '<td><div style="display:flex;gap:10px;align-items:center">' +
        '<img src="' + esc(p.image || S.imageUrl('litchi_live')) + '" alt="" style="width:44px;height:44px;border-radius:8px;object-fit:cover">' +
        '<div><strong>' + esc(p.name) + '</strong>' +
        '<div style="font-size:12px;color:var(--muted)">' + variants.length + ' variants</div></div></div></td>' +
        '<td>' + esc(cat ? cat.name : '\u2014') + '</td>' +
        '<td>' + money(S.productMinPrice(p.id)) + '</td>' +
        '<td>' + stockBadge(p.stock_qty) + '</td>' +
        '<td>' + (p.is_featured === 1 ? '<span class="badge badge-active">Yes</span>' : '\u2014') + '</td>' +
        '<td>' + activeBadge(p.is_active) + '</td>' +
        '<td><div style="display:flex;gap:6px;flex-wrap:wrap">' +
        '<a href="product-edit.html?id=' + p.id + '" class="btn btn-ghost btn-sm">Edit</a>' +
        '<a href="../product.html?id=' + p.id + '" target="_blank" class="btn btn-ghost btn-sm" title="View">\uD83D\uDC41\uFE0F</a>' +
        '<button type="button" class="btn btn-ghost btn-sm" data-toggle-product="' + p.id + '">' + (p.is_active === 1 ? 'Deactivate' : 'Activate') + '</button>' +
        '</div></td></tr>';
      return row;
    }).join('') || '<tr><td colspan="8" class="empty-state">No products found.</td></tr>';

    body.querySelectorAll('[data-toggle-product]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        S.toggleProductActive(btn.getAttribute('data-toggle-product'));
        LLAdmin.showAlert('Product status updated.', 'success');
        renderProducts();
      });
    });

    var pager = document.getElementById('productsPagination');
    if (pager) pager.innerHTML = paginationHtml(list, pg.page, 20, { q: qs('q'), status: status });
  }

  /* ============================================================
     PRODUCT ADD / EDIT
     ============================================================ */
  function renderProductForm(mode) {
    var id = Number(qs('id')) || 0;
    var product = mode === 'edit' ? S.productById(id) : null;
    var form = document.getElementById('productForm');
    if (!form) return;

    if (mode === 'edit' && !product) {
      var nf = document.getElementById('productNotFound');
      if (nf) nf.style.display = 'block';
      form.style.display = 'none';
      var dz = document.getElementById('dangerZone');
      if (dz) dz.style.display = 'none';
      return;
    }

    // Categories
    var catSelect = document.getElementById('categorySelect');
    if (catSelect) {
      catSelect.innerHTML = '<option value="">Select category</option>' +
        S.categories(true).map(function (c) {
          return '<option value="' + c.id + '"' + (product && product.category_id === c.id ? ' selected' : '') + '>' + esc(c.name) + '</option>';
        }).join('');
    }

    if (mode === 'edit' && product) {
      form.querySelector('[name="name"]').value = product.name;
      form.querySelector('[name="short_description"]').value = product.short_description || '';
      form.querySelector('[name="description"]').value = product.description || '';
      form.querySelector('[name="image"]').value = product.image || '';
      form.querySelector('[name="compare_price"]').value = product.compare_price != null ? product.compare_price : '';
      form.querySelector('[name="is_featured"]').checked = product.is_featured === 1;
      form.querySelector('[name="is_active"]').checked = product.is_active === 1;
      var preview = document.getElementById('imagePreview');
      if (preview) {
        preview.style.display = 'block';
        preview.src = product.image || S.imageUrl('litchi_live');
      }

      // Variants
      var wrap = document.getElementById('variantRows');
      if (wrap) {
        wrap.innerHTML = '';
        var variants = S.variantsFor(product.id);
        variants.forEach(function (v) {
          wrap.insertAdjacentHTML('beforeend', variantRowHtml(v));
        });
      }
    }

    if (form.getAttribute('data-wired')) { return; }
    form.setAttribute('data-wired', '1');
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var data = {
        name: (form.querySelector('[name="name"]').value || '').trim(),
        category_id: form.querySelector('[name="category_id"]').value || '0',
        short_description: (form.querySelector('[name="short_description"]').value || '').trim(),
        description: form.querySelector('[name="description"]').value,
        image: (form.querySelector('[name="image"]').value || '').trim(),
        compare_price: form.querySelector('[name="compare_price"]').value || '',
        is_featured: form.querySelector('[name="is_featured"]').checked ? 1 : 0,
        is_active: form.querySelector('[name="is_active"]').checked ? 1 : 0
      };

      var ids = [], names = [], weights = [], prices = [], compares = [], stocks = [];
      form.querySelectorAll('#variantRows .variant-row').forEach(function (row) {
        var idIn = row.querySelector('[name="variant_ids[]"]');
        ids.push(idIn ? Number(idIn.value) : 0);
        names.push(row.querySelector('[name="variant_names[]"]').value.trim());
        weights.push(row.querySelector('[name="variant_weights[]"]').value || '0');
        prices.push(row.querySelector('[name="variant_prices[]"]').value || '0');
        compares.push(row.querySelector('[name="variant_compare[]"]').value || '');
        stocks.push(row.querySelector('[name="variant_stocks[]"]').value || '0');
      });

      var variants = [];
      names.forEach(function (n, i) {
        var pr = Number(prices[i]);
        if (n === '' || !(pr > 0)) return;
        variants.push({ name: n, weight: weights[i], price: prices[i], compare: compares[i], stock: stocks[i] });
      });

      fieldError('name', '');
      fieldError('category_id', '');
      fieldError('variants', '');
      if (data.name.length < 3) { fieldError('name', 'Product name is required (min 3 chars).'); return; }
      if (Number(data.category_id) <= 0) { fieldError('category_id', 'Please select a category.'); return; }
      if (!variants.length) { fieldError('variants', 'Add at least one valid variant (name + price).'); return; }

      var defaultRadio = form.querySelector('input[name="variant_default"]:checked');
      var variantDefault = defaultRadio ? defaultRadio.value : '0';

      if (mode === 'add') {
        var created = S.createProduct(data, variants);
        LLAdmin.showAlert('Product created successfully.', 'success');
        window.location.href = 'product-edit.html?id=' + created.id;
      } else {
        S.updateProduct(product.id, data, variants, ids, variantDefault);
        LLAdmin.showAlert('Product updated successfully.', 'success');
        renderProductForm('edit');
      }
    });

    if (mode === 'edit') {
      var delForm = document.getElementById('deleteProductForm');
      if (delForm) {
        delForm.addEventListener('submit', function (e) {
          e.preventDefault();
          if (!window.confirm('Delete this product permanently? This cannot be undone.')) return;
          S.deleteProduct(product.id);
          window.location.href = 'products.html';
        });
      }
    }
  }

  function variantRowHtml(v) {
    var isDefault = v.is_default === 1;
    return '' +
      '<div class="variant-row" style="border:1px solid var(--line);border-radius:10px;padding:12px;display:grid;gap:10px;grid-template-columns:1fr 1fr 1fr 1fr 1fr auto;align-items:end;margin-bottom:10px">' +
      '<input type="hidden" name="variant_ids[]" value="' + v.id + '">' +
      '<div><label style="font-size:12px;font-weight:700">Name</label><input type="text" name="variant_names[]" value="' + esc(v.name) + '" required></div>' +
      '<div><label style="font-size:12px;font-weight:700">Weight (kg)</label><input type="number" step="0.01" min="0" name="variant_weights[]" value="' + esc(v.weight) + '"></div>' +
      '<div><label style="font-size:12px;font-weight:700">Price (\u09F3)</label><input type="number" step="0.01" min="0" name="variant_prices[]" value="' + esc(v.price) + '" required></div>' +
      '<div><label style="font-size:12px;font-weight:700">Compare (\u09F3)</label><input type="number" step="0.01" min="0" name="variant_compare[]" value="' + esc(v.compare_price != null ? v.compare_price : '') + '"></div>' +
      '<div><label style="font-size:12px;font-weight:700">Stock</label><input type="number" min="0" name="variant_stocks[]" value="' + esc(v.stock_qty) + '"></div>' +
      '<div style="display:flex;gap:6px;align-items:center">' +
      '<label style="font-size:12px;font-weight:700"><input type="radio" name="variant_default" value="' + v.id + '"' + (isDefault ? ' checked' : '') + '> Default</label>' +
      '<button type="button" class="btn btn-danger btn-sm remove-variant">\u00D7</button>' +
      '</div></div>';
  }

  /* ============================================================
     CUSTOMERS
     ============================================================ */
  function renderCustomers() {
    var form = document.getElementById('customersFilterForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var params = new URLSearchParams();
        var qv = (form.querySelector('[name="q"]').value || '').trim();
        if (qv) params.set('q', qv);
        window.location.href = 'customers.html?' + params.toString();
      });
    }

    var q = qs('q').toLowerCase();
    var stats = S.customerStats();
    if (q) {
      stats = stats.filter(function (s) {
        return String(s.user.name).toLowerCase().indexOf(q) !== -1 ||
          String(s.user.email).toLowerCase().indexOf(q) !== -1 ||
          String(s.user.phone).indexOf(q) !== -1;
      });
    }

    var body = document.getElementById('customersBody');
    if (!body) return;
    var pg = paginate(stats, pageNum(), 20);
    body.innerHTML = pg.items.map(function (s) {
      var u = s.user;
      return '<tr>' +
        '<td>' + u.id + '</td>' +
        '<td><strong>' + esc(u.name) + '</strong><div style="font-size:12px;color:var(--muted)">' + esc(u.email) + '</div></td>' +
        '<td>' + esc(u.phone) + '</td>' +
        '<td>' + s.order_count + '</td>' +
        '<td>' + money(s.total_spent) + '</td>' +
        '<td>' + fmtDate(u.created_at) + '</td>' +
        '<td>' + activeBadge(u.is_active) + '</td>' +
        '</tr>';
    }).join('') || '<tr><td colspan="7" class="empty-state">No customers found.</td></tr>';

    var pager = document.getElementById('customersPagination');
    if (pager) pager.innerHTML = paginationHtml(stats, pg.page, 20, { q: qs('q') });
  }

  /* ============================================================
     COUPONS
     ============================================================ */
  function renderCoupons() {
    var body = document.getElementById('couponsBody');
    if (!body) return;
    var list = S.coupons().slice().sort(function (a, b) { return b.id - a.id; });
    body.innerHTML = list.map(function (c) {
      var value = c.discount_type === 'percent' ? esc(c.discount_value) + '%' : money(c.discount_value);
      var usage = (Number(c.used_count) || 0) + ' / ' + (Number(c.usage_limit) > 0 ? Number(c.usage_limit) : '\u221E');
      return '<tr>' +
        '<td><strong>' + esc(c.code) + '</strong></td>' +
        '<td>' + esc(c.discount_type) + '</td>' +
        '<td>' + value + '</td>' +
        '<td>' + money(c.min_order) + '</td>' +
        '<td>' + (c.max_discount != null ? money(c.max_discount) : '\u2014') + '</td>' +
        '<td>' + (c.expires_at ? esc(c.expires_at) : 'Never') + '</td>' +
        '<td>' + usage + '</td>' +
        '<td>' + activeBadge(c.is_active) + '</td>' +
        '<td><div style="display:flex;gap:6px;flex-wrap:wrap">' +
        '<a href="coupon-edit.html?id=' + c.id + '" class="btn btn-ghost btn-sm">Edit</a>' +
        '<button type="button" class="btn btn-ghost btn-sm" data-toggle-coupon="' + c.id + '">' + (c.is_active === 1 ? 'Deactivate' : 'Activate') + '</button>' +
        '<button type="button" class="btn btn-danger btn-sm" data-delete-coupon="' + c.id + '">Delete</button>' +
        '</div></td></tr>';
    }).join('') || '<tr><td colspan="9" class="empty-state">No coupons yet.</td></tr>';

    body.querySelectorAll('[data-toggle-coupon]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        S.toggleCoupon(btn.getAttribute('data-toggle-coupon'));
        LLAdmin.showAlert('Coupon status updated.', 'success');
        renderCoupons();
      });
    });
    body.querySelectorAll('[data-delete-coupon]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!window.confirm('Delete this coupon?')) return;
        S.deleteCoupon(btn.getAttribute('data-delete-coupon'));
        LLAdmin.showAlert('Coupon deleted.', 'success');
        renderCoupons();
      });
    });
  }

  /* ============================================================
     COUPON ADD / EDIT
     ============================================================ */
  function renderCouponForm(mode) {
    var id = Number(qs('id')) || 0;
    var coupon = null;
    if (mode === 'edit') {
      var all = S.coupons();
      for (var i = 0; i < all.length; i++) if (all[i].id === id) coupon = all[i];
    }
    var form = document.getElementById('couponForm');
    if (!form) return;

    if (mode === 'edit' && !coupon) {
      var nf = document.getElementById('couponNotFound');
      if (nf) nf.style.display = 'block';
      form.style.display = 'none';
      return;
    }

    if (mode === 'edit' && coupon) {
      var t = document.getElementById('couponFormTitle');
      if (t) t.textContent = 'Edit Coupon: ' + coupon.code;
      form.querySelector('[name="code"]').value = coupon.code;
      form.querySelector('[name="discount_type"]').value = coupon.discount_type;
      form.querySelector('[name="discount_value"]').value = coupon.discount_value;
      form.querySelector('[name="min_order"]').value = coupon.min_order;
      form.querySelector('[name="max_discount"]').value = coupon.max_discount != null ? coupon.max_discount : '';
      form.querySelector('[name="expires_at"]').value = coupon.expires_at || '';
      form.querySelector('[name="usage_limit"]').value = coupon.usage_limit;
      form.querySelector('[name="is_active"]').checked = coupon.is_active === 1;
      var hint = document.getElementById('usageHint');
      if (hint) hint.textContent = 'Used so far: ' + (Number(coupon.used_count) || 0);
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var code = (form.querySelector('[name="code"]').value || '').toUpperCase().trim();
      var type = form.querySelector('[name="discount_type"]').value;
      var value = Number(form.querySelector('[name="discount_value"]').value) || 0;
      var data = {
        code: code, discount_type: type, discount_value: value,
        min_order: form.querySelector('[name="min_order"]').value || '0',
        max_discount: form.querySelector('[name="max_discount"]').value || '',
        expires_at: form.querySelector('[name="expires_at"]').value || '',
        usage_limit: form.querySelector('[name="usage_limit"]').value || '0',
        is_active: form.querySelector('[name="is_active"]').checked ? 1 : 0
      };

      var ok = true;
      if (!/^[A-Z0-9_-]{3,30}$/.test(code)) { fieldError('code', 'Enter a valid code (3-30 chars, letters/numbers/-/_).'); ok = false; }
      if (['percent', 'fixed'].indexOf(type) === -1) { fieldError('discount_type', 'Invalid discount type.'); ok = false; }
      if (!(value > 0)) { fieldError('discount_value', 'Discount value must be greater than 0.'); ok = false; }
      if (type === 'percent' && value > 100) { fieldError('discount_value', 'Percent discount cannot exceed 100%.'); ok = false; }
      if (!ok) return;

      var res = mode === 'add' ? S.createCoupon(data) : S.updateCoupon(id, data);
      if (!res.success) {
        fieldError('code', res.message || 'Could not save coupon.');
        return;
      }
      LLAdmin.showAlert(mode === 'add' ? 'Coupon created successfully.' : 'Coupon updated successfully.', 'success');
      window.location.href = 'coupons.html';
    });
  }

  /* ============================================================
     DELIVERY ZONES
     ============================================================ */
  function renderDelivery() {
    var body = document.getElementById('zonesBody');
    if (body) {
      body.innerHTML = S.zones().map(function (z) {
        return '<tr>' +
          '<td>' + esc(z.district) + '</td>' +
          '<td>' + esc(z.division || '\u2014') + '</td>' +
          '<td><form class="zone-update" data-zone="' + z.id + '" style="display:flex;gap:6px;align-items:center">' +
          '<input type="number" step="0.01" min="0" name="delivery_fee" value="' + esc(z.delivery_fee) + '" style="width:80px" required>' +
          '<input type="number" step="0.01" min="0" name="free_delivery_threshold" value="' + esc(z.free_delivery_threshold) + '" style="width:80px" title="Free delivery threshold">' +
          '<input type="checkbox" name="is_active" value="1"' + (z.is_active === 1 ? ' checked' : '') + ' title="Active">' +
          '<button type="submit" class="btn btn-ghost btn-sm">Save</button>' +
          '</form></td>' +
          '<td style="font-size:12px;color:var(--muted)">0 = no free delivery</td>' +
          '<td>' + activeBadge(z.is_active) + '</td>' +
          '<td></td></tr>';
      }).join('') || '<tr><td colspan="6" class="empty-state">No zones yet.</td></tr>';

      body.querySelectorAll('.zone-update').forEach(function (frm) {
        frm.addEventListener('submit', function (e) {
          e.preventDefault();
          S.updateZone(frm.getAttribute('data-zone'), {
            delivery_fee: frm.querySelector('[name="delivery_fee"]').value || '0',
            free_delivery_threshold: frm.querySelector('[name="free_delivery_threshold"]').value || '0',
            is_active: frm.querySelector('[name="is_active"]').checked ? 1 : 0
          });
          LLAdmin.showAlert('Delivery zone updated.', 'success');
          renderDelivery();
        });
      });
    }

    var divSelect = document.getElementById('addZoneDivision');
    if (divSelect) {
      divSelect.innerHTML = '<option value="">Select division</option>' +
        S.bdDivisions().map(function (d) { return '<option value="' + esc(d) + '">' + esc(d) + '</option>'; }).join('');
    }
    var addForm = document.getElementById('addZoneForm');
    if (addForm && !addForm.getAttribute('data-wired')) {
      addForm.setAttribute('data-wired', '1');
      addForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var res = S.createZone({
          district: (addForm.querySelector('[name="district"]').value || '').trim(),
          division: addForm.querySelector('[name="division"]').value || '',
          delivery_fee: addForm.querySelector('[name="delivery_fee"]').value || '0',
          free_delivery_threshold: addForm.querySelector('[name="free_delivery_threshold"]').value || '0',
          is_active: addForm.querySelector('[name="is_active"]').checked ? 1 : 0
        });
        if (res.success) {
          LLAdmin.showAlert('Delivery zone added.', 'success');
          addForm.reset();
          addForm.querySelector('[name="is_active"]').checked = true;
          renderDelivery();
        } else {
          LLAdmin.showAlert(res.message || 'Could not add zone.', 'error');
        }
      });
    }
  }

  /* ============================================================
     REVIEWS
     ============================================================ */
  function renderReviews() {
    var status = qs('status') || 'pending';
    var select = document.getElementById('reviewsStatusSelect');
    if (select) {
      select.value = status;
      select.addEventListener('change', function () {
        var params = new URLSearchParams();
        var v = select.value;
        if (v && v !== 'all') params.set('status', v);
        window.location.href = 'reviews.html?' + params.toString();
      });
      var opt = select.querySelector('option[value="pending"]');
      if (opt) {
        var pendingCount = 0;
        S.reviews().forEach(function (r) { if (r.status === 'pending') pendingCount++; });
        opt.textContent = 'Pending (' + pendingCount + ')';
      }
    }

    var body = document.getElementById('reviewsBody');
    if (!body) return;
    var list = S.reviews().filter(function (r) {
      return status === 'all' || r.status === status;
    });

    body.innerHTML = list.map(function (r) {
      var prod = S.productById(r.product_id);
      var user = null;
      S.customers().forEach(function (u) { if (u.id === r.user_id) user = u; });
      var actionBtns = '';
      if (r.status !== 'approved') actionBtns += '<button type="button" class="btn btn-ghost btn-sm" data-review-act="approve" data-id="' + r.id + '">Approve</button>';
      if (r.status !== 'rejected') actionBtns += '<button type="button" class="btn btn-ghost btn-sm" data-review-act="reject" data-id="' + r.id + '">Reject</button>';
      actionBtns += '<button type="button" class="btn btn-danger btn-sm" data-review-act="delete" data-id="' + r.id + '">Delete</button>';
      return '<tr>' +
        '<td>' + (prod
          ? '<a href="../product.html?id=' + prod.id + '" target="_blank">' + esc(prod.name) + '</a>'
          : 'Deleted product') + '</td>' +
        '<td>' + esc(user ? user.name : 'User #' + r.user_id) + '</td>' +
        '<td style="color:var(--gold)">' + stars(r.rating) + '</td>' +
        '<td style="max-width:300px">' + esc(truncate(r.review, 120)) + '</td>' +
        '<td>' + fmtDate(r.created_at) + '</td>' +
        '<td>' + statusBadge(r.status) + (r.is_demo ? ' <span class="badge badge-inactive">Sample</span>' : '') + '</td>' +
        '<td><div style="display:flex;gap:6px;flex-wrap:wrap">' + actionBtns + '</div></td>' +
        '</tr>';
    }).join('') || '<tr><td colspan="7" class="empty-state">No reviews in this view.</td></tr>';

    body.querySelectorAll('[data-review-act]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var act = btn.getAttribute('data-review-act');
        var id = btn.getAttribute('data-id');
        if (act === 'delete' && !window.confirm('Delete this review?')) return;
        S.reviewAction(id, act);
        LLAdmin.showAlert(cap(act) + 'd review.', 'success');
        renderReviews();
      });
    });
  }

  /* ============================================================
     MESSAGES
     ============================================================ */
  function renderMessages() {
    var filter = qs('filter') || 'unread';
    var select = document.getElementById('messagesFilterSelect');
    if (select) {
      select.value = filter;
      select.addEventListener('change', function () {
        var params = new URLSearchParams();
        var v = select.value;
        if (v && v !== 'unread') params.set('filter', v);
        window.location.href = 'messages.html?' + params.toString();
      });
      var unreadCount = 0;
      S.messages().forEach(function (m) { if (!m.is_read) unreadCount++; });
      var opt = select.querySelector('option[value="unread"]');
      if (opt) opt.textContent = 'Unread (' + unreadCount + ')';
    }

    var body = document.getElementById('messagesBody');
    if (!body) return;
    var list = S.messages().filter(function (m) {
      return filter === 'all' || !m.is_read;
    });

    body.innerHTML = list.map(function (m) {
      var actions = '';
      if (!m.is_read) actions += '<button type="button" class="btn btn-ghost btn-sm" data-msg-act="mark_read" data-id="' + m.id + '">Mark Read</button>';
      actions += '<button type="button" class="btn btn-danger btn-sm" data-msg-act="delete" data-id="' + m.id + '">Delete</button>';
      return '<tr>' +
        '<td><strong>' + esc(m.name) + '</strong></td>' +
        '<td><div>' + esc(m.email) + '</div><div style="font-size:12px;color:var(--muted)">' + esc(m.phone || '\u2014') + '</div></td>' +
        '<td>' + esc(m.subject || '\u2014') + '</td>' +
        '<td style="max-width:320px">' + esc(truncate(m.message, 140)) + '</td>' +
        '<td>' + fmtDateTime(m.created_at) + '</td>' +
        '<td>' + (m.is_read
          ? '<span class="badge badge-active">Read</span>'
          : '<span class="badge badge-pending">Unread</span>') + '</td>' +
        '<td><div style="display:flex;gap:6px;flex-wrap:wrap">' + actions + '</div></td>' +
        '</tr>';
    }).join('') || '<tr><td colspan="7" class="empty-state">No messages.</td></tr>';

    body.querySelectorAll('[data-msg-act]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var act = btn.getAttribute('data-msg-act');
        var id = btn.getAttribute('data-id');
        if (act === 'delete' && !window.confirm('Delete this message?')) return;
        S.messageAction(id, act);
        LLAdmin.showAlert(act === 'mark_read' ? 'Marked as read.' : 'Message deleted.', 'success');
        renderMessages();
      });
    });
  }

  /* ============================================================
     SETTINGS
     ============================================================ */
  function renderSettings() {
    var s = S.settings();
    var form = document.getElementById('settingsForm');
    if (form) {
      ['shop_name', 'shop_tagline', 'announcement', 'shop_email', 'shop_phone', 'shop_address',
       'whatsapp_number', 'facebook_url', 'instagram_url', 'hero_headline', 'hero_subheadline', 'about_text']
        .forEach(function (key) {
          var el = form.querySelector('[name="' + key + '"]');
          if (el && s[key] != null) el.value = s[key];
        });

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var fields = {};
        ['shop_name', 'shop_tagline', 'announcement', 'shop_email', 'shop_phone', 'shop_address',
         'whatsapp_number', 'facebook_url', 'instagram_url', 'hero_headline', 'hero_subheadline', 'about_text']
          .forEach(function (key) {
            var el = form.querySelector('[name="' + key + '"]');
            fields[key] = el ? el.value : '';
          });
        S.updateSettingsFields(fields);
        LLAdmin.showAlert('Settings saved.', 'success');
      });
    }

    var pwForm = document.getElementById('passwordForm');
    if (pwForm) {
      pwForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var admin = S.currentAdmin();
        var current = pwForm.querySelector('[name="current_password"]').value || '';
        var next = pwForm.querySelector('[name="new_password"]').value || '';
        var confirm = pwForm.querySelector('[name="confirm_password"]').value || '';
        fieldError('current_password', '');
        fieldError('new_password', '');
        fieldError('confirm_password', '');
        if (next !== confirm) { fieldError('confirm_password', 'Passwords do not match.'); return; }
        var res = S.changeAdminPassword(admin.id, current, next);
        if (!res.success) {
          fieldError(res.field || 'current_password', res.message || 'Could not update password.');
          return;
        }
        pwForm.reset();
        LLAdmin.showAlert('Password updated. Please keep it safe!', 'success');
      });
    }
  }

  /* ============================================================
     BOOT
     ============================================================ */
  function boot() {
    if (PAGE === 'login') { wireLogin(); return; }
    if (!S.currentAdmin()) return;
    try {
      if (PAGE === 'dashboard') renderDashboard();
      else if (PAGE === 'orders') renderOrders();
      else if (PAGE === 'order-view') renderOrderView();
      else if (PAGE === 'products') renderProducts();
      else if (PAGE === 'product-add') renderProductForm('add');
      else if (PAGE === 'product-edit') renderProductForm('edit');
      else if (PAGE === 'customers') renderCustomers();
      else if (PAGE === 'coupons') renderCoupons();
      else if (PAGE === 'coupon-add') renderCouponForm('add');
      else if (PAGE === 'coupon-edit') renderCouponForm('edit');
      else if (PAGE === 'delivery') renderDelivery();
      else if (PAGE === 'reviews') renderReviews();
      else if (PAGE === 'messages') renderMessages();
      else if (PAGE === 'settings') renderSettings();
    } catch (err) {
      if (window.LLAdmin) LLAdmin.showAlert(err.message || 'Something went wrong.', 'error');
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
