# Showreel tools

The earlier Python-composited reel (`capture.js`, `compose.py`, `mix.py`) has been replaced by the HyperFrames reel below; those scripts are kept for reference.

1. `capture.js` (desktop at 2× pixel density, scene at full resolution) drives the real site (Apache on `localhost:8080`, `?rsforce`) in headless Chromium on a virtual clock, one frame at a time: `node capture.js phone phone/` and `node capture.js desktop desk/`. It records the scripted touch and mouse positions next to the frames.
2. `compose.py` builds the vertical frames: the desktop browser window on top and the phone below, both whole and always visible, the leading one larger and brighter; then the end card. It adds the cursor, touch ripples and captions. It needs `anybody.ttf` and `atkinson.ttf` next to it (convert the site's WOFF2 files with fontTools). Run `python3 compose.py comp/`.
3. `mix.py` places sound effects on the beat grid: `SFX_DIR=sfx python3 mix.py sfx-mix.wav`.
4. Encode: `ffmpeg -framerate 30 -i comp/%04d.png -i sfx-mix.wav -c:v libx264 -profile:v high -tune animation -crf 6 -maxrate 40M -bufsize 80M -pix_fmt yuv420p -c:a aac -b:a 320k -af loudnorm=I=-14:TP=-1.5 -movflags +faststart -shortest out.mp4`

## Sound effects

From Mixkit (mixkit.co), under the Mixkit Sound Effects Free License: allowed in social media posts and commercial projects, no attribution required; the files themselves may not be redistributed on their own, so they are not in this repository. Download them by id from `https://assets.mixkit.co/active_storage/sfx/<id>/<id>-preview.mp3` into `sfx/`, convert to 48 kHz WAV, and name them as in `mix.py`:

1490 whoosh-fast · 1492 whoosh-cine · 1491 whoosh-arrow · 166 sweep-small · 787 whoosh-stutter · 2596 whoosh-electric · 1143 impact-deep · 788 impact-big · 772 impact-zoom · 2655 impact-blow · 498 impact-heartbeat · 2295 bass-pulse · 2297 bass-rumble · 1486 whoosh-tunnel · 275 click-double · 1117 click-classic · 1433 shutter

## Music

No music is in the file. Add the track inside TikTok or Instagram from their licensed music library, so it is cleared for the platform.

## HyperFrames reel (`media/ramyar-salah-reel.mp4`)

`hyperframes/index.html` is the HyperFrames composition (1080×1920, 60 fps, 12 bars = 20.6 s at 140 BPM): every cut and entrance lands on the Mist grid; text never pulses. Start Mist on its first downbeat at 0:00 in the app.
Its `media/` folder is not committed (large). Rebuild it from the captures: `bake.py` draws the fingertip/cursor
into the phone and desktop frames, then ffmpeg encodes `phone.mp4`, `desktop.mp4`, `scene.mp4`; copy the two fonts,
`gsap.min.js` (from the gsap npm package) and the Mixkit WAVs next to it. Render:
`npx hyperframes@0.9.77 render -f 60 -q delivery --video-bitrate 16M --video-frame-format png -o reel.mp4`.
