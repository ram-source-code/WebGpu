---
name: Ramyar Salah
description: One night of Newroz on a Zagros ridge, three inks, scroll is the hour, a torch procession simulated on the visitor's GPU.
colors:
  night: "oklch(0.2 0.055 272)"
  dusk: "oklch(0.29 0.075 272)"
  ink: "oklch(0.22 0.06 272)"
  ink-soft: "oklch(0.4 0.06 272)"
  haze: "oklch(0.82 0.035 272)"
  field-border: "oklch(0.5 0.05 272)"
  ember: "oklch(0.72 0.16 52)"
  ember-deep: "oklch(0.52 0.17 38)"
  ember-muted: "oklch(0.3 0.08 40)"
  saffron: "oklch(0.87 0.15 85)"
  limestone: "oklch(0.95 0.02 85)"
  limestone-2: "oklch(0.9 0.03 80)"
  field-paper: "oklch(0.99 0.008 85)"
  error: "oklch(0.48 0.19 28)"
typography:
  display:
    fontFamily: "Anybody, Arial Narrow, Roboto Condensed, system-ui, sans-serif"
    fontSize: "clamp(3.4rem, 1.6rem + 8.6vw, 6rem)"
    fontWeight: 820
    lineHeight: 0.86
    letterSpacing: "-0.01em"
    fontVariation: "'wdth' 62"
  display-band:
    fontFamily: "Anybody, Arial Narrow, Roboto Condensed, system-ui, sans-serif"
    fontSize: "clamp(2.9rem, 1.6rem + 6vw, 6rem)"
    fontWeight: 820
    lineHeight: 0.9
    fontVariation: "'wdth' 64"
  headline:
    fontFamily: "Anybody, Arial Narrow, Roboto Condensed, system-ui, sans-serif"
    fontSize: "clamp(2.3rem, 1.4rem + 3.6vw, 4rem)"
    fontWeight: 780
    lineHeight: 0.95
    letterSpacing: "-0.005em"
    fontVariation: "'wdth' 70"
  title:
    fontFamily: "Anybody, Arial Narrow, Roboto Condensed, system-ui, sans-serif"
    fontSize: "clamp(1.75rem, 1.2rem + 2.2vw, 2.9rem)"
    fontWeight: 760
    lineHeight: 1
    fontVariation: "'wdth' 70"
  subtitle:
    fontFamily: "Anybody, Arial Narrow, Roboto Condensed, system-ui, sans-serif"
    fontSize: "clamp(1.5rem, 1.2rem + 1vw, 2rem)"
    fontWeight: 760
    lineHeight: 1.05
    fontVariation: "'wdth' 75"
  mark:
    fontFamily: "Anybody, Arial Narrow, Roboto Condensed, system-ui, sans-serif"
    fontSize: "1.2rem"
    fontWeight: 760
    lineHeight: 1
    fontVariation: "'wdth' 72"
  caption:
    fontFamily: "Anybody, Arial Narrow, Roboto Condensed, system-ui, sans-serif"
    fontSize: "1.15rem"
    fontWeight: 700
    lineHeight: 1.25
    fontVariation: "'wdth' 75"
  lede:
    fontFamily: "Atkinson Hyperlegible Next, system-ui, -apple-system, Segoe UI, sans-serif"
    fontSize: "clamp(1.15rem, 1rem + 0.6vw, 1.4rem)"
    fontWeight: 400
    lineHeight: 1.4
  body:
    fontFamily: "Atkinson Hyperlegible Next, system-ui, -apple-system, Segoe UI, sans-serif"
    fontSize: "1.0625rem"
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: "Atkinson Hyperlegible Next, system-ui, -apple-system, Segoe UI, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 700
    lineHeight: 1.6
    fontFeature: "'tnum' 1"
rounded:
  focus: "2px"
  slot: "0.3em"
  block: "0.5rem"
  field: "0.55rem"
  panel: "0.6rem"
  pill: "999px"
spacing:
  gutter: "max(1rem, 4vw)"
  ridge: "clamp(36px, 6vw, 96px)"
  ground: "clamp(3.5rem, 9vw, 7.5rem)"
  wrap: "68rem"
  prose: "38rem"
  stack-sm: "0.75rem"
  stack-md: "1.25rem"
  stack-lg: "2.5rem"
components:
  button-fire:
    backgroundColor: "{colors.ember}"
    textColor: "{colors.night}"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: "0 1.35rem"
    height: "48px"
  button-fire-hover:
    backgroundColor: "{colors.saffron}"
    textColor: "{colors.night}"
  button-ink:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.limestone}"
    rounded: "{rounded.pill}"
    padding: "0 1.35rem"
    height: "48px"
  button-ink-hover:
    backgroundColor: "{colors.dusk}"
    textColor: "{colors.limestone}"
  button-line:
    textColor: "{colors.ink}"
    rounded: "{rounded.pill}"
    padding: "0 1.35rem"
    height: "48px"
  scene-toggle:
    textColor: "{colors.limestone}"
    rounded: "{rounded.pill}"
    padding: "0 1rem"
    height: "44px"
  field-input:
    backgroundColor: "{colors.field-paper}"
    textColor: "{colors.ink}"
    typography: "{typography.body}"
    rounded: "{rounded.field}"
    padding: "0.7rem 0.9rem"
    height: "48px"
  climb-link:
    textColor: "{colors.limestone}"
    typography: "{typography.title}"
    padding: "1.6rem 3rem 1.6rem 1.6rem"
  client-slot:
    typography: "{typography.label}"
    rounded: "{rounded.slot}"
    padding: "0.3em 0.65em"
  window-caption:
    backgroundColor: "{colors.night}"
    textColor: "{colors.limestone}"
    typography: "{typography.caption}"
    rounded: "{rounded.block}"
    padding: "0.5rem 0.85rem"
---

# Design System: Ramyar Salah

## Overview

**Creative North Star: "One Night on the Ridge"**

The whole site is a single night of Newroz on a Zagros ridge. A live GPU scene of torches climbing switchbacks to a summit fire sits behind the page, and the page itself is the night passing: dusk indigo at the top, deep night through the work, an ember-lit slope for the story, limestone dawn at the close. Each section is drenched in its hour's light; nothing sits on a neutral grey panel, and nothing is black.

The system is printed in three inks, mixed like overprints: an indigo you can name, a torch ember with a saffron core, and limestone. Type is tall and condensed like flames for display (Anybody, narrowed through its width axis) and plainly legible for reading (Atkinson Hyperlegible Next). Where a ground opens onto the scene it ends in a drawn mountain profile, not a box edge. Density is low and the copy is short: the scene carries the proof, and type carries the content, with no stock imagery.

The scene is an enhancement over a finished page. WebGPU draws it where it can, WebGL2 draws it where it cannot, and a still poster of the same ridge stands in everywhere else, including software GPUs, data saver, JavaScript off and reduced motion. Every tier is meant to look intended.

**Key Characteristics:**
- Three inks only: indigo (hue 272), ember to saffron (hues 38 to 85), limestone (hue 80 to 85). Every neutral is tinted by one of them.
- Grounds change with the hour; each carries its own token set for background, text, muted, accent, rule line and focus.
- Display in Anybody at 62 to 75% width and weights 700 to 820; reading in Atkinson Hyperlegible Next at 17px with 1.6 leading.
- Ridgeline edges from one `ridge.svg` mask, top and mirrored bottom.
- Scroll is the hour: page progress drives the scene's time of night and an altitude rail.
- One accent action per view: the ember pill on night, the ink pill on dawn.

## Colors

Three inks overprinted on each other; light is additive, like torchlight on stone.

### Primary
- **Torch Ember** (`ember`): the fire. The primary action button on night grounds, the ground of the about section, the torch marker on each work entry, the rail's mark, selection, caret and scrollbar thumb.
- **Saffron Core** (`saffron`): the hot centre of the flame. Accent text and focus rings on night grounds, the fire button's hover, the lit work marker and title on hover, the rail mark at first light.
- **Deep Ember** (`ember-deep`): ember darkened for limestone. The accent on the dawn ground, where ember itself would not hold contrast.
- **Banked Ember** (`ember-muted`): the muted text colour on the ember ground.

### Secondary
- **Dusk Indigo** (`dusk`): the evening sky. The ink button's hover and the work entry's hover wash (at 50% alpha).
- **Night Indigo** (`night`): the deepest hour, never black. Default page and scene background, the night ground, every scrim over the scene (hero gradient at 55%, scene bar at 70%, band gradients at 50 to 85%, caption plate at 62%, see-through ground at 86%), the fire button's text.
- **Ink** (`ink`): indigo as text. Body text on ember and dawn grounds, the ink button, focus on light grounds, field text.

### Neutral
- **Limestone** (`limestone`): text on every indigo ground and over the scene; the dawn ground itself.
- **Worn Limestone** (`limestone-2`): the footer, one step down from dawn.
- **Field Paper** (`field-paper`): the inside of form fields on dawn, a lighter sheet of the same stone.
- **Haze** (`haze`): muted text on night and the scene bar; at 28% alpha it is the night ground's rule line, at 50% the scene toggle's border.
- **Soft Ink** (`ink-soft`): muted text on dawn and the footer's legal line.
- **Field Border** (`field-border`): the resting stroke of form fields.

### Functional
- **Error Red** (`error`): form errors only: the invalid field's 2px stroke, the error line under it, and the stroke and links of the error summary (on an `oklch(0.97 0.02 40)` sheet). It sits next to the ember hues and appears nowhere else.

### Ground token sets
Each ground redefines six local tokens; components read only these, never the raw inks.

| Ground | `--bg` | `--fg` | `--muted` | `--accent` | `--line` | `--focus` |
|---|---|---|---|---|---|---|
| Night (default) | night | limestone | haze | saffron | haze / 0.28 | saffron |
| Ember (`color-scheme: light`) | ember | ink | ember-muted | ink | ink / 0.3 | ink |
| Dawn (`color-scheme: light`) | limestone | ink | ink-soft | ember-deep | ink / 0.18 | ink |
| See-through night | night / 0.8 | as night | as night | as night | as night | as night |

The see-through night is the work list's ground: it lets the procession show faintly behind the entries, so hovering an entry visibly lights its stretch of the road.

### Named Rules
**The Three Inks Rule.** Indigo, ember-to-saffron and limestone are the only hues. A new colour is a new mix of these (an alpha, a lightness step on the same hue), never a fourth hue. Error red is the single functional exception.

**The Never Black Rule.** The darkest colour is night indigo at L 0.2 with chroma 0.055. Shadows and scrims are indigo at an alpha, not black or grey.

**The Hour Ground Rule.** A section takes the ground of its hour and uses that ground's six tokens. Never place a neutral panel on a ground; change the ground instead.

## Typography

**Display Font:** Anybody (variable, width 50 to 150%, weight 100 to 900), falling back to Arial Narrow, Roboto Condensed, system-ui
**Body Font:** Atkinson Hyperlegible Next (variable, weight 200 to 800), falling back to system-ui, -apple-system, Segoe UI
Both are self-hosted Latin subsets, preloaded, `font-display: swap`; the body uses `font-size-adjust: from-font`.

**Character:** Anybody is narrowed with `font-stretch` into tall, heavy columns that read like flames standing on the ridge; Atkinson is a calm, open reading face that holds up on a small phone over a moving scene.

### Hierarchy
- **Display** (820, 62% width, clamp(3.4rem, 1.6rem + 8.6vw, 6rem), 0.86): the name in the home hero, one word per line on phones.
- **Display band** (820, 64% width, clamp(2.9rem, 1.6rem + 6vw, 6rem), 0.9): inner-page titles over the scene band and plain page heads. The dawn close uses the same voice at clamp(2.8rem, 1.4rem + 6vw, 6rem), capped at 12ch; error titles at clamp(2.6rem, 1.6rem + 5vw, 5rem).
- **Headline** (780, 70% width, clamp(2.3rem, 1.4rem + 3.6vw, 4rem), 0.95): section titles on grounds.
- **Title** (760, 70% width, clamp(1.75rem, 1.2rem + 2.2vw, 2.9rem), 1): work entry names. The next-project link runs larger at 800, 66% width, clamp(2rem, 1.3rem + 3.4vw, 3.8rem).
- **Subtitle** (760, 75% width, clamp(1.5rem, 1.2rem + 1vw, 2rem), 1.05): headings inside prose; the footer ask (1.5rem) and fallback-tier names (1.2rem, saffron) use the same 75% voice.
- **Mark and caption** (760 at 72% width, 1.2rem; 700 at 75% width, 1.15rem): the header name mark and the captions in the scene windows.
- **Lede** (400, clamp(1.15rem, 1rem + 0.6vw, 1.4rem), 1.4, max 26ch in the hero; bands and close at about 1.1 to 1.35rem, 40 to 44ch).
- **Body** (400, 1.0625rem, 1.6): reading text, prose capped at 38rem; section intros at 1.125rem, 52ch, muted.
- **Label** (600 to 700, 0.875rem, tabular figures): the road position on work entries, the scene bar's status, meta and stack lines (0.9 to 0.95rem, muted). Buttons and nav links are Atkinson 600 to 700 at body size.

### Named Rules
**The Flame Width Rule.** Display type is always Anybody narrowed between 62% and 75% width, heavier as it gets narrower (820 at 62 to 64%, 760 to 780 at 70 to 75%). Never set Anybody at 100% width, and never set reading text in it.

**The Reading Face Rule.** Everything a visitor reads in sentences, every label and every control is Atkinson Hyperlegible Next. No uppercase-tracked labels.

## Layout

The page is a vertical climb. On home, a fixed full-bleed scene (`z-index: -1`) sits behind everything; the hero fills `100svh` with the name upper-left and the scene bar at its foot; then grounds alternate with **windows**, transparent gaps (50svh, 42svh before dawn, plus the ridge height) where the scene shows through with a short caption naming the hour. Inner pages open with a **band**: the scene at a fixed hour in a strip of clamp(24rem, 64svh, 42rem), the title set at its foot over an indigo gradient, then plain grounds.

- **Gutter:** max(1rem, 4vw) on every edge; content wraps at min(100% - 2 gutters, 68rem).
- **Ground rhythm:** clamp(3.5rem, 9vw, 7.5rem) block padding per ground; ridge height clamp(36px, 6vw, 96px).
- **Split:** one column below 56rem; from 56rem a 5fr / 7fr grid with 4rem column gap, title or facts on the left (the case-study facts stick at 1.5rem).
- **Measures:** prose 38rem (36rem narrow), intros 52ch, ledes 26 to 44ch, form 40rem.
- **Breakpoints:** 48rem (footer goes two-column), 56rem (split), 80rem (altitude rail shows its hour labels).
- **Header:** absolute over the scene, name mark left, three links right, wrapping on narrow phones.
- **Touch:** every control is at least 44px tall (buttons and fields 48px).

## Elevation & Depth

Flat. Depth comes from the scene behind the page and from translucent indigo laid over it, not from lifted surfaces. Scrims are gradients of night indigo (hero top at 55%, scene bar from 70%, band top 85% to 50%, band foot to 85%); captions and the scene toggle sit on night-indigo plates at 55 to 85%. The see-through ground lets the scene glow through at 14%.

### Shadow Vocabulary
- **Fire glow** (`box-shadow: 0 0.35rem 0.9rem -0.35rem oklch(0.1 0.04 272 / 0.7)`): under the ember button only, to lift it off a bright patch of scene.
- **Mark lift** (`box-shadow: 0 1px 3px oklch(0.1 0.04 272 / 0.6)`): under the altitude rail's dot.

### Named Rules
**The Lit From Behind Rule.** Surfaces do not float. If something needs separation from the scene, lay indigo under it at an alpha; reserve shadows for the two cases above.

## Shapes

Mountain edges and soft-round controls. Where a ground meets the scene (home's work, about and dawn sections) its edge is a ridgeline: `ridge.svg` (a 1440 by 100 profile, stretched with `preserveAspectRatio="none"`) masks a pseudo-element in the ground's own colour, rising above the top edge and mirrored (`scale: 1 -1`) below the bottom, overlapping by 1px so no seam shows. Inside a ground, structure is drawn with 1px rule lines in `--line`, not boxes.

- Actions and the scene toggle are full pills (999px).
- Fields have gently rounded corners (0.55rem); the error summary, image slots and portrait slot 0.6rem; the window caption and client blocks 0.5rem; inline client slots 0.3em; focus outlines 2px.
- The work-entry marker is a small teardrop flame: a 0.6rem square with three rounded corners, rotated -45deg.
- Icons are inline SVG line icons from one sprite (arrow, external, copy, pause, play), 1.15em, stroke 1.75, round caps and joins.

## Components

### Buttons
Firm, warm pills; one carries the page's action.
- **Shape:** full pill (999px), 48px tall, 0 1.35rem padding, 1.5px transparent border, Atkinson 700, icon gap 0.55rem.
- **Fire:** ember on night text, with the fire glow. The primary action wherever the ground is indigo or over the scene ("Get in touch"). Hover turns it saffron.
- **Ink:** ink with limestone text. The primary action on the dawn ground and the form's submit. Hover turns it dusk.
- **Line:** transparent with a currentColor border, inheriting the ground's text colour; hover adds a faint ink wash (ink at 8%). Secondary actions such as copying the email address.
- **States:** a trailing arrow icon slides 0.2rem on hover (0.25s ease-out); press nudges the button down 1px; disabled or sending sits at 60% opacity with a progress cursor. Focus is the global 3px outline in the ground's focus colour, offset 3px.
- **Quiet links** beside a button are plain underlined text (600), and arrow links (700) carry the same sliding arrow.

### Navigation
- Header: name mark in Anybody (760, 72%) left; Work, About, Contact right in Atkinson 600 with a 2px bottom rule: transparent at rest, limestone at 50% on hover, ember for the current page.
- Footer: worn limestone ground, ink text, the ask in Anybody 75% at 1.5rem, GitHub and Privacy links, a legal line in soft ink.

### The Climb (work list)
Work entries are fires on the road up the ridge.
- An ordered list ruled top and bottom by `--line`; each entry is one large link with 1.6rem padding (3rem at the inline end for the arrow).
- Content: the road position (label, accent colour, tabular figures, "N% up the road"), the title (Title style), then year and role, a summary up to 36rem, and the stack, all muted.
- The position is computed, not written: entry i of n sits at (i + 1) / (n + 1) of the road, and the same number goes to the scene, which lights that stretch of the procession on hover or focus.
- Hover and focus: a dusk wash at 50%, the flame marker and title turn saffron, the masked arrow slides 0.3rem and goes fully opaque.
- Placeholder entries keep their place on the road: the title dims to 75%, the marker becomes a dashed ember outline, and a client block stands in for the content.

### Scene band and scene bar
- **Band:** the scene at a fixed hour behind an inner page's title; posters per hour (dusk, night, dawn) as AVIF and WebP at 960 and 1920.
- **Scene bar:** under every scene, the status line (muted, 0.875rem, tabular, max 46ch, `aria-live`) that names the renderer and the torch count when live ("WebGPU · 14,336 torches simulated on your device") or says why a still is showing. A pause/play pill (44px, night plate at 55%, haze border at 50%, limestone text; hover deepens to 85% with a saffron border) appears only when a renderer is running, and its choice is remembered.
- **Hour slider:** on the scene's own case study, a range input (0 to 1, ember accent, 44px, up to 14rem) labelled "Hour" scrubs the night; its value text reads Dusk, Evening, Midnight, Before dawn or First light.

### Inputs / Fields
- **Style:** field-paper inside, 1.5px field-border stroke, 0.55rem corners, 48px minimum (textarea 10rem, vertical resize), 0.7rem 0.9rem padding, ink text, 16px or larger; labels above in Atkinson 700, 0.35rem gap, fields 1.4rem apart.
- **Hover:** stroke goes to ink. **Focus:** 3px ink outline, 2px offset.
- **Error:** after interaction (`:user-invalid` or `aria-invalid`), a 2px error-red stroke and a 600-weight error line beneath it naming the fix. A server-returned form opens on an error summary (2px error stroke, pale ember sheet, links to each field) that takes focus.

### Client placeholders
Content only the owner can supply is marked, never invented.
- **Slot:** inline `[CLIENT: what to write]` in Atkinson 500, at most 1.15rem, in a 1px dashed currentColor outline, 0.3em corners, wrapping cleanly across lines.
- **Block:** the same dashed outline around several labelled lines (0.9rem, labels 700), up to 36rem wide, 0.5rem corners.
- **Image slots:** 1.5px dashed outlines holding the aspect ratio (16:9 shots, 4:5 portrait) with the slot centred.

### Altitude rail
The one clocked "now" on the page: where you are in the night.
- A fixed 1px limestone line at the inline end, 40vh tall, vertically centred, decorative (`aria-hidden`), blended with `difference` so it reads on any hour.
- An ember dot (9px) climbs it as the page scrolls, turning saffron at first light; from 80rem it shows Dusk, Midnight and First light labels (0.75rem).
- Driven by a scroll timeline where supported and motion is allowed; otherwise the dot rests at dusk.

### The scene
- **Ladder:** WebGPU (compute simulation and additive render) first; WebGL2 (motion in the vertex shader) next; otherwise the still poster stays. A software adapter (WebGPU fallback adapter, or a WebGL renderer named SwiftShader, llvmpipe, softpipe, software or Basic Render) keeps the still. Save-Data, `?still` and JavaScript off never load the scene at all.
- **Counts:** WebGPU 14,336 torches, 6,144 embers and 1,536 far-bonfire flames on desktop (6,144 / 2,048 / 768 on phones); WebGL2 10,240 / 3,072 / 1,536 (4,096 / 1,024 / 768).
- **Palette (display sRGB, shared by both renderers):** sky top dusk (0.11, 0.13, 0.34), night (0.035, 0.045, 0.14), dawn (0.43, 0.53, 0.74); horizon dusk (0.82, 0.40, 0.24), night (0.11, 0.12, 0.27), dawn (0.98, 0.87, 0.70); flame ember (1.0, 0.43, 0.12) with saffron core (1.0, 0.83, 0.40). The hour blends dusk to night over 0 to 0.5 and night to dawn over 0.5 to 1, with a west afterglow at dusk and an east glow at dawn; fires dim toward dawn. Flames are blended additively (one, one): light overprints light.
- **Behaviour:** the pointer or a finger is a torch; walkers within reach turn toward it, harder while pressed. The canvas fades in over the poster (0.9s) once the first frame is drawn. It starts after page load and idle; it renders only while on screen and visible. If frames run long it lowers resolution (to 55%), then halves the torches, then, under about 20fps, stops and returns to the still with a status line saying so. A sent contact message flares the summit fire.

## Do's and Don'ts

### Do:
- **Do** read colour only through a ground's six tokens (`--bg`, `--fg`, `--muted`, `--accent`, `--line`, `--focus`) and pick the ground by the hour: night, ember, dawn, or see-through night over the scene.
- **Do** end a ground with the `ridge.svg` profile wherever it opens onto the scene, in the ground's own colour, mirrored at the bottom.
- **Do** set display type in Anybody at 62 to 75% width and 700 to 820 weight, and everything read or operated in Atkinson Hyperlegible Next.
- **Do** keep one primary action per view: ember fire pill on indigo or over the scene, ink pill on dawn; secondary actions are line pills or plain links.
- **Do** keep every control at least 44px tall and give focus a 3px outline in the ground's focus colour.
- **Do** mark missing owner content with a dashed `[CLIENT: …]` slot or block, in place, at its real position.
- **Do** keep the page complete without the scene: posters for every hour, a status line that says which tier is showing, and a pause control whenever anything moves.
- **Do** honour reduced motion: the scene starts paused (unless the visitor chose otherwise), parallax and pointer pull stop, the rail and page transitions stay still.

### Don't:
- **Don't** introduce a fourth hue, a neutral grey, or black; mix a new value from indigo, ember-saffron or limestone instead. Error red stays on form errors.
- **Don't** lift surfaces with shadows beyond the fire glow and the rail dot; separate things from the scene with indigo scrims.
- **Don't** set Anybody at normal width or use it for body text, labels or controls; don't add uppercase-tracked labels.
- **Don't** invent clients, projects, numbers or photos; a placeholder slot stays visible until the owner fills it.
- **Don't** run the scene on a software GPU, with Save-Data on, or below about 20fps; keep the still.
- **Don't** add stock imagery; the scene, the posters and type carry the pages.
