import React from "react";
import { Composition } from "remotion";
import { Promo, defaultProps } from "./Promo";
import { FPS } from "./theme";

export const Root: React.FC = () => (
  <>
    {/* Reels, Shorts, TikTok, Stories */}
    <Composition id="Promo" component={Promo} durationInFrames={30 * FPS} fps={FPS} width={1080} height={1920} defaultProps={defaultProps} />
    {/* YouTube, Facebook feed, website */}
    <Composition id="PromoWide" component={Promo} durationInFrames={30 * FPS} fps={FPS} width={1920} height={1080} defaultProps={defaultProps} />
  </>
);
