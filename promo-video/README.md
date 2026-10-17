# Promo video (Remotion)

A 30-second, Apple-style light promo for the store platform, with Bangla on-screen text.
Cuts land on every bar of a 120 BPM track, following the beat map in the script.

| Composition | Size | Use it for |
|---|---|---|
| `Promo` | 1080×1920 (9:16) | Reels, Shorts, TikTok, Stories |
| `PromoWide` | 1920×1080 (16:9) | YouTube, Facebook feed, website |

## Commands

```bash
cd promo-video
npm install
npm run studio        # preview and tweak in the browser
npm run render        # out/promo-9x16.mp4
npm run render:wide   # out/promo-16x9.mp4
```

`npm run music` regenerates `public/music.wav`, an original track synthesised by
`scripts/make-music.mjs` (build → drop at 0:04 → lift at 0:26 → final hit at 0:28),
so there are no licensing issues. To use a licensed track instead, replace `public/music.wav`
with a 30-second 120 BPM file and keep the drop at 0:04.

## Changing the end card

The phone number and website are props. In the Studio, edit them in the right-hand panel,
or pass them on the command line:

```bash
npx remotion render Promo out/promo-9x16.mp4 --props='{"phone":"০১৭৭৫-০৭৫৫৪৩","website":"vantbd.com","voiceover":false}'
```

## Adding the Bangla voiceover

Record the VO from the script, save it as `public/voiceover.mp3` (starting at 0:00), and render
with `"voiceover": true`. The music then drops to 55% so the voice sits on top.

## Screens

`public/screens/` holds real screenshots of the store and admin panel, taken from a local copy
seeded with the demo data. Retake them after design changes and the video picks them up as-is,
as long as the file names stay the same.

## Scenes

| Time | Scene | File |
|---|---|---|
| 0:00 | ব্যবসা এখন অনলাইনে। | `Intro` |
| 0:04 | Dashboard on a laptop (the drop) | `Platform` |
| 0:06 | Storefront on two phones | `Mobile` |
| 0:08 | এক ট্যাপে অর্ডার — tap on Place Order | `OneTap` |
| 0:10 | Payment methods, one per beat | `Payments` |
| 0:12 | Delivery-history card lifts out of the order page | `Verify` |
| 0:14 | Steadfast, Pathao, RedX light up | `Courier` |
| 0:16 | Scan beam over a parcel label | `Scan` |
| 0:18 | POS sale updates website stock | `OneStock` |
| 0:20 | Net profit counts up | `Profit` |
| 0:22 | Feature tiles, one per beat | `Features` |
| 0:26 | Tiles fold into the logo | `Collapse` |
| 0:28 | Logo, tagline, contact | `EndCard` |

All scenes live in `src/scenes.tsx`; shared pieces (devices, glass cards, the word-by-word
headline) are in `src/ui.tsx`; colours, timing and easing are in `src/theme.ts`.
