/*
 * Camera barcode scanner for the admin scan stations (POS, courier scan).
 *
 *   CameraScanner.open({
 *     title: 'Scan products',
 *     continuous: true,            // keep scanning after each code (default: close after the first)
 *     onScan: async code => ({ ok: true, message: 'Added', close: false }),
 *   });
 *
 * Uses the browser's built-in BarcodeDetector when it can read CODE128, otherwise
 * loads the ZXing decoder from the CDN the first time the scanner is opened.
 * Decoding happens on the device; only the scanned code reaches onScan.
 */
(function () {
  var ZXING_SRC = 'https://cdn.jsdelivr.net/npm/@zxing/library@0.21.3/umd/index.min.js';
  var ZXING_SRI = 'sha384-BzBxP10ZE72aitqj5UMmUsbKFliP/DZqA8Wq+BNNhlIJDGoEd1tpkMYXOg9+n6sB';
  var NATIVE_FORMATS = ['code_128', 'ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_39', 'qr_code'];
  // The same code is only accepted again after it has been out of view this long,
  // so an item held in front of the camera is not added twice.
  var SAME_CODE_GAP = 1500;

  var zxingLoading = null;
  var ui = null;
  var s = null; // state of the open session

  function loadZxing() {
    if (window.ZXing) return Promise.resolve(window.ZXing);
    if (zxingLoading) return zxingLoading;
    zxingLoading = new Promise(function (resolve, reject) {
      var el = document.createElement('script');
      el.src = ZXING_SRC;
      el.integrity = ZXING_SRI;
      el.crossOrigin = 'anonymous';
      el.onload = function () { window.ZXing ? resolve(window.ZXing) : reject(new Error('load')); };
      el.onerror = function () { zxingLoading = null; el.remove(); reject(new Error('load')); };
      document.head.appendChild(el);
    });
    return zxingLoading;
  }

  function nativeDetector() {
    if (!('BarcodeDetector' in window)) return Promise.resolve(null);
    return window.BarcodeDetector.getSupportedFormats().then(function (supported) {
      var formats = NATIVE_FORMATS.filter(function (f) { return supported.indexOf(f) !== -1; });
      return formats.indexOf('code_128') !== -1 ? new window.BarcodeDetector({ formats: formats }) : null;
    }).catch(function () { return null; });
  }

  function icon(paths, cls) {
    return '<svg class="' + (cls || 'w-5 h-5') + '" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + paths + '</svg>';
  }

  function build() {
    var wrap = document.createElement('div');
    wrap.id = 'cameraScanner';
    // Shown/hidden with style.display: a 'hidden' class would lose to 'flex' on wider screens.
    wrap.className = 'fixed inset-0 z-[80] bg-black/80 flex sm:items-center sm:justify-center sm:p-4';
    wrap.style.display = 'none';
    wrap.setAttribute('role', 'dialog');
    wrap.setAttribute('aria-modal', 'true');
    wrap.setAttribute('aria-labelledby', 'cameraScannerTitle');
    wrap.innerHTML =
      '<div class="relative w-full h-full sm:h-auto sm:max-w-lg bg-gray-950 sm:rounded-2xl overflow-hidden flex flex-col text-white shadow-2xl">' +
        '<div class="flex items-center justify-between gap-3 px-4 h-14 shrink-0">' +
          '<div class="min-w-0"><p id="cameraScannerTitle" class="font-semibold text-[15px] truncate"></p>' +
          '<p data-cs="hint" class="text-xs text-white/60"></p></div>' +
          '<button type="button" data-cs="close" class="h-10 w-10 -mr-2 rounded-full hover:bg-white/10 flex items-center justify-center" aria-label="Close camera">' + icon('<path d="M18 6 6 18M6 6l12 12"/>') + '</button>' +
        '</div>' +
        '<div class="relative flex-1 sm:flex-none sm:aspect-[4/3] bg-black overflow-hidden">' +
          '<video data-cs="video" class="absolute inset-0 w-full h-full object-cover" playsinline muted autoplay></video>' +
          '<div data-cs="frame" class="absolute left-[8%] right-[8%] top-1/2 -translate-y-1/2 h-[38%] max-h-48 rounded-2xl border-2 border-white/90 transition-colors" style="box-shadow:0 0 0 9999px rgba(0,0,0,.45)">' +
            '<div class="cs-laser absolute left-3 right-3 top-1/2 h-0.5 rounded-full bg-rose-500/90"></div>' +
          '</div>' +
          '<div data-cs="status" class="absolute inset-0 hidden flex-col items-center justify-center gap-3 p-8 text-center bg-gray-950"></div>' +
        '</div>' +
        '<div class="shrink-0 p-4 space-y-3" style="padding-bottom:max(1rem,env(safe-area-inset-bottom))">' +
          '<div data-cs="result" class="min-h-[2.75rem] rounded-xl bg-white/5 px-3 py-2.5 text-sm text-white/60 flex items-center">Waiting for a barcode…</div>' +
          '<div class="flex items-center gap-2">' +
            '<button type="button" data-cs="torch" class="hidden h-11 w-11 rounded-xl bg-white/10 hover:bg-white/15 items-center justify-center" aria-label="Turn on flashlight" aria-pressed="false">' + icon('<path d="M18 6c0 2-2 2-2 4v10a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2V10c0-2-2-2-2-4V2h12z"/><path d="M6 6h12"/><path d="M12 12v2"/>') + '</button>' +
            '<button type="button" data-cs="switch" class="hidden h-11 w-11 rounded-xl bg-white/10 hover:bg-white/15 items-center justify-center" aria-label="Switch camera">' + icon('<path d="M11 19H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/><path d="M13 5h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-5"/><circle cx="12" cy="12" r="3"/><path d="m18 22-3-3 3-3"/><path d="m6 2 3 3-3 3"/>') + '</button>' +
            '<span data-cs="count" class="text-xs text-white/60 ml-1"></span>' +
            '<button type="button" data-cs="done" class="ml-auto h-11 px-6 rounded-xl bg-white text-gray-900 font-semibold text-sm hover:bg-white/90">Done</button>' +
          '</div>' +
        '</div>' +
      '</div>';
    document.body.appendChild(wrap);

    var style = document.createElement('style');
    style.textContent = '@keyframes csLaser{0%,100%{transform:translateY(-28px);opacity:.6}50%{transform:translateY(28px);opacity:1}}' +
      '#cameraScanner .cs-laser{animation:csLaser 1.6s ease-in-out infinite}' +
      '@media (prefers-reduced-motion:reduce){#cameraScanner .cs-laser{animation:none}}';
    document.head.appendChild(style);

    var get = function (k) { return wrap.querySelector('[data-cs="' + k + '"]'); };
    ui = {
      wrap: wrap, title: wrap.querySelector('#cameraScannerTitle'), hint: get('hint'), video: get('video'), frame: get('frame'),
      status: get('status'), result: get('result'), torch: get('torch'), sw: get('switch'), count: get('count'),
    };
    get('close').addEventListener('click', close);
    get('done').addEventListener('click', close);
    ui.torch.addEventListener('click', toggleTorch);
    ui.sw.addEventListener('click', switchCamera);
  }

  function showStatus(html) {
    ui.status.innerHTML = html;
    ui.status.classList.remove('hidden');
    ui.status.classList.add('flex');
    ui.frame.classList.add('hidden');
  }
  function hideStatus() {
    ui.status.classList.add('hidden');
    ui.status.classList.remove('flex');
    ui.frame.classList.remove('hidden');
    ui.result.classList.remove('hidden');
  }
  function showResult(text, tone) {
    ui.result.textContent = text;
    ui.result.className = 'min-h-[2.75rem] rounded-xl px-3 py-2.5 text-sm font-medium flex items-center ' +
      (tone === 'ok' ? 'bg-emerald-500/15 text-emerald-300' : tone === 'error' ? 'bg-rose-500/15 text-rose-300' : 'bg-white/5 text-white/60');
    ui.frame.style.borderColor = tone === 'ok' ? '#34d399' : tone === 'error' ? '#fb7185' : '';
  }

  function cameraError(err) {
    var name = err && err.name;
    if (!window.isSecureContext) {
      return 'The camera only works on a secure connection. Open the admin over <b>https://</b> to scan with the camera.';
    }
    if (name === 'NotAllowedError' || name === 'SecurityError') {
      return 'Camera access was blocked. Allow the camera for this site in your browser settings, then try again.';
    }
    if (name === 'NotFoundError' || name === 'OverconstrainedError' || name === 'DevicesNotFoundError') {
      return 'No camera was found on this device.';
    }
    if (name === 'NotReadableError' || name === 'TrackStartError') {
      return 'The camera is being used by another app. Close it and try again.';
    }
    if (err && err.message === 'load') {
      return 'The scanner could not be loaded. Check the internet connection and try again.';
    }
    return 'The camera could not be started.';
  }

  function fail(err) {
    stopCamera();
    ui.result.classList.add('hidden');
    showStatus(
      '<div class="h-12 w-12 rounded-full bg-rose-500/15 text-rose-300 flex items-center justify-center">' + icon('<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>', 'w-6 h-6') + '</div>' +
      '<p class="text-sm text-white/80 max-w-xs">' + cameraError(err) + '</p>' +
      '<p class="text-xs text-white/50">You can still type the code or use a barcode gun.</p>'
    );
  }

  function stopCamera() {
    if (!s) return;
    clearTimeout(s.timer);
    if (s.reader) { try { s.reader.reset(); } catch (e) {} s.reader = null; }
    if (s.stream) { s.stream.getTracks().forEach(function (t) { t.stop(); }); s.stream = null; }
    ui.video.srcObject = null;
  }

  function startCamera(deviceId) {
    var session = s;
    stopCamera();
    showStatus('<div class="h-8 w-8 rounded-full border-2 border-white/30 border-t-white animate-spin"></div><p class="text-sm text-white/70">Starting camera…</p>');

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      fail(new Error('unsupported'));
      return;
    }

    var video = deviceId
      ? { deviceId: { exact: deviceId } }
      : { facingMode: { ideal: 'environment' } };
    video.width = { ideal: 1280 };
    video.height = { ideal: 720 };

    Promise.all([
      navigator.mediaDevices.getUserMedia({ video: video, audio: false }),
      session.detector !== undefined ? Promise.resolve(session.detector) : nativeDetector(),
    ]).then(function (res) {
      var stream = res[0];
      if (s !== session) { stream.getTracks().forEach(function (t) { t.stop(); }); return; }
      session.stream = stream;
      session.detector = res[1];
      var track = stream.getVideoTracks()[0];
      session.deviceId = track.getSettings ? track.getSettings().deviceId : null;
      setupTorch(track);
      listCameras();
      return session.detector ? runNative(session) : runZxing(session);
    }).catch(function (err) {
      if (s === session) fail(err);
    });
  }

  function runNative(session) {
    ui.video.srcObject = session.stream;
    return ui.video.play().then(function () {
      hideStatus();
      (function tick() {
        if (s !== session || !session.stream) return;
        var next = function () { session.timer = setTimeout(tick, 120); };
        if (ui.video.readyState < 2) return next();
        session.detector.detect(ui.video).then(function (codes) {
          if (codes.length) found(codes[0].rawValue);
        }).catch(function () {}).then(next);
      })();
    });
  }

  function runZxing(session) {
    return loadZxing().then(function (Z) {
      if (s !== session || !session.stream) return;
      var hints = new Map();
      hints.set(Z.DecodeHintType.POSSIBLE_FORMATS, [
        Z.BarcodeFormat.CODE_128, Z.BarcodeFormat.EAN_13, Z.BarcodeFormat.EAN_8, Z.BarcodeFormat.UPC_A,
        Z.BarcodeFormat.UPC_E, Z.BarcodeFormat.CODE_39, Z.BarcodeFormat.QR_CODE,
      ]);
      // No TRY_HARDER: in ZXing 0.21 it stops live (continuous) decoding from ever matching.
      session.reader = new Z.BrowserMultiFormatReader(hints, 120);
      hideStatus();
      session.reader.decodeFromStream(session.stream, ui.video, function (result) {
        if (result && s === session) found(result.getText());
      }).catch(function (err) { if (s === session) fail(err); });
    });
  }

  function found(raw) {
    var code = String(raw || '').trim();
    if (!s || !code) return;
    var now = Date.now();
    var stillInView = code === s.lastCode && now - s.lastSeen < SAME_CODE_GAP;
    if (code === s.lastCode) s.lastSeen = now;
    if (stillInView || s.busy) return;
    s.lastCode = code;
    s.lastSeen = now;
    s.busy = true;
    if (navigator.vibrate) { try { navigator.vibrate(60); } catch (e) {} }
    showResult('Checking ' + code + '…');

    var session = s;
    Promise.resolve()
      .then(function () { return session.onScan(code); })
      .then(function (r) {
        r = r || {};
        if (s !== session) return;
        var ok = r.ok !== false;
        if (ok) session.count++;
        showResult(r.message || (ok ? 'Scanned ' + code : 'Could not use ' + code), ok ? 'ok' : 'error');
        ui.count.textContent = session.continuous && session.count ? session.count + ' scanned' : '';
        if (r.close || !session.continuous) {
          setTimeout(function () { if (s === session) close(); }, ok ? 350 : 1200);
          return;
        }
        // Short pause so the same item isn't read twice while it is still in view.
        setTimeout(function () { session.busy = false; }, 700);
      })
      .catch(function () {
        if (s !== session) return;
        showResult('Something went wrong. Try again.', 'error');
        setTimeout(function () { session.busy = false; }, 900);
      });
  }

  function setupTorch(track) {
    var caps = track && track.getCapabilities ? track.getCapabilities() : {};
    s.torchOn = false;
    ui.torch.classList.toggle('hidden', !caps.torch);
    ui.torch.classList.toggle('flex', !!caps.torch);
    ui.torch.setAttribute('aria-pressed', 'false');
  }

  function toggleTorch() {
    if (!s || !s.stream) return;
    var track = s.stream.getVideoTracks()[0];
    var on = !s.torchOn;
    track.applyConstraints({ advanced: [{ torch: on }] }).then(function () {
      s.torchOn = on;
      ui.torch.setAttribute('aria-pressed', on ? 'true' : 'false');
      ui.torch.setAttribute('aria-label', on ? 'Turn off flashlight' : 'Turn on flashlight');
      ui.torch.classList.toggle('bg-amber-400', on);
      ui.torch.classList.toggle('text-gray-900', on);
    }).catch(function () {});
  }

  function listCameras() {
    if (!navigator.mediaDevices.enumerateDevices) return;
    var session = s;
    navigator.mediaDevices.enumerateDevices().then(function (devices) {
      if (s !== session) return;
      session.cameras = devices.filter(function (d) { return d.kind === 'videoinput'; });
      var many = session.cameras.length > 1;
      ui.sw.classList.toggle('hidden', !many);
      ui.sw.classList.toggle('flex', many);
    }).catch(function () {});
  }

  function switchCamera() {
    if (!s || !s.cameras || s.cameras.length < 2) return;
    var ids = s.cameras.map(function (c) { return c.deviceId; });
    var next = ids[(ids.indexOf(s.deviceId) + 1) % ids.length];
    startCamera(next);
  }

  function onKey(e) {
    if (e.key === 'Escape' && s) {
      e.preventDefault();
      e.stopPropagation();
      close();
    }
  }
  function onHidden() {
    if (document.hidden && s) close();
  }

  function open(opts) {
    if (!ui) build();
    if (s) close();
    opts = opts || {};
    s = {
      onScan: opts.onScan || function () {},
      onClose: opts.onClose || null,
      continuous: !!opts.continuous,
      count: 0, busy: false, lastCode: null, lastSeen: 0, detector: undefined,
      returnFocus: document.activeElement,
    };
    ui.title.textContent = opts.title || 'Scan barcode';
    ui.hint.textContent = s.continuous
      ? 'Scan items one by one. To add another of the same item, move it away and back.'
      : 'Hold the barcode inside the frame';
    ui.count.textContent = '';
    showResult('Waiting for a barcode…');
    ui.wrap.style.display = '';
    document.documentElement.style.overflow = 'hidden';
    window.addEventListener('keydown', onKey, true);
    document.addEventListener('visibilitychange', onHidden);
    startCamera(null);
  }

  function close() {
    if (!s) return;
    var session = s;
    stopCamera();
    s = null;
    ui.wrap.style.display = 'none';
    document.documentElement.style.overflow = '';
    window.removeEventListener('keydown', onKey, true);
    document.removeEventListener('visibilitychange', onHidden);
    if (session.onClose) session.onClose(session.count);
    // Don't pop the on-screen keyboard on phones by refocusing a text box.
    if (window.innerWidth >= 1024 && session.returnFocus && session.returnFocus.focus) session.returnFocus.focus();
  }

  window.CameraScanner = { open: open, close: close };
})();
