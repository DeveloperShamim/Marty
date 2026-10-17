# Admin panel tutorial video (CodeFlow → After Effects)

A 13½-minute Bangla tutorial, built as an HTML motion video that the **CodeFlow** panel turns into
editable After Effects layers. The style follows the reference video: laptop and phone mockups,
a browser showing the real admin panel, an animated pointer, camera zooms and word-by-word
subtitles, in Vant's burgundy with the Vant logo.

## Watch it
Double-click `VantAdminTutorial.html` (Chrome or Edge). It plays by itself.

## Build it in After Effects (script, no plugin needed)
1. In After Effects: **Preferences → Type → South Asian and Middle Eastern** (then restart AE once), so Bangla letters join.
2. Install the fonts **Hind Siliguri** (Medium, SemiBold, Bold) and **Inter** (Medium, SemiBold) from Google Fonts.
3. **File → Scripts → Run Script File…** and choose `VantAdminTutorial.jsx` (keep it next to the `assets` folder).

It creates a folder "Vant Admin Tutorial" with the main comp and 4 precomps (laptop display, browser window,
browser viewport, phone screen), all native layers: the camera moves are keyframes on the null
"CAMERA (move me)", the pointer is `assets/cursor.png`, typing is a typewriter expression with a blinking caret,
and the subtitles are one text layer whose words light up through a text animator.

To regenerate the script after changing the video: `npm install`, then `npm run ae`
(`node build.mjs` + `node ae/export.mjs`; `node ae/test-jsx.mjs` dry-runs the result).

## Or convert it with CodeFlow
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
