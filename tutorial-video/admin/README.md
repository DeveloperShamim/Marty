# Admin panel tutorial video (CodeFlow → After Effects)

A 13½-minute Bangla tutorial, built as an HTML motion video that the **CodeFlow** panel turns into
editable After Effects layers. The style follows the reference video: laptop and phone mockups,
a browser showing the real admin panel, an animated pointer, camera zooms and word-by-word
subtitles, in Vant's burgundy with the Vant logo.

## Watch it
Double-click `VantAdminTutorial.html` (Chrome or Edge). It plays by itself.

## Convert it in After Effects
1. **Window → Extensions → CodeFlow**
2. Drag `VantAdminTutorial.html` onto the panel (or **Open…**), wait for it to read the frames, then **Build**.
3. CodeFlow needs Chrome or Edge installed and an internet connection (Google Fonts, GSAP).

Comes in as pictures (no editable source): the admin screenshots in `assets/screens/` and the logo PNGs.
Everything else is native: the subtitles, notifications, pointer, highlight rings, devices and camera moves.

## Voiceover
`assets/voiceover.mp3` is the ElevenLabs recording (11:17). It is quicker than the original script timing, so
`voice-timing.json` holds each line's time in the recording, and `admin-tutorial-vo.srt` is the matching subtitle
file. The build uses these automatically; every step inside a line is scaled to the line's new length.

The recording leaves out script lines 167–173 (Profit & Analytics, Export CSV, Expenses), so the video skips
those screens and goes from Abandoned Carts straight to "এই খরচগুলো…" on the Expenses page.

To use a new recording: replace `assets/voiceover.mp3`, update `voice-timing.json` (start/end per script line,
`skip` for lines that aren't spoken), and run `node build.mjs`.

## Change things
| What | Where |
|---|---|
| Subtitle text and timing | `admin-tutorial.srt` |
| What happens at each line (pointer, zoom, highlights, pop-ups) | `src/scenes.js`, one block per section |
| Look of devices, subtitles, colours, end card | `src/template.html` |
| Screens and button positions | `assets/screens/*.jpg` + `screens.json` |

After any change run `node build.mjs` to rebuild `VantAdminTutorial.html`.
