// Builds VantAdminTutorial.html: template + SRT subtitles + captured screens + scene script, in one file.
//   node build.mjs
import { existsSync, readFileSync, writeFileSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const HERE = dirname(fileURLToPath(import.meta.url));
const read = (p) => readFileSync(join(HERE, p), "utf8");

// ---------- SRT → cues ----------
const toSec = (ts) => {
  const [h, m, rest] = ts.trim().split(":");
  const [s, ms] = rest.split(",");
  return +h * 3600 + +m * 60 + +s + +ms / 1000;
};
const cues = read("admin-tutorial.srt")
  .replace(/\r/g, "")
  .split(/\n\s*\n/)
  .map((block) => block.trim().split("\n"))
  .filter((lines) => lines.length >= 3)
  .map((lines) => {
    const [a, b] = lines[1].split("-->");
    const text = lines.slice(2).join(" ").replace(/\[span_\d+\]\((start|end)_span\)/g, "").replace(/\s+/g, " ").trim();
    return { s: toSec(a), e: toSec(b), text };
  });

// Words light up in proportion to their length, finishing just before the line ends.
const esc = (s) => s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
const subs = cues
  .map((c, i) => {
    const words = c.text.split(" ");
    const weight = words.map((w) => [...w].length + 2);
    const total = weight.reduce((a, b) => a + b, 0);
    const span = (c.e - c.s) * 0.92;
    let acc = 0;
    const html = words
      .map((w, j) => {
        const t = c.s + (acc / total) * span;
        acc += weight[j];
        return `<span class="w" data-t="${t.toFixed(3)}">${esc(w)}</span>`;
      })
      .join(" ");
    return `<div class="sub" id="sub${i}"><div class="pill">${html}</div></div>`;
  })
  .join("\n  ");

// ---------- screens ----------
const screens = JSON.parse(read("screens.json"));
const TITLES = {
  login: "Sign in", dashboard: "Dashboard", "dashboard-search": "Dashboard", orders: "Orders", "orders-pending": "Orders",
  "orders-selected": "Orders", "orders-print": "Orders", "orders-more": "Orders", order: "Order #VB-888252", "order-edit": "Order #VB-888252",
  "order-bkash": "Order #VB-579944", "order-shipped": "Order #VB-389031", invoice: "Invoice #VB-888252", label: "Parcel labels",
  "courier-scan": "Courier scan", "courier-in": "Courier scan", manifest: "Courier manifest", pos: "Point of sale", "pos-cart": "Point of sale",
  products: "Products", "product-new": "New product", "product-import": "Products", inventory: "Inventory", "inventory-restock": "Inventory",
  barcodes: "Barcodes", categories: "Categories", brands: "Brands", variations: "Variations", reviews: "Reviews", banners: "Hero banners",
  "news-ticker": "News ticker", "header-menu": "Menu bar", coupons: "Coupons", "flash-sale": "Flash sale", "free-delivery": "Free delivery",
  "abandoned-carts": "Abandoned carts", analytics: "Analytics", expenses: "Expenses", customers: "Customers", blacklist: "Blacklist",
  staff: "Staff", audit: "Activity logs", settings: "Settings", integrations: "Integrations", "integrations-tracking": "Integrations",
};
const ids = Object.keys(screens).filter((id) => id !== "dashboard-phone");
const imgs = ids.map((id) => `<img id="scr-${id}" src="assets/screens/${id}.jpg" alt="">`).join("");
const urls =
  `<div class="url" id="url-newtab" style="color:#999">Search or type a web address</div>` +
  ids.map((id) => `<div class="url" id="url-${id}" style="opacity:0">vantbd.com${esc(screens[id].path.split("?")[0])}</div>`).join("");
const titles =
  `<div class="tabtitle" id="ttl-newtab">New Tab</div>` +
  ids.map((id) => `<div class="tabtitle" id="ttl-${id}" style="opacity:0">${esc(TITLES[id] || id)} — Vant</div>`).join("");

// Phone home screen: a grid of plain app tiles.
const tileColors = ["#2f8cff", "#ffcc33", "#34c759", "#ff3b30", "#8e5cf7", "#ff9500", "#5ac8fa", "#ff2d55", "#30b0c7", "#a2845e", "#64d2ff", "#bf5af2"];
const home = tileColors
  .map((c, i) => `<div style="position:absolute;left:${30 + (i % 4) * 80}px;top:${110 + Math.floor(i / 4) * 92}px;width:60px;height:60px;border-radius:15px;background:${c}"></div>`)
  .join("") +
  `<div style="position:absolute;left:24px;top:668px;width:312px;height:84px;border-radius:30px;background:rgba(255,255,255,.22)"></div>` +
  [0, 1, 2, 3].map((i) => `<div style="position:absolute;left:${42 + i * 74}px;top:680px;width:60px;height:60px;border-radius:15px;background:${tileColors[i * 3]}"></div>`).join("");

const scene = read("src/scenes.js");
const duration = Math.ceil(cues[cues.length - 1].e + 3);

// The recorded voiceover, if it has been added: assets/voiceover.mp3 (or .wav), starting at 0:00.
const voice = ["voiceover.mp3", "voiceover.wav"].find((f) => existsSync(join(HERE, "assets", f)));
const audio = voice ? `<audio src="assets/${voice}" data-start="0"></audio>` : "";

const html = read("src/template.html")
  .replace("<!--AUDIO-->", audio)
  .replace("<!--SCREENS-->", imgs)
  .replace("<!--URLS-->", urls)
  .replace("<!--TABTITLES-->", titles)
  .replace("<!--PHONEHOME-->", home)
  .replace("<!--SUBS-->", subs)
  .replace("/*DURATION*/", String(duration))
  .replace("/*CUES*/", JSON.stringify(cues.map(({ s, e }) => ({ s, e }))))
  .replace("/*SCREENS*/", JSON.stringify(Object.fromEntries(Object.entries(screens).map(([k, v]) => [k, { h: v.h, boxes: v.boxes }]))))
  .replace("/*SCENE*/", scene.replace(/^\/\/.*\n/gm, ""));

writeFileSync(join(HERE, "VantAdminTutorial.html"), html);
console.log(`VantAdminTutorial.html: ${cues.length} cues, ${ids.length} screens, ${duration}s${voice ? ", voiceover: " + voice : ", no voiceover yet"}`);
