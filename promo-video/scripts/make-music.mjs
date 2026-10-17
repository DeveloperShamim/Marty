// Generates the promo's 30-second, 120 BPM backing track as public/music.wav.
// Everything is synthesised here, so the track is original and free to use.
//
// Structure (1 bar = 2 s):
//   0–4 s   build: soft pad, rising noise, hats speeding up, snare roll
//   4 s     drop: crash, four-on-the-floor kick, claps, offbeat bass, plucked arp
//   22–26 s extra 16th hats under the fast feature tiles
//   26–28 s short lift: kick drops out, riser
//   28 s    final hit, then the tail rings out to 30 s
import { writeFileSync, mkdirSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const SR = 44100;
const BPM = 120;
const BEAT = 60 / BPM; // 0.5 s
const BAR = BEAT * 4; // 2 s
const LENGTH = 30;
const N = SR * LENGTH;

const L = new Float32Array(N);
const R = new Float32Array(N);

// Deterministic noise so every render sounds the same.
let seed = 1234567;
const rand = () => {
  seed = (seed * 1664525 + 1013904223) >>> 0;
  return seed / 4294967296 * 2 - 1;
};

const midi = (m) => 440 * Math.pow(2, (m - 69) / 12);
const add = (i, l, r = l) => {
  if (i >= 0 && i < N) {
    L[i] += l;
    R[i] += r;
  }
};

// C – G – Am – F, one chord per bar (MIDI notes, root position around C4).
const CHORDS = [
  [60, 64, 67, 72],
  [55, 59, 62, 67],
  [57, 60, 64, 69],
  [53, 57, 60, 65],
];
const BASS = [36, 43, 45, 41];
const chordAt = (t) => Math.floor(t / BAR) % 4;

const DROP = 4;
const LIFT = 26;
const HIT = 28;

// ---------- sidechain envelope (pad and bass duck under the kick) ----------
const duck = new Float32Array(N).fill(1);
const kickTimes = [];
for (let t = DROP; t < LIFT; t += BEAT) kickTimes.push(t);
kickTimes.push(HIT);
for (const kt of kickTimes) {
  const s = Math.floor(kt * SR);
  for (let j = 0; j < SR * 0.3; j++) {
    const g = 0.35 + 0.65 * Math.min(1, j / (SR * 0.3));
    if (s + j < N) duck[s + j] = Math.min(duck[s + j], g);
  }
}

// ---------- instruments ----------
function kick(t, vol = 1) {
  const s = Math.floor(t * SR);
  let phase = 0;
  for (let j = 0; j < SR * 0.45; j++) {
    const x = j / SR;
    const f = 45 + 110 * Math.exp(-x * 28);
    phase += (2 * Math.PI * f) / SR;
    const env = Math.exp(-x * 7.5);
    const click = j < 120 ? rand() * 0.25 * (1 - j / 120) : 0;
    add(s + j, (Math.sin(phase) * env + click) * 0.9 * vol);
  }
}

function clap(t, vol = 1) {
  const s = Math.floor(t * SR);
  let prev = 0;
  for (let j = 0; j < SR * 0.25; j++) {
    const x = j / SR;
    // three quick bursts then a short tail, like hands clapping together
    const burst = x < 0.03 ? (Math.floor(x / 0.01) % 2 === 0 ? 1 : 0.3) : 1;
    const env = Math.exp(-x * 18) * burst;
    const n = rand();
    const hp = n - prev;
    prev = n;
    add(s + j, hp * env * 0.32 * vol, hp * env * 0.28 * vol);
  }
}

function hat(t, open = false, vol = 1, pan = 0) {
  const s = Math.floor(t * SR);
  const len = open ? 0.18 : 0.045;
  let p1 = 0;
  let p2 = 0;
  for (let j = 0; j < SR * len; j++) {
    const x = j / SR;
    const n = rand();
    const hp = n - 2 * p1 + p2; // second-order difference = bright, thin noise
    p2 = p1;
    p1 = n;
    const env = Math.exp(-x * (open ? 22 : 90));
    const v = hp * env * 0.07 * vol;
    add(s + j, v * (1 - pan), v * (1 + pan));
  }
}

function crash(t, vol = 1, len = 2.2) {
  const s = Math.floor(t * SR);
  let pl = 0;
  let pr = 0;
  for (let j = 0; j < SR * len; j++) {
    const x = j / SR;
    const nl = rand();
    const nr = rand();
    const env = Math.exp(-x * 2.2);
    add(s + j, (nl - pl) * env * 0.16 * vol, (nr - pr) * env * 0.16 * vol);
    pl = nl;
    pr = nr;
  }
}

// Soft saw built from a few harmonics.
const saw = (ph, harmonics = 6) => {
  let v = 0;
  for (let h = 1; h <= harmonics; h++) v += Math.sin(ph * h) / h;
  return v * 0.6;
};

function bassNote(t, note, len, vol = 1) {
  const s = Math.floor(t * SR);
  const f = midi(note);
  for (let j = 0; j < SR * len; j++) {
    const x = j / SR;
    const ph = 2 * Math.PI * f * x;
    const env = Math.min(1, x / 0.005) * Math.exp(-x * 3.5);
    const v = (saw(ph, 4) * 0.55 + Math.sin(ph) * 0.6) * env * 0.42 * vol;
    add(s + j, v * duck[s + j] || 0);
  }
}

function pluck(t, note, vol = 1, pan = 0) {
  const s = Math.floor(t * SR);
  const f = midi(note);
  for (let j = 0; j < SR * 0.5; j++) {
    const x = j / SR;
    const ph = 2 * Math.PI * f * x;
    const env = Math.min(1, x / 0.002) * Math.exp(-x * 9);
    const bright = Math.exp(-x * 20);
    const v = (Math.sin(ph) + 0.5 * Math.sin(2 * ph) * bright + 0.25 * Math.sin(3 * ph) * bright) * env * 0.11 * vol;
    add(s + j, v * (1 - pan), v * (1 + pan));
  }
}

function pad(from, to, vol = 1, attack = 0.4) {
  const s0 = Math.floor(from * SR);
  const s1 = Math.floor(to * SR);
  const detune = [-0.11, 0, 0.09];
  for (let i = s0; i < s1; i++) {
    const t = i / SR;
    const chord = CHORDS[chordAt(t)];
    const local = (t % BAR) / BAR;
    const env = Math.min(1, (t - from) / attack) * Math.min(1, (to - t) / 0.3) * (0.85 + 0.15 * Math.sin(local * Math.PI));
    let l = 0;
    let r = 0;
    for (const n of chord) {
      for (let d = 0; d < detune.length; d++) {
        const f = midi(n + detune[d]);
        const v = saw(2 * Math.PI * f * t, 3);
        if (d === 0) l += v;
        else if (d === 2) r += v;
        else {
          l += v * 0.5;
          r += v * 0.5;
        }
      }
    }
    const g = env * 0.028 * vol * duck[i];
    add(i, l * g, r * g);
  }
}

function riser(from, to, vol = 1) {
  const s0 = Math.floor(from * SR);
  const s1 = Math.floor(to * SR);
  let phase = 0;
  let prev = 0;
  for (let i = s0; i < s1; i++) {
    const p = (i - s0) / (s1 - s0);
    const n = rand();
    const hp = n - prev * (1 - p * 0.9);
    prev = n;
    const f = 200 + 1400 * p * p;
    phase += (2 * Math.PI * f) / SR;
    const v = (hp * 0.09 + Math.sin(phase) * 0.03) * p * p * vol;
    add(i, v, v);
  }
}

// ---------- arrangement ----------

// Build (0–4 s)
pad(0, DROP, 2.2, 1.2);
riser(0.5, DROP, 1.4);
for (let t = 0; t < DROP; t += BEAT) hat(t, false, 0.4 + (t / DROP) * 0.6);
for (let t = 2; t < DROP; t += BEAT / 2) hat(t + BEAT / 4, false, 0.5);
for (let i = 0; i < 8; i++) clap(3 + i * (BEAT / 4), 0.25 + i * 0.09);

// Drop (4–26 s)
pad(DROP, HIT, 0.8, 0.05);
crash(DROP, 1.2);
for (let t = DROP; t < LIFT; t += BEAT) kick(t);
for (let t = DROP + BEAT; t < LIFT; t += BEAT * 2) clap(t, 0.9);
for (let t = DROP; t < LIFT; t += BEAT) hat(t + BEAT / 2, true, 0.9, 0.2);
for (let t = 22; t < LIFT; t += BEAT / 4) hat(t, false, 0.7, -0.3);
for (let t = DROP; t < LIFT; t += BEAT) {
  bassNote(t + BEAT / 2, BASS[chordAt(t)], BEAT * 0.45);
}
// Plucked arpeggio, 8th notes, with a simple echo panned to the other side.
for (let t = DROP; t < LIFT; t += BEAT / 2) {
  const chord = CHORDS[chordAt(t)];
  const step = Math.round((t - DROP) / (BEAT / 2));
  const note = chord[[0, 2, 1, 3, 2, 1, 3, 2][step % 8]] + 12;
  pluck(t, note, 1, -0.25);
  pluck(t + BEAT * 0.75, note, 0.35, 0.4);
}
// A crash every 4 bars keeps the energy moving.
for (let t = DROP + BAR * 4; t < LIFT; t += BAR * 4) crash(t, 0.6, 1.2);

// Lift (26–28 s)
riser(LIFT, HIT, 1.8);
for (let i = 0; i < 16; i++) hat(LIFT + 1 + i * (BEAT / 8), false, 0.4 + i * 0.04);

// Final hit (28 s)
kick(HIT, 1.2);
crash(HIT, 1.4, 2);
clap(HIT, 0.8);
bassNote(HIT, 36, 1.8, 1.2);
for (const n of CHORDS[0]) {
  pluck(HIT, n + 12, 1.6, 0);
  pluck(HIT, n, 1.2, 0);
}
pad(HIT, LENGTH, 0.9, 0.05);

// ---------- master: gentle glue, fade-out, 16-bit WAV ----------
let peak = 0;
for (let i = 0; i < N; i++) peak = Math.max(peak, Math.abs(L[i]), Math.abs(R[i]));
const gain = 1.4 / peak;
const out = Buffer.alloc(44 + N * 4);
out.write("RIFF", 0);
out.writeUInt32LE(36 + N * 4, 4);
out.write("WAVE", 8);
out.write("fmt ", 12);
out.writeUInt32LE(16, 16);
out.writeUInt16LE(1, 20);
out.writeUInt16LE(2, 22);
out.writeUInt32LE(SR, 24);
out.writeUInt32LE(SR * 4, 28);
out.writeUInt16LE(4, 32);
out.writeUInt16LE(16, 34);
out.write("data", 36);
out.writeUInt32LE(N * 4, 40);
for (let i = 0; i < N; i++) {
  const t = i / SR;
  const fade = t > LENGTH - 0.6 ? Math.max(0, (LENGTH - t) / 0.6) : 1;
  const l = Math.tanh(L[i] * gain) * 0.89 * fade;
  const r = Math.tanh(R[i] * gain) * 0.89 * fade;
  out.writeInt16LE(Math.round(l * 32767), 44 + i * 4);
  out.writeInt16LE(Math.round(r * 32767), 46 + i * 4);
}

const here = dirname(fileURLToPath(import.meta.url));
const target = join(here, "..", "public", "music.wav");
mkdirSync(dirname(target), { recursive: true });
writeFileSync(target, out);
console.log(`Wrote ${target}`);
