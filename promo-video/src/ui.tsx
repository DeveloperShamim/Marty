import React from "react";
import { AbsoluteFill, Img, staticFile, useCurrentFrame, useVideoConfig } from "remotion";
import { BRAND_GRADIENT, C, FONT, easeOut, mix, prog } from "./theme";

/** Layout helpers shared by every scene. */
export const useLayout = () => {
  const { width, height } = useVideoConfig();
  const vertical = height > width;
  return { width, height, vertical, u: Math.min(width, height) / 1080 };
};

/** Soft, light backdrop with two warm glows drifting slowly. Stays constant under every cut. */
export const Backdrop: React.FC = () => {
  const frame = useCurrentFrame();
  const { width, height } = useLayout();
  const t = frame / 30;
  const glow = (x: number, y: number, r: number, color: string) => (
    <div
      style={{
        position: "absolute",
        left: x - r,
        top: y - r,
        width: r * 2,
        height: r * 2,
        borderRadius: "50%",
        background: `radial-gradient(circle, ${color} 0%, rgba(255,255,255,0) 70%)`,
      }}
    />
  );
  return (
    <AbsoluteFill style={{ background: `linear-gradient(180deg, #FFFFFF 0%, ${C.bg} 100%)`, overflow: "hidden" }}>
      {glow(width * (0.25 + 0.06 * Math.sin(t * 0.5)), height * (0.3 + 0.04 * Math.cos(t * 0.4)), Math.max(width, height) * 0.45, "rgba(192,138,78,0.16)")}
      {glow(width * (0.8 + 0.05 * Math.cos(t * 0.45)), height * (0.75 + 0.05 * Math.sin(t * 0.35)), Math.max(width, height) * 0.4, "rgba(139,90,43,0.10)")}
    </AbsoluteFill>
  );
};

/**
 * Every cut lands on a beat. The new shot settles in with a short, soft zoom and blur
 * so the hard cut feels deliberate rather than jumpy.
 */
export const Shot: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const frame = useCurrentFrame();
  const p = prog(frame, 0, 14);
  return (
    <AbsoluteFill
      style={{
        fontFamily: FONT,
        color: C.ink,
        transform: `scale(${mix(p, 1.05, 1)})`,
        filter: `blur(${mix(prog(frame, 0, 8), 10, 0)}px)`,
        opacity: prog(frame, 0, 5),
      }}
    >
      {children}
    </AbsoluteFill>
  );
};

/**
 * Apple's signature headline: each word rises a little and comes into focus.
 * `lines` are shown one per row; words in `accent` get the brand gradient.
 */
export const Headline: React.FC<{
  lines: string[];
  start?: number;
  stagger?: number;
  size?: number;
  accent?: string[];
  align?: "center" | "left";
  weight?: number;
}> = ({ lines, start = 0, stagger = 5, size = 108, accent = [], align = "center", weight = 800 }) => {
  const frame = useCurrentFrame();
  const { u } = useLayout();
  let i = 0;
  return (
    <div style={{ textAlign: align, fontWeight: weight, fontSize: size * u, lineHeight: 1.28, letterSpacing: -0.01 * size * u }}>
      {lines.map((line, li) => (
        <div key={li} style={{ whiteSpace: "nowrap" }}>
          {line.split(" ").map((word, wi) => {
            const p = prog(frame, start + i++ * stagger, 16);
            const isAccent = accent.includes(word);
            return (
              <span
                key={wi}
                style={{
                  display: "inline-block",
                  marginRight: wi < line.split(" ").length - 1 ? "0.26em" : 0,
                  opacity: p,
                  transform: `translateY(${mix(p, 0.32, 0)}em)`,
                  filter: `blur(${mix(p, 14, 0) * u}px)`,
                  paddingBottom: "0.08em",
                  ...(isAccent
                    ? { backgroundImage: BRAND_GRADIENT, WebkitBackgroundClip: "text", backgroundClip: "text", color: "transparent" }
                    : {}),
                }}
              >
                {word}
              </span>
            );
          })}
        </div>
      ))}
    </div>
  );
};

/** Small grey line under a headline. */
export const Sub: React.FC<{ text: string; start?: number; size?: number; align?: "center" | "left" }> = ({
  text,
  start = 10,
  size = 40,
  align = "center",
}) => {
  const frame = useCurrentFrame();
  const { u } = useLayout();
  const p = prog(frame, start, 18);
  return (
    <div
      style={{
        marginTop: 22 * u,
        fontSize: size * u,
        fontWeight: 600,
        color: C.sub,
        textAlign: align,
        opacity: p,
        transform: `translateY(${mix(p, 16, 0) * u}px)`,
      }}
    >
      {text}
    </div>
  );
};

/**
 * Title + visual. Phones stack the title over the visual; widescreen puts them side by side.
 */
export const Stage: React.FC<{ title: React.ReactNode; visual: React.ReactNode }> = ({ title, visual }) => {
  const { vertical, u } = useLayout();
  if (vertical) {
    return (
      <AbsoluteFill style={{ flexDirection: "column" }}>
        <div style={{ height: "30%", display: "flex", flexDirection: "column", justifyContent: "flex-end", alignItems: "center", paddingBottom: 30 * u }}>
          {title}
        </div>
        <div style={{ flex: 1, position: "relative" }}>{visual}</div>
      </AbsoluteFill>
    );
  }
  return (
    <AbsoluteFill style={{ flexDirection: "row" }}>
      <div style={{ width: "44%", display: "flex", flexDirection: "column", justifyContent: "center", alignItems: "flex-start", paddingLeft: 130 * u }}>
        {title}
      </div>
      <div style={{ flex: 1, position: "relative" }}>{visual}</div>
    </AbsoluteFill>
  );
};

/** Centres its child inside the visual area. */
export const Center: React.FC<{ children: React.ReactNode; style?: React.CSSProperties }> = ({ children, style }) => (
  <AbsoluteFill style={{ alignItems: "center", justifyContent: "center", ...style }}>{children}</AbsoluteFill>
);

/** Frosted glass card, in the style of Apple's current UI. */
export const Glass: React.FC<{ children: React.ReactNode; style?: React.CSSProperties; radius?: number }> = ({ children, style, radius = 36 }) => {
  const { u } = useLayout();
  return (
    <div
      style={{
        background: "rgba(255,255,255,0.68)",
        backdropFilter: `blur(${28 * u}px) saturate(180%)`,
        WebkitBackdropFilter: `blur(${28 * u}px) saturate(180%)`,
        border: `${1.5 * u}px solid rgba(255,255,255,0.95)`,
        boxShadow: `0 ${24 * u}px ${70 * u}px rgba(80,50,20,0.14), inset 0 ${1 * u}px 0 rgba(255,255,255,0.9)`,
        borderRadius: radius * u,
        ...style,
      }}
    >
      {children}
    </div>
  );
};

/** A screenshot from public/screens. */
export const Screen: React.FC<{ name: string; style?: React.CSSProperties }> = ({ name, style }) => (
  <Img src={staticFile(`screens/${name}.jpg`)} style={{ display: "block", width: "100%", ...style }} />
);

/** MacBook-style laptop. `width` is the screen width in px; the screen is 16:10. */
export const Laptop: React.FC<{ width: number; children: React.ReactNode }> = ({ width, children }) => {
  const bezel = width * 0.018;
  const screenH = (width / 16) * 10;
  return (
    <div style={{ width: width + bezel * 2, display: "flex", flexDirection: "column", alignItems: "center" }}>
      <div
        style={{
          width: width + bezel * 2,
          height: screenH + bezel * 2,
          background: "#0E0E10",
          borderRadius: width * 0.03,
          padding: bezel,
          boxShadow: `0 0 0 ${width * 0.0015}px #9A9AA0`,
          position: "relative",
        }}
      >
        <div style={{ width, height: screenH, overflow: "hidden", borderRadius: width * 0.012, background: "#fff" }}>{children}</div>
        <div
          style={{
            position: "absolute",
            top: bezel * 0.5,
            left: "50%",
            transform: "translateX(-50%)",
            width: width * 0.1,
            height: bezel * 0.9,
            borderRadius: bezel,
            background: "#0E0E10",
          }}
        />
      </div>
      <div
        style={{
          width: (width + bezel * 2) * 1.16,
          height: width * 0.03,
          background: "linear-gradient(180deg, #E8E8EB 0%, #C7C8CC 70%, #A9AAAF 100%)",
          borderRadius: `${width * 0.004}px ${width * 0.004}px ${width * 0.03}px ${width * 0.03}px`,
          position: "relative",
        }}
      >
        <div
          style={{
            position: "absolute",
            top: 0,
            left: "50%",
            transform: "translateX(-50%)",
            width: width * 0.16,
            height: width * 0.012,
            background: "linear-gradient(180deg, #B5B6BA, #D8D9DC)",
            borderRadius: `0 0 ${width * 0.01}px ${width * 0.01}px`,
          }}
        />
      </div>
    </div>
  );
};

/** iPhone-style phone. `width` is the outer width; the screen is 390×844. */
export const Phone: React.FC<{ width: number; children: React.ReactNode }> = ({ width, children }) => {
  const border = width * 0.04;
  const screenW = width - border * 2;
  const screenH = (screenW / 390) * 844;
  return (
    <div
      style={{
        width,
        height: screenH + border * 2,
        borderRadius: width * 0.17,
        background: "#1D1D1F",
        padding: border,
        boxShadow: `0 0 0 ${width * 0.008}px #D2D2D7, 0 0 0 ${width * 0.011}px #B9B9BE`,
        position: "relative",
      }}
    >
      <div style={{ width: screenW, height: screenH, borderRadius: width * 0.135, overflow: "hidden", background: "#fff", position: "relative" }}>
        {children}
      </div>
      <div
        style={{
          position: "absolute",
          top: border + width * 0.03,
          left: "50%",
          transform: "translateX(-50%)",
          width: width * 0.3,
          height: width * 0.085,
          borderRadius: width,
          background: "#000",
        }}
      />
    </div>
  );
};

/** Big soft shadow under a floating device. */
export const FloorShadow: React.FC<{ width: number; opacity?: number }> = ({ width, opacity = 1 }) => (
  <div
    style={{
      width,
      height: width * 0.08,
      margin: "0 auto",
      borderRadius: "50%",
      background: "radial-gradient(ellipse at center, rgba(60,40,20,0.28) 0%, rgba(60,40,20,0) 70%)",
      opacity,
    }}
  />
);

/** The store's "V" mark, from public/favicon.svg. */
export const Logo: React.FC<{ size: number }> = ({ size }) => (
  <svg width={size} height={size} viewBox="0 0 64 64" style={{ display: "block", filter: `drop-shadow(0 ${size * 0.12}px ${size * 0.25}px rgba(139,90,43,0.35))` }}>
    <rect width="64" height="64" rx="14" fill={C.brand} />
    <path d="M15 17h9.2L32 39.6 39.8 17H49L36.6 48h-9.2z" fill="#FAF7F2" />
  </svg>
);

/** Lucide-style line icon, drawn from raw SVG children. */
export const Icon: React.FC<{ size: number; color?: string; children: React.ReactNode }> = ({ size, color = C.brand, children }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke={color} strokeWidth={1.8} strokeLinecap="round" strokeLinejoin="round">
    {children}
  </svg>
);

export const Check: React.FC<{ size: number; color?: string }> = ({ size, color = "#fff" }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke={color} strokeWidth={3} strokeLinecap="round" strokeLinejoin="round">
    <path d="M20 6 9 17l-5-5" />
  </svg>
);

/** A pop-in used for chips and tiles: rises, scales up from 92% and fades in. */
export const pop = (frame: number, start: number) => {
  const p = prog(frame, start, 14, easeOut);
  return { opacity: p, transform: `translateY(${mix(p, 30, 0)}px) scale(${mix(p, 0.92, 1)})` };
};
