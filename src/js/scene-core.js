// Newroz on the ridge: one procedural Zagros ridge, a torch procession climbing its switchbacks
// to a summit fire, rendered with WebGPU (compute + render) or WebGL2, else the still poster stays.
// Scroll is the night passing: dusk at the top of the page, first light at the bottom.

const TAU = Math.PI * 2;
export const WORLD = { X0: -70, Z0: -95, SX: 140, SZ: 142, SUMMIT: [8, 0, -26], LANES: 3 };

/* ---------- procedural terrain (CPU, baked once; both renderers sample the same heightmap) ---------- */

function hash(x, y) {
  let h = (x * 374761393 + y * 668265263) | 0;
  h = Math.imul(h ^ (h >>> 13), 1274126177);
  return ((h ^ (h >>> 16)) >>> 0) / 4294967296;
}
function vnoise(x, y) {
  const xi = Math.floor(x), yi = Math.floor(y), xf = x - xi, yf = y - yi;
  const u = xf * xf * (3 - 2 * xf), v = yf * yf * (3 - 2 * yf);
  const a = hash(xi, yi), b = hash(xi + 1, yi), c = hash(xi, yi + 1), d = hash(xi + 1, yi + 1);
  return a + (b - a) * u + (c - a) * v + (a - b - c + d) * u * v;
}
function fbm(x, y, oct) {
  let s = 0, a = 0.5, f = 1;
  for (let i = 0; i < oct; i++) { s += a * vnoise(x * f, y * f); f *= 2.03; a *= 0.5; }
  return s;
}
function ridged(x, y, oct) {
  let s = 0, a = 0.5, f = 1, w = 1;
  for (let i = 0; i < oct; i++) {
    let n = 1 - Math.abs(vnoise(x * f, y * f) * 2 - 1);
    n *= n * w; w = Math.min(1, n * 2); s += n * a; f *= 2.1; a *= 0.5;
  }
  return s;
}
export function terrainHeight(x, z) {
  const dx = x - 8, dz = z + 26;
  const massif = 19 * Math.exp(-(dx * dx) / 450 - (dz * dz) / 288);
  const rz = z + 52 + x * 0.22;
  const range = 15 * Math.exp(-(rz * rz) / 242) * (0.55 + 0.6 * fbm(x * 0.03 + 7.3, 1.7, 3));
  const fz = z + 85;
  const far = 11 * Math.exp(-(fz * fz) / 288) * (0.6 + 0.6 * fbm(x * 0.02 - 3.1, 9.2, 3));
  let h = Math.max(massif, range, far) + 0.2 * Math.min(massif, range);
  h += -1.5 + 3.0 * fbm(x * 0.04, z * 0.04, 4);
  h += ridged(x * 0.07 + 1.3, z * 0.07 - 4.1, 5) * (1.3 + 0.24 * Math.max(h, 0));
  return h;
}

/** Bake the heightmap in slices so start-up never blocks input for long. */
export async function bakeHeights(n) {
  const out = new Float32Array(n * n);
  const yieldNow = () => (globalThis.scheduler?.yield ? scheduler.yield() : new Promise((r) => setTimeout(r, 0)));
  let t0 = performance.now();
  for (let iz = 0; iz < n; iz++) {
    const z = WORLD.Z0 + (iz / (n - 1)) * WORLD.SZ;
    for (let ix = 0; ix < n; ix++) out[iz * n + ix] = terrainHeight(WORLD.X0 + (ix / (n - 1)) * WORLD.SX, z);
    if (performance.now() - t0 > 12) { await yieldNow(); t0 = performance.now(); }
  }
  return out;
}

export function sampleHeights(h, n, x, z) {
  const gx = ((x - WORLD.X0) / WORLD.SX) * (n - 1), gz = ((z - WORLD.Z0) / WORLD.SZ) * (n - 1);
  const ix = Math.max(0, Math.min(n - 2, Math.floor(gx))), iz = Math.max(0, Math.min(n - 2, Math.floor(gz)));
  const fx = Math.min(1, Math.max(0, gx - ix)), fz = Math.min(1, Math.max(0, gz - iz));
  const a = h[iz * n + ix], b = h[iz * n + ix + 1], c = h[(iz + 1) * n + ix], d = h[(iz + 1) * n + ix + 1];
  return (a + (b - a) * fx) * (1 - fz) + (c + (d - c) * fx) * fz;
}

/* ---------- small matrix kit (column-major) ---------- */

const sub = (a, b) => [a[0] - b[0], a[1] - b[1], a[2] - b[2]];
const cross = (a, b) => [a[1] * b[2] - a[2] * b[1], a[2] * b[0] - a[0] * b[2], a[0] * b[1] - a[1] * b[0]];
const norm = (a) => { const l = Math.hypot(a[0], a[1], a[2]) || 1; return [a[0] / l, a[1] / l, a[2] / l]; };
const dot = (a, b) => a[0] * b[0] + a[1] * b[1] + a[2] * b[2];
const mix = (a, b, t) => a + (b - a) * t;
const mix3 = (a, b, t) => [mix(a[0], b[0], t), mix(a[1], b[1], t), mix(a[2], b[2], t)];
const smooth = (t) => t * t * (3 - 2 * t);

function mul4(a, b, o = new Float32Array(16)) {
  for (let c = 0; c < 4; c++) for (let r = 0; r < 4; r++) {
    o[c * 4 + r] = a[r] * b[c * 4] + a[4 + r] * b[c * 4 + 1] + a[8 + r] * b[c * 4 + 2] + a[12 + r] * b[c * 4 + 3];
  }
  return o;
}

/* ---------- camera rig ---------- */

const VIEW = new Float32Array(16), PROJ = new Float32Array(16), VIEWPROJ = new Float32Array(16);

export function camera(state, w, h, zeroToOneDepth) {
  const p = smooth(Math.min(1, Math.max(0, state.progress)));
  const portrait = w < h;
  const eye = mix3(portrait ? [3, 5.5, 40] : [-3, 4.6, 31], portrait ? [6, 16, 12] : [3, 15, 6], p);
  const look = mix3(portrait ? [8, 19.5, -22] : [9, 9.5, -20], [8, 15.5, -26], p);
  // a little parallax from the pointer, damped in the loop
  eye[0] += state.parallax[0] * 1.6;
  eye[1] += state.parallax[1] * 0.6;
  const fovY = (portrait ? 62 : 46) * Math.PI / 180;
  const fwd = norm(sub(look, eye));
  const right = norm(cross(fwd, [0, 1, 0]));
  const up = cross(right, fwd);
  // reused every frame: no per-frame matrix garbage, so no collector pauses mid-scroll
  const view = VIEW, proj = PROJ;
  view.set([
    right[0], up[0], -fwd[0], 0,
    right[1], up[1], -fwd[1], 0,
    right[2], up[2], -fwd[2], 0,
    -dot(right, eye), -dot(up, eye), dot(fwd, eye), 1,
  ]);
  const near = 0.3, far = 260, f = 1 / Math.tan(fovY / 2), aspect = w / h;
  proj.fill(0);
  proj[0] = f / aspect; proj[5] = f; proj[11] = -1;
  proj[10] = zeroToOneDepth ? far / (near - far) : (far + near) / (near - far);
  proj[14] = zeroToOneDepth ? (near * far) / (near - far) : (2 * far * near) / (near - far);
  const tanY = Math.tan(fovY / 2);
  return { viewProj: mul4(proj, view, VIEWPROJ), eye, right, up, fwd, tanY, tanX: tanY * aspect, focal: (h * 0.5) / tanY };
}

/** Ray from the camera through a pointer position, marched onto the baked terrain. */
export function pickGround(cam, nx, ny, heights, n) {
  const dir = norm([
    cam.fwd[0] + nx * cam.tanX * cam.right[0] + ny * cam.tanY * cam.up[0],
    cam.fwd[1] + nx * cam.tanX * cam.right[1] + ny * cam.tanY * cam.up[1],
    cam.fwd[2] + nx * cam.tanX * cam.right[2] + ny * cam.tanY * cam.up[2],
  ]);
  let t = 0.5, prev = null;
  for (let i = 0; i < 96 && t < 220; i++) {
    const x = cam.eye[0] + dir[0] * t, y = cam.eye[1] + dir[1] * t, z = cam.eye[2] + dir[2] * t;
    const g = sampleHeights(heights, n, x, z);
    if (y < g) {
      if (!prev) return [x, g, z];
      return [mix(prev[0], x, 0.5), g, mix(prev[2], z, 0.5)];
    }
    prev = [x, y, z];
    t += 0.35 + t * 0.03;
  }
  return null;
}

/* ---------- boot ---------- */


export async function startScene(root) {
  let canvas = root.querySelector('canvas');
  const toggle = document.querySelector('[data-scene-toggle]');
  if (!canvas) return;

  const mode = root.dataset.scene || 'full';            // full = page-long, band = header strip
  let fixedHour = root.dataset.hour ? Number(root.dataset.hour) : null;
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  const coarse = matchMedia('(pointer: coarse)').matches;
  const small = Math.min(screen.width, screen.height) < 700;
  let paused = reduced.matches;
  try {
    const saved = localStorage.getItem('rs-scene-paused');
    if (saved !== null) paused = saved === '1';
  } catch { /* storage blocked: keep the motion preference */ }

  const state = {
    progress: mode === 'full' ? 0 : Number(root.dataset.progress || 0.35),
    progressTarget: mode === 'full' ? 0 : Number(root.dataset.progress || 0.35),
    hour: fixedHour ?? 0,
    parallax: [0, 0], parallaxTarget: [0, 0],
    pointer: [0, 0, 0, 0], pointerNdc: null, pointerDown: false,
    flare: 0, time: 0,
    focus: [0.5, 0], focusTarget: 0,
    quality: 1,
  };

  // the scene is an enhancement: let the page load and settle first
  if (document.readyState !== 'complete') await new Promise((r) => addEventListener('load', r, { once: true }));
  await new Promise((r) => (window.requestIdleCallback ? requestIdleCallback(r, { timeout: 1200 }) : setTimeout(r, 150)));

  let renderer = null;
  // software rasterisers (no real GPU) would make the page janky: keep the still image instead.
  // ?rsforce overrides this for testing in headless browsers.
  // test switches: ?rsforce draws even on a software renderer and never gives up; ?rssoft allows a
  // software renderer but keeps the slow-device guard
  const q = new URLSearchParams(location.search);
  const force = q.has('rsforce');
  const opts = { canvas, lowPower: coarse || small, state, allowSoftware: force || q.has('rssoft') };
  if ('gpu' in navigator) {
    try {
      const m = await import('rs/a');
      renderer = await m.createRenderer(opts);
    } catch {
      // a canvas that already holds a WebGPU context can never give out a WebGL2 one
      const fresh = canvas.cloneNode(false);
      canvas.replaceWith(fresh);
      canvas = opts.canvas = fresh;
    }
  }
  if (!renderer) {
    try {
      const m = await import('rs/b');
      renderer = await m.createRenderer(opts);
    } catch { /* keep the still image */ }
  }
  if (!renderer) { if (toggle) toggle.hidden = true; return; }
  if (toggle) toggle.hidden = false;

  /* sizing */
  let cssW = 1, cssH = 1;
  const resize = () => {
    const r = canvas.getBoundingClientRect();
    // the phone address bar showing or hiding changes the height while you scroll: keep the
    // backing store (the browser scales it) instead of reallocating it mid-scroll
    const sameWidth = Math.abs(r.width - cssW) < 1;
    if (sameWidth && cssH > 1 && Math.abs(r.height - cssH) / cssH < 0.2 && canvas.width > 1 && state.quality === lastQuality) return;
    lastQuality = state.quality;
    cssW = Math.max(1, r.width); cssH = Math.max(1, r.height);
    const dpr = Math.min(window.devicePixelRatio || 1, force ? 3 : opts.lowPower ? 1.5 : 1.75) * state.quality;   // ?rsforce: full resolution for captures
    const w = Math.max(1, Math.round(cssW * dpr)), h = Math.max(1, Math.round(cssH * dpr));
    if (canvas.width !== w || canvas.height !== h) { canvas.width = w; canvas.height = h; renderer.resize(w, h); }
    if (paused) drawOnce();
  };
  let lastQuality = state.quality;
  const ro = new ResizeObserver(resize);
  ro.observe(canvas);

  /* input: the pointer (or a finger) is a torch the procession bends toward */
  const onMove = (e) => {
    const r = canvas.getBoundingClientRect();
    const nx = ((e.clientX - r.left) / r.width) * 2 - 1, ny = 1 - ((e.clientY - r.top) / r.height) * 2;
    if (ny < -1.2 || ny > 1.2) { state.pointerNdc = null; return; }
    state.pointerNdc = [nx, ny];
    if (!coarse) state.parallaxTarget = [nx * 0.5, ny * 0.3];   // no camera nudge from taps on phones
  };
  addEventListener('pointermove', onMove, { passive: true });
  addEventListener('pointerdown', (e) => { state.pointerDown = true; onMove(e); }, { passive: true });
  const release = (e) => { state.pointerDown = false; if (coarse || e.pointerType === 'touch') state.pointerNdc = null; };
  addEventListener('pointerup', release, { passive: true });
  // a touch that turns into a scroll ends with pointercancel, never pointerup: drop the torch then too
  addEventListener('pointercancel', release, { passive: true });
  document.documentElement.addEventListener('pointerleave', () => { state.pointerNdc = null; });

  /* scroll = the hour */
  const readScroll = () => {
    if (mode !== 'full') return;
    const max = document.documentElement.scrollHeight - innerHeight;
    state.progressTarget = max > 0 ? Math.min(1, Math.max(0, scrollY / max)) : 0;
  };
  readScroll();
  state.progress = state.progressTarget;
  addEventListener('scroll', () => { readScroll(); if (paused) drawOnce(); }, { passive: true });

  /* visibility */
  let onScreen = true;
  const io = new IntersectionObserver((es) => { onScreen = es[0].isIntersecting; if (onScreen && !paused) kick(); });
  io.observe(canvas);
  document.addEventListener('visibilitychange', () => { if (!document.hidden && !paused) kick(); });

  /* the loop */
  // Slow-device guard, measured in time (not frames) so a very slow device is caught in seconds:
  // over ~1 s of frames slower than 38 fps -> lower the resolution, then halve the torches; at the
  // floor, ~2 s under 20 fps, or two frames in a row over 200 ms, -> stop and keep the still image.
  let raf = 0, last = 0, slowT = 0, crawlT = 0, awful = 0, warm = 0, stopped = false, floor = false;
  const frame = (now) => {
    raf = 0;
    const raw = last ? Math.min(1, (now - last) / 1000) : 1 / 60;
    const dt = Math.min(0.05, raw);
    last = now;
    step(dt);
    warm += raw;
    if (warm > 0.8 && !force) {                        // skip start-up hiccups; ?rsforce never adapts
      slowT = raw > 0.026 ? slowT + raw : Math.max(0, slowT - raw);
      crawlT = raw > 0.05 ? crawlT + raw : Math.max(0, crawlT - raw);
      awful = raw > 0.2 ? awful + 1 : 0;
      if (slowT > 1) {
        slowT = 0;
        if (state.quality > 0.55) { state.quality *= 0.8; resize(); }
        else if (!(renderer.reduce && renderer.reduce())) floor = true;
      }
      if (!force && (awful >= 2 || (floor && crawlT > 2))) { giveUp(); return; }
    }
    if (!paused && onScreen && !document.hidden) raf = requestAnimationFrame(frame);
  };
  const kick = () => { if (!raf && !stopped) { last = 0; warm = 0; raf = requestAnimationFrame(frame); } };

  function step(dt) {
    state.time += paused ? 0 : dt;
    const k = 1 - Math.exp(-dt * 3);
    state.parallax[0] += (state.parallaxTarget[0] - state.parallax[0]) * k;
    state.parallax[1] += (state.parallaxTarget[1] - state.parallax[1]) * k;
    if (reduced.matches) state.parallax = [0, 0];
    // the camera eases toward the scroll position: anchor jumps, flings and address-bar resizes
    // glide instead of snapping (time constant ~0.22 s); paused or reduced motion follows exactly
    if (paused || reduced.matches || dt === 0) state.progress = state.progressTarget;
    else state.progress += (state.progressTarget - state.progress) * (1 - Math.exp(-dt * 4.5));
    state.hour = fixedHour ?? state.progress;
    state.flare = Math.max(0, state.flare - dt * 0.25);
    state.focus[1] += (state.focusTarget - state.focus[1]) * Math.min(1, (dt || 0.016) * 5);
    const cam = camera(state, cssW, cssH, renderer.zeroToOne);
    let strength = 0;
    if (state.pointerNdc && !reduced.matches) {
      const hit = pickGround(cam, state.pointerNdc[0], state.pointerNdc[1], renderer.heights, renderer.gridN);
      if (hit) { state.pointer[0] = hit[0]; state.pointer[1] = hit[1]; state.pointer[2] = hit[2]; strength = state.pointerDown ? 1.6 : 1; }
    }
    state.pointer[3] += (strength - state.pointer[3]) * Math.min(1, dt * 6);
    renderer.render(cam, dt, paused);
    if (!root.classList.contains('is-live')) root.classList.add('is-live');
  }
  // one redraw per frame however many scroll or input events arrive while paused
  let pending = false;
  function drawOnce() {
    if (stopped || pending) return;
    pending = true;
    requestAnimationFrame(() => { pending = false; step(0); });
  }
  function giveUp() {
    cancelAnimationFrame(raf); raf = 0; paused = true; stopped = true;
    root.classList.remove('is-live');
    if (toggle) toggle.hidden = true;
  }
  // the page snapshot for a cross-page transition is taken after this: make sure it holds a fresh frame
  addEventListener('pageswap', () => { if (!stopped) step(0.0001); });

  const setPaused = (v, remember) => {
    paused = v;
    if (toggle) {
      toggle.setAttribute('aria-label', paused ? 'Play the scene' : 'Pause the scene');
      toggle.querySelector('use')?.setAttribute('href', toggle.dataset[paused ? 'iconPlay' : 'iconPause']);
      const label = toggle.querySelector('[data-label]');
      if (label) label.textContent = paused ? 'Play' : 'Pause';
    }
    if (remember) { try { localStorage.setItem('rs-scene-paused', paused ? '1' : '0'); } catch { /* ignore */ } }
    if (paused) drawOnce(); else kick();
  };
  toggle?.addEventListener('click', () => setPaused(!paused, true));
  setPaused(paused, false);

  // the case study's hour slider
  const hourInput = document.querySelector('[data-scene-hour]');
  if (hourInput) {
    const words = (h) => (h < 0.15 ? 'Dusk' : h < 0.4 ? 'Evening' : h < 0.6 ? 'Midnight' : h < 0.85 ? 'Before dawn' : 'First light');
    const sync = () => { fixedHour = Number(hourInput.value); hourInput.setAttribute('aria-valuetext', words(fixedHour)); if (paused) drawOnce(); };
    hourInput.addEventListener('input', sync);
    hourInput.closest('[data-hour-control]')?.removeAttribute('hidden');
    sync();
  }

  // the contact page lights the summit when a message is sent
  window.addEventListener('rs:flare', () => { state.flare = 1; if (paused) drawOnce(); else kick(); });
  if (document.querySelector('[data-flare]')) setTimeout(() => window.dispatchEvent(new Event('rs:flare')), 400);
  addEventListener('pagehide', () => { cancelAnimationFrame(raf); raf = 0; });
  addEventListener('pageshow', (e) => { if (e.persisted && !paused) kick(); });
  renderer.onLost?.(() => giveUp());
}

/* ---------- shared parameters for both renderers ---------- */

// colours in display (sRGB) space
export const PALETTE = {
  duskTop: [0.11, 0.13, 0.34], duskHorizon: [0.82, 0.40, 0.24],
  nightTop: [0.035, 0.045, 0.14], nightHorizon: [0.11, 0.12, 0.27],
  dawnTop: [0.43, 0.53, 0.74], dawnHorizon: [0.98, 0.87, 0.70],
  ember: [1.0, 0.43, 0.12], saffron: [1.0, 0.83, 0.40],
};

// torches walk the road; embers rise from the summit; bonfires burn on the far ranges (64 fires × 24 flames)
export function torchCounts(lowPower, backend) {
  if (backend === 'a') return lowPower ? { torches: 6144, embers: 2048, hill: 768 } : { torches: 14336, embers: 6144, hill: 1536 };
  return lowPower ? { torches: 4096, embers: 1024, hill: 768 } : { torches: 10240, embers: 3072, hill: 1536 };
}

export const GRID = (lowPower) => (lowPower ? 129 : 193);
export { TAU };

