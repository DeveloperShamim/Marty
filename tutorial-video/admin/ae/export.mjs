// Turns VantAdminTutorial.html into an After Effects script: VantAdminTutorial.jsx
//
// It opens the page in Chrome, steps through every frame of the GSAP timeline, records how each moving
// element changes (camera, screens, pointer, rings, typing, pop-ups, end card), thins those values out to
// keyframes, and writes an ExtendScript that rebuilds the video as native After Effects layers.
//
//   node ae/export.mjs              (Chrome path: CHROME_PATH, or the Playwright browser in /opt/pw-browsers)
import { chromium } from "playwright-core";
import { existsSync, readFileSync, writeFileSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const HERE = dirname(fileURLToPath(import.meta.url));
const ROOT = join(HERE, "..");
const FPS = 30;
const chrome = process.env.CHROME_PATH || ["/opt/pw-browsers/chromium-1194/chrome-linux/chrome"].find(existsSync);

const browser = await chromium.launch({ executablePath: chrome, args: ["--ignore-certificate-errors"] });
const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });
await page.route("**/gsap.min.js", (r) => r.fulfill({ contentType: "application/javascript", body: readFileSync(join(ROOT, "node_modules/gsap/dist/gsap.min.js")) }));
await page.goto("file://" + join(ROOT, "VantAdminTutorial.html"));
await page.waitForFunction(() => window.__timeline && document.fonts.status === "loaded", null, { timeout: 60000 });

const data = await page.evaluate((FPS) => {
  const tl = window.__timeline;
  tl.pause();
  const D = window.DURATION;
  const N = Math.round(D * FPS) + 1;
  const G = (el, p) => parseFloat(gsap.getProperty(el, p)) || 0;
  const $ = (s) => document.querySelector(s);

  // ---- what to record: [name, element, {prop: reader}] ----
  const tracks = [];
  const track = (name, el, props, extra = {}) => tracks.push({ name, el, props, extra, series: {} });
  track("world", $("#world"), { x: (e) => G(e, "x"), y: (e) => G(e, "y"), s: (e) => G(e, "scale") });
  track("browser", $("#browser"), { o: (e) => G(e, "opacity"), s: (e) => G(e, "scale"), y: (e) => G(e, "y") });
  track("dock", $("#dock"), { o: (e) => G(e, "opacity") });
  track("menubar", $("#menubar"), { o: (e) => G(e, "opacity") });
  track("newtab", $("#newtab"), { o: (e) => G(e, "opacity") });
  track("phoneImg", $("#phoneImg"), { o: (e) => G(e, "opacity") });
  track("sidebar", $("#sidebarOverlay"), { o: (e) => G(e, "opacity"), y: (e) => G(e, "y") });
  for (const img of document.querySelectorAll("#viewport img[id^=scr-]")) {
    track(img.id, img, { o: (e) => G(e, "opacity"), y: (e) => G(e, "y") }, { id: img.id.slice(4) });
  }
  for (const el of document.querySelectorAll(".url, .tabtitle")) {
    track(el.id, el, { o: (e) => G(e, "opacity") }, { text: el.textContent, kind: el.classList.contains("url") ? "url" : "title" });
  }
  track("cursor", $("#cursor"), { x: (e) => G(e, "x"), y: (e) => G(e, "y"), o: (e) => G(e, "opacity"), s: (e) => G(e, "scale") });
  track("ripple", $("#ripple"), { x: (e) => G(e, "x"), y: (e) => G(e, "y"), o: (e) => G(e, "opacity"), s: (e) => G(e, "scale") });
  const box = { cx: (e) => G(e, "x") + G(e, "width") / 2, cy: (e) => G(e, "y") + G(e, "height") / 2, w: (e) => G(e, "width"), h: (e) => G(e, "height"), o: (e) => G(e, "opacity") };
  document.querySelectorAll(".ring").forEach((el, i) => track("ring" + i, el, { ...box, s: (e) => G(e, "scale") }));
  document.querySelectorAll(".cover").forEach((el, i) => track("cover" + i, el, box, { color: getComputedStyle(el).backgroundColor }));
  document.querySelectorAll(".typed").forEach((el, i) =>
    track("typed" + i, el, { x: (e) => G(e, "x"), y: (e) => G(e, "y"), o: (e) => G(e, "opacity"), mw: (e) => parseFloat(getComputedStyle(e).maxWidth) || 0 },
      { text: el.textContent, size: parseFloat(el.style.fontSize) || 15 }));
  document.querySelectorAll(".toast").forEach((el, i) =>
    track("toast" + i, el, { o: (e) => G(e, "opacity"), y: (e) => G(e, "y") },
      { icon: el.querySelector(".ic").textContent, color: el.querySelector(".ic").style.background, t1: el.querySelector(".t1").textContent, t2: el.querySelector(".t2").textContent }));
  track("end", $("#end"), { o: (e) => G(e, "opacity") });
  track("endLogo", $("#endLogo"), { o: (e) => G(e, "opacity"), s: (e) => G(e, "scale") });
  for (const id of ["endTag", "endLine", "endHelp", "endThanks"]) track(id, $("#" + id), { o: (e) => G(e, "opacity"), y: (e) => G(e, "y") });

  // ---- sample every frame ----
  for (const t of tracks) for (const p in t.props) t.series[p] = new Float32Array(N);
  for (let f = 0; f < N; f++) {
    tl.seek(f / FPS, false);
    for (const t of tracks) for (const p in t.props) t.series[p][f] = t.props[p](t.el);
  }

  // ---- thin out to keyframes (Ramer–Douglas–Peucker on value over time) ----
  const TOL = { x: 0.25, y: 0.25, cx: 0.25, cy: 0.25, w: 0.25, h: 0.25, mw: 0.5, s: 0.0006, o: 0.004 };
  function thin(v, tol) {
    let allSame = true;
    for (let i = 1; i < v.length; i++) if (Math.abs(v[i] - v[0]) > 1e-6) { allSame = false; break; }
    if (allSame) return [[0, +v[0].toFixed(4)]];
    const keep = new Uint8Array(v.length);
    keep[0] = keep[v.length - 1] = 1;
    const stack = [[0, v.length - 1]];
    while (stack.length) {
      const [a, b] = stack.pop();
      let worst = -1, wi = -1;
      for (let i = a + 1; i < b; i++) {
        const lin = v[a] + ((v[b] - v[a]) * (i - a)) / (b - a);
        const d = Math.abs(v[i] - lin);
        if (d > worst) { worst = d; wi = i; }
      }
      if (worst > tol) { keep[wi] = 1; stack.push([a, wi], [wi, b]); }
    }
    const out = [];
    for (let i = 0; i < v.length; i++) if (keep[i]) out.push([i, +v[i].toFixed(4)]);
    return out;
  }
  const result = {};
  for (const t of tracks) {
    const r = { extra: t.extra };
    for (const p in t.series) r[p] = thin(t.series[p], TOL[p] ?? 0.01);
    result[t.name] = r;
  }

  // ---- subtitles: text, when shown, when each word lights up ----
  const subs = [];
  window.__CUES.forEach((c, i) => {
    if (c.skip) return;
    const el = document.getElementById("sub" + i);
    const words = [...el.querySelectorAll(".w")].map((w) => ({ text: w.textContent, t: +w.dataset.t }));
    const next = window.__CUES.slice(i + 1).find((x) => !x.skip);
    const off = next && next.s - c.e < 0.7 ? next.s : c.e + 0.25;
    subs.push({ on: Math.max(0, c.s - 0.05), off: off - 0.06, words });
  });
  const screens = {};
  for (const [id, s] of Object.entries(window.__SCREENS)) screens[id] = s.h;
  return { D, N, FPS, tracks: result, subs, screens, audio: !!document.querySelector("audio") };
}, FPS);
await browser.close();

// ---------------------------------------------------------------------------------------------
// Write the ExtendScript. Data goes in as an object literal (ExtendScript has no JSON object),
// with every non-ASCII character escaped so the file is plain ASCII.
const ascii = (s) => s.replace(/[\u0080-￿]/g, (c) => "\\u" + c.charCodeAt(0).toString(16).padStart(4, "0"));
const DATA = ascii(JSON.stringify(data));
const jsx = ascii(readFileSync(join(HERE, "builder.jsx"), "utf8")).replace("/*DATA*/null", DATA);
writeFileSync(join(ROOT, "VantAdminTutorial.jsx"), jsx);

let keys = 0;
for (const t of Object.values(data.tracks)) for (const [p, v] of Object.entries(t)) if (p !== "extra") keys += v.length;
console.log(`VantAdminTutorial.jsx: ${(jsx.length / 1e6).toFixed(1)} MB, ${Object.keys(data.tracks).length} tracks, ${keys} keyframes, ${data.subs.length} subtitle lines, ${data.D}s`);
