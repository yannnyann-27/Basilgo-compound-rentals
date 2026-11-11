/* Basilgo Compound interface behaviour. Presentation only - all rules are enforced again on the server. */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var php = function (n) { return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
  var esc = function (s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
  var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // staggered entrance
  $$('.stagger').forEach(function (g) { Array.prototype.forEach.call(g.children, function (el, i) { el.style.setProperty('--i', i); }); });
  $$('tbody.rows-in').forEach(function (tb) { Array.prototype.forEach.call(tb.children, function (tr, i) { tr.style.setProperty('--i', Math.min(i, 10)); }); });

  // mobile drawer
  var sb = $('#sidebar'), ov = $('#sidebar-overlay'), mb = $('#menu-btn');
  function drawer(open) {
    if (!sb) return;
    sb.classList.toggle('open', open); ov.classList.toggle('opacity-0', !open); ov.classList.toggle('pointer-events-none', !open);
    document.body.style.overflow = open ? 'hidden' : '';
  }
  mb && mb.addEventListener('click', function () { drawer(!sb.classList.contains('open')); });
  ov && ov.addEventListener('click', function () { drawer(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') drawer(false); });

  $$('[data-today]').forEach(function (el) { el.textContent = new Date().toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' }); });

  // count-up numbers
  $$('[data-count]').forEach(function (el) {
    if (calm) return;
    var to = parseFloat(el.dataset.count) || 0, money = el.dataset.money === '1', t0 = performance.now();
    (function tick(now) { var k = Math.min(1, (now - t0) / 1000), v = to * (1 - Math.pow(1 - k, 3)); el.textContent = money ? php(v) : Math.round(v); if (k < 1) requestAnimationFrame(tick); })(t0);
  });
  // ring + bars grow in
  setTimeout(function () {
    $$('.ring-arc').forEach(function (c) { c.style.strokeDasharray = c.dataset.dash + ' 999'; });
    $$('.fill[data-w]').forEach(function (f) { f.style.width = f.dataset.w + '%'; });
  }, 80);

  // toasts auto-dismiss
  $$('.toast').forEach(function (t) {
    var x = function () { t.classList.add('out'); setTimeout(function () { t.remove(); }, 300); };
    setTimeout(x, 5000); var c = $('.toast-close', t); c && c.addEventListener('click', x);
  });
  // confirm dialogs
  $$('[data-confirm]').forEach(function (el) { el.addEventListener('click', function (e) { if (!confirm(el.dataset.confirm)) e.preventDefault(); }); });
  // "/" focuses search
  document.addEventListener('keydown', function (e) { var s = $('input[name=search]'); if (e.key === '/' && s && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) { e.preventDefault(); s.focus(); } });
  // auto-submit filters
  $$('select[data-auto]').forEach(function (s) { s.addEventListener('change', function () { s.form.submit(); }); });
  // password reveal
  $$('[data-toggle-pass]').forEach(function (b) { b.addEventListener('click', function () { var i = $(b.dataset.togglePass); i.type = i.type === 'password' ? 'text' : 'password'; }); });
  // demo account fill
  $$('[data-fill]').forEach(function (b) { b.addEventListener('click', function () { var p = b.dataset.fill.split(':'); $('#username').value = p[0]; $('#password').value = p[1]; $('#username').form.requestSubmit(); }); });

  // double-submit protection: after a form is sent, lock its buttons and show a spinner (important for payments)
  function unlock(f) { $$('button', f).forEach(function (b) { b.disabled = b.hasAttribute('data-was-disabled'); b.classList.remove('is-loading'); }); }
  $$('form[data-once]').forEach(function (f) {
    $$('button[disabled]', f).forEach(function (b) { b.setAttribute('data-was-disabled', '1'); });
    f.addEventListener('submit', function (e) {
      if (e.defaultPrevented) return;
      var sub = e.submitter || $('button:not([type=button])', f);
      setTimeout(function () { $$('button:not([type=button])', f).forEach(function (b) { b.disabled = true; }); if (sub) sub.classList.add('is-loading'); }, 0); // after the browser has read the clicked button's value
    });
  });
  window.addEventListener('pageshow', function (e) { if (e.persisted) $$('form[data-once]').forEach(unlock); });

  // live bill preview (billing page)
  var bf = $('#bill-form');
  if (bf) {
    var T = JSON.parse(bf.dataset.tenants), R = JSON.parse(bf.dataset.rates), pv = $('#bill-preview');
    var upd = function () {
      var t = T[bf.tenant_id.value]; if (!t) return;
      var k = parseFloat(bf.kwh.value) || 0, m = parseFloat(bf.m3.value) || 0, free = t.water_plan === 'free';
      var el = k * R.elec, w = free ? 0 : m * R.water, tot = t.rent + el + w;
      pv.innerHTML = '<div class="line"><span>Monthly rent</span><b>' + php(t.rent) + '</b></div>' +
        '<div class="line"><span>Electricity · ' + k + ' kWh × ' + php(R.elec) + '</span><b>' + php(el) + '</b></div>' +
        '<div class="line"><span>Water · ' + (free ? 'Free water account' : m + ' m³ × ' + php(R.water)) + '</span><b>' + php(w) + '</b></div>' +
        '<div class="line line-total"><span>Total to bill</span><b>' + php(tot) + '</b></div>' +
        '<p class="mt-2 rounded-xl bg-slate-50 p-3 text-sm text-slate-500">Balance ' + php(t.balance) + ' → <b class="text-rose-600">' + php(t.balance + tot) + '</b> after this bill</p>';
      bf.m3.disabled = free; bf.m3.placeholder = free ? 'Free water — not billed' : '0';
    };
    ['tenant_id', 'kwh', 'm3'].forEach(function (n) { bf[n].addEventListener('input', upd); }); upd();
  }

  // cash payment form: show what the balance becomes
  var pf = $('#pay-form');
  if (pf) {
    var P = JSON.parse(pf.dataset.tenants), sel = pf.tenant_id, amt = pf.amount, out = $('#pay-preview'), full = $('[data-pay-full]', pf);
    var ini = function (n) { var p = n.trim().split(/\s+/); return (p[0][0] + (p.length > 1 ? p[p.length - 1][0] : '')).toUpperCase(); };
    var draw = function () {
      var t = P[sel.value];
      if (!t) { out.innerHTML = '<p class="text-sm text-slate-500">Choose a tenant to see their balance.</p>'; amt.removeAttribute('max'); return; }
      amt.max = t.due;
      var a = parseFloat(amt.value) || 0, over = a > t.due + 0.001, after = Math.max(0, t.balance - a);
      out.innerHTML = '<div class="mb-3 flex items-center gap-3"><span class="avatar">' + esc(ini(t.name)) + '</span><div><b class="text-slate-900">' + esc(t.name) + '</b><div class="text-xs text-slate-400">Room ' + esc(t.room) + '</div></div></div>' +
        '<div class="line"><span>Current balance</span><b>' + php(t.balance) + '</b></div>' +
        (t.pending > 0 ? '<div class="line"><span>Cash already awaiting approval</span><b class="text-amber-700">' + php(t.pending) + '</b></div>' : '') +
        '<div class="line"><span>Cash received now</span><b class="text-emerald-700">− ' + php(a) + '</b></div>' +
        '<div class="line line-total"><span>Balance after</span><b class="' + (after > 0 ? '!text-rose-600' : '!text-emerald-700') + '">' + php(after) + '</b></div>' +
        (over ? '<div class="alert alert-error mb-0 mt-3"><span>More than what is still owed (' + php(t.due) + ').</span></div>' : '');
    };
    sel.addEventListener('change', function () { draw(); if (sel.value) amt.focus(); });
    amt.addEventListener('input', draw);
    full && full.addEventListener('click', function () { var t = P[sel.value]; if (!t) { sel.focus(); return; } amt.value = t.due.toFixed(2); draw(); });
    draw();
  }
})();
