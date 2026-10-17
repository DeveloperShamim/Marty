import { continueRender, delayRender, Easing, interpolate } from "remotion";
import "@fontsource/inter/400.css";
import "@fontsource/inter/600.css";
import "@fontsource/inter/700.css";
import "@fontsource/noto-sans-bengali/400.css";
import "@fontsource/noto-sans-bengali/600.css";
import "@fontsource/noto-sans-bengali/800.css";

// Hold the first frame until both scripts' glyphs are ready, so no frame renders in a fallback font.
const fontHandle = delayRender("Loading fonts");
Promise.all([
  document.fonts.load('800 80px "Noto Sans Bengali"', "অনলাইনে"),
  document.fonts.load('600 40px "Noto Sans Bengali"', "অনলাইনে"),
  document.fonts.load('400 40px "Noto Sans Bengali"', "অনলাইনে"),
  document.fonts.load('700 40px "Inter"', "Aa"),
  document.fonts.load('600 40px "Inter"', "Aa"),
  document.fonts.load('400 40px "Inter"', "Aa"),
]).then(() => continueRender(fontHandle));

export const FONT = "'Inter', 'Noto Sans Bengali', system-ui, sans-serif";

// Apple's light palette, warmed with the store's leather brown.
export const C = {
  bg: "#F5F5F7",
  ink: "#1D1D1F",
  sub: "#6E6E73",
  faint: "#86868B",
  brand: "#8B5A2B",
  brandLight: "#C08A4E",
  brandDark: "#2B1D14",
  green: "#28A745",
  red: "#FF3B30",
};

export const BRAND_GRADIENT = `linear-gradient(100deg, ${C.brandDark} 0%, ${C.brand} 45%, ${C.brandLight} 100%)`;

// 120 BPM at 30 fps: a beat is 15 frames, a bar is 60.
export const FPS = 30;
export const BEAT = 15;
export const BAR = 60;

// Apple-style motion: quick start, long soft settle, never a bounce.
export const easeOut = Easing.bezier(0.16, 1, 0.3, 1);
export const easeInOut = Easing.bezier(0.65, 0, 0.35, 1);

/** 0→1 progress of an animation that starts at `start` and lasts `dur` frames. */
export const prog = (frame: number, start: number, dur: number, easing = easeOut) =>
  interpolate(frame, [start, start + dur], [0, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
    easing,
  });

export const mix = (p: number, a: number, b: number) => a + (b - a) * p;

const BN = "০১২৩৪৫৬৭৮৯";
export const bn = (s: string | number) => String(s).replace(/[0-9]/g, (d) => BN[Number(d)]);

/** 26188 → "২৬,১৮৮" (Bangladeshi grouping isn't needed below a lakh). */
export const bnMoney = (n: number) => bn(Math.round(n).toLocaleString("en-US"));
