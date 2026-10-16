import React from "react";
import { AbsoluteFill, interpolate, useCurrentFrame } from "remotion";
import { BEAT, C, bn, bnMoney, easeInOut, easeOut, mix, prog } from "./theme";
import {
  Center,
  Check,
  FloorShadow,
  Glass,
  Headline,
  Icon,
  Laptop,
  Logo,
  Phone,
  Screen,
  Stage,
  Sub,
  pop,
  useLayout,
} from "./ui";

const titleSize = (vertical: boolean) => (vertical ? 104 : 92);
const align = (vertical: boolean) => (vertical ? "center" : "left") as "center" | "left";

/** Gentle float so nothing ever sits perfectly still. */
const useFloat = (amp = 8) => {
  const frame = useCurrentFrame();
  return Math.sin(frame / 22) * amp;
};

// 0:00 — "ব্যবসা এখন অনলাইনে।"
export const Intro: React.FC = () => {
  const frame = useCurrentFrame();
  const { u } = useLayout();
  const out = prog(frame, 100, 20, easeInOut);
  return (
    <Center style={{ flexDirection: "column", transform: `scale(${mix(out, 1, 0.94)})`, opacity: mix(out, 1, 0.6) }}>
      <Headline lines={["ব্যবসা এখন", "অনলাইনে।"]} start={6} stagger={BEAT} size={128} accent={["অনলাইনে।"]} />
      <div style={{ marginTop: 40 * u, display: "flex", gap: 22 * u, fontSize: 36 * u, fontWeight: 600, color: C.faint }}>
        {["অর্ডার", "কুরিয়ার", "স্টক", "হিসাব"].map((w, i) => (
          <span key={w} style={{ opacity: prog(frame, 60 + i * 8, 14), transform: `translateY(${mix(prog(frame, 60 + i * 8, 14), 14, 0)}px)` }}>
            {w}
            {i < 3 ? <span style={{ marginLeft: 22 * u, color: "#C7C7CC" }}>·</span> : null}
          </span>
        ))}
      </div>
    </Center>
  );
};

// 0:04 — the drop: the dashboard rises out of the light.
export const Platform: React.FC = () => {
  const frame = useCurrentFrame();
  const { vertical, u } = useLayout();
  const rise = prog(frame, 0, 30);
  const w = (vertical ? 900 : 880) * u;
  return (
    <Stage
      title={
        <>
          <Headline lines={["একটি পূর্ণাঙ্গ", "ই-কমার্স প্ল্যাটফর্ম"]} size={titleSize(vertical) * 0.92} accent={["ই-কমার্স", "প্ল্যাটফর্ম"]} align={align(vertical)} stagger={3} />
          <Sub text="অর্ডার থেকে মুনাফা — এক ড্যাশবোর্ডে" align={align(vertical)} start={12} />
        </>
      }
      visual={
        <Center style={{ perspective: 2400 * u }}>
          <div
            style={{
              transform: `translateY(${mix(rise, 380, 0) * u}px) rotateX(${mix(rise, 34, 10) - frame * 0.06}deg) scale(${mix(rise, 0.86, 1) + frame * 0.0008})`,
              opacity: prog(frame, 0, 10),
            }}
          >
            <Laptop width={w}>
              <Screen name="admin-dashboard" style={{ transform: `translateY(${-prog(frame, 20, 40, easeInOut) * 8}%)` }} />
            </Laptop>
            <FloorShadow width={w * 1.1} />
          </div>
        </Center>
      }
    />
  );
};

// 0:06 — the storefront on a phone, scrolling.
export const Mobile: React.FC = () => {
  const frame = useCurrentFrame();
  const { vertical, u } = useLayout();
  const float = useFloat(6 * u);
  const w = (vertical ? 500 : 430) * u;
  const scroll = interpolate(frame, [4, 60], [0, -17], { extrapolateLeft: "clamp", extrapolateRight: "clamp", easing: easeInOut });
  const turn = prog(frame, 0, 40);
  return (
    <Stage
      title={
        <>
          <Headline lines={["মোবাইল-উপযোগী", "ডিজাইন"]} size={titleSize(vertical)} accent={["ডিজাইন"]} align={align(vertical)} stagger={4} />
          <Sub text="পুরো দোকান, কাস্টমারের হাতের মুঠোয়" align={align(vertical)} />
        </>
      }
      visual={
        <Center style={{ perspective: 2200 * u }}>
          <div style={{ position: "relative", width: w * 1.55, height: w * 2.1 }}>
            <div
              style={{
                position: "absolute",
                left: w * 0.62,
                top: w * 0.16,
                transform: `rotateY(${mix(turn, -30, -16)}deg) scale(0.84)`,
                opacity: 0.95 * prog(frame, 6, 18),
                filter: `blur(${1.5 * u}px)`,
              }}
            >
              <Phone width={w}>
                <Screen name="store-product-phone" />
              </Phone>
            </div>
            <div style={{ position: "absolute", left: 0, top: 0, transform: `translateY(${float}px) rotateY(${mix(turn, -24, -8)}deg) rotateX(${mix(turn, 8, 2)}deg)` }}>
              <Phone width={w}>
                <Screen name="store-home-phone" style={{ transform: `translateY(${scroll}%)` }} />
              </Phone>
            </div>
          </div>
        </Center>
      }
    />
  );
};

// 0:08 — "এক ট্যাপে অর্ডার।" A tap on Place Order, then a confirmation.
export const OneTap: React.FC = () => {
  const frame = useCurrentFrame();
  const { vertical, u } = useLayout();
  const w = (vertical ? 500 : 430) * u;
  const rise = prog(frame, 0, 26);
  const tap = 26;
  const ripple = prog(frame, tap, 16);
  const press = frame >= tap && frame < tap + 6 ? 0.97 : 1;
  const toast = prog(frame, tap + 10, 16);
  return (
    <Stage
      title={
        <>
          <Headline lines={["এক ট্যাপে", "অর্ডার।"]} size={titleSize(vertical) * 1.1} accent={["অর্ডার।"]} align={align(vertical)} stagger={6} />
          <Sub text="পণ্য বেছে নিন, সরাসরি অর্ডার করুন" align={align(vertical)} />
        </>
      }
      visual={
        <Center>
          <div style={{ position: "relative", transform: `translateY(${mix(rise, 260, 0) * u}px) scale(${press})`, opacity: prog(frame, 0, 8) }}>
            <Phone width={w}>
              <Screen name="store-checkout-phone" />
              {/* Tap ripple on the Place Order button (bottom of the screen) */}
              <div
                style={{
                  position: "absolute",
                  left: "50%",
                  top: "94.5%",
                  width: w * 0.5,
                  height: w * 0.5,
                  marginLeft: -w * 0.25,
                  marginTop: -w * 0.25,
                  borderRadius: "50%",
                  background: "rgba(255,255,255,0.55)",
                  transform: `scale(${mix(ripple, 0.1, 1.6)})`,
                  opacity: frame >= tap ? 1 - ripple : 0,
                }}
              />
            </Phone>
            <Glass
              style={{
                position: "absolute",
                left: "50%",
                top: "38%",
                transform: `translate(-50%, ${mix(toast, 40, 0)}px) scale(${mix(toast, 0.9, 1)})`,
                opacity: toast,
                padding: `${26 * u}px ${36 * u}px`,
                display: "flex",
                alignItems: "center",
                gap: 20 * u,
                whiteSpace: "nowrap",
              }}
            >
              <div style={{ width: 56 * u, height: 56 * u, borderRadius: "50%", background: C.green, display: "flex", alignItems: "center", justifyContent: "center" }}>
                <Check size={30 * u} />
              </div>
              <div>
                <div style={{ fontSize: 36 * u, fontWeight: 800 }}>অর্ডার সম্পন্ন</div>
                <div style={{ fontSize: 26 * u, fontWeight: 600, color: C.sub }}>#VB-409798 · ৳১,১৯০</div>
              </div>
            </Glass>
          </div>
        </Center>
      }
    />
  );
};

// 0:10 — payment methods arrive one per beat.
export const Payments: React.FC = () => {
  const frame = useCurrentFrame();
  const { vertical, u } = useLayout();
  const float = useFloat(5 * u);
  const methods: [string, string, string][] = [
    ["ক্যাশ অন ডেলিভারি", C.green, "৳"],
    ["বিকাশ", "#E2136E", "b"],
    ["নগদ", "#F6921E", "n"],
    ["রকেট", "#8C3494", "r"],
  ];
  return (
    <Stage
      title={
        <>
          <Headline lines={["সকল পেমেন্ট", "পদ্ধতি"]} size={titleSize(vertical)} accent={["পদ্ধতি"]} align={align(vertical)} stagger={4} />
          <Sub text="ক্যাশ অন ডেলিভারি ও মোবাইল ব্যাংকিং" align={align(vertical)} />
        </>
      }
      visual={
        <Center>
          <div style={{ display: "flex", flexDirection: "column", gap: 30 * u, transform: `translateY(${float}px)` }}>
            {methods.map(([label, color, mark], i) => (
              <div key={label} style={{ ...pop(frame, i * BEAT), marginLeft: (i % 2 ? 90 : -10) * u }}>
                <Glass style={{ display: "flex", alignItems: "center", gap: 26 * u, padding: `${26 * u}px ${44 * u}px ${26 * u}px ${28 * u}px` }} radius={999}>
                  <div
                    style={{
                      width: 76 * u,
                      height: 76 * u,
                      borderRadius: "50%",
                      background: color,
                      color: "#fff",
                      fontSize: 38 * u,
                      fontWeight: 800,
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "center",
                      boxShadow: `0 ${10 * u}px ${24 * u}px ${color}55`,
                    }}
                  >
                    {mark}
                  </div>
                  <div style={{ fontSize: 46 * u, fontWeight: 800, whiteSpace: "nowrap" }}>{label}</div>
                </Glass>
              </div>
            ))}
          </div>
        </Center>
      }
    />
  );
};

// 0:12 — "exploded UI": the delivery-history card lifts out of the order page.
export const Verify: React.FC = () => {
  const frame = useCurrentFrame();
  const { vertical, u } = useLayout();
  const lift = prog(frame, 4, 26);
  const bars = prog(frame, 14, 26);
  const badge = prog(frame, 32, 14);
  const cardW = (vertical ? 760 : 640) * u;
  return (
    <Stage
      title={
        <>
          <Headline lines={["স্মার্ট অর্ডার", "যাচাই"]} size={titleSize(vertical)} accent={["যাচাই"]} align={align(vertical)} stagger={4} />
          <Sub text="সন্দেহজনক অর্ডার শনাক্ত হয় শুরুতেই" align={align(vertical)} />
        </>
      }
      visual={
        <Center style={{ perspective: 2400 * u }}>
          <div
            style={{
              position: "absolute",
              width: 1000 * u,
              transform: `rotateY(-16deg) rotateX(10deg) translateZ(${-200 * u}px) scale(${mix(lift, 1.02, 0.95)})`,
              filter: `blur(${mix(lift, 0, 5) * u}px)`,
              opacity: mix(lift, 1, 0.55),
              borderRadius: 24 * u,
              overflow: "hidden",
              boxShadow: `0 ${40 * u}px ${90 * u}px rgba(60,40,20,0.18)`,
            }}
          >
            <Screen name="admin-order" />
          </div>
          <div style={{ transform: `translateY(${mix(lift, 120, 0) * u}px) translateZ(${mix(lift, 0, 120) * u}px)`, opacity: prog(frame, 4, 10) }}>
            <Glass style={{ width: cardW, padding: 48 * u }}>
              <div style={{ fontSize: 30 * u, fontWeight: 600, color: C.sub }}>কাস্টমারের ডেলিভারি রেকর্ড</div>
              <div style={{ display: "flex", alignItems: "baseline", gap: 18 * u, marginTop: 6 * u }}>
                <div style={{ fontSize: 110 * u, fontWeight: 800, letterSpacing: -2 * u }}>{bn(Math.round(mix(bars, 0, 95)))}%</div>
                <div style={{ fontSize: 34 * u, fontWeight: 700, color: C.green }}>সফল ডেলিভারি</div>
              </div>
              {[
                ["ডেলিভারড", 18, 0.95, C.green],
                ["রিটার্ন", 1, 0.05, C.red],
              ].map(([label, n, share, color]) => (
                <div key={label as string} style={{ marginTop: 26 * u }}>
                  <div style={{ display: "flex", justifyContent: "space-between", fontSize: 30 * u, fontWeight: 700 }}>
                    <span>{label}</span>
                    <span>{bn(Math.round(mix(bars, 0, n as number)))}</span>
                  </div>
                  <div style={{ height: 16 * u, borderRadius: 99, background: "rgba(0,0,0,0.06)", marginTop: 10 * u, overflow: "hidden" }}>
                    <div style={{ height: "100%", width: `${(share as number) * 100 * bars}%`, background: color as string, borderRadius: 99 }} />
                  </div>
                </div>
              ))}
              <div
                style={{
                  marginTop: 36 * u,
                  display: "inline-flex",
                  alignItems: "center",
                  gap: 14 * u,
                  padding: `${14 * u}px ${26 * u}px`,
                  borderRadius: 999,
                  background: C.green,
                  color: "#fff",
                  fontSize: 30 * u,
                  fontWeight: 800,
                  opacity: badge,
                  transform: `scale(${mix(badge, 0.85, 1)})`,
                }}
              >
                <Check size={30 * u} /> নিরাপদ অর্ডার · অটো-কনফার্মড
              </div>
            </Glass>
          </div>
        </Center>
      }
    />
  );
};

// 0:14 — courier partners light up one per beat.
export const Courier: React.FC = () => {
  const frame = useCurrentFrame();
  const { vertical, u } = useLayout();
  const couriers = ["Steadfast", "Pathao", "RedX"];
  const booked = prog(frame, 3 * BEAT, 14);
  return (
    <Stage
      title={
        <>
          <Headline lines={["এক ক্লিকে", "কুরিয়ার"]} size={titleSize(vertical) * 1.05} accent={["কুরিয়ার"]} align={align(vertical)} stagger={6} />
          <Sub text="অর্ডার সরাসরি কুরিয়ারে, আপডেট নিজে থেকেই" align={align(vertical)} />
        </>
      }
      visual={
        <Center style={{ flexDirection: "column", gap: 30 * u }}>
          {couriers.map((name, i) => {
            const on = prog(frame, i * BEAT, 12);
            return (
              <div key={name} style={pop(frame, i * BEAT)}>
                <Glass
                  radius={999}
                  style={{
                    width: 620 * u,
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "space-between",
                    padding: `${30 * u}px ${36 * u}px ${30 * u}px ${48 * u}px`,
                    border: `${2 * u}px solid ${on > 0.5 ? "rgba(139,90,43,0.35)" : "rgba(255,255,255,0.95)"}`,
                  }}
                >
                  <span style={{ fontSize: 52 * u, fontWeight: 700, letterSpacing: -1 * u }}>{name}</span>
                  <span
                    style={{
                      width: 64 * u,
                      height: 64 * u,
                      borderRadius: "50%",
                      background: C.brand,
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "center",
                      transform: `scale(${on})`,
                    }}
                  >
                    <Check size={32 * u} />
                  </span>
                </Glass>
              </div>
            );
          })}
          <div
            style={{
              marginTop: 16 * u,
              fontSize: 34 * u,
              fontWeight: 800,
              color: C.green,
              opacity: booked,
              transform: `translateY(${mix(booked, 20, 0)}px)`,
            }}
          >
            ● পার্সেল বুক হয়েছে
          </div>
        </Center>
      }
    />
  );
};

/** A fixed, made-up barcode pattern (bar widths in units). */
const BARS = [2, 1, 1, 3, 1, 2, 2, 1, 3, 1, 1, 2, 1, 1, 3, 2, 1, 2, 1, 3, 1, 1, 2, 2, 1, 3, 1, 2, 1, 1, 2, 3, 1, 1, 2, 1, 3, 1, 2, 1];

// 0:16 — a scan beam sweeps across the parcel label.
export const Scan: React.FC = () => {
  const frame = useCurrentFrame();
  const { vertical, u } = useLayout();
  const sweep = interpolate(frame, [8, 30], [0, 1], { extrapolateLeft: "clamp", extrapolateRight: "clamp", easing: easeInOut });
  const done = prog(frame, 32, 12);
  const w = 700 * u;
  let x = 0;
  const unit = (w - 96 * u) / BARS.reduce((a, b) => a + b, 0) / 1.6;
  return (
    <Stage
      title={
        <>
          <Headline lines={["স্ক্যান করুন,", "পাঠান।"]} size={titleSize(vertical)} accent={["পাঠান।"]} align={align(vertical)} stagger={5} />
          <Sub text="কুরিয়ার ইন / আউট স্ক্যান স্টেশন" align={align(vertical)} />
        </>
      }
      visual={
        <Center>
          <div style={{ ...pop(frame, 0), transform: `rotate(-4deg) ${pop(frame, 0).transform}` }}>
            <Glass style={{ width: w, padding: 48 * u, position: "relative", overflow: "hidden", background: "rgba(255,255,255,0.88)" }}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                <div>
                  <div style={{ fontSize: 26 * u, fontWeight: 600, color: C.sub }}>পার্সেল</div>
                  <div style={{ fontSize: 48 * u, fontWeight: 800, fontFamily: "Inter", letterSpacing: -1 * u }}>#VB-409798</div>
                </div>
                <div
                  style={{
                    padding: `${12 * u}px ${24 * u}px`,
                    borderRadius: 999,
                    fontSize: 28 * u,
                    fontWeight: 800,
                    background: done > 0.5 ? C.green : "#FFF4E0",
                    color: done > 0.5 ? "#fff" : "#B26A00",
                    transform: `scale(${done > 0 && done < 1 ? mix(done, 1.12, 1) : 1})`,
                  }}
                >
                  {done > 0.5 ? "✓ শিপড" : "পেন্ডিং"}
                </div>
              </div>
              <div style={{ display: "flex", height: 200 * u, marginTop: 40 * u, gap: 0 }}>
                {BARS.map((b, i) => {
                  const el = <div key={i} style={{ width: b * unit, marginRight: (i % 3 === 0 ? 2 : 1) * unit * 0.6, background: C.ink }} />;
                  x += b;
                  return el;
                })}
              </div>
              <div style={{ marginTop: 18 * u, fontSize: 26 * u, fontWeight: 600, color: C.sub }}>Steadfast · খুলনা · ৳৮,৩৩০ COD</div>
              {/* the scan beam */}
              <div
                style={{
                  position: "absolute",
                  top: 0,
                  bottom: 0,
                  left: `${mix(sweep, -5, 105)}%`,
                  width: 6 * u,
                  background: C.red,
                  boxShadow: `0 0 ${30 * u}px ${12 * u}px rgba(255,59,48,0.35)`,
                  opacity: frame >= 8 && frame <= 32 ? 1 : 0,
                }}
              />
            </Glass>
          </div>
        </Center>
      }
    />
  );
};

// 0:18 — one stock for the shop counter and the website.
export const OneStock: React.FC = () => {
  const frame = useCurrentFrame();
  const { vertical, u } = useLayout();
  const sold = 30;
  const tick = prog(frame, sold, 12);
  const link = prog(frame, 10, 24, easeInOut);
  const w = (vertical ? 430 : 380) * u;
  return (
    <Stage
      title={
        <>
          <Headline lines={["অনলাইন ও শোরুম", "একটি স্টক"]} size={titleSize(vertical) * 0.95} accent={["একটি", "স্টক"]} align={align(vertical)} stagger={4} />
          <Sub text="POS আর ওয়েবসাইট — সবসময় মিলে থাকে" align={align(vertical)} />
        </>
      }
      visual={
        <Center>
          <div style={{ display: "flex", alignItems: "center", gap: 0 }}>
            <div style={{ ...pop(frame, 0), transform: `rotateY(10deg) ${pop(frame, 0).transform}` }}>
              <Phone width={w}>
                <Screen name="admin-pos-phone" />
              </Phone>
            </div>
            <svg width={140 * u} height={20 * u} style={{ overflow: "visible" }}>
              <line x1={0} y1={10 * u} x2={140 * u} y2={10 * u} stroke={C.brand} strokeWidth={4 * u} strokeDasharray={`${10 * u} ${10 * u}`} strokeDashoffset={-frame * 2} opacity={link} />
            </svg>
            <div style={pop(frame, 8)}>
              <Glass style={{ padding: `${44 * u}px ${52 * u}px`, width: (vertical ? 400 : 420) * u }}>
                <div style={{ fontSize: 28 * u, fontWeight: 600, color: C.sub }}>Vant Classic Bifold Wallet</div>
                <div style={{ fontSize: 28 * u, fontWeight: 600, color: C.sub, marginTop: 4 * u }}>ওয়েবসাইটে স্টক</div>
                <div style={{ position: "relative", height: 150 * u, marginTop: 10 * u, overflow: "hidden" }}>
                  {[61, 60].map((n, i) => (
                    <div
                      key={n}
                      style={{
                        position: "absolute",
                        fontSize: 140 * u,
                        fontWeight: 800,
                        lineHeight: 1,
                        letterSpacing: -3 * u,
                        transform: `translateY(${(i - tick) * 100}%)`,
                        color: i === 1 ? C.brand : C.ink,
                      }}
                    >
                      {bn(n)}
                    </div>
                  ))}
                </div>
                <div style={{ fontSize: 28 * u, fontWeight: 800, color: C.green, opacity: tick }}>● দোকানে বিক্রি · স্টক আপডেট</div>
              </Glass>
            </div>
          </div>
        </Center>
      }
    />
  );
};

// 0:20 — the real profit figure counts up, full screen.
export const Profit: React.FC = () => {
  const frame = useCurrentFrame();
  const { u, vertical } = useLayout();
  const count = prog(frame, 4, 34, Easing2);
  const draw = prog(frame, 6, 44, easeInOut);
  const chartW = (vertical ? 860 : 1100) * u;
  const chartH = 220 * u;
  const pts = [0.15, 0.22, 0.2, 0.34, 0.3, 0.46, 0.42, 0.58, 0.55, 0.72, 0.68, 0.9];
  const d = pts.map((p, i) => `${i ? "L" : "M"}${(i / (pts.length - 1)) * chartW},${chartH - p * chartH}`).join(" ");
  return (
    <Center style={{ flexDirection: "column" }}>
      <div style={{ fontSize: 40 * u, fontWeight: 600, color: C.sub, opacity: prog(frame, 0, 10) }}>এই মাসের প্রকৃত মুনাফা</div>
      <div
        style={{
          fontSize: (vertical ? 230 : 250) * u,
          fontWeight: 800,
          letterSpacing: -6 * u,
          lineHeight: 1.2,
          backgroundImage: `linear-gradient(100deg, ${C.brandDark}, ${C.brand} 55%, ${C.brandLight})`,
          WebkitBackgroundClip: "text",
          backgroundClip: "text",
          color: "transparent",
          transform: `scale(${mix(prog(frame, 0, 20), 0.9, 1)})`,
        }}
      >
        ৳{bnMoney(26188 * count)}
      </div>
      <div style={{ fontSize: 34 * u, fontWeight: 600, color: C.faint, opacity: prog(frame, 20, 14) }}>ক্রয়মূল্য, কুরিয়ার চার্জ ও বিজ্ঞাপন খরচ বাদে</div>
      <svg width={chartW} height={chartH + 20 * u} style={{ marginTop: 50 * u, overflow: "visible" }}>
        <defs>
          <linearGradient id="area" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stopColor={C.brandLight} stopOpacity={0.28} />
            <stop offset="1" stopColor={C.brandLight} stopOpacity={0} />
          </linearGradient>
          <clipPath id="reveal">
            <rect x={0} y={-20} width={chartW * draw} height={chartH + 40 * u} />
          </clipPath>
        </defs>
        <g clipPath="url(#reveal)">
          <path d={`${d} L${chartW},${chartH} L0,${chartH} Z`} fill="url(#area)" />
          <path d={d} fill="none" stroke={C.brand} strokeWidth={6 * u} strokeLinecap="round" strokeLinejoin="round" />
        </g>
      </svg>
    </Center>
  );
};

// Counting numbers read best with a slightly gentler settle than the default.
function Easing2(t: number) {
  return 1 - Math.pow(1 - t, 4);
}

const TILES: [string, React.ReactNode][] = [
  ["লাইভ চ্যাট", <path key="a" d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />],
  ["কুপন", <><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z" /><path d="M13 5v2" /><path d="M13 17v2" /><path d="M13 11v2" /></>],
  ["ফ্ল্যাশ সেল", <path key="z" d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z" />],
  ["স্টাফ রোল", <><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /></>],
  ["বিজ্ঞাপন ট্র্যাকিং", <><circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2" /></>],
  ["বারকোড", <><path d="M3 7V5a2 2 0 0 1 2-2h2" /><path d="M17 3h2a2 2 0 0 1 2 2v2" /><path d="M21 17v2a2 2 0 0 1-2 2h-2" /><path d="M7 21H5a2 2 0 0 1-2-2v-2" /><path d="M8 7v10" /><path d="M12 7v10" /><path d="M17 7v10" /></>],
  ["অ্যানালিটিক্স", <><path d="M3 3v16a2 2 0 0 0 2 2h16" /><path d="m19 9-5 5-4-4-3 3" /></>],
  ["থিম কালার", <path key="d" d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z" />],
];

const useTileGrid = () => {
  const { vertical, u, width, height } = useLayout();
  const cols = vertical ? 2 : 4;
  const tw = (vertical ? 440 : 380) * u;
  const th = (vertical ? 250 : 230) * u;
  const gap = 34 * u;
  const gridW = cols * tw + (cols - 1) * gap;
  const rows = Math.ceil(TILES.length / cols);
  const gridH = rows * th + (rows - 1) * gap;
  const top = vertical ? height * 0.27 : height * 0.32;
  const pos = (i: number) => ({
    x: (width - gridW) / 2 + (i % cols) * (tw + gap),
    y: top + Math.floor(i / cols) * (th + gap),
  });
  return { tw, th, pos, width, gridH, top, height, u, vertical };
};

const Tile: React.FC<{ i: number; style?: React.CSSProperties }> = ({ i, style }) => {
  const { tw, th, u } = useTileGrid();
  const [label, icon] = TILES[i];
  return (
    <Glass style={{ position: "absolute", width: tw, height: th, padding: 34 * u, display: "flex", flexDirection: "column", justifyContent: "space-between", ...style }}>
      <div style={{ width: 84 * u, height: 84 * u, borderRadius: 24 * u, background: "rgba(139,90,43,0.10)", display: "flex", alignItems: "center", justifyContent: "center" }}>
        <Icon size={46 * u}>{icon}</Icon>
      </div>
      <div style={{ fontSize: 42 * u, fontWeight: 800, whiteSpace: "nowrap" }}>{label}</div>
    </Glass>
  );
};

// 0:22 — feature tiles flip in, one per beat.
export const Features: React.FC = () => {
  const frame = useCurrentFrame();
  const { pos, u, top, gridH, vertical, height } = useTileGrid();
  return (
    <AbsoluteFill style={{ perspective: 2000 * u }}>
      {TILES.map((_, i) => {
        const p = prog(frame, i * BEAT, 16);
        const { x, y } = pos(i);
        return (
          <Tile
            key={i}
            i={i}
            style={{
              left: x,
              top: y,
              opacity: p,
              transform: `rotateX(${mix(p, -70, 0)}deg) translateY(${mix(p, 40, 0) * u}px)`,
              transformOrigin: "50% 100%",
            }}
          />
        );
      })}
      <div style={{ position: "absolute", left: 0, right: 0, top: vertical ? top - 260 * u : top - 220 * u, display: "flex", justifyContent: "center" }}>
        <Headline lines={["সবকিছু, একটি প্ল্যাটফর্মে।"]} start={3 * BEAT} stagger={4} size={vertical ? 84 : 80} accent={["প্ল্যাটফর্মে।"]} />
      </div>
      <div style={{ position: "absolute", left: 0, right: 0, top: top + gridH + 40 * u, display: "flex", justifyContent: "center", height: height * 0.1 }} />
    </AbsoluteFill>
  );
};

// 0:26 — the tiles fold into the logo; "একটি প্ল্যাটফর্ম। সম্পূর্ণ সমাধান।"
export const Collapse: React.FC = () => {
  const frame = useCurrentFrame();
  const { pos, tw, th, width, height, u } = useTileGrid();
  const fold = prog(frame, 0, 22, easeInOut);
  const logo = prog(frame, 14, 20);
  return (
    <AbsoluteFill style={{ fontFamily: undefined }}>
      {TILES.map((_, i) => {
        const { x, y } = pos(i);
        const cx = width / 2 - tw / 2;
        const cy = height * 0.36 - th / 2;
        return (
          <Tile
            key={i}
            i={i}
            style={{
              left: mix(fold, x, cx),
              top: mix(fold, y, cy),
              transform: `scale(${mix(fold, 1, 0.2)})`,
              opacity: 1 - prog(frame, 14, 8),
            }}
          />
        );
      })}
      <Center style={{ flexDirection: "column" }}>
        <div style={{ transform: `scale(${mix(logo, 0.4, 1)})`, opacity: logo, marginBottom: 60 * u }}>
          <Logo size={190 * u} />
        </div>
        <Headline lines={["একটি প্ল্যাটফর্ম।", "সম্পূর্ণ সমাধান।"]} start={22} stagger={BEAT / 2} size={104} accent={["সম্পূর্ণ", "সমাধান।"]} />
      </Center>
    </AbsoluteFill>
  );
};

// 0:28 — the final hit: logo, tagline and contact.
export const EndCard: React.FC<{ phone: string; website: string }> = ({ phone, website }) => {
  const frame = useCurrentFrame();
  const { u } = useLayout();
  const hit = prog(frame, 0, 18);
  const contact = prog(frame, 10, 16);
  return (
    <Center style={{ flexDirection: "column" }}>
      <div style={{ transform: `scale(${mix(hit, 1.25, 1)})`, marginBottom: 56 * u }}>
        <Logo size={170 * u} />
      </div>
      <Headline lines={["আপনার ব্যবসার", "ডিজিটাল ভিত্তি।"]} start={0} stagger={3} size={100} accent={["ডিজিটাল", "ভিত্তি।"]} />
      <div style={{ marginTop: 60 * u, opacity: contact, transform: `translateY(${mix(contact, 24, 0)}px)` }}>
        <Glass radius={999} style={{ display: "flex", alignItems: "center", gap: 22 * u, padding: `${24 * u}px ${44 * u}px` }}>
          <Icon size={40 * u}>
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z" />
          </Icon>
          <span style={{ fontSize: 44 * u, fontWeight: 800 }}>{phone}</span>
          {website ? <span style={{ fontSize: 36 * u, fontWeight: 600, color: C.sub }}>· {website}</span> : null}
        </Glass>
      </div>
      <div style={{ marginTop: 26 * u, fontSize: 32 * u, fontWeight: 600, color: C.faint, opacity: prog(frame, 18, 14) }}>লাইভ ডেমো দেখতে আজই যোগাযোগ করুন</div>
    </Center>
  );
};

// Keep the unused-import checker quiet for helpers some layouts don't use.
void easeOut;
