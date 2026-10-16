import React from "react";
import { AbsoluteFill, Audio, interpolate, Sequence, staticFile } from "remotion";
import { BAR, BEAT } from "./theme";
import { Backdrop, Shot } from "./ui";
import { Collapse, Courier, EndCard, Features, Intro, Mobile, OneStock, OneTap, Payments, Platform, Profit, Scan, Verify } from "./scenes";

export type PromoProps = {
  /** Shown on the end card. */
  phone: string;
  website: string;
  /** Set to true once public/voiceover.mp3 (the recorded Bangla VO) is in place. */
  voiceover: boolean;
};

export const defaultProps: PromoProps = {
  phone: "০১XXX-XXXXXX",
  website: "",
  voiceover: false,
};

// One shot per bar (2 s at 120 BPM), as in the beat map of the script.
const SHOTS: [number, number, React.FC<any>][] = [
  [0, 2 * BAR, Intro], //            0:00 build
  [2 * BAR, BAR, Platform], //       0:04 drop
  [3 * BAR, BAR, Mobile], //         0:06
  [4 * BAR, BAR, OneTap], //         0:08
  [5 * BAR, BAR, Payments], //       0:10
  [6 * BAR, BAR, Verify], //         0:12
  [7 * BAR, BAR, Courier], //        0:14
  [8 * BAR, BAR, Scan], //           0:16
  [9 * BAR, BAR, OneStock], //       0:18
  [10 * BAR, BAR, Profit], //        0:20
  [11 * BAR, 2 * BAR, Features], //  0:22 tiles, one per beat
  [13 * BAR, BAR, Collapse], //      0:26
  [14 * BAR, BAR, EndCard], //       0:28 final hit
];

export const Promo: React.FC<PromoProps> = (props) => {
  return (
    <AbsoluteFill>
      <Backdrop />
      {SHOTS.map(([from, duration, Scene], i) => (
        <Sequence key={i} from={from} durationInFrames={duration} premountFor={BEAT}>
          <Shot>
            <Scene {...props} />
          </Shot>
        </Sequence>
      ))}
      <Audio src={staticFile("music.wav")} volume={(f) => interpolate(f, [0, 10], [0, props.voiceover ? 0.55 : 1], { extrapolateRight: "clamp" })} />
      {props.voiceover ? <Audio src={staticFile("voiceover.mp3")} /> : null}
    </AbsoluteFill>
  );
};
