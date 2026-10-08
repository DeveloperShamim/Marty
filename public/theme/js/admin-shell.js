/* Admin shell: phone menu drawer */
(function () {
  var sb = document.getElementById('sidebar');
  var bd = document.getElementById('backdrop');
  var btn = document.getElementById('menuBtn');
  var closeBtn = document.getElementById('sidebarClose');

  function openSidebar() {
    if (!sb) return;
    sb.classList.remove('-translate-x-full');
    if (bd) bd.classList.remove('hidden');
    document.body.classList.add('admin-sidebar-open', 'overflow-hidden');
    var cur = sb.querySelector('[aria-current="page"]');
    if (cur) cur.scrollIntoView({ block: 'center' });
  }

  function closeSidebar() {
    if (!sb) return;
    sb.classList.add('-translate-x-full');
    if (bd) bd.classList.add('hidden');
    document.body.classList.remove('admin-sidebar-open', 'overflow-hidden');
  }

  if (btn) btn.addEventListener('click', openSidebar);
  if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
  if (bd) bd.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSidebar(); });
  window.addEventListener('resize', function () { if (window.innerWidth >= 1024) closeSidebar(); });
})();

/* Desktop icon rail: each group button opens a flyout with its pages; Dashboard / Log out get tooltips. */
(function () {
  var sb = document.getElementById('sidebar');
  if (!sb) return;
  var tip = document.getElementById('sidebarTip');
  var desktop = function () { return window.innerWidth >= 1024; };
  var groups = Array.prototype.slice.call(sb.querySelectorAll('.sb-group')).filter(function (g) { return g.querySelector('.sb-flyout'); });
  var openGroup = null, closeTimer = null, openTimer = null;

  function place(g) {
    var btn = g.querySelector('.sb-rail-btn');
    var fly = g.querySelector('.sb-flyout');
    var r = btn.getBoundingClientRect();
    var h = fly.offsetHeight || 200;
    var top = Math.max(12, Math.min(r.top - 8, window.innerHeight - h - 12));
    fly.style.setProperty('--fly-top', top + 'px');
  }
  function open(g) {
    clearTimeout(closeTimer);
    if (openGroup && openGroup !== g) close(openGroup);
    openGroup = g;
    g.classList.add('is-open');
    place(g);
    g.querySelector('.sb-rail-btn').setAttribute('aria-expanded', 'true');
    if (tip) tip.classList.add('hidden');
  }
  function close(g) {
    if (!g) return;
    g.classList.remove('is-open');
    g.querySelector('.sb-rail-btn').setAttribute('aria-expanded', 'false');
    if (openGroup === g) openGroup = null;
  }

  groups.forEach(function (g) {
    var btn = g.querySelector('.sb-rail-btn');
    btn.addEventListener('click', function () {
      if (g.classList.contains('is-open')) close(g); else { open(g); var first = g.querySelector('.sb-flyout .sb-item'); if (first && document.activeElement === btn && !btn.matches(':hover')) first.focus(); }
    });
    g.addEventListener('mouseenter', function () {
      if (!desktop()) return;
      clearTimeout(closeTimer); clearTimeout(openTimer);
      openTimer = setTimeout(function () { open(g); }, openGroup ? 0 : 80);
    });
    g.addEventListener('mouseleave', function () {
      if (!desktop()) return;
      clearTimeout(openTimer);
      closeTimer = setTimeout(function () { close(g); }, 180);
    });
    g.addEventListener('focusout', function (e) { if (!g.contains(e.relatedTarget)) close(g); });
  });
  document.addEventListener('click', function (e) { if (openGroup && !openGroup.contains(e.target)) close(openGroup); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && openGroup) { var b = openGroup.querySelector('.sb-rail-btn'); close(openGroup); b.focus(); }
  });
  window.addEventListener('resize', function () { if (openGroup) close(openGroup); });

  if (tip) {
    sb.querySelectorAll('.sb-direct').forEach(function (a) {
      a.addEventListener('mouseenter', function () {
        if (!desktop()) return;
        var r = a.getBoundingClientRect();
        tip.textContent = a.dataset.label;
        tip.style.left = (r.right + 12) + 'px';
        tip.style.top = (r.top + r.height / 2) + 'px';
        tip.style.transform = 'translateY(-50%)';
        tip.classList.remove('hidden');
      });
      a.addEventListener('mouseleave', function () { tip.classList.add('hidden'); });
    });
  }
})();

/* Page search in the top bar: lists matching menu pages; Enter opens the first, "/" focuses it. */
(function () {
  var search = document.getElementById('sidebarSearch');
  var box = document.getElementById('navSearchResults');
  var sb = document.getElementById('sidebar');
  if (!search || !box || !sb) return;
  var pages = Array.prototype.slice.call(sb.querySelectorAll('a.sb-item')).map(function (a) {
    var g = a.closest('.sb-group');
    return { href: a.href, label: a.dataset.label, search: a.dataset.search || '', group: g ? g.dataset.group : '', icon: (a.querySelector('svg') || {}).outerHTML || '' };
  });
  var hits = [], active = 0;
  function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
  function render() {
    var q = search.value.trim().toLowerCase();
    if (!q) { box.classList.add('hidden'); hits = []; return; }
    hits = pages.filter(function (p) { return q.split(/\s+/).every(function (w) { return (p.search + ' ' + p.group.toLowerCase()).indexOf(w) !== -1; }); }).slice(0, 8);
    active = 0;
    box.innerHTML = hits.length ? hits.map(function (p, i) {
      return '<a href="' + p.href + '" role="option" data-i="' + i + '" class="flex items-center gap-2.5 h-9 px-2.5 rounded-xl text-[13px] text-gray-700 hover:bg-gray-100' + (i === 0 ? ' bg-gray-100 text-gray-900' : '') + '">' +
        '<span class="text-gray-400 [&>svg]:w-4 [&>svg]:h-4">' + p.icon + '</span><span class="flex-1 truncate font-medium">' + esc(p.label) + '</span>' +
        '<span class="text-[11px] text-gray-400">' + esc(p.group) + '</span></a>';
    }).join('') : '<p class="px-3 py-3 text-[13px] text-gray-400">No matching pages</p>';
    box.classList.remove('hidden');
  }
  function highlight() {
    box.querySelectorAll('[data-i]').forEach(function (a) {
      var on = +a.dataset.i === active;
      a.classList.toggle('bg-gray-100', on); a.classList.toggle('text-gray-900', on);
    });
  }
  search.addEventListener('input', render);
  search.addEventListener('focus', render);
  search.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowDown' && hits.length) { e.preventDefault(); active = (active + 1) % hits.length; highlight(); }
    else if (e.key === 'ArrowUp' && hits.length) { e.preventDefault(); active = (active - 1 + hits.length) % hits.length; highlight(); }
    else if (e.key === 'Enter' && hits[active]) { e.preventDefault(); window.location = hits[active].href; }
    else if (e.key === 'Escape') { e.stopPropagation(); if (search.value) { search.value = ''; render(); } else search.blur(); }
  });
  document.addEventListener('click', function (e) { if (!box.contains(e.target) && e.target !== search) box.classList.add('hidden'); });
  document.addEventListener('keydown', function (e) {
    if (e.key !== '/' || e.metaKey || e.ctrlKey || e.altKey || window.innerWidth < 768) return;
    var t = e.target;
    if (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName)) return;
    e.preventDefault();
    search.focus();
  });
})();

/* Invoice format: chosen on the order page (or on the invoice itself) and used by every invoice link. */
(function () {
  var KEY = 'admin.invoice.format';
  function get() { try { return localStorage.getItem(KEY) || 'a4'; } catch (e) { return 'a4'; } }

  document.querySelectorAll('select[data-invoice-format]').forEach(function (sel) {
    if (sel.querySelector('option[value="' + get() + '"]')) sel.value = get();
    sel.addEventListener('change', function () {
      try { localStorage.setItem(KEY, sel.value); } catch (e) {}
    });
  });

  // Applied when the link is used, so it also covers middle-click and "open in new tab".
  function apply(a) {
    var url = new URL(a.href, window.location.href);
    url.searchParams.set('format', get());
    a.href = url.toString();
  }
  ['click', 'auxclick', 'contextmenu'].forEach(function (type) {
    document.addEventListener(type, function (e) {
      var a = e.target.closest && e.target.closest('a[data-invoice-link]');
      if (a) apply(a);
    }, true);
  });
})();

/* Warn before printing an order's invoice or label again, so it isn't packed twice. */
(function () {
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[data-print-link]');
    if (!a) return;
    var warning = a.getAttribute('data-print-warning');
    if (warning && !window.confirm(warning + '\n\nMake sure it is not packed twice. Print again?')) {
      e.preventDefault();
      e.stopImmediatePropagation();
      return;
    }
    // This page doesn't reload after printing in the other tab, so remember the click here.
    if (!warning) {
      var time = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
      a.setAttribute('data-print-warning', 'This was opened for printing from this page at ' + time + '.');
    }
  }, true);
})();

/* Account menu in the top bar: close on outside click or Escape. */
(function () {
  var menu = document.querySelector('[data-account-menu]');
  if (!menu) return;
  document.addEventListener('click', function (e) { if (menu.open && !menu.contains(e.target)) menu.open = false; });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') menu.open = false; });
})();
