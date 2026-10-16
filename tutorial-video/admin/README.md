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

## Add the voiceover
Put the recording in `assets/voiceover.mp3` (starting at 0:00, matching `admin-tutorial.srt`), then run
`node build.mjs`. The audio is added to the page automatically.

## Change things
| What | Where |
|---|---|
| Subtitle text and timing | `admin-tutorial.srt` |
| What happens at each line (pointer, zoom, highlights, pop-ups) | `src/scenes.js`, one block per section |
| Look of devices, subtitles, colours, end card | `src/template.html` |
| Screens and button positions | `assets/screens/*.jpg` + `screens.json` |

After any change run `node build.mjs` to rebuild `VantAdminTutorial.html`.
