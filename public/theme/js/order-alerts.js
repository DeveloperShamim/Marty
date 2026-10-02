/*
 * New-order alerts for the admin.
 * Polls the order feed (every 20s, 60s in a background tab) and shows a card per new storefront
 * order with Confirm / Open / Print. Alerts live in localStorage, so they follow the admin from
 * page to page and stay in sync across tabs; the chime and desktop notification fire once.
 */
(function () {
  var cfg = document.getElementById('orderAlertsConfig');
  if (!cfg || !window.fetch) return;

  var FEED = cfg.getAttribute('data-feed');
  var REVIEW_URL = cfg.getAttribute('data-review-url');
  var ICON = cfg.getAttribute('data-icon');
  var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var K = {
    seen: 'admin.orders.lastSeen', alerts: 'admin.orders.alerts', more: 'admin.orders.more',
    chimed: 'admin.orders.chimed', sound: 'admin.orders.sound', count: 'admin.orders.reviewCount',
  };
  var VISIBLE_MS = 20000, HIDDEN_MS = 60000, MAX_ALERTS = 20;
  function shownCount() { return window.innerWidth < 640 ? 1 : 3; } // one card on phones, three on larger screens

  function get(k, d) { try { var v = localStorage.getItem(k); return v === null ? d : JSON.parse(v); } catch (e) { return d; } }
  function set(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }
  function esc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; }

  /* ---------- UI ---------- */
  var stack = document.createElement('section');
  stack.id = 'orderAlerts';
  stack.setAttribute('aria-label', 'New orders');
  stack.setAttribute('aria-live', 'polite');
  stack.className = 'fixed z-[45] top-[4.5rem] inset-x-3 sm:inset-x-auto sm:right-4 sm:w-[370px] space-y-2 pointer-events-none';
  document.body.appendChild(stack);

  var feedback = {}; // order id -> { text, tone } while an action is in progress

  function ago(iso) {
    var s = Math.round((Date.now() - new Date(iso).getTime()) / 1000);
    if (s < 45) return 'just now';
    var m = Math.round(s / 60);
    if (m < 60) return m + ' min ago';
    var h = Math.round(m / 60);
    return h < 24 ? h + ' h ago' : new Date(iso).toLocaleDateString();
  }

  var RISK = {
    low: ['Low risk', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
    medium: ['Medium risk', 'bg-amber-50 text-amber-800 border-amber-200'],
    high: ['High risk', 'bg-rose-50 text-rose-700 border-rose-200'],
  };

  // Courier delivery history (BD Courier), when it has been looked up.
  function history(h) {
    if (!h) return '';
    var tone = { high: 'text-rose-700', medium: 'text-amber-800', low: 'text-emerald-700' }[h.level] || 'text-gray-600';
    var text = h.reports ? 'Reported for fraud ' + h.reports + '×' : h.total ? h.delivered + '/' + h.total + ' parcels delivered (' + Math.round(h.ratio) + '%)' : 'No courier history yet';
    return '<p class="mt-1 text-[11px] font-semibold ' + tone + '" title="' + esc(h.label) + '">Courier history: ' + esc(text) + '</p>';
  }

  function card(o) {
    var risk = RISK[o.risk] || RISK.low;
    var fb = feedback[o.id];
    var pay = o.is_cod ? 'Cash on delivery'
      : esc(o.method) + (o.txn ? ' · TrxID <span class="font-mono font-semibold text-gray-800">' + esc(o.txn) + '</span>' : '') +
        (o.sender ? ' · from <span class="font-mono">' + esc(o.sender) + '</span>' : '');
    return '' +
      '<article class="pointer-events-auto bg-white rounded-2xl shadow-xl ring-1 ring-black/5 border-l-4 ' + (o.risk === 'high' ? 'border-rose-500' : 'border-teal-600') + ' p-3.5 text-[13px] text-gray-700" data-alert="' + o.id + '">' +
        '<header class="flex items-center gap-2 text-xs">' +
          '<span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75 animate-ping"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-teal-600"></span></span>' +
          '<b class="text-gray-900">New order</b><span class="text-gray-400">' + ago(o.created_at) + '</span>' +
          '<button type="button" data-act="dismiss" class="ml-auto -mr-1 h-7 w-7 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 flex items-center justify-center" aria-label="Dismiss alert for ' + esc(o.number) + '">' +
            '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>' +
        '</header>' +
        '<div class="mt-1.5 flex items-baseline justify-between gap-3">' +
          '<a href="' + esc(o.urls.show) + '" data-act="open" class="font-mono font-bold text-teal-800 hover:underline truncate">' + esc(o.number) + '</a>' +
          '<span class="font-bold text-gray-900 text-[15px] whitespace-nowrap">' + esc(o.total) + '</span>' +
        '</div>' +
        '<p class="mt-0.5 truncate"><b class="text-gray-900 font-semibold">' + esc(o.customer) + '</b> · <span class="font-mono">' + esc(o.phone) + '</span>' + (o.city ? ' · ' + esc(o.city) : '') + '</p>' +
        '<p class="mt-0.5 text-gray-500 truncate" title="' + esc(o.items) + '">' + esc(o.items) + '</p>' +
        history(o.history) +
        '<div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px]">' +
          '<span class="text-gray-600">' + pay + '</span>' +
          '<span class="ml-auto px-1.5 py-0.5 rounded-md border font-semibold ' + risk[1] + '">' + risk[0] + '</span>' +
        '</div>' +
        (fb ? '<p class="mt-2 text-xs font-medium ' + (fb.tone === 'ok' ? 'text-emerald-700' : fb.tone === 'error' ? 'text-rose-700' : 'text-gray-500') + '">' + esc(fb.text) + '</p>' : '') +
        '<div class="mt-2.5 flex items-center gap-1.5">' +
          (o.needs_review
            ? '<button type="button" data-act="accept" class="h-8 px-3 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold disabled:opacity-60"' + (fb && fb.tone === 'busy' ? ' disabled' : '') + '>' + esc(o.accept_label) + '</button>'
            : '') +
          '<a href="' + esc(o.urls.show) + '" data-act="open" class="h-8 px-3 rounded-lg border border-gray-200 hover:bg-gray-50 text-xs font-semibold text-gray-800 inline-flex items-center">Open</a>' +
          '<a href="' + esc(o.urls.invoice) + '" target="_blank" data-invoice-link class="h-8 px-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 inline-flex items-center gap-1 text-xs font-semibold" title="Print invoice">' +
            '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>Print</a>' +
        '</div>' +
      '</article>';
  }

  function render() {
    var alerts = get(K.alerts, []);
    var shown = shownCount();
    var extra = Math.max(0, alerts.length - shown) + (get(K.more, 0) || 0);
    if (!alerts.length) { stack.innerHTML = ''; updateTitle(); return; }

    var soundOn = get(K.sound, true);
    var canAsk = 'Notification' in window && Notification.permission === 'default';
    stack.innerHTML =
      '<div class="pointer-events-auto flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/95 shadow ring-1 ring-black/5 text-[11px] text-gray-500">' +
        '<span class="font-semibold text-gray-700">' + alerts.length + (alerts.length === 1 ? ' new order' : ' new orders') + '</span>' +
        (canAsk ? '<button type="button" data-act="notify" class="underline underline-offset-2 hover:text-gray-800">Desktop alerts</button>' : '') +
        '<button type="button" data-act="sound" class="ml-auto h-6 w-6 rounded-md hover:bg-white/80 flex items-center justify-center" aria-pressed="' + soundOn + '" title="' + (soundOn ? 'Sound on' : 'Sound off') + '" aria-label="' + (soundOn ? 'Turn alert sound off' : 'Turn alert sound on') + '">' +
          (soundOn
            ? '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5 6 9H2v6h4l5 4z"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>'
            : '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5 6 9H2v6h4l5 4z"/><path d="m22 9-6 6"/><path d="m16 9 6 6"/></svg>') +
        '</button>' +
        '<button type="button" data-act="dismiss-all" class="underline underline-offset-2 hover:text-gray-800">Dismiss all</button>' +
      '</div>' +
      alerts.slice(0, shown).map(card).join('') +
      (extra ? '<a href="' + esc(REVIEW_URL) + '" data-act="view-all" class="pointer-events-auto block text-center text-xs font-semibold text-teal-800 bg-white/95 rounded-xl py-2 shadow ring-1 ring-black/5 hover:bg-white">+ ' + extra + ' more new ' + (extra === 1 ? 'order' : 'orders') + ' · View all</a>' : '');
    updateTitle();
  }

  function removeAlert(id) {
    set(K.alerts, get(K.alerts, []).filter(function (a) { return a.id !== id; }));
    delete feedback[id];
    render();
  }

  stack.addEventListener('click', function (e) {
    var el = e.target.closest('[data-act]');
    if (!el) return;
    var act = el.getAttribute('data-act');
    var box = el.closest('[data-alert]');
    var id = box ? parseInt(box.getAttribute('data-alert'), 10) : null;

    if (act === 'dismiss') removeAlert(id);
    else if (act === 'open') { e.preventDefault(); removeAlert(id); window.location.href = el.href; }
    else if (act === 'dismiss-all' || act === 'view-all') { set(K.alerts, []); set(K.more, 0); render(); }
    else if (act === 'sound') { set(K.sound, !get(K.sound, true)); render(); }
    else if (act === 'notify') { Notification.requestPermission().then(render); }
    else if (act === 'accept') accept(id);
  });

  function accept(id) {
    var o = get(K.alerts, []).filter(function (a) { return a.id === id; })[0];
    if (!o) return;
    feedback[id] = { text: 'Saving…', tone: 'busy' };
    render();
    fetch(o.urls.accept, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    }).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, d: d, status: r.status }; });
    }).then(function (res) {
      if (res.ok && res.d.success) {
        feedback[id] = { text: res.d.message || 'Done.', tone: 'ok' };
        var alerts = get(K.alerts, []).map(function (a) { if (a.id === id) a.needs_review = false; return a; });
        set(K.alerts, alerts);
        render();
        setTimeout(function () { removeAlert(id); }, 2500);
        pollNow();
      } else {
        feedback[id] = { text: res.d.message || ('Could not save (error ' + res.status + '). Open the order instead.'), tone: 'error' };
        render();
      }
    }).catch(function () {
      feedback[id] = { text: 'Network error. Try again.', tone: 'error' };
      render();
    });
  }

  /* ---------- Counters (topbar bell, sidebar badge) ---------- */
  function updateCounts(n) {
    set(K.count, n);
    document.querySelectorAll('[data-live-count]').forEach(function (el) {
      el.textContent = n > 99 ? '99+' : n;
      el.style.display = n > 0 ? '' : 'none';
    });
    document.querySelectorAll('[data-live-badge="orders"]').forEach(function (wrap) {
      wrap.style.display = n > 0 ? 'contents' : 'none';
      var b = wrap.querySelector('.sb-badge');
      if (b) b.textContent = n > 99 ? '99+' : n;
      var sr = wrap.querySelector('.sr-only');
      if (sr) sr.textContent = '(' + n + ' to review)';
    });
  }

  /* ---------- Sound, title and desktop notification ---------- */
  var audio = null;
  document.addEventListener('pointerdown', function unlock() {
    try { audio = audio || new (window.AudioContext || window.webkitAudioContext)(); audio.resume(); } catch (e) {}
    document.removeEventListener('pointerdown', unlock);
  });
  function chime() {
    if (!get(K.sound, true) || !audio || audio.state !== 'running') return;
    [[880, 0], [1320, 0.16]].forEach(function (n) {
      var o = audio.createOscillator(), g = audio.createGain(), t = audio.currentTime + n[1];
      o.type = 'sine'; o.frequency.value = n[0];
      g.gain.setValueAtTime(0.0001, t); g.gain.exponentialRampToValueAtTime(0.25, t + 0.02); g.gain.exponentialRampToValueAtTime(0.0001, t + 0.35);
      o.connect(g); g.connect(audio.destination); o.start(t); o.stop(t + 0.4);
    });
  }

  var baseTitle = document.title, flash = null;
  function updateTitle() {
    var n = get(K.alerts, []).length;
    clearInterval(flash); flash = null;
    document.title = baseTitle;
    if (n && document.hidden) {
      var on = false;
      flash = setInterval(function () { on = !on; document.title = on ? '(' + n + ') New order' : baseTitle; }, 1200);
    }
  }

  function notify(orders) {
    if (!('Notification' in window) || Notification.permission !== 'granted' || !document.hidden) return;
    var o = orders[0];
    try {
      var n = new Notification(orders.length > 1 ? orders.length + ' new orders' : 'New order ' + o.number, {
        body: o.customer + ' · ' + o.total + (orders.length > 1 ? ' (and more)' : ''), icon: ICON, tag: 'new-order-' + o.id,
      });
      n.onclick = function () { window.focus(); window.location = o.urls.show; n.close(); };
    } catch (e) {}
  }

  /* ---------- Polling ---------- */
  var timer = null, inFlight = false;
  function schedule() { clearTimeout(timer); timer = setTimeout(poll, document.hidden ? HIDDEN_MS : VISIBLE_MS); }
  function pollNow() { clearTimeout(timer); setTimeout(poll, 300); }

  function poll() {
    if (inFlight) return;
    inFlight = true;
    var seen = get(K.seen, null);
    fetch(FEED + (seen === null ? '' : '?after=' + encodeURIComponent(seen)), {
      headers: { 'Accept': 'application/json' }, credentials: 'same-origin', cache: 'no-store',
    }).then(function (r) { return r.ok ? r.json() : null; }).then(function (d) {
      if (!d || typeof d.latest_id !== 'number') return; // logged out or error: try again later
      updateCounts(d.needs_review);
      // First visit (or a fresh database): start from now, don't alert old orders.
      if (seen === null || d.latest_id < seen) { set(K.seen, d.latest_id); return; }
      set(K.seen, Math.max(seen, d.latest_id));
      if (!d.orders.length) return;

      var alerts = get(K.alerts, []);
      var known = alerts.map(function (a) { return a.id; });
      d.orders.slice().reverse().forEach(function (o) { if (known.indexOf(o.id) === -1) alerts.unshift(o); });
      set(K.alerts, alerts.slice(0, MAX_ALERTS));
      if (d.more) set(K.more, (get(K.more, 0) || 0) + d.more);
      render();

      // Only one tab chimes / notifies per new order.
      if ((get(K.chimed, 0) || 0) < d.orders[0].id) {
        set(K.chimed, d.orders[0].id);
        chime();
        notify(d.orders);
      }
    }).catch(function () {}).then(function () { inFlight = false; schedule(); });
  }

  document.addEventListener('visibilitychange', function () {
    updateTitle();
    if (!document.hidden) pollNow(); else schedule();
  });
  window.addEventListener('storage', function (e) {
    if (e.key === K.alerts || e.key === K.sound || e.key === K.more) render();
    if (e.key === K.count) updateCounts(get(K.count, 0));
  });

  render();
  poll();
})();
