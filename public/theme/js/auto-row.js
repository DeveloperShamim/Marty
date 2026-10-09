// One-line product rows ([data-auto-row]: homepage category rows, "You may also like") slide by themselves:
// one card every few seconds, back to the start at the end, on phones and desktops alike. They wait a few
// seconds after someone swipes, scrolls or clicks in the row, and while the row is off screen. People who
// prefer reduced motion get a jump instead of a glide. Kept apart from storefront.js so nothing else on the
// page can stop it.
(function () {
  function start() {
    var rows = document.querySelectorAll("[data-auto-row]");
    if (!rows.length) return;
    var behavior = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth";
    var STEP_MS = 3500, PAUSE_MS = 6000;
    Array.prototype.forEach.call(rows, function (row) {
      var pausedUntil = 0, visible = !("IntersectionObserver" in window);
      var pause = function () { pausedUntil = Date.now() + PAUSE_MS; };
      ["touchstart", "pointerdown", "wheel", "focusin"].forEach(function (ev) { row.addEventListener(ev, pause, { passive: true }); });
      if (!visible) new IntersectionObserver(function (entries) { visible = entries[0].isIntersecting; }, { threshold: 0.25 }).observe(row);
      setInterval(function () {
        if (!visible || document.hidden || Date.now() < pausedUntil) return;
        var card = row.firstElementChild;
        if (!card || row.scrollWidth <= row.clientWidth + 4) return;
        var atEnd = row.scrollLeft + row.clientWidth >= row.scrollWidth - 4;
        if (atEnd) row.scrollTo({ left: 0, behavior: behavior });
        else row.scrollBy({ left: card.getBoundingClientRect().width + (parseFloat(getComputedStyle(row).columnGap) || 0), behavior: behavior });
      }, STEP_MS);
    });
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", start);
  else start();
})();
