/* ============================================================
   Litchi Lover — admin.js
   Sidebar toggle, lucide icons, charts, loading states, confirm dialogs.
   ============================================================ */

(function () {
  'use strict';

  function initLucide() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  }

  function initSidebar() {
    var btn = document.getElementById('adminMenuBtn');
    var sidebar = document.getElementById('adminSidebar');
    if (btn && sidebar) {
      btn.addEventListener('click', function () { sidebar.classList.toggle('open'); });
    }
  }

  function initForms() {
    document.querySelectorAll('form[data-loading]').forEach(function (form) {
      form.addEventListener('submit', function () {
        var btn = form.querySelector('[type="submit"]');
        if (btn && !btn.disabled) { btn.classList.add('loading'); btn.disabled = true; }
      });
    });
  }

  function initConfirm() {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        var msg = form.getAttribute('data-confirm') || 'Are you sure?';
        if (!window.confirm(msg)) { e.preventDefault(); return false; }
      });
    });
    document.querySelectorAll('[data-confirm-link]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        var msg = a.getAttribute('data-confirm-link') || 'Are you sure?';
        if (!window.confirm(msg)) { e.preventDefault(); return false; }
      });
    });
  }

  // ---------- Charts (vanilla canvas) ----------
  function drawBarChart(canvas, labels, values, color, moneyLabel) {
    if (!canvas) return;
    var dpr = window.devicePixelRatio || 1;
    var ctx = canvas.getContext('2d');
    var w = canvas.clientWidth || 300;
    var h = parseInt(canvas.getAttribute('height') || '220', 10);
    canvas.width = w * dpr; canvas.height = h * dpr;
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, w, h);

    var padL = 52, padR = 12, padT = 14, padB = 28;
    var chartW = w - padL - padR;
    var chartH = h - padT - padB;
    var max = Math.max.apply(null, values.concat([1]));
    var barW = Math.min(46, (chartW / values.length) * 0.55);
    var gap = (chartW - barW * values.length) / (values.length + 1);

    // grid + y labels
    ctx.font = '11px sans-serif';
    ctx.fillStyle = '#7c8b83';
    ctx.strokeStyle = '#e6ece8';
    ctx.lineWidth = 1;
    var steps = 4;
    for (var i = 0; i <= steps; i++) {
      var gy = padT + chartH - (chartH * i / steps);
      ctx.beginPath(); ctx.moveTo(padL, gy); ctx.lineTo(w - padR, gy); ctx.stroke();
      var gv = max * i / steps;
      ctx.fillText(formatNumber(gv, moneyLabel), 4, gy + 4);
    }

    // bars
    for (var j = 0; j < values.length; j++) {
      var bh = (values[j] / max) * chartH;
      var bx = padL + gap + j * (barW + gap);
      var by = padT + chartH - bh;
      var grad = ctx.createLinearGradient(0, by, 0, padT + chartH);
      grad.addColorStop(0, '#3a9a58');
      grad.addColorStop(1, '#2e7d49');
      ctx.fillStyle = grad;
      ctx.beginPath();
      ctx.roundRect(bx, by, barW, bh, 4);
      ctx.fill();
      // label
      ctx.fillStyle = '#4b5a52';
      ctx.textAlign = 'center';
      ctx.fillText(labels[j], bx + barW / 2, h - 8);
    }
    ctx.textAlign = 'left';
  }

  function drawStatusChart(canvas, data) {
    if (!canvas) return;
    var dpr = window.devicePixelRatio || 1;
    var ctx = canvas.getContext('2d');
    var w = canvas.clientWidth || 300;
    var h = parseInt(canvas.getAttribute('height') || '220', 10);
    canvas.width = w * dpr; canvas.height = h * dpr;
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, w, h);

    var palette = {
      pending: '#f2b13c', confirmed: '#4a90d9', processing: '#9b6bdb',
      shipped: '#2bbd7e', delivered: '#3a9a58', cancelled: '#e03444'
    };
    var total = 0;
    for (var k in data) total += data[k];
    if (total === 0) {
      ctx.fillStyle = '#7c8b83'; ctx.textAlign = 'center';
      ctx.fillText('No data yet', w / 2, h / 2); ctx.textAlign = 'left';
      return;
    }

    var cx = w / 2, cy = h / 2, radius = Math.min(w, h) / 2 - 26;
    var start = -Math.PI / 2;
    ctx.lineWidth = 26;
    for (var key in data) {
      var val = data[key];
      if (val <= 0) continue;
      var angle = (val / total) * Math.PI * 2;
      ctx.beginPath();
      ctx.arc(cx, cy, radius, start, start + angle);
      ctx.strokeStyle = palette[key] || '#3a9a58';
      ctx.stroke();
      start += angle;
    }

    // center label
    ctx.fillStyle = '#1c2320';
    ctx.textAlign = 'center';
    ctx.font = '700 22px sans-serif';
    ctx.fillText(String(total), cx, cy - 6);
    ctx.font = '12px sans-serif';
    ctx.fillStyle = '#7c8b83';
    ctx.fillText('orders', cx, cy + 14);
    ctx.textAlign = 'left';

    // legend
    var ly = h - 20;
    var lx = 12;
    ctx.font = '12px sans-serif';
    ctx.fillStyle = '#4b5a52';
    for (var lk in data) {
      ctx.fillStyle = palette[lk] || '#3a9a58';
      ctx.fillRect(lx, ly - 9, 12, 12);
      ctx.fillStyle = '#4b5a52';
      ctx.fillText(cap(lk) + ': ' + data[lk], lx + 18, ly);
      lx += 78;
      if (lx > w - 90) { lx = 12; ly -= 22; }
    }
  }

  function formatNumber(n, moneyLabel) {
    if (moneyLabel) return '\u09F3' + n.toLocaleString('en-US');
    return n.toLocaleString('en-US');
  }
  function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

  function initCharts() {
    var sales = document.getElementById('salesChart');
    if (sales && window.SALES_DATA) {
      var labels = window.SALES_DATA.map(function (d) { return d.day; });
      var values = window.SALES_DATA.map(function (d) { return d.total; });
      drawBarChart(sales, labels, values, '#2e7d49', true);
    }
    var status = document.getElementById('statusChart');
    if (status && window.STATUS_DATA) {
      drawStatusChart(status, window.STATUS_DATA);
    }
  }

  // ---------- Dynamic variants in product form ----------
  function initVariantRows() {
    var wrap = document.getElementById('variantRows');
    var addBtn = document.getElementById('addVariantBtn');
    if (!wrap || !addBtn) return;

    function rowHtml(id, name, weight, price, compare, stock, isDefault) {
      return '' +
        '<div class="variant-row" style="border:1px solid var(--line);border-radius:10px;padding:12px;display:grid;gap:10px;grid-template-columns:1fr 1fr 1fr 1fr 1fr auto;align-items:end;margin-bottom:10px">' +
        '<div><label style="font-size:12px;font-weight:700">Name</label><input type="text" name="variant_names[]" value="' + (name || '') + '" placeholder="1 KG" required></div>' +
        '<div><label style="font-size:12px;font-weight:700">Weight (kg)</label><input type="number" step="0.01" min="0" name="variant_weights[]" value="' + (weight || '') + '" required></div>' +
        '<div><label style="font-size:12px;font-weight:700">Price</label><input type="number" step="0.01" min="0" name="variant_prices[]" value="' + (price || '') + '" required></div>' +
        '<div><label style="font-size:12px;font-weight:700">Compare Price</label><input type="number" step="0.01" min="0" name="variant_compare[]" value="' + (compare || '') + '"></div>' +
        '<div><label style="font-size:12px;font-weight:700">Stock</label><input type="number" min="0" name="variant_stocks[]" value="' + (stock || '0') + '"></div>' +
        '<div style="display:flex;gap:6px;align-items:center">' +
        '<label style="font-size:12px;font-weight:700"><input type="radio" name="variant_default" value="' + id + '" ' + (isDefault ? 'checked' : '') + '> Default</label>' +
        '<button type="button" class="btn btn-danger btn-sm remove-variant">×</button>' +
        '</div></div>';
    }

    addBtn.addEventListener('click', function () {
      var idx = wrap.children.length;
      wrap.insertAdjacentHTML('beforeend', rowHtml('new' + idx, '', '', '', '', '0', false));
      wireRemove();
      initLucide();
    });

    function wireRemove() {
      wrap.querySelectorAll('.remove-variant').forEach(function (btn) {
        btn.addEventListener('click', function () {
          btn.closest('.variant-row').remove();
        });
      });
    }
    wireRemove();
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
            ? '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7 10 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>'
            : '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>') +
          '</svg>';
        input.focus();
      });
    });
  }

  function init() {
    initLucide();
    initSidebar();
    initForms();
    initConfirm();
    initCharts();
    initVariantRows();
    initPasswordToggle();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
