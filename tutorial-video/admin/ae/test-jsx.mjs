// Dry-runs VantAdminTutorial.jsx against a small mock of the After Effects scripting API.
// It cannot show how the result looks, but it catches script errors, unknown property names,
// bad keyframe data (NaN, unsorted times, mismatched lengths) and missing asset files.
//   node ae/test-jsx.mjs
import { readFileSync, existsSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";
import vm from "node:vm";

const HERE = dirname(fileURLToPath(import.meta.url));
const ROOT = join(HERE, "..");
const src = readFileSync(join(ROOT, "VantAdminTutorial.jsx"), "utf8");
if (/[^\x00-\x7f]/.test(src)) throw new Error("JSX contains non-ASCII characters");

// Property names the script may use, by parent.
const KNOWN = new Set(["(layer)", 
  "ADBE Transform Group", "ADBE Anchor Point", "ADBE Position", "ADBE Position_0", "ADBE Position_1", "ADBE Scale", "ADBE Opacity",
  "ADBE Root Vectors Group", "ADBE Vector Group", "ADBE Vectors Group", "ADBE Vector Shape - Rect", "ADBE Vector Shape - Ellipse",
  "ADBE Vector Rect Size", "ADBE Vector Rect Roundness", "ADBE Vector Ellipse Size", "ADBE Vector Graphic - Fill", "ADBE Vector Graphic - Stroke",
  "ADBE Vector Fill Color", "ADBE Vector Fill Opacity", "ADBE Vector Stroke Color", "ADBE Vector Stroke Width", "ADBE Vector Stroke Opacity",
  "ADBE Text Properties", "ADBE Text Document", "ADBE Text Animators", "ADBE Text Animator", "ADBE Text Animator Properties", "ADBE Text Fill Color",
  "ADBE Text Selectors", "ADBE Text Selector", "ADBE Text Range Advanced", "ADBE Text Range Type2", "ADBE Text Percent End",
  "ADBE Effect Parade", "ADBE Ramp", "ADBE Ramp-0001", "ADBE Ramp-0002", "ADBE Ramp-0003", "ADBE Ramp-0004", "ADBE Ramp-0005",
  "ADBE Drop Shadow", "ADBE Drop Shadow-0002", "ADBE Drop Shadow-0003", "ADBE Drop Shadow-0004", "ADBE Drop Shadow-0005",
  "ADBE Mask Parade", "ADBE Mask Atom", "ADBE Mask Shape",
]);
const stats = { layers: 0, keys: 0, props: 0, expressions: 0, comps: 0, imports: 0 };
const problems = [];
const bad = (v) => (Array.isArray(v) ? v.some(bad) : typeof v === "number" && !Number.isFinite(v));
// Value ranges After Effects enforces (it rejects the whole call when one value is outside).
const RANGE = { "ADBE Opacity": [0, 100], "ADBE Vector Fill Opacity": [0, 100], "ADBE Vector Stroke Opacity": [0, 100],
  "ADBE Text Percent End": [0, 100], "ADBE Drop Shadow-0002": [0, 100], "ADBE Vector Rect Roundness": [0, 10000] };
function outOfRange(name, v) {
  const r = RANGE[name];
  if (!r) return false;
  const vals = Array.isArray(v) ? v : [v];
  return vals.some((x) => typeof x === "number" && (x < r[0] || x > r[1]));
}

function makeProp(matchName) {
  if (!KNOWN.has(matchName) && typeof matchName === "string") problems.push("unknown property " + matchName);
  stats.props++;
  const children = new Map();
  const p = {
    matchName, numKeys: 0, dimensionsSeparated: false, _value: matchName === "ADBE Text Document" ? new TextDocument("") : [0, 0],
    get value() { return this._value instanceof TextDocument ? Object.assign(new TextDocument(""), this._value) : this._value; },
    set expression(e) { stats.expressions++; if (typeof e !== "string" || !e.length) problems.push("empty expression on " + matchName); },
    property(n) {
      if (typeof n === "number") return [...children.values()][n - 1] || makeProp("(index " + n + ")");
      if (!children.has(n)) children.set(n, makeProp(n));
      return children.get(n);
    },
    addProperty(n) { const c = makeProp(n); children.set(n + "#" + children.size, c); return c; },
    setValue(v) { if (bad(v)) problems.push("NaN value on " + matchName); if (outOfRange(matchName, v)) problems.push("value " + v + " out of range on " + matchName); this._value = v; },
    setValuesAtTimes(ts, vs) {
      if (ts.length !== vs.length) problems.push("times/values length mismatch on " + matchName);
      for (let i = 0; i < ts.length; i++) {
        if (!Number.isFinite(ts[i]) || ts[i] < 0) problems.push("bad time on " + matchName);
        if (i && ts[i] < ts[i - 1]) problems.push("unsorted times on " + matchName);
        if (bad(vs[i])) problems.push("NaN value on " + matchName + " at " + ts[i]);
        if (outOfRange(matchName, vs[i])) problems.push("value " + vs[i] + " out of range on " + matchName + " at " + ts[i].toFixed(2) + "s");
      }
      this.numKeys += ts.length; stats.keys += ts.length;
    },
    setInterpolationTypeAtKey(k) { if (k < 1 || k > this.numKeys) problems.push("bad key index on " + matchName); },
  };
  return p;
}
function makeLayer(kind, comp) {
  stats.layers++;
  const props = makeProp("(layer)");
  const L = { kind, name: "", parent: null, property: (n) => props.property(n), moveAfter() {} };
  comp._layers.unshift(L);
  return L;
}
function makeComp(name, w, h, par, dur, fps) {
  stats.comps++;
  if (!(dur > 0 && fps > 0)) problems.push("bad comp " + name);
  const comp = {
    name, _layers: [], parentFolder: null, get numLayers() { return this._layers.length; },
    layer: (i) => comp._layers[i - 1],
    openInViewer() {},
  };
  comp.layers = {
    addShape: () => makeLayer("shape", comp),
    addText: (s) => { if (typeof s !== "string") problems.push("addText without string"); return makeLayer("text", comp); },
    addSolid: (c, n, w2, h2, p, d) => { if (bad(c) || !(d > 0)) problems.push("bad solid " + n); return makeLayer("solid", comp); },
    addNull: (d) => { if (!(d > 0)) problems.push("bad null duration"); return makeLayer("null", comp); },
    add: (item) => { if (!item) problems.push("add() without item"); return makeLayer("footage", comp); },
  };
  return comp;
}
class TextDocument { constructor(t) { this.text = t; } resetCharStyle() {} resetParagraphStyle() {} }
// ExtendScript lets File be called with or without "new".
function File(p) {
  if (!(this instanceof File)) return new File(p);
  this.fsName = p;
}
Object.defineProperty(File.prototype, "parent", { get() { return new File(ROOT); } });
Object.defineProperty(File.prototype, "exists", { get() { return existsSync(this.fsName); } });
class Shape {}
const sandbox = {
  $: { fileName: join(ROOT, "VantAdminTutorial.jsx") },
  File, Shape, TextDocument,
  ImportOptions: class { constructor(f) { this.file = f; } },
  ParagraphJustification: { LEFT_JUSTIFY: 1, CENTER_JUSTIFY: 2, RIGHT_JUSTIFY: 3 },
  KeyframeInterpolationType: { HOLD: 3, LINEAR: 1 },
  alert: (m) => console.log("alert:", m.split("\n")[0]),
  app: {
    beginUndoGroup() {}, endUndoGroup() {},
    project: {
      items: { addFolder: (n) => ({ name: n, parentFolder: null }), addComp: (...a) => makeComp(...a) },
      importFile: (o) => { stats.imports++; if (!o.file.exists) problems.push("missing " + o.file.fsName); return { name: o.file.fsName, parentFolder: null }; },
    },
  },
};
vm.createContext(sandbox);
vm.runInContext(src, sandbox, { filename: "VantAdminTutorial.jsx" });
console.log(stats);
if (problems.length) {
  console.log([...new Set(problems)].slice(0, 30).join("\n"));
  process.exit(1);
}
console.log("OK: no problems found");
