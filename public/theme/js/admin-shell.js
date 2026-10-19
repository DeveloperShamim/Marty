/* StyleHub Laravel admin shell — mobile sidebar + scroll chaining */
(function () {
  var sb = document.getElementById('sidebar');
  var bd = document.getElementById('backdrop');
  var btn = document.getElementById('menuBtn');
  var closeBtn = document.getElementById('sidebarClose');
  var nav = sb ? (sb.classList.contains('sb-cards') ? sb : sb.querySelector('.sidebar-nav')) : null;

  function openSidebar() {
    if (!sb) return;
    sb.classList.remove('-translate-x-full');
    if (bd) bd.classList.remove('hidden');
    document.body.classList.add('admin-sidebar-open');
  }

  function closeSidebar() {
    if (!sb) return;
    sb.classList.add('-translate-x-full');
    if (bd) bd.classList.add('hidden');
    document.body.classList.remove('admin-sidebar-open');
  }

  if (btn) btn.addEventListener('click', openSidebar);
  if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
  if (bd) bd.addEventListener('click', closeSidebar);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeSidebar();
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth >= 1024) closeSidebar();
  });

  // When the pointer is over the sidebar and the nav can't scroll further
  // (or has nothing to scroll), forward the wheel to the page.
  if (sb) {
    sb.addEventListener('wheel', function (e) {
      if (document.body.classList.contains('admin-sidebar-open')) return;

      var target = nav && nav.contains(e.target) ? nav : null;
      if (!target) {
        window.scrollBy({ top: e.deltaY, left: 0, behavior: 'auto' });
        e.preventDefault();
        return;
      }

      var atTop = target.scrollTop <= 0;
      var atBottom = target.scrollTop + target.clientHeight >= target.scrollHeight - 1;
      var scrollingDown = e.deltaY > 0;
      var scrollingUp = e.deltaY < 0;

      if ((scrollingDown && atBottom) || (scrollingUp && atTop) || target.scrollHeight <= target.clientHeight + 1) {
        window.scrollBy({ top: e.deltaY, left: 0, behavior: 'auto' });
        e.preventDefault();
      }
    }, { passive: false });
  }
})();

/* Sidebar: group folding, menu search, desktop icon rail */
(function () {
  var sb = document.getElementById('sidebar');
  if (!sb) return;
  var root = document.documentElement;
  var nav = sb.querySelector('.sidebar-nav');
  var search = document.getElementById('sidebarSearch');
  var empty = document.getElementById('sidebarNoResults');
  var tip = document.getElementById('sidebarTip');
  var collapseBtn = document.getElementById('sidebarCollapse');
  var groups = Array.prototype.slice.call(sb.querySelectorAll('.sb-group'));

  function store(key, value) { try { localStorage.setItem(key, value); } catch (e) {} }

  // Fold / unfold a group and remember it.
  sb.querySelectorAll('.sb-group-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var g = btn.closest('.sb-group');
      var folded = g.classList.toggle('is-folded');
      btn.setAttribute('aria-expanded', folded ? 'false' : 'true');
      store('admin.sidebar.folded', JSON.stringify(groups.filter(function (x) {
        return x.classList.contains('is-folded');
      }).map(function (x) { return x.dataset.group; })));
    });
  });

  // Keep the current page visible in a long menu.
  var current = nav && nav.querySelector('[aria-current="page"]');
  if (current && nav.scrollHeight > nav.clientHeight) {
    var top = current.offsetTop - nav.offsetTop;
    if (top + current.offsetHeight > nav.clientHeight) nav.scrollTop = top - nav.clientHeight / 2;
  }

  // Search filters the menu; Enter opens the first match.
  function hits() { return Array.prototype.slice.call(nav.querySelectorAll('.sb-item:not(.hidden)')); }
  function filter() {
    var q = search.value.trim().toLowerCase();
    sb.classList.toggle('is-searching', q !== '');
    var any = false;
    groups.forEach(function (g) {
      var shown = 0;
      g.querySelectorAll('.sb-item').forEach(function (a) {
        var ok = !q || q.split(/\s+/).every(function (w) { return a.dataset.search.indexOf(w) !== -1; });
        a.classList.toggle('hidden', !ok);
        a.classList.remove('is-hit');
        if (ok) shown++;
      });
      g.classList.toggle('hidden', shown === 0);
      if (shown) any = true;
    });
    if (q && any) hits()[0].classList.add('is-hit');
    if (empty) empty.classList.toggle('hidden', any);
  }
  if (search) {
    search.addEventListener('input', filter);
    search.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        var first = hits()[0];
        if (search.value.trim() && first) { e.preventDefault(); window.location = first.href; }
      } else if (e.key === 'Escape') {
        e.stopPropagation();
        if (search.value) { search.value = ''; filter(); } else { search.blur(); }
      }
    });
  }

  // "/" jumps to the menu search (desktop), unless the user is typing somewhere.
  document.addEventListener('keydown', function (e) {
    if (e.key !== '/' || e.metaKey || e.ctrlKey || e.altKey || !search || window.innerWidth < 1024) return;
    var t = e.target;
    if (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName)) return;
    e.preventDefault();
    if (root.classList.contains('sb-collapsed')) setCollapsed(false);
    search.focus();
  });

  // Desktop icon rail.
  function setCollapsed(on) {
    root.classList.toggle('sb-collapsed', on);
    store('admin.sidebar.collapsed', on ? '1' : '0');
    if (collapseBtn) collapseBtn.setAttribute('aria-label', on ? 'Expand sidebar' : 'Collapse sidebar');
    if (on && search && search.value) { search.value = ''; filter(); }
    if (tip) tip.classList.add('hidden');
  }
  if (collapseBtn) {
    collapseBtn.setAttribute('aria-label', root.classList.contains('sb-collapsed') ? 'Expand sidebar' : 'Collapse sidebar');
    collapseBtn.addEventListener('click', function () { setCollapsed(!root.classList.contains('sb-collapsed')); });
  }

  // Labels as tooltips while collapsed (the nav scrolls, so CSS tooltips would be clipped).
  if (tip) {
    sb.addEventListener('mouseover', function (e) {
      var a = e.target.closest('.sb-item');
      if (!a || !root.classList.contains('sb-collapsed') || window.innerWidth < 1024) return;
      var r = a.getBoundingClientRect();
      var badge = a.querySelector('.sb-badge');
      tip.textContent = a.dataset.label + (badge ? ' (' + badge.textContent.trim() + ')' : '');
      tip.style.left = (r.right + 10) + 'px';
      tip.style.top = (r.top + r.height / 2) + 'px';
      tip.style.transform = 'translateY(-50%)';
      tip.classList.remove('hidden');
    });
    sb.addEventListener('mouseout', function (e) {
      if (!e.relatedTarget || !sb.contains(e.relatedTarget) || !e.relatedTarget.closest('.sb-item')) tip.classList.add('hidden');
    });
    if (nav) nav.addEventListener('scroll', function () { tip.classList.add('hidden'); });
  }
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


/* ⋯ row menus (details.order-menu): open as a floating panel pinned to the button, so a card or
   table that clips its overflow can't cut the menu off. Opens upward when there's no room below. */
(function () {
  var GAP = 6, EDGE = 8;
  function panelOf(d) { return d.querySelector(':scope > summary + *'); }
  function reset(p) { ['position', 'top', 'bottom', 'left', 'right', 'margin', 'maxHeight', 'overflowY'].forEach(function (k) { p.style[k] = ''; }); }
  function place(d) {
    var p = panelOf(d);
    if (!p) return;
    reset(p);
    var s = d.querySelector('summary').getBoundingClientRect();
    var vw = document.documentElement.clientWidth, vh = window.innerHeight;
    // The phone tab bar covers the bottom of the screen.
    var bar = document.getElementById('bottomNav');
    var bottomEdge = bar && bar.getBoundingClientRect().height && getComputedStyle(bar).display !== 'none' ? bar.getBoundingClientRect().top : vh;
    p.style.position = 'fixed'; p.style.margin = '0'; p.style.right = 'auto'; p.style.bottom = 'auto';
    var w = p.offsetWidth, h = p.offsetHeight;
    var below = bottomEdge - s.bottom - GAP - EDGE, above = s.top - GAP - EDGE;
    var top = (h <= below || below >= above) ? s.bottom + GAP : s.top - GAP - Math.min(h, above);
    if (h > Math.max(below, above)) { p.style.maxHeight = Math.max(below, above) + 'px'; p.style.overflowY = 'auto'; }
    var left = Math.min(Math.max(EDGE, s.right - w), vw - w - EDGE);
    p.style.top = top + 'px'; p.style.left = left + 'px';
    // A blurred or transformed ancestor becomes the fixed panel's frame: correct for its offset.
    var r = p.getBoundingClientRect();
    if (Math.abs(r.top - top) > 1 || Math.abs(r.left - left) > 1) {
      p.style.top = (2 * top - r.top) + 'px'; p.style.left = (2 * left - r.left) + 'px';
    }
  }
  document.addEventListener('toggle', function (e) {
    var d = e.target;
    if (!d.matches || !d.matches('details.order-menu')) return;
    if (d.open) {
      document.querySelectorAll('details.order-menu[open]').forEach(function (o) { if (o !== d) o.removeAttribute('open'); });
      // Lift the row's own layer so the next row's buttons can't show through the menu.
      for (var a = d.parentElement; a && a !== document.body; a = a.parentElement) {
        if (getComputedStyle(a).zIndex !== 'auto') { a.style.zIndex = '30'; d._lifted = a; break; }
      }
      place(d);
    } else {
      var p = panelOf(d);
      if (p) reset(p);
      if (d._lifted) { d._lifted.style.zIndex = ''; d._lifted = null; }
    }
  }, true);
  // The panel doesn't follow the page, so close it when the page scrolls or resizes.
  function closeAll() { document.querySelectorAll('details.order-menu[open]').forEach(function (d) { d.removeAttribute('open'); }); }
  window.addEventListener('scroll', closeAll, { passive: true });
  window.addEventListener('resize', closeAll);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAll(); });
})();
